<template>
    <div :class="[color]">
        <select ref="select2" :id="selectId"
                :class="selectClass" :style="selectStyle" :data-placeholder="placeholder"
                :multiple="multiple" :disabled="disabled || loading">
            <option v-for="map in maps" :value="map[valueColumn]">
                {{ map[optionColumn] }}
            </option>
        </select>
    </div>
</template>

<script>
import $ from 'jquery';
import _ from 'lodash';
import axios from 'axios';

export default {
    name: "MapListSelect",
    props: {
        value: {
            type: [String, Array]
        },
        valueColumn: {
            type: String,
            default: 'guid'
        },
        optionColumn: {
            type: String,
            default: 'name'
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
        selectId: String,
        selectClass: String,
        selectStyle: String,
        selectWidth: {type: String, default: 'auto'},
        allowClear: {type: Boolean, default: false}
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
        async items(newVal) {
            if(newVal === null) {
                await this.fetchData();
            } else {
                this.maps = newVal;
            }
            this.setSelect2Val();
            this.init();
        }
    },
    data() {
        return {
            loading: false,
            maps: []
        };
    },
    async created() {
        if(this.items === null) {
            await this.fetchData();
        } else {
            this.maps = this.items;
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
                allowClear: this.allowClear,
                closeOnSelect: !this.multiple,
                dropdownAutoWidth: true,
                language: {noResults: () => "查無選項"},
                minimumResultsForSearch: -1,
                width: this.selectWidth
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
                let res = await axios.get('/api/maps', {
                    params: {
                        pagination: 0
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.maps = data.maps;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
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
            } else if(arr.length > 0) {
                this.$emit('input', arr[0].id);
            } else {
                this.$emit('input', null);
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
