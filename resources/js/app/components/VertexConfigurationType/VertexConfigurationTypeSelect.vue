<template>
    <div :class="[color]">
        <select ref="select2"
                class="select2 form-control"
                :data-placeholder="placeholder"
                :disabled="disabled">
            <option :value="null">{{ placeholder }}</option>
            <option v-for="vertexConfigurationType in filterVertexConfigurationTypes" :value="vertexConfigurationType.vertex_configuration_column.name">
                {{ vertexConfigurationType.vertex_configuration_column.name }}
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

export default {
    name: "VertexConfigurationTypeSelect",
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
            default: '請選擇自訂屬性'
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        tags: {
            type: Boolean,
            default: false
        },
        filterVertexConfigurationTypes: {
            type: Array,
            default() {
                return [];
            }
        },
        vertexConfigurationTypes: {
            type: Array,
            default() {
                return [];
            }
        },
        vertexConfigurationType: {
            type: Object
        },
        vertexConfigurations: {
            type: Array,
            default() {
                return [];
            }
        }
    },
    watch: {
        value() {
            this.refresh();
        },
        vertexConfigurationTypes(newVal) {
            const that = this;
            const vertexConfigurationType = _.find(newVal, (r) => {
                return r.vertex_configuration_column.name == that.value;
            });
            if(vertexConfigurationType) {
                this.$emit('update:vertexConfigurationType', vertexConfigurationType);
            } else {
                this.$emit('update:vertexConfigurationType', null);
            }
            this.refresh();
        }
    },
    computed: {
        showValue() {
            const that = this;
            const vertexConfigurationType = _.find(this.filterVertexConfigurationTypes, (r) => {
                return r.vertex_configuration_column.name == that.value;
            });
            return !vertexConfigurationType;
        }
    },
    mounted() {
        this.init();
        this.setSelect2Val();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                tags: this.tags,
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
                let uniqueVertexConfigurationType = _.find(this.vertexConfigurationTypes, (r) => {
                    return r.vertex_configuration_column && r.vertex_configuration_column.is_unique == 1 && r.vertex_configuration_column.name == arr[0].id;
                });
                let isUnique = !!uniqueVertexConfigurationType;

                if(isUnique) {
                    const vertexConfiguration = _.filter(this.vertexConfigurations, (r) => {
                        return r.type == arr[0].id;
                    });
                    if(vertexConfiguration.length > 1) {
                        this.$toast.warn({
                            title: '警告訊息',
                            message: `${arr[0].id}已存在，該欄位不能重複！`
                        });
                        this.setSelect2Val();
                        return;
                    }
                }

                this.$emit('input', arr[0].id);
                const vertexConfigurationType = _.find(this.vertexConfigurationTypes, (r) => {
                    return r.vertex_configuration_column.name == arr[0].id;
                });
                if(vertexConfigurationType) {
                    this.$emit('update:vertexConfigurationType', vertexConfigurationType);
                } else {
                    this.$emit('update:vertexConfigurationType', null);
                }
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
