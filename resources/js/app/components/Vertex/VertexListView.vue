<!--suppress JSUnusedLocalSymbols -->
<template>
    <div>
        <div v-for="(vertex, i) in vertices">
            <vertex-point v-model="vertices[i]"
                          :key="vertex.id"
                          :scale="scale"
                          :is-deploy="isDeploy"
                          :disabled="disabled"
                          :diameter="vertexPointDiameter"
                          :map-type="mapType"
                          :view-type="viewType"
                          :cleanliness-grade="regionMgmtCleanlinessGrade"
                          :acceptance-grades="acceptanceGrades"
                          :pollution-conditions="pollutionConditions"
                          @select="changeSelectedId(vertex, true)"
                          @unselect="changeSelectedId(vertex, false)"
                          @dbclick="showModal"
                          @drag="onDrag"
                          @update="resetRow"
                          @delete="deleteRow"
                          @copy="copyVertex"/>
        </div>
        <vertex-modal v-if="!isDeploy"
                      v-model="vertexModal.show"
                      :nearby-vertices="vertexModal.nearbyVertices"
                      :disabled="disabled"
                      :is-deploy.sync="isDeploy"
                      :vertex.sync="vertexModal.vertex"
                      :map-type="mapType"
                      @update="resetRow"
                      @close="allUnselect"/>
        <div :class="searchVertexName ? 'black-mask' : ''" @click="cancelFocusVertex"/>
    </div>
</template>

<script>
import _ from 'lodash';
import VertexPoint from './VertexPoint';
import VertexModal from './VertexModal';
import axios from 'axios';
import {mapGetters} from 'vuex';

