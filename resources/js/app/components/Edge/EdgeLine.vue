<!--suppress HtmlUnknownTag -->
<template>
    <div v-if="edge && showEdge">
        <drag v-if="!isDeploy && showEdgePoint"
              containment="#region"
              :drag-after-reset="false"
              style="position: absolute; z-index: 1;"
              :style="{
                  top: `${!dragging ? edge.y1 : edge.dragging_y1}px`,
                  left: `${!dragging ? edge.x1 : edge.dragging_x1}px`
              }"
              :scale="scale"
              @drag="(position) => {onPointDrag(position,'start')}"
              @stop="(position) => {onPointStop(position,'start')}">
            <div class="edge-point"></div>
        </drag>
        <drag v-if="!isDeploy && showEdgePoint"
              containment="#region"
              :drag-after-reset="false"
              style="position: absolute; z-index: 1;"
              :style="{
                  top: `${!dragging ? edge.y2 : edge.dragging_y2}px`,
                  left: `${!dragging ? edge.x2 : edge.dragging_x2}px`
              }"
              :scale="scale"
              @drag="(position) => {onPointDrag(position,'end')}"
              @stop="(position) => {onPointStop(position,'end')}">
            <div class="edge-point"></div>
        </drag>
        <div class="edge-line" :style="`position: absolute; top: ${edge.yMid}px; left: ${edge.xMid - edge.length / 2}px; transform: rotate(${edge.degrees}deg); width: ${edge.length}px; border-color: ${edgeColor}`"
             :class="{'edge-line-disabled' : !edge.enable}"
             @click="onClick">
            <img v-if="showAutoDoor" src="/img/VertexConfigurations/auto_door.png" alt="自動門" class="auto-door" :style="`left: ${(edge.length / 2)}px; transform: rotate(${-edge.degrees}deg)`">
            <i v-if="showArrow && (edge.direction == 1 || edge.direction == 0)" class="edge-arrow edge-arrow-1 glyphicon glyphicon-play" :class="{'edge-arrow-disabled' : !edgeEnable}" :style="`color:  ${edgeColor}`"/>
            <i v-if="showArrow && (edge.direction == 0)" class="edge-arrow edge-arrow-2 glyphicon glyphicon-play" :class="{'edge-arrow-disabled' : !edgeEnable}" :style="`color:  ${edgeColor}`"/>
        </div>
        <context-menu v-show="!isDeploy" class="right-menu" width="120px">
            <context-menu-item class="right-menu-item" label="刪除" @click="$emit('delete', edge)">
                <div>
                    <i class="glyphicon glyphicon-trash"/>
                    <span>刪除</span>
                </div>
            </context-menu-item>
        </context-menu>
    </div>
</template>

<script>
import Drag from '../Module/Drag';
import _ from 'lodash';

