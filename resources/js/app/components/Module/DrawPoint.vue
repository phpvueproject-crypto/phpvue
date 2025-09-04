<!--suppress HtmlUnknownTag -->
<template>
    <div v-if="vertex">
        <div :style="`position: absolute; top: ${vertex.y_px}px; left: ${vertex.x_px}px; width: 15px; height: 15px; background: #FFFF88; border-radius: 50%; border: 3px #545c51 solid;`"/>
    </div>
</template>

<script>
import _ from 'lodash';

export default {
    name: "DrawPoint",
    props: {
        value: {
            type: Object,
            default() {
                return null;
            }
        },
        scale: {
            type: Number,
            default: 1
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        value(newVal) {
            this.vertex = newVal;
        }
    },
    computed: {},
    data() {
        return {
            vertex: this.value
        };
    },
    created() {
        if(this.value) {
            this.vertex = _.cloneDeep(this.value);
        } else {
            this.vertex = {
                id: null,
                x_px: 0,
                y_px: 0
            };
        }
    },
    methods: {}
}
</script>

<style lang="scss" scoped>
.animation-fade{
    animation: fade 1500ms infinite;
}
@keyframes fade{
    from{
        opacity: 1.0;
    }
    50%{
        opacity: 0.5;
    }
    to{
        opacity: 1.0;
    }
}
</style>
