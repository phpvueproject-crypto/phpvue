<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ objUid ? '更新載具' : '創建載具' }}</div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">裝置編號<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="obj_id" v-model="form.obj_id" v-validate="'required'" placeholder="請填寫裝置編號" @input="idDuplicate = false" :disabled="objUid">
                        <span v-show="errors.has('obj_id:required')" class="help is-danger">請填寫裝置編號</span>
                        <span v-show="idDuplicate" class="help is-danger">該裝置編號已存在！</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">設備類別<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <object-class-select v-model="form.object_class" v-validate="'required'" name="object_class" class="form-control" :disabled="objUid"/>
                        <span v-show="errors.has('object_class:required')" class="help is-danger">請選擇設備類別</span>
                    </div>
                </div>
                <template v-if="form.object_class == 'AMDR'">
                    <div class="form-group">
                        <label class="col-md-3 control-label">速度高標值<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.vehicle_mgmt.high_speed" name="high_speed" v-validate="'decimal|min_value:0'" placeholder="請填寫速度高標值">
                                <div class="input-group-addon">m/s</div>
                            </div>
                            <span v-show="errors.has(`high_speed:decimal`)" class="help is-danger">請填寫數值</span>
                            <span v-show="errors.has(`high_speed:min_value`)" class="help is-danger">請填寫正值</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">速度常標值<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.vehicle_mgmt.normal_speed" name="normal_speed" v-validate="'decimal|min_value:0'" placeholder="請填寫速度常標值">
                                <div class="input-group-addon">m/s</div>
                            </div>
                            <span v-show="errors.has(`normal_speed:decimal`)" class="help is-danger">請填寫數值</span>
                            <span v-show="errors.has(`normal_speed:min_value`)" class="help is-danger">請填寫正值</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">速度低標值<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input class="form-control" v-model.number="form.vehicle_mgmt.low_speed" name="low_speed" v-validate="'decimal|min_value:0'" placeholder="請填寫速度低標值">
                                <div class="input-group-addon">m/s</div>
                            </div>
                            <span v-show="errors.has(`low_speed:decimal`)" class="help is-danger">請填寫數值</span>
                            <span v-show="errors.has(`low_speed:min_value`)" class="help is-danger">請填寫正值</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">車輛MAC<span class="is-hidden">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" v-model="form.vehicle_mgmt.macaddr" name="macaddr" placeholder="請填寫車輛MAC" v-validate="{regex: /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/}">
                            <span v-if="errors.has('macaddr:regex')" class="help is-danger">請填寫有效的MAC Address</span>
                            <span v-else-if="macaddrDuplicate" class="help is-danger">本組MAC已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">車輛IP<span class="is-hidden">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.vehicle_mgmt.ipaddr" v-validate="{regex: /(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))(\/([0-9]|[12][0-9]|3[0-2]))?$/}" class="form-control" name="ipaddr" placeholder="請填寫車輛IP">
                            <span v-if="errors.has('ipaddr:regex')" class="help is-danger">請填寫有效的IP</span>
                            <span v-else-if="ipaddrDuplicate" class="help is-danger">本組IP已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">充電功能<span class="is-hidden">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.vehicle_mgmt.chargeable" type="checkbox" name="chargeable" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">指令類型<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">
                                <input v-model="form.vehicle_mgmt.assign_switch" type="checkbox" id="AUTO" true-value="AUTO" :false-value="null"/>
                                <label for="AUTO"><span class="checkbox-label-text">自動</span></label>&emsp;
                                <input v-model="form.vehicle_mgmt.assign_switch" type="checkbox" id="EXPLICIT" true-value="EXPLICIT" :false-value="null"/>
                                <label for="EXPLICIT"><span class="checkbox-label-text">精確</span></label>&emsp;
                                <input v-model="form.vehicle_mgmt.assign_switch" type="checkbox" id="MANUAL" true-value="MANUAL" :false-value="null"/>
                                <label for="MANUAL"><span class="checkbox-label-text">手動</span></label>&emsp;
                                <input v-model="form.vehicle_mgmt.assign_switch" type="checkbox" id="DISABLE" true-value="DISABLE" :false-value="null"/>
                                <label for="DISABLE"><span class="checkbox-label-text">禁用</span></label>&emsp;
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">車輛類型<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.vehicle_mgmt.vehicle_type" v-validate="'required'" name="vehicle_type" class="form-control" placeholder="請填寫車輛類型">
                            <span v-show="errors.has('vehicle_type:required')" class="help is-danger">請填寫車輛類型</span>
                        </div>
                    </div>
                    <div v-if="form.obj_uid" class="form-group">
                        <label class="col-md-3 control-label">車輛位置<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9" style="margin-top: 4px">
                            <template v-if="form.vehicle_mgmt && form.vehicle_mgmt.vehicle_status">
                                {{ form.vehicle_mgmt.vehicle_status.vehicle_location_x }}<span v-show="form.vehicle_mgmt.vehicle_status.vehicle_location_y">, </span>{{ form.vehicle_mgmt.vehicle_status.vehicle_location_y }}
                            </template>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">UI停止秒數<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.vehicle_mgmt.stoppable_second" v-validate="'required|numeric'" name="stoppable_second" class="form-control" placeholder="請填寫UI停止秒數">
                            <span v-show="errors.has('stoppable_second:required')" class="help is-danger">請填寫UI停止秒數</span>
                            <span v-show="errors.has('stoppable_second:numeric')" class="help is-danger">請填寫數字(整數)</span>
                        </div>
                    </div>
                </template>
                <template v-else-if="form.object_class == 'DOOR'">
                    <div class="form-group">
                        <label class="col-md-3 control-label">軌道名稱<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <edge-list-select v-model="form.door_mgmt.edge_id"
                                              v-validate="{
                                                required: true
                                              }"
                                              :is-deploy="1"
                                              name="edge_id"
                                              select2-class="form-control"
                                              placeholder="請選擇軌道名稱"/>
                            <span v-show="errors.has('edge_id:required')" class="help is-danger">請選擇軌道名稱</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">首選車輛<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <vehicle-mgmt-list-select v-model="doorMgmtPreferVehicles"
                                                      placeholder="請選擇首選車輛"
                                                      :multiple="true"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">MAC<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.door_mgmt.macaddr" v-validate="{regex: /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/, required: true}" class="form-control" name="macaddr" placeholder="請填寫MAC">
                            <span v-if="errors.has('macaddr:regex')" class="help is-danger">請填寫有效的MAC Address</span>
                            <span v-else-if="macaddrDuplicate" class="help is-danger">本組MAC已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">IP<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="form.door_mgmt.ipaddr" v-validate="{regex: /(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))(\/([0-9]|[12][0-9]|3[0-2]))?$/, required: true}" class="form-control" name="ipaddr" placeholder="請填寫IP">
                            <span v-if="errors.has('ipaddr:regex')" class="help is-danger">請填寫有效的IP Address</span>
                            <span v-else-if="ipaddrDuplicate" class="help is-danger">本組IP已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">啟用狀態<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input v-model="form.door_mgmt.enable" type="checkbox" class="form-control-static">
                        </div>
                    </div>
                </template>
                <template v-else-if="form.object_class == 'STATION'">
                    <div class="form-group">
                        <label class="col-md-3 control-label">站點名稱<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <vertex-list-select v-model="form.station_mgmt.vertex_id"
                                                v-validate="{
                                                    required: true,
                                                    vertex_deploy: form.station_mgmt && form.station_mgmt.vertex && form.station_mgmt.vertex.id ? form.station_mgmt.vertex : false
                                                }"
                                                name="vertex_id"
                                                value-column="id"
                                                :vertex-type-id="4"
                                                :device-name.sync="form.station_mgmt.device_name"
                                                :allow-clear="true"
                                                :is-deploy="1"/>
                            <span v-if="errors.has('vertex_name:required')" class="help is-danger">請選擇站點名稱</span>
                            <span v-if="errors.has('vertex_name:vertex_deploy')" class="help is-danger">該站點尚未被燒入</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">裝置名稱<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">
                                {{ form.station_mgmt.device_name }}
                                <span v-show="form.station_mgmt.vertex_name && !form.station_mgmt.device_name" class="help is-danger" style="position: relative; top: -3px">此站點尚未設定裝置名稱！</span>
                                <input v-model="form.station_mgmt.device_name" v-validate="`${form.station_mgmt.vertex_name ? 'required' : ''}`" name="device_name" type="hidden">
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">群組<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">
                                <input v-model="form.station_mgmt.station_group" type="checkbox" id="red" value="Red">
                                <label for="red"><span class="checkbox-label-text">紅線</span></label>&emsp;
                                <input v-model="form.station_mgmt.station_group" type="checkbox" id="blue" value="Blue"/>
                                <label for="blue"><span class="checkbox-label-text">藍線</span></label>&emsp;
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">屬性<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">
                                <input v-model="form.station_mgmt.bypass" type="checkbox" id="pass" :true-value="true" :false-value="false"/>
                                <label for="pass"><span class="checkbox-label-text">不一定過站</span></label>&emsp;
                                <input v-model="form.station_mgmt.bypass" type="checkbox" id="normal" :true-value="false" :false-value="true"/>
                                <label for="normal"><span class="checkbox-label-text">必過站</span></label>&emsp;
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">啟用狀態<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input v-model="form.station_mgmt.enable" type="checkbox" class="form-control-static">
                        </div>
                    </div>
                </template>
                <template v-else-if="form.object_class == 'ELEVATOR'">
                    <div class="form-group">
                        <label class="col-md-3 control-label">站點名稱<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <vertex-list-select v-model="form.elevator_mgmt.vertices"
                                                v-validate="{
                                                    required: true,
                                                    vertex_deploy: form.elevator_mgmt && form.elevator_mgmt.vertex && form.elevator_mgmt.vertex.id ? form.elevator_mgmt.vertex : false
                                                }"
                                                name="vertices"
                                                :multiple="true"
                                                :vertex-type-id="2"
                                                :allow-clear="true"
                                                :is-deploy="1"/>
                            <span v-if="errors.has('vertices:required')" class="help is-danger">請選擇站點</span>
                            <span v-if="errors.has('vertices:vertex_deploy')" class="help is-danger">該站點尚未被燒入</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">特定車輛<span class="is-danger">&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <vehicle-mgmt-list-select v-model="elevatorMgmtPreferVehicles"
                                                      placeholder="請選擇特定車輛"
                                                      :multiple="true"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">MAC<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input class="form-control" v-model="form.elevator_mgmt.macaddr" name="elevatorMgmtMacaddr" placeholder="請填寫MAC" v-validate="{regex: /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/}">
                            <span v-if="errors.has('elevatorMgmtMacaddr:regex')" class="help is-danger">請填寫有效的MAC Address</span>
                            <span v-else-if="macaddrDuplicate" class="help is-danger">本組MAC已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">IP<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input v-model="form.elevator_mgmt.ipaddr" v-validate="{regex: /(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))(\/([0-9]|[12][0-9]|3[0-2]))?$/}" class="form-control" name="elevatorMgmtIpaddr" placeholder="請填寫IP">
                            <span v-if="errors.has('elevatorMgmtIpaddr:regex')" class="help is-danger">請填寫有效的IP Address</span>
                            <span v-else-if="ipaddrDuplicate" class="help is-danger">本組IP已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">啟用狀態<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">
                                <input type="checkbox" v-model="form.elevator_mgmt.enable"/>
                            </p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="form-group">
                        <label class="col-md-3 control-label">MAC<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input v-model="form.equipment_mgmt.macaddr" v-validate="{regex: /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/}" class="form-control" name="macaddr" placeholder="請填寫MAC">
                            <span v-if="errors.has('macaddr:regex')" class="help is-danger">請填寫有效的MAC Address</span>
                            <span v-else-if="macaddrDuplicate" class="help is-danger">本組MAC已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">IP<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input v-model="form.equipment_mgmt.ipaddr" v-validate="{regex: /(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))(\/([0-9]|[12][0-9]|3[0-2]))?$/}" class="form-control" name="ipaddr" placeholder="請填寫IP">
                            <span v-if="errors.has('ipaddr:regex')" class="help is-danger">請填寫有效的IP Address</span>
                            <span v-else-if="ipaddrDuplicate" class="help is-danger">本組IP已被綁定在車輛編號「{{ duplicateVehicleId }}」！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">啟用狀態<span>&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <input v-model="form.equipment_mgmt.enable" type="checkbox" class="form-control-static">
                        </div>
                    </div>
                </template>
                <div class="form-group">
                    <label class="col-md-3 control-label">供應商<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <vendor-mgmt-select v-model="form.vendor" v-validate="'required'" name="vendor"/>
                        <span v-show="errors.has('vendor:required')" class="help is-danger">請選擇供應商</span>
                    </div>
                </div>
                <div class="text-right">
                    <button class="btn btn-light-green" :disabled="sending">送出</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
        </div>
    </modal>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import ObjectClassSelect from '../ObjectClass/ObjectClassSelect';
