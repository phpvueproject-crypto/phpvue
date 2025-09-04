<template>
    <div :class="[color]">
        <select ref="select2"
                class="select2 form-control"
                :data-placeholder="placeholder"
                :disabled="disabled">
            <option v-for="type in filterTypes" :value="type">
                {{ type }}
            </option>
            <option v-if="customType" :value="customType">
                {{ customType }}
            </option>
        </select>
    </div>
</template>

<script>
import $ from 'jquery';
import _ from 'lodash';

export default {
    name: "EdgeConfigurationTypeSelect",
    components: {},
    computed: {
        filterTypes() {
            return this.types;
        }
    },
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
        customItem: {
            type: String,
            default: null
        }
    },
    watch: {
        value() {
            this.refresh();
        },
        filterTypes() {
            this.refresh();
        }
    },
    data() {
        return {
            types: ['rail_switch', 'weight'],
            customType: null
        };
    },
    mounted() {
        const isOrigin = _.find(this.types, (r) => {
            return r == this.customItem
        });
        if(!isOrigin)
            this.customType = this.customItem;

        this.init();
        this.setSelect2Val();
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                tags: that.tags,
                minimumResultsForSearch: 5
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
                this.$emit('input', arr[0].id);
                this.$emit('change');
            }
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