let singleClickTimer = null;
export default {
    name: "EdgeLine",
    components: {Drag},
    props: {
        value: {
            type: Object,
            default() {
                return null;
            }
        },
        nearbyVertex: {
            type: Object,
            default() {
                return null;
            }
        },
        dragVertexPos: {
            type: Object,
            default() {
                return {
                    x: -1,
                    y: -1,
                    vertex_id: null
                }
            }
        },
        scale: {
            type: Number
        },
        edges: {
            type: Array,
            default() {
                return [];
            }
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        },
        vertexPointDiameter: {
            type: Number,
            default: 20
        },
        resetVertexPosList: {
            type: Array,
            default() {
                return [];
            }
        }
    },
    computed: {
        edgeColor() {
            return (typeof this.edge.vehicle_mgmts !== 'undefined' && this.edge.vehicle_mgmts.length > 0) ? this.edge.vehicle_mgmts[0].color : '#ffca55';
        },
        edgeEnable() {
            return this.value ? this.value.enable : this.edge ? this.edge.enable : false;
        },
        showEdge() {
            return Math.sqrt((this.edge.start_vertex.x_px - this.edge.end_vertex.x_px) * (this.edge.start_vertex.x_px - this.edge.end_vertex.x_px) + (this.edge.start_vertex.y_px - this.edge.end_vertex.y_px) * (this.edge.start_vertex.y_px - this.edge.end_vertex.y_px)) >= this.vertexPointDiameter;
        },
        showArrow() {
            return Math.sqrt((this.edge.start_vertex.x_px - this.edge.end_vertex.x_px) * (this.edge.start_vertex.x_px - this.edge.end_vertex.x_px) + (this.edge.start_vertex.y_px - this.edge.end_vertex.y_px) * (this.edge.start_vertex.y_px - this.edge.end_vertex.y_px)) >= (this.vertexPointDiameter * 2.7);
        },
        showEdgePoint() {
            return Math.sqrt((this.edge.start_vertex.x_px - this.edge.end_vertex.x_px) * (this.edge.start_vertex.x_px - this.edge.end_vertex.x_px) + (this.edge.start_vertex.y_px - this.edge.end_vertex.y_px) * (this.edge.start_vertex.y_px - this.edge.end_vertex.y_px)) >= (this.vertexPointDiameter * 2.7);
        },
        showAutoDoor() {
            return !!_.find(this.edge.edge_configurations, ['type', 'auto_door']);
        }
    },
    watch: {
        value() {
            this.resetForm();
        },
        dragVertexPos(newVal) {
            this.resetEdge(newVal);
            this.resetForm();
        },
        disabled() {
            this.resetForm();
        },
        resetVertexPosList: {
            handler(newVal) {
                const that = this;
                if(newVal.length > 0) {
                    _.each(newVal, (r) => {
                        that.resetEdge(r);
                    });
                }
                this.resetForm();
            },
            deep: true
        }
    },
    data() {
        return {
            edge: null,
            dragging: false,
            clickNum: 0
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        onClick() {
            this.clickNum++;
            if(this.clickNum == 1) {
                singleClickTimer = setTimeout(() => {
                    this.clickNum = 0;
                }, 400);
            } else if(this.clickNum == 2) {
                clearTimeout(singleClickTimer);
                this.clickNum = 0;
                this.$emit('dbclick', this.edge);
            }
        },
        onPointDrag(position, startOrEnd) {
            this.dragging = true;
            const newX = position.x - (this.vertexPointDiameter / 2);
            const newY = position.y - (this.vertexPointDiameter / 2) - 2;
            if(startOrEnd == 'start') {
                this.edge.dragging_x1 = newX;
                this.edge.dragging_y1 = newY;
                this.edge.degrees = this.calculateDegrees(newX, newY, this.edge.dragging_x2, this.edge.dragging_y2);
                this.edge.length = Math.sqrt((this.edge.dragging_x2 - newX) * (this.edge.dragging_x2 - newX) + (newY - this.edge.dragging_y2) * (newY - this.edge.dragging_y2) + (this.vertexPointDiameter / 2));
                this.edge.xMid = (newX + this.edge.dragging_x2) / 2;
                this.edge.yMid = (newY + this.edge.dragging_y2) / 2;
                const modDegrees = (this.edge.degrees % 360);
                if(modDegrees > -90 && modDegrees < 90) {
                    this.edge.yMid += 2.4;
                }
                if(modDegrees > 0 && modDegrees < 180) {
                    this.edge.xMid += 2.4;
                } else {
                    this.edge.xMid += 5;
                }
            } else {
                this.edge.dragging_x2 = newX;
                this.edge.dragging_y2 = newY;
                this.edge.degrees = this.calculateDegrees(this.edge.dragging_x1, this.edge.dragging_y1, newX, newY);
                this.edge.length = Math.sqrt((this.edge.dragging_x1 - newX) * (this.edge.dragging_x1 - newX) + (this.edge.dragging_y1 - newY) * (this.edge.dragging_y1 - newY) + (this.vertexPointDiameter / 2));
                this.edge.xMid = (this.edge.dragging_x1 + newX) / 2;
                this.edge.yMid = (this.edge.dragging_y1 + newY) / 2;
                const modDegrees = (this.edge.degrees % 360);
                if(modDegrees > -90 && modDegrees < 90) {
                    this.edge.yMid += 2.4;
                }
                if(modDegrees > 0 && modDegrees < 180) {
                    this.edge.xMid += 2.4;
                } else {
                    this.edge.xMid += 5;
                }
            }
            this.$emit('drag', position);
        },
        onPointStop(position, startOrEnd) {
            this.dragging = false
            if(!this.nearbyVertex) {
                this.resetForm();
            } else {
                this.nearbyVertex.is_nearby = false;
                const edge = _.clone(this.edge);
                if(startOrEnd == 'start') {
                    edge.start_vertex = this.nearbyVertex;
                    edge.start_vertex_id = edge.start_vertex.id;
                } else {
                    edge.end_vertex = this.nearbyVertex;
                    edge.end_vertex_id = edge.end_vertex.id;
                }
                if(edge.start_vertex_id == edge.end_vertex_id) {
                    this.resetForm();
                    this.$toast.error({
                        title: '無法移動軌道',
                        message: '起點跟終點不能一樣！'
                    })
                    return;
                }
                const duplicateEdge = _.find(this.edges, (r) => {
                    return (r.start_vertex_id == edge.start_vertex_id && r.end_vertex_id == edge.end_vertex_id) || (r.start_vertex_id == edge.end_vertex_id && r.end_vertex_id == edge.start_vertex_id);
                });
                if(duplicateEdge) {
                    this.resetForm();
                    this.$toast.error({
                        title: '無法移動軌道',
                        message: '該區段已有軌道！'
                    })
                    return;
                }

                this.updateEdge(edge, position);
            }
        },
        setView(position) {
            position = this.degToXY(this.edge.start_vertex.x_px, this.edge.start_vertex.y_px, this.vertexPointDiameter, this.edge.end_vertex.x_px, this.edge.end_vertex.y_px, 8);
            this.edge.x1 = position.x - (this.vertexPointDiameter / 2);
            this.edge.y1 = position.y - (this.vertexPointDiameter / 2) - 2;
            this.edge.dragging_x1 = position.x - (this.vertexPointDiameter / 2);
            this.edge.dragging_y1 = position.y - (this.vertexPointDiameter / 2) - 2;
            position = this.degToXY(this.edge.end_vertex.x_px, this.edge.end_vertex.y_px, this.vertexPointDiameter, this.edge.start_vertex.x_px, this.edge.start_vertex.y_px, 8);
            this.edge.x2 = position.x - (this.vertexPointDiameter / 2);
            this.edge.y2 = position.y - (this.vertexPointDiameter / 2) - 2;
            this.edge.dragging_x2 = position.x - (this.vertexPointDiameter / 2);
            this.edge.dragging_y2 = position.y - (this.vertexPointDiameter / 2) - 2;
            this.edge.degrees = this.calculateDegrees(this.edge.x1, this.edge.y1, this.edge.x2, this.edge.y2);
            this.edge.length = Math.sqrt((this.edge.x1 - this.edge.x2) * (this.edge.x1 - this.edge.x2) + (this.edge.y1 - this.edge.y2) * (this.edge.y1 - this.edge.y2) + (this.vertexPointDiameter / 2));
            this.edge.xMid = (this.edge.x1 + this.edge.x2) / 2;
            this.edge.yMid = (this.edge.y1 + this.edge.y2) / 2;
            const modDegrees = (this.edge.degrees % 360);
            if(modDegrees > -90 && modDegrees < 90) {
                this.edge.yMid += 2.4;
            }
            if(modDegrees > 0 && modDegrees < 180) {
                this.edge.xMid += 2.4;
            } else {
                this.edge.xMid += 5;
            }
        },
        degToXY(x1, y1, d1, x2, y2, td) {
            let movement = {
                degrees: this.calculateDegrees(x1, y1, x2, y2),
                amount: this.isDeploy ? 8 : 14
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
            let position = this.degToXY(edge.start_vertex.x_px, edge.start_vertex.y_px, this.vertexPointDiameter, edge.end_vertex.x_px, edge.end_vertex.y_px, 8);
            edge.x1 = position.x - (this.vertexPointDiameter / 2);
            edge.y1 = position.y - (this.vertexPointDiameter / 2) - 2;
            edge.dragging_x1 = position.x - (this.vertexPointDiameter / 2);
            edge.dragging_y1 = position.y - (this.vertexPointDiameter / 2) - 2;
            position = this.degToXY(edge.end_vertex.x_px, edge.end_vertex.y_px, this.vertexPointDiameter, edge.start_vertex.x_px, edge.start_vertex.y_px, 8);
            edge.x2 = position.x - (this.vertexPointDiameter / 2);
            edge.y2 = position.y - (this.vertexPointDiameter / 2) - 2;
            edge.dragging_x2 = position.x - (this.vertexPointDiameter / 2);
            edge.dragging_y2 = position.y - (this.vertexPointDiameter / 2) - 2;
            edge.degrees = this.calculateDegrees(edge.start_vertex.x_px, edge.start_vertex.y_px, edge.end_vertex.x_px, edge.end_vertex.y_px);
            edge.length = Math.sqrt((edge.x1 - edge.x2) * (edge.x1 - edge.x2) + (edge.y1 - edge.y2) * (edge.y1 - edge.y2) + (this.vertexPointDiameter / 2));
            edge.xMid = (edge.x1 + edge.x2) / 2;
            edge.yMid = (edge.y1 + edge.y2) / 2;
            const modDegrees = (edge.degrees % 360);
            if(modDegrees > -90 && modDegrees < 90) {
                edge.yMid += 2.4;
            }
            if(modDegrees > 0 && modDegrees < 180) {
                edge.xMid += 2.4;
            } else {
                edge.xMid += 5;
            }
            this.edge = _.clone(edge);
        },
        async updateEdge(edge, position) {
            this.edge = edge;
            this.$emit('input', this.edge);
            this.setView(position);
        },
        resetEdge(vertexPoint) {
            if(this.edge.start_vertex_id == vertexPoint.vertex_id) {
                this.edge.start_vertex.x_px = vertexPoint.x;
                this.edge.start_vertex.y_px = vertexPoint.y;
            } else if(this.edge.end_vertex_id == vertexPoint.vertex_id) {
                this.edge.end_vertex.x_px = vertexPoint.x;
                this.edge.end_vertex.y_px = vertexPoint.y;
            }
        }
    }
}
</script>

<style scoped>
.edge-point{
    background-color: white;
    border-radius:    50%;
    border:           1px solid grey;
    width:            8px;
    min-height:       8px;
}
.edge-point:hover{
    background-color: rgba(100, 100, 100, 50);
    cursor:           pointer;
}
.edge-line{
    height:     5px;
    cursor:     pointer;
    border-top: 2px solid;
}
.edge-line-disabled{
    border-color: #bababa !important;
}
.edge-arrow{
    font-size: 1px;
}
.edge-arrow-1{
    position: absolute;
    top:      -7.6px;
    right:    5px;
}
.edge-arrow-2{
    top:       -15.4px;
    left:      5px;
    transform: rotate(180deg);
}
.edge-arrow-disabled{
    color: #bababa !important;
}
.auto-door{
    position:      absolute;
    width:         10px;
    border-radius: 3px;
}
</style>