import VendorMgmtSelect from '../VendorMgmt/VendorMgmtSelect';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect';
import EdgeListSelect from '../Edge/EdgeListSelect.vue';
import VertexListSelect from '../Vertex/VertexListSelect.vue';

export default {
    name: "ObjectMgmtModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        ClipLoader,
        ObjectClassSelect,
        VendorMgmtSelect,
        VehicleMgmtListSelect,
        EdgeListSelect,
        VertexListSelect
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        objUid: {
            type: String
        }
    },
    computed: {
        doorMgmtPreferVehicles: {
            get() {
                if(!this.form.door_mgmt || !this.form.door_mgmt.prefer_vehicle) {
                    return [];
                }
                return this.getPreferVehiclesArray(this.form, 'door_mgmt');
            },
            set(newVal) {
                const preferVehicles = _.map(newVal, (r) => {
                    return r.vehicle_id;
                }).toString();
                this.$set(this.form.door_mgmt, 'prefer_vehicle', preferVehicles);
            }
        },
        elevatorMgmtPreferVehicles: {
            get() {
                if(!this.form.elevator_mgmt || !this.form.elevator_mgmt.prefer_vehicle) {
                    return [];
                }
                return this.getPreferVehiclesArray(this.form, 'elevator_mgmt');
            },
            set(newVal) {
                const preferVehicles = _.map(newVal, (r) => {
                    return r.vehicle_id;
                }).toString();
                this.$set(this.form.elevator_mgmt, 'prefer_vehicle', preferVehicles);
            }
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        },
        'form.object_class': {
            handler() {
                this.resetFormValidate();
            },
            deep: true
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {},
            idDuplicate: false,
            ipaddrDuplicate: false,
            macaddrDuplicate: false,
            duplicateVehicleId: null
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/objectMgmts/${this.objUid}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const objectMgmt = data.objectMgmt;
                    if(objectMgmt.station_mgmt) {
                        if(!objectMgmt.station_mgmt.station_group) {
                            objectMgmt.station_mgmt.station_group = [];
                        } else {
                            objectMgmt.station_mgmt.station_group = objectMgmt.station_mgmt.station_group.split(',');
                        }
                    }
                    if(!objectMgmt.elevator_mgmt) {
                        objectMgmt.elevator_mgmt = {vertices: []};
                    }
                    this.form = objectMgmt;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async onShowModal() {
            this.resetForm();
            if(this.objUid) {
                this.loading = true;
                await this.fetchData();
            }
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }

                const form = _.cloneDeep(this.form);
                if(form.station_mgmt) {
                    form.station_mgmt.station_group = form.station_mgmt.station_group.toString();
                }

                this.sending = true;
                let res = null;
                if(!this.objUid) {
                    res = await axios.post(`/api/objectMgmts`, form);
                } else {
                    res = await axios.patch(`/api/objectMgmts/${this.objUid}`, form);
                }
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.objectMgmt;
                    if(!this.form.elevator_mgmt) {
                        this.form.elevator_mgmt = {vertices: []};
                    }
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.objUid ? '創建' : '更新'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                } else if(res.status == -7) {
                    const data = res.data;
                    if(data.column == 'obj_id') {
                        this.idDuplicate = true;
                        this.scrollTo('obj_id');
                    } else if(data.column == 'ipaddr') {
                        this.ipaddrDuplicate = true;
                        this.duplicateVehicleId = data.vehicle_id;
                        this.scrollTo('ipaddr');
                    } else if(data.column == 'macaddr') {
                        this.macaddrDuplicate = true;
                        this.duplicateVehicleId = data.vehicle_id;
                        this.scrollTo('macaddr');
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                obj_id: null,
                object_class: null,
                vendor: null,
                vehicle_mgmt: {
                    low_speed: 0,
                    normal_speed: 0,
                    high_speed: 0,
                    macaddr: null,
                    ipaddr: null,
                    chargeable: 0,
                    vehicle_type: null,
                    assign_switch: 'AUTO',
                    group: [],
                    stoppable_second: 5
                },
                door_mgmt: {
                    edge_id: null,
                    prefer_vehicle: null,
                    macaddr: null,
                    ipaddr: null,
                    enable: true
                },
                station_mgmt: {
                    vertex_id: null,
                    device_name: null,
                    station_group: [],
                    bypass: false,
                    enable: true
                },
                elevator_mgmt: {
                    elevator_id: null,
                    vertices: [],
                    prefer_vehicle: null,
                    macaddr: null,
                    ipaddr: null,
                    enable: true
                },
                equipment_mgmt: {
                    equipment_id: null,
                    enable: true,
                    macaddr: null,
                    ipaddr: null
                }
            };
            this.idDuplicate = false;
            this.ipaddrDuplicate = false;
            this.macaddrDuplicate = false;
            this.duplicateVehicleId = null;
            this.resetFormValidate();
        },
        getPreferVehiclesArray(objectMgmt, table) {
            let preferVehicleIds = objectMgmt[table].prefer_vehicle.split(',');
            return _.map(preferVehicleIds, (r) => {
                return {
                    vehicle_id: r
                }
            });
        }
    }
}
</script>

<style scoped>
hr{
    margin-bottom: 20px;
}
.checkbox-label-text{
    position: relative;
    top:      -1px;
    cursor:   pointer;
}
</style>
