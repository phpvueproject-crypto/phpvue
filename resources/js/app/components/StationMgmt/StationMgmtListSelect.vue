<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "StationMgmtListSelect",
    props: {
        value: {
            type: [String, Array]
        },
        valueProp: {
            type: String,
            default: 'station_id'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        color: {
            type: String,
            default: 'theme-blue'
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
            type: Array,
            default: null
        },
        stationGroup: {
            type: String
        },
        vertexId: {
            type: Number
        },
        vertexName: {
            type: String
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple) {
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r;
                }));
            } else {
                $(this.$refs.select2).select2('data', [newVal]);
            }
            const that = this;
            this.$nextTick(() => {
                that.refresh();
            });
        },
        items(newVal) {
            this.stationMgmts = newVal;
            this.refresh();
        }
    },
    data() {
        return {
            stationMgmts: []
        };
    },
    async created() {
        if(this.items) {
            this.stationMgmts = this.items;
        } else {
            await this.fetchData();
        }
        const that = this;
        this.$nextTick(() => {
            that.init();
            that.setSelect2Val();
        });
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/stationMgmts', {
                    params: {
                        station_group: this.stationGroup
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.stationMgmts = data.stationMgmts;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: 1,
                allowClear: true,
                language: {
                    noResults: function() {
                        return '查無資料';
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
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                if(that.multiple) {
                    $(that.$refs.select2).val(_.map(that.value, (r) => {
                        return r;
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
                    if(that.valueProp == 'id') {
                        return parseInt(r.id);
                    } else {
                        return r.id;
                    }
                }));
            } else if(arr.length > 0) {
                if(this.valueProp == 'id') {
                    this.$emit('input', parseInt(arr[0].id));
                } else {
                    this.$emit('input', arr[0].id);

                    const vertex = this.getVertex(arr[0].id);
                    this.$emit('update:vertexName', vertex.name);
                    this.$emit('update:vertexId', vertex.id);
                }
            } else {
                this.$emit('input', null);
                this.$emit('update:vertexName', null);
                this.$emit('update:vertexId', null);
            }
            this.$emit('change');
        },
        refresh() {
            $(this.$refs.select2).val(null).trigger('change');
            $(this.$refs.select2).select2('destroy');
            this.init();
            this.setSelect2Val();
        },
        getVertex(key) {
            const that = this;
            const stationMgmt = _.find(this.stationMgmts, (r) => {
                return r[that.valueProp] == key;
            });
            const vertex = stationMgmt.vertex;
            return vertex ? vertex : null;
        }
    }
}
</script>

<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                style="width: 100%"
                :multiple="multiple"
                :data-placeholder="placeholder"
                :disabled="disabled">
            <option v-for="stationMgmt in stationMgmts" :value="stationMgmt[valueProp]">
                {{ stationMgmt.station_id }}
            </option>
        </select>
    </div>
</template>

<style scoped>

</style>