export default {
    name: "VertexListView",
    components: {VertexModal, VertexPoint},
    computed: {
        ...mapGetters({
            user: 'user/user'
        }),
        regionMgmtCleanlinessGrade() {
            if(!this.regionMgmt || !this.regionMgmt.cleanliness_grade) {
                return null;
            }
            return this.regionMgmt.cleanliness_grade;
        }
    },
    props: {
        value: {
            type: Array,
            default() {
                return [];
            }
        },
        regionMgmtId: [Number, String],
        selectedIds: {
            type: Array,
            default() {
                return [];
            }
        },
        isAllUnselected: {
            type: Boolean,
            default: false
        },
        dragEdgePos: {
            type: Object,
            default() {
                return {
                    x: -1,
                    y: -1
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
        modal: {
            type: Object,
            default() {
                return {
                    show: false,
                    nearbyVertices: [],
                    vertex: null
                };
            }
        },
        scale: {
            type: Number,
            default: 1
        },
        deleteId: {
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
        searchVertex: {
            type: String,
            default: null
        },
        regionMgmt: {
            type: Object,
            default: () => {
                return {
                    mm: 0,
                    resolution: 0,
                    origin_x: 0,
                    origin_y: 0,
                    img_height: 0
                }
            }
        },
        loading: {
            type: Boolean,
            default: true
        },
        mapType: {
            type: String,
            default: 'radar'
        },
        viewType: {
            type: String,
            default: 'vertex'
        },
        acceptanceGrades: {
            type: Array,
            default: () => []
        },
        pollutionConditions: {
            type: Array,
            default: () => []
        }
    },
    watch: {
        value(newVal) {
            const that = this;
            const vertices = _.cloneDeep(newVal);
            this.vertices = _.map(vertices, (r) => {
                r.is_selected = false;
                r.is_nearby = false;
                const nearbyVertices = that.getNearbyVertices({
                    x_px: r.x_px,
                    y_px: r.y_px
                }, vertices);
                r.nearby_vertex_length = nearbyVertices.length - 1;
                r.is_show = nearbyVertices.length == 0 ? true : (r.id == nearbyVertices[nearbyVertices.length - 1].id);
                return r;
            });
        },
        isAllUnselected(newVal) {
            if(!newVal) {
                return;
            }

            this.allUnselect();
            this.$emit('update:isAllUnselected', false);
        },
        dragEdgePos(newVal) {
            const vertexIdx = _.findIndex(this.vertices, (r) => {
                return ((newVal.x - r.x_px) * (newVal.x - r.x_px) + (newVal.y - r.y_px) * (newVal.y - r.y_px) <= (20 * 20));
            });
            if(vertexIdx == -1) {
                this.vertices = _.map(this.vertices, (r) => {
                    r.is_nearby = false;
                    return r
                });
            } else {
                this.vertices[vertexIdx].is_nearby = true;
                this.vertices = _.cloneDeep(this.vertices);
            }
            this.$emit('update:nearbyVertex', this.vertices[vertexIdx]);
        },
        modal(newVal) {
            this.vertexModal = newVal;
        },
        'vertexModal.show': {
            handler(newVal) {
                if(this.isDeploy) {
                    if(!newVal) {
                        this.allUnselect();
                    } else {
                        this.changeSelectedId(this.vertexModal.vertex, newVal);
                    }
                }
            },
            deep: true
        },
        searchVertex(newVal) {
            if(this.oldVertexName) {
                $(this.oldVertexName).css('z-index', '1');
            }
            if(newVal == '') {
                this.searchVertexName = null;
                return;
            }
            let target = '[title|="' + newVal + '"]';
            $(target).css('z-index', '3');

            if($(target).length > 0) {
                let $container = $('html, body');
                const $scrollTo = $(target);
                $container.animate({
                    scrollTop: $scrollTo.offset().top - 350
                }, 500, () => {
                    $scrollTo.focus();
                });
            }

            this.searchVertexName = target;
            this.oldVertexName = target;
        },
        vertices: {
            handler(newVal) {
                this.$store.commit('vertex/UPDATE_VERTICES', newVal);
            },
            deep: true
        },
        mapType(newVal) {
            this.resetNearbyVertices();
        }
    },
    data() {
        return {
            vertices: [],
            vertexModal: {
                show: false,
                nearbyVertices: [],
                vertex: null
            },
            searchVertexName: null,
            oldVertexName: null,
            vertexPointDiameter: 20,
            oriVertices: []
        };
    },
    created() {
        const that = this;
        window.Echo.private(`vehicleMgmts`).listen('VehicleStatusUpdated', (res) => {
            const vehicleStatus = _.cloneDeep(res.vehicleStatus);
            const vertexIdx = _.findIndex(that.vertices, (r) => {
                return r.id == vehicleStatus.vertex_id;
            });
            if(vertexIdx != -1) {
                this.$set(this.vertices[vertexIdx], 'vehicle_status', vehicleStatus);
                this.$emit('input', that.vertices);
            }
        });
        window.Echo.private('parkingLotStatuses').listen('ParkingLotStatusUpdated', (res) => {
            const parkingLotStatus = res.parkingLotStatus;
            const parkingLotMgmt = parkingLotStatus.parking_lot_mgmt;
            const vertex = parkingLotMgmt.vertex;
            delete parkingLotStatus.parking_lot_mgmt;
            parkingLotMgmt.parking_lot_status = parkingLotStatus;
            const vertexIdx = _.findIndex(that.vertices, (r) => {
                return r.id == vertex.id;
            });
            if(vertexIdx == -1) {
                return;
            }
            this.$set(this.vertices[vertexIdx], 'parking_lot_mgmt', parkingLotMgmt);
            this.$emit('input', that.vertices);
        });
    },
    destroyed() {
        window.Echo.leave(`vehicleMgmts`);
        window.Echo.leave(`parkingLotStatuses`);
    },
    methods: {
        async fetchData() {
            this.$emit('update:loading', true);
            try {
                let res = await axios.get(`/api/vertices`, {
                    params: {
                        region_mgmt_id: this.regionMgmtId,
                        is_deploy: this.isDeploy,
                        map_type: this.viewType == 'vertex' ? null : this.mapType
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const vertices = data.vertices;
                    this.oriVertices = vertices;
                    this.$emit('input', vertices);
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.$emit('update:loading', false);
        },
        allUnselect() {
            this.vertices = _.map(this.vertices, (r) => {
                if(r.is_selected != false) {
                    r.is_selected = false;
                    r = _.cloneDeep(r);
                }
                return r;
            });
        },
        changeSelectedId(vertex, isSelected) {
            const vertexIdx = _.findIndex(this.vertices, (r) => {
                return r.id == vertex.id;
            });
            if(vertexIdx == -1) {
                return;
            }
            this.vertices[vertexIdx].is_selected = isSelected;
            const isSelectedVertices = _.filter(this.vertices, (r) => {
                return r.is_selected == true;
            });
            this.$emit('update:selectedIds', _.map(isSelectedVertices, (r) => {
                return r.id;
            }));
        },
        resetRow(row) {
            row.is_selected = false;
            row.is_nearby = false;
            const vertexIdx = _.findIndex(this.vertices, (r) => {
                return r.id == row.id;
            });
            if(vertexIdx == -1) {
                this.vertices.splice(0, 0, row);
            } else {
                this.vertices.splice(vertexIdx, 1, row);
            }
            this.$emit('update:dragPos', {
                vertex_id: row.id,
                x: row.x_px,
                y: row.y_px
            });
            this.$emit('input', this.vertices);
        },
        showModal(rowId, isCopy) {
            const vertexIdx = _.findIndex(this.vertices, (r) => {
                return r.id == rowId;
            });
            const vertex = _.cloneDeep(this.vertices[vertexIdx]);
            const nearbyVertices = this.getNearbyVertices({
                x_px: vertex.x_px,
                y_px: vertex.y_px
            });
            if(isCopy) {
                vertex.id = null;
                vertex.name = null;
                vertex.x_px = Math.abs(vertex.x_px - 50);
                vertex.x = this.imgCoordToQuadrantCoordX(this.vertex.region_mgmt.resolution, vertex.x_px, this.vertex.region_mgmt.origin_x);
                vertex.name = null;
                vertex.vertex_configurations = _.map(vertex.vertex_configurations, (r) => {
                    r.id = this.generateUUID();
                    if(r.type == 'vertex_name') {
                        r.data = null;
                    }
                    return r;
                });
            }

            if(this.mapType == 'cad' && this.viewType == 'pollution') {
                const microOrganism = _.cloneDeep(vertex.location.micro_organism);
                microOrganism.vertex = {
                    x_px: vertex.x_px,
                    y_px: vertex.y_px
                };
                this.$emit('dbclick', microOrganism);
            } else {
                this.vertexModal = {
                    show: false,
                    nearbyVertices: nearbyVertices,
                    vertex: vertex
                };
                this.$emit('update:modal', this.vertexModal);
            }

            const that = this;
            this.$nextTick(() => {
                that.vertexModal = {
                    show: true,
                    nearbyVertices: nearbyVertices,
                    vertex: vertex
                };
                that.$emit('update:modal', that.vertexModal);
            });
        },
        getNearbyVertices(position, vertices) {
            const x_px = position.x_px;
            const y_px = position.y_px;
            let nearbyVertices = null;
            if(this.mapType == 'cad') {
                nearbyVertices = _.filter(vertices ? vertices : this.vertices, (r) => {
                    return x_px == r.x_px && y_px == r.y_px && r.vertex_type_id == 4;
                });
            } else {
                nearbyVertices = _.filter(vertices ? vertices : this.vertices, (r) => {
                    return x_px == r.x_px && y_px == r.y_px;
                });
            }
            return _.orderBy(nearbyVertices, ['id'], ['desc']);
        },
        onDrag(pos) {
            this.$emit('update:dragPos', pos);
        },
        deleteRow(id) {
            this.vertices = _.filter(this.vertices, (r) => {
                return r.id != id;
            });
            this.$emit('input', this.vertices);
            this.$emit('update:deleteId', id);
        },
        cancelFocusVertex() {
            if(this.searchVertexName == null) {
                $(this.oldVertexName).css('z-index', '1');
            } else {
                $(this.searchVertexName).css('z-index', '1');
                this.searchVertexName = null;
            }
        },
        copyVertex(vertexId) {
            this.showModal(vertexId, true);
        },
        resetNearbyVertices() {
            const that = this;
            const vertices = _.cloneDeep(this.vertices);
            this.vertices = _.map(vertices, (r) => {
                const nearbyVertices = that.getNearbyVertices({
                    x_px: r.x_px,
                    y_px: r.y_px
                }, vertices);
                r.nearby_vertex_length = nearbyVertices.length - 1;
                return r;
            });
        }
    }
}
</script>
<style scoped>
.black-mask{
    position:         absolute;
    top:              0;
    z-index:          2;
    background-color: rgba(0, 0, 0, 0.5);
    min-width:        100%;
    min-height:       100%;
}
</style>
