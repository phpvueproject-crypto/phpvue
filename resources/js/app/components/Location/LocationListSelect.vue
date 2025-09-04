<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                :multiple="multiple"
                :data-placeholder="placeholder"
                :style="selectStyle"
                :disabled="disabled">
            <option v-for="location in filterLocations" :value="location[valueProp]">
                {{ location.device_name }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "LocationListSelect",
    props: {
        value: {
            type: [Array, Number, String]
        },
        valueProp: {
            type: String,
            default: 'id'
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        minimumResultsForSearch: {
            type: Number,
            default: 5
        },
        placeholder: {
            type: String,
            default: '請選擇'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        items: {
            type: Array
        },
        filterIds: {
            type: Array,
            default: () => []
        },
        regionMgmtId: {
            type: Number,
            default: null
        },
        vertexName: {
            type: String
        },
        vertexNames: {
            type: Array
        },
        selectStyle: String,
        requireRegionMgmtId: Boolean
    },
    computed: {
        filterLocations() {
            const that = this;
            return _.filter(this.locations, (r) => {
                return !that.filterIds.contains(r.id);
            });
        }
    },
    watch: {
        value(newVal) {
            const that = this;
            if(this.multiple) {
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r[that.valueProp];
                }));
                this.$emit('update:vertexNames', this.getVertexName());
            } else {
                $(this.$refs.select2).select2('data', [newVal]);
                this.$emit('update:vertexName', this.getVertexName());
            }
        },
        items(newVal) {
            this.locations = newVal;
            if(newVal.length <= 0) {
                this.$emit('input', null);
            }
            this.setSelect2Val();
            this.init();
        },
        async regionMgmtId() {
            if(!this.items) {
                await this.fetchData();
                this.setSelect2Val();
                this.init();
            }
        }
    },
    data() {
        return {
            locations: []
        };
    },
    async mounted() {
        if(this.items) {
            this.locations = this.items;
        } else {
            await this.fetchData();
        }
        this.init();
        this.setSelect2Val();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: that.minimumResultsForSearch,
                language: {noResults: () => "查無選項"}
            });
            $el.on("select2:select", (e) => {
                that.setVal();
            });
            $el.on("select2:unselect", (e) => {
                that.setVal();
            });
        },
        async fetchData() {
            this.loading = true;
            if(this.requireRegionMgmtId && !this.regionMgmtId) {
                return;
            }
            try {
                let res = await axios.get('/api/locations', {
                    params: {
                        region_mgmt_id: this.regionMgmtId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.locations = data.locations;
                    if(this.value) {
                        const that = this;
                        const idx = _.findIndex(this.locations, (r) => {
                            return r.id == that.value;
                        });
                        if(idx == -1) {
                            this.$emit('input', null);
                        }
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                if(that.multiple) {
                    $(that.$refs.select2).val(_.map(that.value, (r) => {
                        return r[that.valueProp];
                    }));
                } else {
                    $(that.$refs.select2).val([that.value]);
                }
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const that = this;
            const arr = $(this.$refs.select2).select2('data');
            if(this.multiple) {
                this.$emit('input', _.map(arr, (r) => {
                    return {
                        [that.valueProp]: r.id
                    };
                }));
            } else if(arr.length > 0) {
                this.$emit('input', arr[0].id);
            } else {
                this.$emit('input', null);
            }
            this.$emit('change');
        },
        getVertexName() {
            const that = this;
            let vertexNames = [];
            let vertexName = null;
            if(this.multiple) {
                _.forEach(this.value, (r1) => {
                    const location = _.find(this.locations, (r2) => {
                        return r2[that.valueProp] == r1[that.valueProp];
                    });
                    if(location) {
                        vertexNames.push(location.vertex_name);
                    }
                });
                return vertexNames;
            } else {
                const location = _.find(this.locations, (r) => {
                    return r[that.valueProp] == that.value;
                });
                if(location) {
                    vertexName = location.vertex_name;
                }
                return vertexName;
            }
        }
    }
}
</script>
