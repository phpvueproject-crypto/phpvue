<template>
    <div :class="[color]">
        <select ref="select2"
                class="form-control select2"
                :multiple="multiple"
                :data-placeholder="placeholder"
                style="width: 100%;"
                :disabled="disabled">
            <option v-for="row in parkingLotMgmts" :value="row.parking_lot_id">
                {{ row.parking_lot_id }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "ParkingLotMgmtListSelect",
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
            default: '請選擇'
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
        isFilterHasSpaceLocation: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple)
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r.vehicle_id;
                }));
            else
                $(this.$refs.select2).select2('data', [newVal]);
            this.refresh();
        }
    },
    data() {
        return {
            parkingLotMgmts: []
        };
    },
    async created() {
        if(this.items === null) {
            await this.fetchData();
        } else {
            this.parkingLotMgmts = this.items;
        }
        if(!this.multiple && this.value) {
            this.$emit('update:selected', _.find(this.parkingLotMgmts, (r) => {
                return r.parking_lot_id == this.value;
            }));
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
                minimumResultsForSearch: 1,
                allowClear: true,
                closeOnSelect: !that.multiple
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
                let res = await axios.get('/api/parkingLotMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    if(this.isFilterHasSpaceLocation) {
                        this.parkingLotMgmts = _.filter(data.parkingLotMgmts, (r) => {
                            return r.parking_space_location == null || r.parking_lot_id == this.value;
                        });
                    } else {
                        this.parkingLotMgmts = data.parkingLotMgmts;
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
                        return r;
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
                    return r.id;
                }));
            else if(arr.length > 0) {
                this.$emit('input', arr[0].id);
                this.$emit('update:selected', _.find(this.parkingLotMgmts, (r) => {
                    return r.parking_lot_id == arr[0].id;
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
