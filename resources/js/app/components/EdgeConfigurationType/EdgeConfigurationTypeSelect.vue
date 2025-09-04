<template>
    <div :class="[color]">
        <select ref="select2"
                class="select2 form-control"
                :data-placeholder="placeholder"
                :disabled="disabled || loading">
            <option :value="null">{{ placeholder }}</option>
            <option v-for="edgeConfigurationType in filterEdgeConfigurationTypes" :value="edgeConfigurationType.name">
                {{ edgeConfigurationType.name }}
            </option>
            <option v-if="value && showValue" :value="value">
                {{ value }}
            </option>
        </select>
    </div>
</template>

<script>
import $ from 'jquery';
import _ from 'lodash';
import axios from 'axios';

export default {
    name: "EdgeConfigurationTypeSelect",
    props: {
        value: {
            type: String
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default() {
                return '請選擇自訂屬性';
            }
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        tags: {
            type: Boolean,
            default: false
        },
        edgeConfigurationTypes: {
            type: Array,
            default() {
                return [];
            }
        },
        filterEdgeConfigurationTypes: {
            type: Array,
            default() {
                return [];
            }
        },
        edgeConfigurationType: {
            type: Object
        }
    },
    watch: {
        value() {
            this.refresh();
        },
        edgeConfigurationTypes() {
            const that = this;
            const edgeConfigurationType = _.find(this.edgeConfigurationTypes, (r) => {
                return r.name == that.value;
            });
            if(edgeConfigurationType) {
                this.$emit('update:edgeConfigurationType', edgeConfigurationType);
            } else {
                this.$emit('update:edgeConfigurationType', null);
            }
            this.refresh();
        }
    },
    computed: {
        showValue() {
            const that = this;
            const edgeConfigurationType = _.find(this.types, (r) => {
                return r.name == that.value;
            });
            return !edgeConfigurationType;
        }
    },
    data() {
        return {
            loading: true
        };
    },
    async mounted() {
        if(this.filterEdgeConfigurationTypes.length == 0) {
            this.loading = true;
            await this.fetchData();
        }
        this.loading = false;
        this.init();
        this.setSelect2Val();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                tags: true,
                minimumResultsForSearch: 0,
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
        async fetchData() {
            try {
                let res = await axios.get('/api/edgeConfigurationTypes');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.$emit('update:edgeConfigurationTypes', data.edgeConfigurationTypes);
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                $(that.$refs.select2).val([that.value]);
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            if(arr.length == 0) {
                this.$emit('input', null);
            } else {
                this.$emit('input', arr[0].id);
                const edgeConfigurationType = _.find(this.edgeConfigurationTypes, (r) => {
                    return r.name == arr[0].id;
                });
                if(edgeConfigurationType) {
                    this.$emit('update:edgeConfigurationType', edgeConfigurationType);
                } else {
                    this.$emit('update:edgeConfigurationType', null);
                }
            }
            this.$emit('change');
        },
        refresh() {
            $(this.$refs.select2).val(null).trigger('change');
            this.init();
            this.setSelect2Val();
        }
    }
}
</script>
