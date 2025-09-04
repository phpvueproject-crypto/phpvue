<template>
    <div :class="[color]">
        <select ref="select2" :id="id"
                :class="selectClass" :style="selectStyle" :data-placeholder="placeholder"
                :multiple="multiple" :disabled="disabled">
            <option v-for="roomEnvironment in roomEnvironments" :value="roomEnvironment[valueColumn]">
                {{ roomEnvironment[optionColumn] }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import $ from 'jquery';

export default {
    name: "RoomEnvironmentSelect",
    props: {
        value: {
            type: [String, Array],
            default: null
        },
        valueColumn: {
            type: String,
            default: 'room_name'
        },
        optionColumn: {
            type: String,
            default: 'room_name'
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
            default: null
        },
        id: String,
        selectClass: String,
        selectStyle: String,
        regionMgmtId: {
            type: Number,
            default: null
        },
        locations: {
            type: Array,
            default() {
                return [];
            }
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
            this.refresh();
        },
        items(newVal) {
            this.roomEnvironments = newVal;
            this.setSelect2Val();
            this.init();

            if(this.regionMgmtId) {
                this.getLocationsByRegionMgmtId(this.regionMgmtId);
            }
        },
        regionMgmtId(newVal) {
            if(newVal) {
                this.getLocationsByRegionMgmtId(newVal);
            }
        }
    },
    data() {
        return {
            roomEnvironments: []
        }
    },
    async created() {
        if(this.items === null) {
            await this.fetchData();
        } else {
            this.roomEnvironments = this.items;
        }
        this.setSelect2Val();
        if(this.value) {
            this.getLocationsByValue(this.value);
        }
        if(this.regionMgmtId) {
            this.getLocationsByRegionMgmtId(this.regionMgmtId);
        }
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
                closeOnSelect: !this.multiple,
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
            try {
                let res = await axios.get('/api/roomEnvironments');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.roomEnvironments = data.roomEnvironments;
                    this.$emit('update:roomEnvironments', this.roomEnvironments);
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
            if(this.multiple) {
                this.$emit('input', _.map(arr, (r) => {
                    return r.id;
                }));
            } else {
                if((arr.length > 0)) {
                    this.$emit('input', arr[0].id);
                    this.getLocationsByValue(arr[0].id);
                } else {
                    this.$emit('input', null);
                    this.getLocationsByValue(null);
                }
            }
            this.$emit('change');
        },
        refresh() {
            $(this.$refs.select2).val(null).trigger('change');
            $(this.$refs.select2).select2('destroy');
            this.init();
            this.setSelect2Val();
        },
        getLocationsByValue(value) {
            const roomEnvironment = _.find(this.roomEnvironments, [this.valueColumn, value]);
            if(roomEnvironment) {
                this.$emit('update:regionMgmtId', roomEnvironment.region_mgmt_id);
                this.$emit('update:locations', roomEnvironment.locations);
            } else {
                this.$emit('update:regionMgmtId', null);
                this.$emit('update:locations', []);
            }
        },
        getLocationsByRegionMgmtId(regionMgmtId) {
            const roomEnvironment = _.find(this.roomEnvironments, (r) => {
                return r.region_mgmt_id == regionMgmtId;
            });
            if(roomEnvironment) {
                this.$emit('update:locations', roomEnvironment.locations);
            } else {
                this.$emit('update:locations', []);
            }

            const that = this;
            if(!that.multiple) {
                this.$nextTick(() => {
                    $(that.$refs.select2).val([roomEnvironment ? roomEnvironment[this.valueColumn] : null]);
                    $(that.$refs.select2).trigger('change');
                    that.init();
                });
            }
        }
    }
}
</script>
