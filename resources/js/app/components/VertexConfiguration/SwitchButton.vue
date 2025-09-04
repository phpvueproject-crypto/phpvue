<template>
    <toggle-button v-model.lazy="dataBool" :sync="true" :labels="labels" :width="width" :height="height" :font-size="fontSize" @change="onChange" :disabled="disabled"/>
</template>

<script>
import {ToggleButton} from 'vue-js-toggle-button';

export default {
    name: "SwitchButton",
    components: {ToggleButton},
    props: {
        value: {
            type: String,
            default: 'on'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        labels: {
            type: [Boolean, Object],
            default: false
        },
        width: {
            type: Number,
            default: 50
        },
        height: {
            type: Number,
            default: 22
        },
        fontSize: {
            type: Number
        }
    },
    watch: {
        value(newVal) {
            this.dataBool = newVal == 'on';
        }
    },
    data() {
        return {
            dataBool: true
        };
    },
    created() {
        if(!this.value) {
            this.$emit('input', 'on');
        } else {
            if(this.value == 'on') {
                this.dataBool = true;
            } else {
                this.dataBool = false;
            }
        }
    },
    methods: {
        onChange(event) {
            this.$emit('input', this.dataBool ? 'on' : 'off');
            this.$emit('change', event);
        }
    }
}
</script>
