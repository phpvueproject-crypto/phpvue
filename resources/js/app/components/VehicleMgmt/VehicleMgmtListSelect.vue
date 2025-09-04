<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                :multiple="multiple"
                :data-placeholder="placeholder"
                style="width: 100%;"
                :disabled="disabled">
            <option v-for="vehicle in vehicleMgmts" :value="vehicle.vehicle_id">
                {{ vehicle.vehicle_id }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "VehicleMgmtListSelect",
    props: {
        value: {
            type: [Array, String]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: '請選擇AMDR車輛'
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        items: {
            type: Array,
            default() {
                return null;
            }
        },
        regionMgmtId: {
            type: Number,
            default: null
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple) {
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r.vehicle_id;
                }));
            } else {
                $(this.$refs.select2).select2('data', [newVal]);
            }
            this.refresh();
        }
    },
    data() {
        return {
            vehicleMgmts: []
        };
    },
    async created() {
        if(this.items === null) {
            await this.fetchData();
        } else {
            this.vehicleMgmts = this.items;
        }
        this.setSelect2Val();
    },
    mounted() {
        this.init();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: -1,
                allowClear: true,
                closeOnSelect: !this.multiple
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
            try {
                let res = await axios.get('/api/vehicleMgmts', {
                    params: {
                        region_mgmt_id: this.regionMgmtId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.vehicleMgmts = data.vehicleMgmts;
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
                        return r.vehicle_id;
                    }));
                } else {
                    $(that.$refs.select2).val([that.value]);
                }
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            if(this.multiple)
                this.$emit('input', _.map(arr, (r) => {
                    return {
                        vehicle_id: r.id
                    };
                }));
            else if(arr.length > 0) {
                this.$emit('input', arr[0].id);
                this.$emit('update:selected', _.find(this.vehicleMgmts, (r) => {
                    return r.vehicle_id == arr[0].id;
                }));
            } else {
                this.$emit('input', null);
                this.$emit('update:selected', null);
            }
            this.$emit('change');
        },
        refresh() {
            $(this.$refs.select2).val(null).trigger('change');
            $(this.$refs.select2).select2('destroy');
            this.init();
            this.setSelect2Val();
        }
    }
}
</script>
