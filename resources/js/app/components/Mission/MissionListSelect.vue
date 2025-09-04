<template>
    <div :class="[color]">
        <select ref="select2"
                class="select2"
                :class="select2Class"
                :multiple="multiple"
                :data-placeholder="placeholder"
                style="width: 100%"
                :disabled="disabled">
            <option v-for="row in missions" :value="row.guid">
                {{ row.name }}
            </option>
            <option v-if="value && showValue" :value="value">
                {{ value }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';

export default {
    name: "MissionListSelect",
    props: {
        value: {
            type: [Array, Number, String]
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
                return [];
            }
        },
        select2Class: {
            type: String,
            default: null
        },
        isDeploy: {
            type: Number,
            default: null
        }
    },
    computed: {
        showValue() {
            const that = this;
            const edge = _.find(this.missions, (r) => {
                return r.name == that.value;
            });
            return !edge;
        }
    },
    watch: {
        value(newVal) {
            if(this.multiple) {
                $(this.$refs.select2).select2('data', _.map(newVal, (r) => {
                    return r.name;
                }));
            } else {
                $(this.$refs.select2).select2('data', [newVal]);
            }
        }
    },
    data() {
        return {
            missions: []
        };
    },
    async created() {
        if(this.items.length == 0) {
            await this.fetchData();
        } else {
            this.missions = this.items;
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
                closeOnSelect: !that.multiple,
                language: {
                    noResults: function() {
                        return '查無資料';
                    }
                },
                ajax: {
                    delay: 250, // 延遲
                    transport: async function(params, success, failure) {
                        await that.fetchData(params.data.term);
                        success(that.missions)
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.guid,
                                text: item.name // 根據你的模型調整字段
                            }))
                        };
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
        async fetchData(name = null) {
            this.loading = true;
            try {
                let res = await axios.get('/api/missions', {
                    params: {
                        name: name
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.missions = data.missions;
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
                    return r;
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
