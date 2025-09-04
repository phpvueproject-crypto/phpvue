<template>
    <toggle-button v-model.lazy="dataBool" :sync="true" @change="onChange" :disabled="disabled"/>
</template>

<script>
import {ToggleButton} from 'vue-js-toggle-button';

export default {
    name: "RotationCondition",
    components: {ToggleButton},
    props: {
        value: {
            type: String,
            default: 'head_forward_always'
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            dataBool: true
        };
    },
    created() {
        this.dataBool = this.value == 'head_forward_always';
    },
    methods: {
        onChange() {
            this.$emit('input', this.dataBool ? 'head_forward_always' : null);
        }
    }
}
</script>
