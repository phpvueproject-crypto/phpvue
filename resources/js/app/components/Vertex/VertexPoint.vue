<!--suppress HtmlUnknownTag -->
<template>
    <div v-if="vertex">
        <drag containment="#region"
              :id="`vertex${vertex.id}`"
              :title="vertexName"
              :drag-after-reset="!vertex.id"
              :scale="scale"
              :style="vertex.id ? {
                  position: 'absolute',
                  top: `${vertex[yPx] - (diameter / 2)}px`,
                  left: `${vertex[xPx] - (diameter / 2)}px`
              }: {position: 'relative'}"
              :disabled="!!isDeploy || disabled"
              :diameter="diameter"
              @click="onClick"
              @drag="(pos) => $emit('drag', {
                  x: pos.x,
                  y: pos.y,
                  vertex_id: vertex.id
              })"
              @stop="onStop"
              @mouseup="$emit('mouseup')">
            <div class="vertex-point" :class="{'disappear-vertex-point': (vertex.id && !vertex.is_show)}">
                <span v-show="vertex.nearby_vertex_length" class="badge bg-red vertex-badge">{{ vertex.nearby_vertex_length + 1 }}</span>
                <img class="vertex-point" v-if="!vertex.id" :src="`/img/vertex/${vertexTypeId}.png?v=202401101740`" alt="站點">
                <img class="vertex-point" v-else-if="vertex.is_selected || vertex.is_nearby"
                     :src="`/img/vertex/${vertex.vertex_type_id}_selected.png?v=202401101740`" alt="站點"/>
                <img class="vertex-point" v-else :src="`/img/vertex/${vertex.vertex_type_id}.png?v=202401101740`" alt="站點"/>
            </div>
            <context-menu v-show="vertex.id && !isDeploy" class="right-menu" width="100px">
                <context-menu-item label="複製" class="right-menu-item" @click="$emit('copy', vertex.id)">
                    <div>
                        <i class="glyphicon glyphicon-copy"/>
                        <span>複製</span>
                    </div>
                </context-menu-item>
                <context-menu-item label="刪除" class="right-menu-item" @click="$emit('delete', vertex.id)">
                    <div>
                        <i class="glyphicon glyphicon-trash"/>
                        <span>刪除</span>
                    </div>
                </context-menu-item>
            </context-menu>
        </drag>
    </div>
</template>

<script>
import _ from 'lodash';
import Drag from '../Module/Drag';

let singleClickTimer = null;
export default {
    name: "VertexPoint",
    components: {Drag},
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
        vertexTypeId: {
            type: Number,
            default: null
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        },
        diameter: {
            type: Number,
            default: 20
        },
        acceptanceGrades: {
            type: Array,
            default: () => []
        },
        pollutionConditions: {
            type: Array,
            default: () => []
        },
        cleanlinessGrade: String
    },
    watch: {
        value(newVal) {
            this.vertex = newVal;
        }
    },
    computed: {
        vertexName() {
            if(this.vertex.vertex_configurations.length > 0) {
                const vertexConfigurations = this.vertex.vertex_configurations;
                const vertexConfiguration = _.find(vertexConfigurations, (r) => {
                    return r.type == 'vertex_name';
                });
                if(vertexConfiguration) {
                    return vertexConfiguration.data;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        },
        xPx() {
            return 'x_px';
        },
        yPx() {
            return 'y_px';
        }
    },
    data() {
        return {
            vertex: this.value,
            clickNum: 0,
            isDraggableEvent: false
        };
    },
    created() {
        if(this.value) {
            this.vertex = _.cloneDeep(this.value);
        } else {
            this.vertex = {
                id: null,
                x: 0,
                y: 0,
                is_selected: false,
                is_nearby: false,
                region_mgmt_id: null,
                vertex_configurations: []
            };
        }
    },
    methods: {
        onClick() {
            if(this.isDraggableEvent) {
                return;
            }

            this.clickNum++;
            if(this.clickNum == 1) {
                this.$emit('input', this.vertex);
                if(!this.vertex.is_selected) {
                    this.$emit('select');
                } else {
                    this.$emit('unselect');
                }
                singleClickTimer = setTimeout(() => {
                    this.clickNum = 0;
                }, 400);
            } else if(this.clickNum == 2) {
                clearTimeout(singleClickTimer);
                this.clickNum = 0;
                if(this.vertex.id) {
                    this.$emit('select');
                    this.$emit('dbclick', this.vertex.id);
                }
            }
        },
        onStop(position) {
            const that = this;
            this.isDraggableEvent = true;
            setTimeout(() => {
                that.isDraggableEvent = false;
            }, 200);
            if(this.vertex.id) {
                this.updatePosition(position);
            } else {
                this.$emit('stop', position);
            }
        },
        async updatePosition(position) {
            const vertex = _.cloneDeep(this.vertex);
            vertex[this.xPx] = position.x;
            vertex[this.yPx] = position.y;
            vertex.x = this.imgCoordToQuadrantCoordX(vertex.region_mgmt.resolution, vertex[this.xPx], vertex.region_mgmt.origin_x);
            vertex.y = this.imgCoordToQuadrantCoordY(vertex.region_mgmt.resolution, vertex[this.yPx], vertex.region_mgmt.img_height, vertex.region_mgmt.origin_y, vertex.region_mgmt.origin_start_direction);
            vertex.is_selected = this.vertex.is_selected;
            vertex.is_nearby = this.vertex.is_nearby;
            this.vertex = vertex;
            this.$emit('update', this.vertex);
        }
    }
}
</script>

<style lang="scss" scoped>
.vertex-point{
    position:          relative;
    top:               3px;
    border-radius:     50%;
    width:             20px;
    max-height:        20px;
    -webkit-user-drag: none;
    > img{
        position:          relative;
        top:               -6.4px;
        border-radius:     50%;
        width:             20px;
        max-height:        20px;
        -webkit-user-drag: none;
    }
}
.vertex-point:hover{
    cursor: pointer;
    > img{
        scale:   2;
        z-index: 2;
    }
}
.animation-fade{
    animation: fade 1500ms infinite;
}
.vertex-badge{
    position:   absolute;
    left:       16px;
    top:        -6px;
    min-width:  20px;
    min-height: 20px;
    z-index:    1;
}
.disappear-vertex-point{
    z-index: -1 !important;
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
