<template>
    <div>
        <template v-if="isDeploy && showModal">
            <div style="position: absolute; top: 0; left: 0; min-width: 450px; max-width: 450px;">
                <div class="box box-default" style="margin-bottom: 0;">
                    <div class="box-header with-border">
                        <h3 class="box-title">站點資訊</h3>
                    </div>
                    <div class="box-body" style="display: block">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">站點類型：</label>
                                <div class="col-md-9">
                                    <vertex-type-select v-model="form.vertex_type_id" class="display-select" style="font-weight: normal" disabled/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">X軸：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.x }}&nbsp;m</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">Y軸：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.y }}&nbsp;m</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">自訂屬性：</label>
                                <div class="col-md-9" style="margin-top: 6px;font-size: 12pt">
                                    <div v-for="row in form.vertex_configurations" style="margin-bottom: 15px">
                                        <span style="float: left">{{ row.type }}：</span>
                                        <span style="float: left" v-if="row.type == 'remapping' || row.type == 'intersection'">
                                            <template v-if="row.type == 'remapping'">
                                                <div>
                                                    <span class="text-right" style="font-size: 12pt">id:</span>
                                                    <span style="font-size: 12pt; padding-left: 0;">{{ row.data.id }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-right" style="font-size: 12pt">port:</span>
                                                    <span style="font-size: 12pt; padding-left: 0;">{{ row.data.port }}</span>
                                                </div>
                                            </template>
                                            <template v-else-if="row.type == 'intersection'">
                                                <span v-for="(give_way_vertex, i) in row.data.give_way_vertices">
                                                    <span class="text-right" style="font-size: 12pt">避車點{{ i + 1 }}:</span>
                                                    <span style="font-size: 12pt; padding-left: 0">{{ give_way_vertex }}</span>
                                                    <br>
                                                </span>
                                            </template>
                                        </span>
                                        <span v-else style="float: left">
                                            <template v-if="row.type == 'e84'">
                                                <switch-button v-if="form.vertex_type_id != 2" v-model="row.data" class="toggle-btn" disabled/>
                                                <span v-else>on</span>
                                            </template>
                                            <switch-button v-else-if="row.type == 'charger' || row.type == 'break_point'" v-model="row.data" class="toggle-btn" disabled/>
                                            <span v-else-if="row.type == 'rotation_condition'">{{ row.data ? row.data : 'Null' }}</span>
                                            <span v-else style="font-size: 12pt">{{ row.data }}</span>
                                        </span>
                                        <div class="clearfix"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">啟用狀態：</label>
                                <div class="col-md-9">
                                    <input v-model.number.lazy="form.enable" :true-value="1" :false-value="0" type="checkbox" class="form-control-static" name="enable" :disabled="!allNotAssigned" @change="updateEnable"/>
                                </div>
                            </div>
                            <div v-if="form.vertex_type_id == 2" class="form-group" style="border-top: 1px solid lightgrey;">
                                <div class="col-md-4">
                                    <div style="font-size: 16pt; font-weight: bold; margin: 10px 0 0 20px;">電梯點</div>
                                    <div style="font-size: 10pt; font-weight: bold; margin: 24px 0 0 22px; color: #5d5c84">電梯門狀態</div>
                                    <div class="booking-sign">
                                        <div v-if="!form.elevator_mgmt || !form.elevator_mgmt.elevator_status" class="booking-tag none-booking">已關門</div>
                                        <div v-else class="booking-tag" :class="{'booking' : form.elevator_mgmt.elevator_status.elevator_door_status == 'open', 'none-booking' : !(form.elevator_mgmt.elevator_status.elevator_door_status == 'open')}">{{ form.elevator_mgmt.elevator_status.elevator_door_status == 'open' ? "已開門" : "已關門" }}</div>
                                    </div>
                                </div>
                                <div class="col-md-8 elevator-command">
                                    <switch-button class="switch-btn" v-model="switchAuthorization" :labels="{checked: '要授權', unchecked: '還釋放'}" :width="64" :height="28" @change="onSwitchButtonChange"/>
                                    <input type="text" ref="targetFloorInput" placeholder="F" class="ml-15 border-gray input-target-floor" :disabled="sending" v-model="mqttCommandForm.target_floor">
                                    <button type="button" class="btn btn-default btn-sm border-gray elevator-btn" :disabled="sending" @click="submitMqttCommand(33, mqttCommandForm.target_floor)">
                                        <i class="fa fa-arrow-up"/><i class="fa fa-arrow-down"/>
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm border-gray close-btn" :disabled="sending" @click="submitMqttCommand(34)">
                                        <i class="fa fa-play"/>|<i class="fa fa-play" style="transform: rotate(180deg);"/>
                                    </button>
                                </div>
                                <div class="col-md-8" style="margin: 10px 0 0 -4px; font-size: 12pt; line-height: 1.9">
                                    <div class="col-md-5" style="padding: 0;">
                                        <label class="full-line">電梯點編號</label>
                                        <label class="full-line">狀態</label>
                                        <label class="full-line">樓層</label>
                                        <label class="full-line">接收時間</label>
                                    </div>
                                    <div class="col-md-1" style="padding: 0; margin-left: -24px;">
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                    </div>
                                    <div v-if="form.elevator_mgmt" class="col-md-6" style="padding: 0;">
                                        <label class="full-line">{{ form.elevator_mgmt ? form.elevator_mgmt.elevator_id : '' }}</label>
                                        <label class="full-line">
                                            <template v-if="form.elevator_mgmt.elevator_status">
                                                <template v-if="form.elevator_mgmt.elevator_status.elevator_door_status == 'open'">開門</template>
                                                <template v-else-if="form.elevator_mgmt.elevator_status.elevator_door_status == 'closed'">關門</template>
                                                <span v-if="form.elevator_mgmt.elevator_status.elevator_status && form.elevator_mgmt.elevator_status.elevator_door_status">&nbsp;|&nbsp;</span>
                                                <template v-if="form.elevator_mgmt.elevator_status.elevator_status == 'idle'">閒置</template>
                                                <template v-else-if="form.elevator_mgmt.elevator_status.elevator_status == 'busy'">運轉</template>
                                                <template v-else-if="form.elevator_mgmt.elevator_status.elevator_status == 'disable'">故障</template>
                                            </template>
                                        </label>
                                        <label class="full-line">
                                            <template v-if="form.elevator_mgmt.elevator_status">
                                                {{ form.elevator_mgmt.elevator_status.elevator_position }}
                                            </template>
                                        </label>
                                        <label v-if="form.elevator_mgmt.elevator_status" class="full-line">{{ form.elevator_mgmt.elevator_status.update_ts | datetime }}</label>
                                        <label v-else class="full-line">{{ null }}</label>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="form.vertex_type_id == 4" class="form-group" style="border-top: 1px solid lightgrey;">
                                <div class="col-md-4">
                                    <div style="font-size: 16pt; font-weight: bold; margin: 10px 0 0 20px;">工作站</div>
                                </div>
                                <div class="col-md-8" style="margin: 18px 0 0 -4px; font-size: 12pt;">
                                    <div class="col-md-5" style="padding: 0;">
                                        <label class="full-line">工作站編號</label>
                                        <label class="full-line">工作站狀態</label>
                                    </div>
                                    <div class="col-md-1" style="padding: 0; margin-left: -24px;">
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                    </div>
                                    <div v-if="form.station_mgmt" class="col-md-6" style="padding: 0;">
                                        <label class="full-line">{{ form.station_mgmt ? form.station_mgmt.station_id : '' }}</label>
                                        <label class="full-line">{{ form.station_mgmt.station_status ? form.station_mgmt.station_status.station_status : '' }}</label>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="hasCharger" class="form-group" style="border-top: 1px solid lightgrey;">
                                <div class="col-md-4">
                                    <div style="font-size: 16pt; font-weight: bold; margin: 10px 0 0 20px;">停車位</div>
                                    <div style="font-size: 10pt; font-weight: bold; margin: 12px 0 0 33px; color: #5d5c84;">預定資訊</div>
                                    <div class="booking-sign">
                                        <div v-if="!form.parking_lot_mgmt || !form.parking_lot_mgmt.parking_lot_status" class="booking-tag none-booking">未預定</div>
                                        <div v-else class="booking-tag" :class="{'booking' : form.parking_lot_mgmt.parking_lot_status.booking_vehicle_id, 'none-booking' : !form.parking_lot_mgmt.parking_lot_status.booking_vehicle_id}">{{ form.parking_lot_mgmt.parking_lot_status.booking_vehicle_id ? '已預定' : '未預定' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-8" style="margin: 18px 0 0 -4px; font-size: 12pt;">
                                    <div class="col-md-5" style="padding: 0;">
                                        <label class="full-line">停車位編號</label>
                                        <label class="full-line">預定車輛</label>
                                        <label class="full-line">目前停放</label>
                                        <label class="full-line">接收時間</label>
                                    </div>
                                    <div class="col-md-1" style="padding: 0; margin-left: -24px;">
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                        <label class="full-line">：</label>
                                    </div>
                                    <div v-if="form.parking_lot_mgmt" class="col-md-6" style="padding: 0;">
                                        <label class="full-line">{{ form.parking_lot_mgmt ? form.parking_lot_mgmt.parking_lot_id : '' }}</label>
                                        <label class="full-line">{{ form.parking_lot_mgmt.parking_lot_status ? form.parking_lot_mgmt.parking_lot_status.booking_vehicle_id : '' }}</label>
                                        <label class="full-line">{{ form.parking_lot_mgmt.parking_lot_status ? form.parking_lot_mgmt.parking_lot_status.occupied_vehicle_id : '' }}</label>
                                        <label v-if="form.parking_lot_mgmt.parking_lot_status" class="full-line">{{ form.parking_lot_mgmt.parking_lot_status.update_ts | datetime }}</label>
                                        <label v-else class="full-line">{{ null }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right" style="margin-right: 15px;">
                                <template v-if="nearbyVertices.length > 1">
                                    <button type="button" class="btn btn-default" title="上一個站點" @click="switchVertex(true)" v-show="nearbyVertexIdx < nearbyVertices.length - 1">上一個站點</button>
                                    <button type="button" class="btn btn-default" title="下一個站點" @click="switchVertex(false)" v-show="nearbyVertexIdx  > 0">下一個站點</button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <modal v-else v-model="showModal" @show="onShowModal" :size="isDeploy ? 'md' : 'lg'" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
            <div slot="title">{{ form.id ? '更新站點' : '新增站點' }}</div>
            <div slot="default">
                <form novalidate class="form-horizontal" @submit.prevent="submit">
                    <div class="form-group">
                        <label class="col-md-3 control-label">站點Tag<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input class="form-control" v-model="form.tag" placeholder="請填寫站點Tag">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">站點類型<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <vertex-type-select name="vertex_type_id"
                                                v-validate="'required'"
                                                v-model="form.vertex_type_id"
                                                :items.sync="vertexTypes"
                                                :disabled="mapType == 'cad'"/>
                            <span v-show="errors.has('vertex_type_id:required')" class="help is-danger">請選擇站點類型</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">X軸<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.x" name="x" v-validate="'required|decimal'"/>
                                <div class="input-group-addon">m</div>
                            </div>
                            <span v-show="errors.has('x:required')" class="help is-danger">請填寫X軸</span>
                            <span v-show="errors.has('x:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Y軸<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.y" name="y" v-validate="'required|decimal'"/>
                                <div class="input-group-addon">m</div>
                            </div>
                            <span v-show="errors.has('y:required')" class="help is-danger">請填寫Y軸</span>
                            <span v-show="errors.has('y:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <vertex-configuration-list-view v-if="vertexConfigurationTypes.length"
                                                    v-model="form.vertex_configurations"
                                                    :vertex-type-id="form.vertex_type_id"
                                                    :vertex-configuration-types="vertexConfigurationTypes"
                                                    :vertex="form"/>
                    <form v-if="form.vertex_type_id == 2" class="form-group" novalidate>
                        <label class="col-md-3 control-label">電梯點軌道&nbsp;&nbsp;</label>
                        <div class="col-md-9">
                            <table class="table table-bordered table-hover">
                                <thead class="table-head">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center" style="width: 300px">區域</th>
                                    <th class="text-center" style="width: 150px">編輯</th>
                                    <th class="text-center">刪除</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(row, index) in form.edges">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td style="width: 300px">
                                        <region-mgmt-list-select v-model="row.end_vertex.region_mgmt_id"
                                                                 v-validate="'required'"
                                                                 :name="`end_vertex_region-${index}`"
                                                                 :project-id="form.region_mgmt.project_id"
                                                                 :single-select2="true"
                                                                 :exclude-region="form.region_mgmt_id"/>
                                        <input type="hidden" v-model="row.end_vertex_id" v-validate="'required'" :name="`end_vertex_id-${index}`"/>
                                        <span v-if="errors.has(`end_vertex_region-${index}:required`)" class="help is-danger">請選擇區域</span>
                                        <span v-else-if="errors.has(`end_vertex_id-${index}:required`)" class="help is-danger">請編輯軌道</span>
                                    </td>
                                    <td class="text-center" style="width: 150px">
                                        <i class="glyphicon glyphicon-edit" :style="`cursor: ${row.end_vertex.region_mgmt_id ? 'pointer' : 'not-allowed'}`" type="button" @click="showEdgeModal(row)" title="編輯"/>
                                    </td>
                                    <td class="text-center">
                                        <i class="glyphicon glyphicon-trash cursor-pointer" @click="deleteEdgeRow(index)" title="刪除"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center cursor-pointer" @click="addEdge">
                                        <i class="glyphicon glyphicon-plus-sign cursor-pointer" title="新增"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <div class="text-right">
                        <template v-if="nearbyVertices.length > 1">
                            <button type="button" class="btn btn-default" title="上一個站點" @click="switchVertex(true)" v-show="nearbyVertexIdx < nearbyVertices.length - 1">上一個站點</button>
                            <button type="button" class="btn btn-default" title="下一個站點" @click="switchVertex(false)" v-show="nearbyVertexIdx > 0">下一個站點</button>
                        </template>
                        <button class="btn btn-light-green" title="送出">送出</button>
                        <button type="button" class="btn btn-default" title="取消" @click="showModal = false">取消</button>
                    </div>
                </form>
                <edge-modal v-model="edgeModal.show"
                            :is-deploy="isDeploy"
                            :vertex-type-id="2"
                            :edge="edgeModal.edge"
                            :project-id="edgeModal.project_id"
                            :region-mgmt-id="edgeModal.region_mgmt_id"
                            @update="resetEdgeRow"/>
            </div>
        </modal>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import UploadButton from '../Module/UploadButton';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect';
import VertexConfigurationTypeSelect from '../VertexConfigurationType/VertexConfigurationTypeSelect';
import VertexTypeSelect from '../VertexType/VertexTypeSelect';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect';
import EdgeModal from '../Edge/EdgeModal.vue';
import VertexConfigurationListView from '../VertexConfiguration/VertexConfigurationListView.vue';
import SwitchButton from '../VertexConfiguration/SwitchButton.vue';

export default {
    name: "VertexModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        SwitchButton,
        VertexConfigurationListView,
        EdgeModal,
        VehicleMgmtListSelect,
        VertexTypeSelect,
        VertexConfigurationTypeSelect,
        RegionMgmtListSelect,
        UploadButton,
        ClipLoader
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        nearbyVertices: {
            type: Array,
            default() {
                return []
            }
        },
        disabled: {
            type: Boolean,
            default: false
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        vertex: {
            type: Object,
            default: null
        },
        mapType: {
            type: String,
            default: 'radar'
        }
    },
    computed: {
        nearbyVertexIdx() {
            const that = this;
            return _.findIndex(this.nearbyVertices, (r) => {
                return r.id == that.form.id;
            })
        },
        vertexName() {
            const vertexConfiguration = _.find(this.form.vertex_configurations, (r) => {
                return r.type == 'vertex_name';
            });
            if(vertexConfiguration) {
                return vertexConfiguration.data;
            } else {
                return null;
            }
        },
        hasCharger() {
            const hasCharger = _.find(this.form.vertex_configurations, (r) => {
                return r.type == 'charger';
            });
            return !!hasCharger;
        },
        vertexConfigurationTypes() {
            const that = this;
            const vertexType = _.find(this.vertexTypes, (r) => {
                return r.id == that.form.vertex_type_id;
            });
            if(vertexType) {
                return vertexType.vertex_configuration_types;
            } else {
                return [];
            }
        },
        allNotAssigned() {
            const notNotAssigned = _.filter(this.vehicleMgmts, (r) => {
                return r.vehicle_status && r.vehicle_status.vehicle_status != 'NOT_ASSIGNED';
            });
            return notNotAssigned.length <= 0;
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
            if(this.isDeploy) {
                if(newVal) {
                    this.onShowModal();
                } else {
                    this.onHideModal();
                }
            }
        },
        'form.vertex_configurations': {
            handler() {
                this.form.name = this.vertexName;
            },
            deep: true
        }
    },
    data() {
        return {
            sending: false,
            showModal: this.value,
            form: {
                id: null,
                region_mgmt_id: null,
                tag: null,
                vertex_type_id: null,
                x_px: null,
                y_px: null,
                x: null,
                y: null,
                enable: 1,
                vertex_configurations: [],
                region_mgmt: {
                    project_id: null
                }
            },
            edgeModal: {
                show: false,
                edge_index: null,
                edge: null,
                project_id: null,
                region_mgmt_id: null
            },
            vertexTypes: [],
            switchAuthorization: 'off',
            mqttCommandForm: {
                target_floor: null
            },
            vehicleMgmts: []
        }
    },
    destroyed() {
        if(this.mqttCommandId) {
            window.Echo.leave(`mqttCommands.${this.mqttCommandId}`);
        }
    },
    methods: {
        async onShowModal() {
            this.resetFormValidate();
            if(!this.isDeploy) {
                this.form = _.cloneDeep(this.vertex);
                if(this.form.elevator_mgmts && this.form.elevator_mgmts.length > 0) {
                    this.$set(this.form, 'elevator_mgmt', this.form.elevator_mgmts[0]);
                } else {
                    this.$set(this.form, 'elevator_mgmt', null);
                }
            } else {
                this.form.id = this.vertex.id;
                await this.fetchData();
            }
            if(this.isDeploy) {
                const that = this;
                window.Echo.private(`vertices.${this.form.id}`).listen('ElevatorMgmtVertexCreated', (res) => {
                    const elevatorMgmtVertex = res.elevatorMgmtVertex;
                    const elevatorMgmt = elevatorMgmtVertex.elevator_mgmt;
                    if(elevatorMgmtVertex.vertex_id == that.form.id) {
                        that.$set(that.form, 'elevator_mgmt', elevatorMgmt);
                    } else {
                        that.$set(that.form, 'elevator_mgmt', null);
                    }
                }).listen('ElevatorMgmtVertexDeleted', (res) => {
                    const elevatorMgmtVertex = res.elevatorMgmtVertex;
                    if(elevatorMgmtVertex.vertex_id == that.form.id) {
                        that.$set(that.form, 'elevator_mgmt', null);
                    }
                }).listen('ElevatorMgmtDeleted', () => {
                    that.$set(that.form, 'elevator_mgmt', null);
                }).listen('ElevatorStatusUpdated', (res) => {
                    let elevatorStatus = res.elevatorStatus;
                    let elevatorMgmt = elevatorStatus.elevator_mgmt;
                    if(elevatorMgmt) {
                        delete elevatorStatus.elevator_mgmt;
                    } else if(that.form.elevator_mgmt) {
                        elevatorMgmt = that.form.elevator_mgmt;
                    } else {
                        return;
                    }
                    elevatorMgmt.elevator_status = elevatorStatus;
                    that.$set(that.form, 'elevator_mgmt', elevatorMgmt);
                }).listen('ParkingLotMgmtUpdated', (res) => {
                    const parkingLotMgmt = res.parkingLotMgmt;
                    if(parkingLotMgmt.vertex_name == that.form.name) {
                        that.$set(that.form, 'parking_lot_mgmt', parkingLotMgmt);
                    } else {
                        that.$set(that.form, 'parking_lot_mgmt', null);
                    }
                }).listen('ParkingLotMgmtDeleted', () => {
                    that.$set(that.form, 'parking_lot_mgmt', null);
                }).listen('ParkingLotStatusUpdated', (res) => {
                    let parkingLotStatus = res.parkingLotStatus;
                    let parkingLotMgmt = parkingLotStatus.parking_lot_mgmt;
                    if(parkingLotMgmt) {
                        delete parkingLotStatus.parking_lot_mgmt;
                    } else if(that.form.parking_lot_mgmt) {
                        parkingLotMgmt = that.form.parking_lot_mgmt;
                    } else {
                        return;
                    }
                    parkingLotMgmt.parking_lot_status = parkingLotStatus;
                    that.$set(that.form, 'parking_lot_mgmt', parkingLotMgmt);
                }).listen('StationMgmtUpdated', (res) => {
                    const stationMgmt = res.stationMgmt;
                    if(stationMgmt.vertex_name == that.form.name) {
                        that.$set(that.form, 'station_mgmt', stationMgmt);
                    } else {
                        that.$set(that.form, 'station_mgmt', null);
                    }
                }).listen('StationMgmtDeleted', () => {
                    that.$set(that.form, 'station_mgmt', null);
                }).listen('StationStatusUpdated', (res) => {
                    let stationStatus = res.stationStatus;
                    let stationMgmt = stationStatus.station_mgmt;
                    if(stationMgmt) {
                        delete stationStatus.station_mgmt;
                    } else if(that.form.station_mgmt) {
                        stationMgmt = that.form.station_mgmt;
                    } else {
                        return;
                    }
                    stationMgmt.station_status = stationStatus;
                    that.$set(that.form, 'station_mgmt', stationMgmt);
                });
                window.Echo.private('vehicleMgmts').listen('VehicleStatusUpdated', (e) => {
                    const vehicleStatus = e.vehicleStatus;
                    const vehicleMgmtIdx = _.findIndex(that.vehicleMgmts, (r) => {
                        return r.vehicle_id == vehicleStatus.vehicle_id;
                    });
                    if(vehicleMgmtIdx != -1) {
                        that.vehicleMgmts[vehicleMgmtIdx].vehicle_status = vehicleStatus;
                    }
                });
            }
        },
        onHideModal() {
            this.resetForm();
            if(this.isDeploy) {
                window.Echo.leave(`vertices.${this.form.id}`);
                window.Echo.leave('vehicleMgmts');
            }
            this.$emit('input', false);
            this.$emit('close');
        },
        async fetchData() {
            try {
                let res = await axios.get(`/api/vertices/${this.form.id}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.vertex;
                    if(data.vertex.elevator_mgmts.length > 0) {
                        this.$set(this.form, 'elevator_mgmt', data.vertex.elevator_mgmts[0]);
                    } else {
                        this.$set(this.form, 'elevator_mgmt', null);
                    }
                    this.vehicleMgmts = data.vehicleMgmts;
                    const elevatorControlAuthorization = data.elevatorControlAuthorization;
                    if(!elevatorControlAuthorization) {
                        return;
                    }
                    let receiveCommand = JSON.parse(elevatorControlAuthorization.receive_command);
                    if(elevatorControlAuthorization && ((elevatorControlAuthorization.mqtt_command_type_id == 31 && receiveCommand.reply.condition == 'accepted') || (elevatorControlAuthorization.mqtt_command_type_id == 32 && receiveCommand.reply.condition != 'accepted'))) {
                        this.switchAuthorization = 'on';
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async submit() {
            const isPass = await this.validate();
            if(!isPass) {
                return;
            }
            if(!this.form.id) {
                this.form.id = this.generateUUID();
                const that = this;
                this.form.edges = _.map(this.form.edges, (r) => {
                    r.start_vertex_id = that.form.id;
                    return r
                });
            }
            this.form.x_px = this.quadrantCoordToImgCoordX(this.form.region_mgmt.resolution, this.form.x, this.form.region_mgmt.origin_x);
            this.form.y_px = this.quadrantCoordToImgCoordY(this.form.region_mgmt.resolution, this.form.y, this.form.region_mgmt.img_height, this.form.region_mgmt.origin_y, this.form.region_mgmt.origin_start_direction);
            this.$emit('update', _.cloneDeep(this.form));
            this.$emit('close');
            this.showModal = false;
        },
        resetForm() {
            this.form = {
                id: null,
                region_mgmt_id: null,
                tag: null,
                vertex_type_id: null,
                x: null,
                y: null,
                x_px: null,
                y_px: null,
                z: 0,
                enable: 1,
                vertex_configurations: [],
                region_mgmt: {
                    project_id: null
                },
                edges: []
            };
            this.resetFormValidate();
        },
        deleteEdgeRow(index) {
            if(!confirm(`確定要刪除第${index + 1}筆資料？`)) {
                return;
            }
            this.form.edges.splice(index, 1);
        },
        addEdge() {
            this.form.edges.push({
                id: this.generateUUID(),
                name: null,
                region_mgmt_id: this.form.region_mgmt_id,
                direction: 0,
                start_vertex_id: this.form.id,
                start_vertex: {
                    name: this.form.name
                },
                end_vertex_id: null,
                end_vertex: {
                    region_mgmt_id: null
                },
                enable: 1,
                edge_configurations: [{
                    id: this.generateUUID(),
                    type: 'connection_region',
                    data: null
                }],
                region_mgmt: {
                    project_id: this.form.region_mgmt.project_id
                }
            });
        },
        async switchVertex(next) {
            let nextId = null;
            let nextVertex = null;
            if(next) {
                nextId = this.nearbyVertices[this.nearbyVertexIdx + 1].id;
                nextVertex = this.nearbyVertices[this.nearbyVertexIdx + 1];
            } else {
                nextId = this.nearbyVertices[this.nearbyVertexIdx - 1].id;
                nextVertex = this.nearbyVertices[this.nearbyVertexIdx - 1];
            }
            this.$emit('update:vertex', nextVertex);
            this.resetForm();
            const that = this;
            this.$nextTick(() => {
                that.onShowModal();
            });
        },
        async showEdgeModal(edge) {
            if(!edge.end_vertex.region_mgmt_id) {
                return;
            }
            const isPass = await this.$validator.validate('custom-0');
            if(!isPass) {
                return;
            }
            edge.start_vertex_id = this.form.id;
            edge.start_vertex.name = this.form.name;
            this.edgeModal.edge = edge;
            this.edgeModal.project_id = this.vertex.region_mgmt.project_id;
            this.edgeModal.region_mgmt_id = this.vertex.region_mgmt_id;
            this.edgeModal.show = true;
        },
        resetEdgeRow(row) {
            const edgeIdx = _.findIndex(this.form.edges, (r) => {
                return r.id == row.id;
            });
            this.form.edges[edgeIdx] = row;
            this.form.edges = _.cloneDeep(this.form.edges);
        },
        async updateEnable() {
            try {
                let res = await axios.patch(`/api/vertices/${this.form.id}`, {
                    enable: this.form.enable
                });
                res = res.data;
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        async submitMqttCommand(mqttCommandTypeId, targetFloor) {
            if(mqttCommandTypeId == 33 && !targetFloor) {
                alert('請先輸入樓層');
                this.$refs.targetFloorInput.focus();
                return;
            }
            try {
                this.sending = true;
                let res = await axios.post(`/api/mqttCommands`, {
                    mqtt_command_type_id: mqttCommandTypeId,
                    target_floor: targetFloor
                });
                res = res.data;
                if(res.status == 0) {
                    if(this.mqttCommandId) {
                        window.Echo.leave(`mqttCommands.${this.mqttCommandId}`);
                    }
                    const data = res.data;
                    this.mqttCommandId = data.mqttCommand.id;
                    window.Echo.private(`mqttCommands.${this.mqttCommandId}`).listen('MqttCommandUpdated', (e) => {
                        const that = this;
                        const receiveCommand = JSON.parse(e.mqttCommand.receive_command);
                        const typeName = receiveCommand.typename;
                        const condition = receiveCommand.reply.condition;
                        const description = receiveCommand.reply.description;
                        if(condition == 'accepted') {
                            that.$toast.success({
                                title: '成功訊息',
                                message: description
                            });
                        } else {
                            if(typeName == 'get_elevator_control_authorization') {
                                that.switchAuthorization = 'off';
                            } else if(typeName == 'release_elevator_control_authorization') {
                                that.switchAuthorization = 'on';
                            }
                            that.$toast.error({
                                title: '錯誤訊息',
                                message: description
                            });
                        }
                        that.sending = false;
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        onSwitchButtonChange(event) {
            const value = event.value;
            if(value) {
                this.submitMqttCommand(31);
            } else {
                this.submitMqttCommand(32);
            }
        }
    }
}
</script>

<style scoped>
table tbody{
    display: block;
}
table thead, tbody tr{
    display:      table;
    width:        100%;
    table-layout: fixed;
}
.glyphicon-plus-sign{
    font-size: 31px;
}
.toggle-btn{
    margin-top: 5px;
}
.booking-sign{
    width:         120px;
    height:        60px;
    border:        grey 1px solid;
    border-radius: 5px 5px 5px 5px;
    margin:        0 0 0 3px;
}
.booking-tag{
    width:      100%;
    text-align: center;
    color:      white;
    align-self: end;
}
.booking{
    background-color: green !important;
    border-radius:    5px 5px 0 0;
}
.none-booking{
    background-color: grey;
    border-radius:    0 0 5px 5px;
    margin:           30px 0 0 0;
}
.full-line{
    min-width:   100%;
    min-height:  21.2px;
    font-weight: normal;
}
.display-select{
    appearance:       none;
    background-color: transparent;
    border-color:     transparent;
    color:            black;
    cursor:           unset;
    font-weight:      bold;
    font-size:        12pt;
    margin:           2px 0 0 -15px;
    user-select:      text;
}
.cursor-pointer{
    cursor: pointer;
}
.ml-15{
    margin-left: 15px;
}
.switch-btn{
    margin-top: 10px;
}
.border-gray{
    border: 1px solid #adadad;
}
.border-gray:focus{
    outline: none !important;
    border:  1px solid #adadad;
}
.input-target-floor{
    font-size:   16px;
    font-weight: normal;
    height:      30px;
    width:       120px;
    text-align:  center;
    position:    relative;
    top:         2px;
}
.close-btn, .input-target-floor, .elevator-btn{
    border-radius: 50%;
    width:         42px;
    height:        42px;
    padding:       5px;
}
.elevator-btn{
    position: relative;
}
.fa-arrow-up{
    font-size: 14px;
    position:  absolute;
    top:       12px;
    left:      6px;
}
.fa-arrow-down{
    font-size: 14px;
    position:  absolute;
    top:       13px;
    left:      19px;
}
.fa-play{
    font-size: 6px;
}
.elevator-command{
    margin: 5px 0 0 -6px;
    height: 44px;
}
</style>
