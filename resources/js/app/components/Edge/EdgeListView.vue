<template>
    <div>
        <template v-if="mapType == 'radar'">
            <div v-for="(edge, i) in edges">
                <edge-line v-model="edges[i]"
                           :nearby-vertex="nearbyVertex"
                           :scale="scale"
                           :edges="edges"
                           :drag-vertex-pos="dragVertexPos"
                           :reset-vertex-pos-list="resetVertexPosList"
                           :is-deploy="isDeploy"
                           :disabled="disabled"
                           @dbclick="showModal"
                           @drag="(pos)=>{$emit('update:dragPos', pos)}"
                           @delete="deleteRow"/>
            </div>
        </template>
        <edge-modal v-if="!isDeploy"
                    v-model="modal.show"
                    :is-deploy="isDeploy"
                    :vertices="vertices"
                    :edge="edgeModal.edge"
                    :project-id="projectId"
                    @update="resetRow"
                    @close="$emit('update:isAllVertexUnselected', true)"/>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';
import EdgeLine from './EdgeLine';
import EdgeModal from './EdgeModal';

export default {
    name: "EdgeListView",
    components: {EdgeModal, EdgeLine},
    props: {
        value: {
            type: Array,
            default() {
                return [];
            }
        },
        selectedVertexIds: {
            type: Array,
            default() {
                return [];
            }
        },
        isAllVertexUnselected: {
            type: Boolean,
            default: true
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
        dragPos: {
            type: Object,
            default() {
                return {
                    x: -1,
                    y: -1
                }
            }
        },
        nearbyVertex: {
            type: Object,
            default() {
                return null;
            }
        },
        projectId: {
            type: Number,
            default: null
        },
        regionMgmtId: {
            type: Number,
            default: null
        },
        scale: {
            type: Number
        },
        deleteVertexId: {
            type: [Number, String]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        vertices: {
            type: Array,
            default() {
                return [];
            }
        },
        loading: {
            type: Boolean,
            default: true
        },
        modal: {
            type: Object,
            default() {
                return {
                    show: false,
                    edge: null
                };
            }
        },
        resetVertexPosList: {
            type: Array,
            default() {
                return [];
            }
        },
        vehicleMgmts: {
            type: Array,
            default() {
                return [];
            }
        },
        mapType: {
            type: String,
            default: 'radar'
        }
    },
    watch: {
        value(newVal) {
            this.edges = newVal;
        },
        edges(newVal) {
            this.$emit('input', newVal);
            this.$store.commit('edge/UPDATE_EDGES', newVal);
        },
        modal(newVal) {
            this.edgeModal = newVal;
        },
        selectedVertexIds(newVal, oldVal) {
            if(newVal.length != 2 || this.isDeploy) {
                return;
            }
            const sortedVertexIds = _.sortBy(newVal, (r) => {
                return r != oldVal
            });

            const duplicate = _.find(this.edges, (r) => {
                return r.start_vertex_id == newVal[0] && r.end_vertex_id == newVal[1] || r.start_vertex_id == newVal[1] && r.end_vertex_id == newVal[0];
            });
            if(duplicate) {
                this.$toast.error({
                    title: '軌道已重複',
                    message: '已創建過相同軌道！'
                });
                this.$emit('update:isAllVertexUnselected', true);
            } else {
                const startVertex = _.find(this.vertices, (r) => {
                    return r.id == sortedVertexIds[0];
                });
                const endVertex = _.find(this.vertices, (r) => {
                    return r.id == sortedVertexIds[1];
                });
                this.showModal({
                    id: null,
                    name: null,
                    start_vertex_id: sortedVertexIds[0],
                    end_vertex_id: sortedVertexIds[1],
                    region_mgmt_id: this.regionMgmtId,
                    direction: 0,
                    start_vertex: {
                        name: startVertex.name
                    },
                    end_vertex: {
                        region_mgmt_id: this.regionMgmtId,
                        name: endVertex.name
                    },
                    enable: 1,
                    edge_configurations: [],
                    region_mgmt: {
                        project_id: this.projectId
                    }
                });
            }
        },
        deleteVertexId(newVal) {
            this.edges = _.filter(this.edges, (r) => {
                return r.start_vertex_id != newVal && r.end_vertex_id != newVal;
            });
            this.$emit('deleteVertexId', null);
        }
    },
    data() {
        return {
            edges: [],
            oriEdges: [],
            edgeModal: {
                show: false,
                edge: null
            }
        };
    },
    destroyed() {
        window.Echo.leave(`edges`);
    },
    methods: {
        async fetchData(projectId) {
            this.$emit('update:loading', true);
            try {
                let res = await axios.get(`/api/edges`, {
                    params: {
                        project_id: projectId,
                        region_mgmt_id: projectId ? undefined : this.regionMgmtId,
                        is_deploy: this.isDeploy
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const that = this;
                    this.$emit('input', data.edges);
                    this.$nextTick(() => {
                        that.oriEdges = _.cloneDeep(that.edges);
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.$emit('update:loading', false);
        },
        resetRow(row) {
            if(this.isDeploy && this.vehicleId) {
                this.fetchData();
            } else {
                const startVertex = _.find(this.vertices, (r) => {
                    return r.id == row.start_vertex_id;
                });
                const endVertex = _.find(this.vertices, (r) => {
                    return r.id == row.end_vertex_id;
                });
                row.start_vertex.x_px = startVertex.x_px;
                row.start_vertex.y_px = startVertex.y_px;
                row.end_vertex.x_px = endVertex.x_px;
                row.end_vertex.y_px = endVertex.y_px;
                const edgeIdx = _.findIndex(this.edges, (r) => {
                    return r.id == row.id;
                });
                if(edgeIdx == -1) {
                    this.edges.push(row);
                } else {
                    this.edges[edgeIdx] = row;
                }
                this.edges = _.cloneDeep(this.edges);
                this.$emit('input', this.edges);
            }
        },
        deleteRow(row) {
            this.edges = _.filter(this.edges, (r) => {
                return r.id != row.id;
            });
            this.$emit('input', this.edges);
        },
        showModal(row) {
            if(this.modal.show == true) {
                this.edgeModal = {
                    show: false,
                    edge: null
                };
                this.$emit('update:modal', this.edgeModal);
            }
            const that = this;
            this.$nextTick(() => {
                that.edgeModal = {
                    show: true,
                    edge: row
                };
                that.$emit('update:modal', that.edgeModal);
            });
        }
    }
}
</script>
