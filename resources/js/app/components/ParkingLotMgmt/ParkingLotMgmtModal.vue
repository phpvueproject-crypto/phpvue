<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ pid ? '修改停車位' : '創建停車位' }}</div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">停車位編號<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="parking_lot_id" v-model="form.parking_lot_id" v-validate="'required'" placeholder="請填寫停車位編號" :disabled="pid">
                        <span v-show="errors.has('parking_lot_id:required')" class="help is-danger">請填寫停車位編號</span>
                        <span v-show="parkingLotIdDuplicate" class="help is-danger">該停車位編號已存在！</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">專案<span class="is-danger">&nbsp;&nbsp;&nbsp;</span></label>
                    <div class="col-md-9">
                        <project-list-select select-class="form-control select-only-read" defaultText="請選擇"
                                             v-model="projectId" :is-deploy="1" :disabled="true"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">區域<span class="is-danger">&nbsp;&nbsp;&nbsp;</span></label>
                    <div class="col-md-9">
                        <region-mgmt-list-select v-model="form.vertex.region_mgmt_id" name="region_mgmt_id" :single-select2="true" :min-num-for-search="5" :disabled="!projectId" :project-id.sync="projectId"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">站點名稱<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <vertex-list-select v-model="form.vertex_id"
                                            v-validate="{
                                                    required: true,
                                                    vertex_deploy: form.vertex && form.vertex.id ? form.vertex : false
                                                }"
                                            name="vertex_id"
                                            :region-mgmt-id="form.vertex.region_mgmt_id"
                                            :vertex-name.sync="form.vertex_name"
                                            :allow-clear="true"
                                            :is-deploy="1"
                                            :disabled="!form.vertex.region_mgmt_id"/>
                        <span v-if="errors.has('vertex_id:required')" class="help is-danger">{{ (form.vertex && form.vertex.region_mgmt_id) ? '請選擇站點名稱' : '請先選擇區域，再選擇站點名稱' }}</span>
                        <span v-else-if="errors.has('vertex_id:vertex_deploy')" class="help is-danger">該站點尚未被燒入</span>
                        <span v-else-if="vertexNameDuplicate" class="help is-danger">此站點名稱已綁定於其他停車位！</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">首選車輛<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <vehicle-mgmt-list-select placeholder="請選擇首選車輛"
                                                  name="parkingLotMgmtPreferVehicleIds"
                                                  v-model="parkingLotMgmtPreferVehicleIds"
                                                  v-validate="'required'"
                                                  :region-mgmt-id="form.vertex.region_mgmt_id"
                                                  :multiple="true"/>
                        <span v-show="errors.has('parkingLotMgmtPreferVehicleIds:required')" class="help is-danger">請選擇首選車輛</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">充電類型<span>&nbsp;&nbsp;</span></label>
                    <div class="col-md-9">
                        <p class="form-control-static">
                            <input v-model="form.attribute" type="checkbox" id="charging" true-value="charging" :false-value="null"/>
                            <label for="charging"><span class="checkbox-label-text">充電站</span></label>&emsp;
                            <input v-model="form.attribute" type="checkbox" id="battery_switch" true-value="battery_switch" :false-value="null"/>
                            <label for="battery_switch"><span class="checkbox-label-text">電池交換站</span></label>&emsp;
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">啟用狀態<span>&nbsp;&nbsp;</span></label>
                    <div class="col-md-9">
                        <p class="form-control-static">
                            <input type="checkbox" v-model="form.enable"/>
                        </p>
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
import ProjectListSelect from '../Project/ProjectListSelect.vue';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect';
import VertexListSelect from '../Vertex/VertexListSelect.vue';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect';

export default {
    name: "ParkingLotMgmtModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        ClipLoader,
        ProjectListSelect,
        RegionMgmtListSelect,
        VertexListSelect,
        VehicleMgmtListSelect
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        pid: {
            type: String,
            default: null
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        },
        parkingLotMgmtPreferVehicleIds(newVal) {
            let preferVehicle = '';
            _.forEach(newVal, (r) => {
                preferVehicle += r.vehicle_id + ',';
            });
            preferVehicle = preferVehicle.slice(0, preferVehicle.length - 1);
            this.form.prefer_vehicle = preferVehicle;
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {},
            parkingLotIdDuplicate: false,
            projectId: null,
            vertexNameDuplicate: false,
            parkingLotMgmtPreferVehicleIds: []
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async onShowModal() {
            this.resetForm();
            if(this.pid) {
                this.loading = true;
                await this.fetchData();
            }
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async fetchData() {
            try {
                let res = await axios.get(`/api/parkingLotMgmts/${this.pid}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const parkingLotMgmt = data.parkingLotMgmt;
                    if(!parkingLotMgmt.vertex) {
                        parkingLotMgmt.vertex = {
                            region_mgmt_id: null
                        };
                    }
                    this.form = parkingLotMgmt;
                    if(data.parkingLotMgmt.prefer_vehicle) {
                        let parkingLotMgmtPreferVehicleIds = data.parkingLotMgmt.prefer_vehicle.split(',');
                        parkingLotMgmtPreferVehicleIds = _.map(parkingLotMgmtPreferVehicleIds, (r) => {
                            return {
                                vehicle_id: r
                            }
                        });
                        this.parkingLotMgmtPreferVehicleIds = parkingLotMgmtPreferVehicleIds;
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }

                if(!this.form.vertex.region_mgmt_id) {
                    this.form.vertex_id = null;
                }

                this.sending = true;
                let res = null;
                if(this.pid) {
                    res = await axios.patch(`/api/parkingLotMgmts/${this.pid}`, this.form);
                } else {
                    res = await axios.post(`/api/parkingLotMgmts`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const parkingLotMgmt = data.parkingLotMgmt;
                    if(!parkingLotMgmt.vertex) {
                        parkingLotMgmt.vertex = {
                            region_mgmt_id: null,
                            region_mgmt: {
                                region: null
                            }
                        };
                    }
                    this.form = parkingLotMgmt;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.pid ? '新增' : '修改'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                } else if(res.status == -7) {
                    this.parkingLotIdDuplicate = true;
                    this.scrollTo('parking_lot_id');
                } else if(res.status == -9) {
                    this.vertexNameDuplicate = true;
                    this.scrollTo('vertex_id');
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                parking_lot_id: null,
                vertex_id: null,
                vertex: {
                    region_mgmt_id: null
                },
                prefer_vehicle: null,
                attribute: null,
                enable: true
            };
            this.parkingLotMgmtPreferVehicleIds = [];
            this.parkingLotIdDuplicate = false;
            this.vertexNameDuplicate = false;
            this.projectId = null;
            this.resetFormValidate();
        }
    }
}
</script>

<style scoped>
.checkbox-label-text{
    font-size:   18px;
    font-weight: normal;
}
</style>
