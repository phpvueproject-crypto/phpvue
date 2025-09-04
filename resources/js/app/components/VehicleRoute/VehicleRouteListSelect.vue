<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                :multiple="multiple"
                :data-placeholder="placeholder"
                style="width: 100%;"
                :disabled="disabled">
            <optgroup v-for="(routeName, idx) in routeNames" :label="idx">
                <option v-for="vehicleRoute in routeName" :value="vehicleRoute[valueProp]">
                    {{ vehicleRoute.region_id }}
                </option>
            </optgroup>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "VehicleRouteListSelect",
    props: {
        value: {
            type: [Number, String]
        },
        valueProp: {
            type: String,
            default: 'id'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default() {
                return '請選擇';
            }
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
        filter: {
            type: Array,
            default: () => []
        },
        isAutoLoad: {
            type: Boolean,
            default: true
        },
        routeName: {
            type: String,
            default: null
        },
        backgroundFileUrl: {
            type: String
        },
        group: {
            type: Number,
            default: null
        },
        filterSameRoutName: {
            type: Boolean,
            default: true
        }
    },
    computed: {
        filterVehicleRoutes() {
            const that = this;
            return _.filter(this.vehicleRoutes, (r) => {
                return !that.filter.contains(r.route_name);
            });
        },
        routeNames() {
            return _.groupBy(this.filterVehicleRoutes, 'route_name');
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple) {
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r.route_name;
                }));
            } else {
                $(this.$refs.select2).select2('data', [newVal]);
            }
            this.refresh();
        },
        filterVehicleRoutes() {
            this.refresh();
        }
    },
    data() {
        return {
            vehicleRoutes: []
        };
    },
    async mounted() {
        const that = this;
        if(this.isAutoLoad) {
            await this.fetchData();
        }
        if(this.filterSameRoutName) {
            const vehicleRoute = _.find(this.vehicleRoutes, (r) => {
                return r[that.valueProp] == that.value;
            });
            if(vehicleRoute) {
                this.$emit('update:routeName', vehicleRoute.route_name);
                this.vehicleRoutes = _.filter(this.vehicleRoutes, (r) => {
                    return r.route_name == vehicleRoute.route_name;
                });
            }
        }
        this.refresh();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: 5,
                allowClear: that.allowClear,
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
        async fetchData(routeName) {
            try {
                let res = await axios.get('/api/vehicleRoutes', {
                    params: {
                        route_name: routeName,
                        group: this.group
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.vehicleRoutes = data.vehicleRoutes;
                    this.refresh();
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
                        return r.route_name;
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
                        route_name: parseInt(r.id)
                    };
                }));
            } else {
                if(arr.length != 0) {
                    if(that.valueProp == 'id') {
                        this.$emit('input', parseInt(arr[0].id));
                    } else {
                        this.$emit('input', arr[0].id);
                    }
                    const vehicleRoute = _.find(this.vehicleRoutes, (r) => {
                        return r[that.valueProp] == arr[0].id;
                    });
                    this.$emit('update:backgroundFileUrl', vehicleRoute.background_file_url);
                } else {
                    this.$emit('input', null);
                    this.$emit('update:backgroundFileUrl', null);
                }
            }
        },
        refresh() {
            this.init();
            this.setSelect2Val();
        }
    }
}
</script>
