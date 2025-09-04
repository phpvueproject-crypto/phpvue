<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ eid ? '更新電梯點' : '創建電梯點' }}</div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">電梯點編號<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="id" v-model="form.id" v-validate="'required'" placeholder="請填寫電梯點編號" :disabled="eid">
                        <span v-show="errors.has('id:required')" class="help is-danger">請填寫電梯點編號</span>
                        <span v-show="idDuplicate" class="help is-danger">該電梯點編號已存在！</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">專案&nbsp;&nbsp;&nbsp;</label>
                    <div class="col-md-9">
                        <project-list-select select-class="form-control select-only-read" defaultText="請選擇"
                                             v-model="projectId" :is-deploy="1" :disabled="true"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">區域&nbsp;&nbsp;&nbsp;</label>
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
                        <span v-else-if="vertexNameDuplicate" class="help is-danger">此站點名稱已綁定於其他電梯點！</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">特定車輛<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <vehicle-mgmt-list-select placeholder="請選擇特定車輛"
                                                  name="preferVehicleIds"
                                                  v-model="preferVehicleIds"
                                                  v-validate="'required'"
                                                  :region-mgmt-id="form.vertex.region_mgmt_id"
                                                  :multiple="true"/>
                        <span v-show="errors.has('preferVehicleIds:required')" class="help is-danger">請選擇特定車輛</span>
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
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';
import ProjectListSelect from '../Project/ProjectListSelect.vue';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect';
import _ from 'lodash';
import VertexListSelect from '../Vertex/VertexListSelect.vue';

export default {
    name: "ElevatorMgmtModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        VertexListSelect,
        ClipLoader,
        VehicleMgmtListSelect,
        RegionMgmtListSelect,
        ProjectListSelect
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        eid: {
            type: String,
            default: null
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        },
        preferVehicleIds(newVal) {
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
            idDuplicate: false,
            locationDuplicate: false,
            preferVehicleIds: [],
            vertexNameDuplicate: false,
            projectId: null
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async onShowModal() {
            this.resetForm();
            if(this.eid) {
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
                let res = await axios.get(`/api/elevatorMgmts/${this.eid}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const elevatorMgmt = data.elevatorMgmt;
                    if(!elevatorMgmt.vertex) {
                        elevatorMgmt.vertex = {
                            region_mgmt_id: null
                        };
                    }
                    this.form = elevatorMgmt;
                    if(data.elevatorMgmt.prefer_vehicle) {
                        let preferVehicleIds = data.elevatorMgmt.prefer_vehicle.split(',');
                        preferVehicleIds = _.map(preferVehicleIds, (r) => {
                            return {
                                vehicle_id: r
                            }
                        });
                        this.preferVehicleIds = preferVehicleIds;
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass)
                    return;

                if(!this.form.vertex.region_mgmt_id) {
                    this.form.location = null;
                }

                this.sending = true;
                let res = null;
                if(this.eid) {
                    res = await axios.patch(`/api/elevatorMgmts/${this.eid}`, this.form);
                } else {
                    res = await axios.post(`/api/elevatorMgmts`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const elevatorMgmt = data.elevatorMgmt;
                    if(!elevatorMgmt.vertex) {
                        elevatorMgmt.vertex = {
                            region_mgmt_id: null
                        };
                    }
                    this.form = elevatorMgmt;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.eid ? '新增' : '更新'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                } else if(res.status == -7) {
                    this.idDuplicate = true;
                    this.locationDuplicate = false;
                    this.scrollTo('id');
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
                id: null,
                location: null,
                prefer_vehicle: null,
                vertex: {
                    region_mgmt_id: null
                }
            };
            this.preferVehicleIds = [];
            this.idDuplicate = false;
            this.locationDuplicate = false;
            this.projectId = null;
            this.vertexNameDuplicate = false;
            this.resetFormValidate();
        }
    }
}
</script>

<style scoped>
.img-size{
    margin-right: 16px;
    font-size:    16px;
}
.glyphicon-plus-sign{
    height: 19px;
}
</style>
