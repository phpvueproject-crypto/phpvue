<!--suppress HtmlUnknownTag -->
<template>
    <div v-if="edge">
        <div class="edge-line"
             :style="`position: absolute; top: ${edge.yMid}px; left: ${edge.xMid - edge.length / 2}px; transform: rotate(${edge.degrees}deg); width: ${edge.length}px;`"
             :class="{'edge-line-disabled' : disabled}">
        </div>
    </div>
</template>

<script>
import _ from 'lodash';

let singleClickTimer = null;
export default {
    name: "DrawLine",
    props: {
        value: {
            type: Object,
            default() {
                return null;
            }
        },
        scale: {
            type: Number
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        value() {
            this.resetForm();
        }
    },
    data() {
        return {
            edge: null
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        setView(position) {
            position = this.degToXY(this.edge.start.x_px, this.edge.start.y_px, 20, this.edge.end.x_px, this.edge.end.y_px, 8);
            this.edge.x1 = position.x + 3;
            this.edge.y1 = position.y + 1;
            position = this.degToXY(this.edge.end.x_px, this.edge.end.y_px, 20, this.edge.start.x_px, this.edge.start.y_px, 8);
            this.edge.x2 = position.x;
            this.edge.y2 = position.y + 1;
            this.edge.degrees = this.calculateDegrees(this.edge.x1, this.edge.y1, this.edge.x2, this.edge.y2);
            this.edge.length = Math.sqrt((this.edge.x1 - this.edge.x2) * (this.edge.x1 - this.edge.x2) + (this.edge.y1 - this.edge.y2) * (this.edge.y1 - this.edge.y2)) + 12;
            this.edge.xMid = (this.edge.x1 + this.edge.x2) / 2 + 2;
            this.edge.yMid = (this.edge.y1 + this.edge.y2) / 2;
        },
        degToXY(x1, y1, d1, x2, y2, td) {
            let movement = {
                degrees: this.calculateDegrees(x1, y1, x2, y2),
                amount: this.disabled ? 12 : 14
            }
            const offset = (d1 - td) / 2;
            const angle = movement.degrees / 180 * Math.PI;
            x1 += offset + movement.amount * Math.cos(angle);
            y1 += offset + movement.amount * Math.sin(angle);
            return {
                x: x1,
                y: y1
            };
        },
        resetForm() {
            const edge = this.value;
            let position = this.degToXY(edge.start.x_px, edge.start.y_px, 14, edge.end.x_px, edge.end.y_px, 8);
            edge.x1 = position.x;
            edge.y1 = position.y + 2.25;
            position = this.degToXY(edge.end.x_px, edge.end.y_px, 14, edge.start.x_px, edge.start.y_px, 8);
            edge.x2 = position.x;
            edge.y2 = position.y + 2.25;
            edge.degrees = this.calculateDegrees(edge.start.x_px, edge.start.y_px, edge.end.x_px, edge.end.y_px);
            edge.length = Math.sqrt((edge.x1 - edge.x2) * (edge.x1 - edge.x2) + (edge.y1 - edge.y2) * (edge.y1 - edge.y2)) + 16;
            edge.xMid = (edge.x1 + edge.x2) / 2 + 4.5;
            edge.yMid = (edge.y1 + edge.y2) / 2;
            this.edge = _.clone(edge);
        }
    }
}
</script>

<style scoped>
.edge-line{
    background-color: #545c51;
    min-height:       5px;
}
.edge-line-disabled{
    background-color: #666666;
}
.edge-arrow-1{
    width:     16px;
    height:    14px;
    position:  absolute;
    top:       -4.4px;
    right:     -4px;
    transform: rotate(90deg);
}
.edge-arrow-2{
    width:     16px;
    height:    14px;
    position:  absolute;
    top:       -4.4px;
    left:      -4px;
    transform: rotate(-90deg);
}
</style>
