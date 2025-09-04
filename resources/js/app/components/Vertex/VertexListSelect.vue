<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                style="width: 100%"
                :multiple="multiple"
                :data-placeholder="placeholder"
                :disabled="disabled || loading">
            <option v-for="vertex in filterVertices" :value="vertex[valueColumn]">
                {{ vertex.name ? vertex.name : '此站點尚未設定站點名稱' }}
                <template v-if="showDeviceName">（{{ vertex.device_name }}）</template>
            </option>
            <option v-if="value && showValue && !multiple" :value="value">
                {{ vertexName }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "VertexListSelect",
    props: {
        value: {
            type: [Number, String, Object, Array]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: '請選擇'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        allowClear: {
            type: Boolean,
            default: false
        },
        regionMgmtId: [Number, String],
        filterValueColumns: {
            type: Array,
            default: () => []
        },
        valueColumn: {
            type: String,
            default: 'id'
        },
        showDeviceName: {
            type: Boolean,
            default: false
        },
        vertexTypeId: {
            type: Number
        },
        isDeploy: {
            type: Number,
            default: null
        },
        items: {
            type: Array,
            default() {
                return [];
            }
        }
    },
    computed: {
        filterVertices() {
            const that = this;
            return _.filter(this.vertices, (r) => {
                return !that.filterValueColumns.contains(r[that.valueColumn]);
            });
        },
        showValue() {
            const that = this;
            const vertex = _.find(this.vertices, (r) => {
                return r[that.valueColumn] == that.value;
            });
            return !vertex;
        },
        vertexName() {
            const that = this;
            const vertex = _.find(this.vertices, (r) => {
                return r[that.valueColumn] == that.value;
            });
            if(vertex) {
                return vertex.name;
            } else {
                return this.value;
            }
        }
    },
    watch: {
        value(newVal) {
            const that = this;
            if(this.multiple) {
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r[that.valueColumn];
                }));
            } else {
                $(this.$refs.select2).select2('data', [newVal]);
            }
            const vertex = _.find(this.vertices, (r) => {
                return r[that.valueColumn] == newVal;
            });
            if(vertex) {
                this.$emit('update:vertexName', vertex.name);
            } else {
                this.$emit('update:vertexName', null);
            }
            this.init();
            this.setSelect2Val();
        },
        async regionMgmtId() {
            if(this.items.length > 0) {
                this.vertices = this.items;
            } else {
                this.loading = true;
                await this.fetchData();
                this.loading = false;
            }
            this.init();
            this.setSelect2Val();
        },
        filterVertices() {
            this.init();
            this.setSelect2Val();
        },
        items(newVal) {
            this.vertices = newVal;
        }
    },
    data() {
        return {
            vertices: [],
            loading: false
        };
    },
    async mounted() {
        if(this.items.length > 0) {
            this.vertices = this.items;
        } else {
            this.loading = true;
            await this.fetchData();
            this.loading = false;
        }
        this.init();
        this.setSelect2Val();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: 1,
                allowClear: that.allowClear,
                language: {
                    noResults: function() {
                        return '查無選項';
                    }
                }
            });
            $el.on("select2:select", (e) => {
                that.setVal();
            });
            $el.on("select2:unselect", (e) => {
                that.setVal();
            });
        },
        async fetchData() {
            try {
                let res = await axios.get('/api/vertices', {
                    params: {
                        region_mgmt_id: this.regionMgmtId,
                        vertex_type_id: this.vertexTypeId,
                        is_deploy: this.isDeploy
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.vertices = _.map(data.vertices, (r) => {
                        const deviceName = _.find(r.vertex_configurations, (r2) => {
                            return r2.type == 'device_name';
                        });
                        r.device_name = deviceName ? deviceName.data : null;
                        return r;
                    });
                    const that = this;
                    const vertex = _.find(this.vertices, (r) => {
                        return r[that.valueColumn] == that.value;
                    });
                    if(vertex) {
                        this.$emit('update:vertexName', vertex.name);
                    } else {
                        this.$emit('update:vertexName', null);
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                if(that.multiple) {
                    $(that.$refs.select2).val(_.map(that.value, (r) => {
                        return r[that.valueColumn];
                    }));
                } else {
                    $(that.$refs.select2).val([that.value]);
                }
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            const that = this;
            if(this.multiple) {
                this.$emit('input', _.map(arr, (r) => {
                    if(that.valueColumn == 'id') {
                        return {id: parseInt(r.id)};
                    } else {
                        return {id: r.id};
                    }
                }));
            } else {
                if(arr.length != 0) {
                    if(this.valueColumn == 'id') {
                        this.$emit('input', parseInt(arr[0].id));
                    } else {
                        this.$emit('input', arr[0].id);

                        const deviceName = this.getDeviceName(arr[0].id);
                        this.$emit('update:deviceName', deviceName);
                    }
                } else {
                    this.$emit('input', null);
                    this.$emit('update:deviceName', null);
                }
            }
        },
        getDeviceName(primaryKey) {
            const that = this;
            const vertex = _.find(this.vertices, (r) => {
                return r[that.valueColumn] == primaryKey;
            });
            const vertexConfiguration = _.find(vertex.vertex_configurations, (r) => {
                return r.type == 'device_name';
            });
            return vertexConfiguration ? vertexConfiguration.data : null;
        }
    }
}
</script>
