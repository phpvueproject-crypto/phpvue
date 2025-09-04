<script>
import _ from 'lodash';
import axios from 'axios';

export default {
    name: "PollutionConditionColorInput",
    props: {
        value: {type: Object, default: () => ({})},
        disabled: {type: Boolean, default: false}
    },
    computed: {
        colorValue: {
            get() {
                if(!this.value || !this.value.color) {
                    return '#ffffff';
                }
                return this.value.color;
            },
            set(newVal) {
                this.$set(this.form, 'color', newVal);
                this.$emit('input', this.form);
            }
        }
    },
    watch: {
        value(newVal) {
            this.form = newVal;
        }
    },
    data() {
        return {
            sending: false,
            form: {}
        }
    },
    mounted() {
        this.form = _.cloneDeep(this.value);
    }
}
</script>

<template>
    <input type="color" class="pollute-color-selector"
           v-model.lazy="colorValue" :disabled="disabled || sending"/>
</template>

<style scoped>
.pollute-color-selector{
    width:  29px;
    height: 27px;
}
input[type="color"]{
    -webkit-appearance: none;
    border:             none;
}
input[type="color"]::-webkit-color-swatch-wrapper{
    padding: 0;
}
input[type="color"]::-webkit-color-swatch{
    border: none;
}
</style>