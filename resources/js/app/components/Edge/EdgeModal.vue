<template>
    <div>
        <template v-if="isDeploy && showModal">
            <div style="position: absolute; top: 0; left: 0; min-width: 450px; max-width: 450px">
                <div class="box box-default" style="margin-bottom: 0">
                    <div class="box-header with-border">
                        <h3 class="box-title">軌道資訊</h3>
                    </div>
                    <div class="box-body" style="display: block">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">名稱：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.name }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">起點：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.start_vertex.name }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">終點：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.end_vertex.name }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">方向：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <p class="form-control-static">{{ form.direction ? '有方向' : '無方向' }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">啟用狀態：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <input v-model.number.lazy="form.enable" :true-value="1" :false-value="0" type="checkbox" class="form-control-static" name="enable" @change="updateEnable">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" style="font-size: 12pt">自訂屬性：</label>
                                <div class="col-md-9" style="font-size: 12pt">
                                    <div v-for="(row, index) in form.edge_configurations" :key="row.id">
                                        <span style="float: left">{{ row.type }}：</span>
                                        <span style="float: left">
                                            <template v-if="row.type == 'rail_switch'">
                                                <div>
                                                    <span class="text-right" style="font-size: 12pt">switch:</span>
                                                    <span style="font-size: 12pt; padding-left: 0;">{{ row.data.switch }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-right" style="font-size: 12pt">angle:</span>
                                                    <span style="font-size: 12pt; padding-left: 0;">{{ row.data.angle }}</span>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div>
                                                    <span style="font-size: 12pt; padding-left: 0;">{{ row.data }}</span>
                                                </div>
                                            </template>
                                        </span>
                                        <div class="clearfix"/>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.door_mgmt" class="form-group" style="border-top: 1px solid lightgrey">
                                <label class="col-md-12" style="margin-top: 10px; padding-left: 18px; padding-right: 10px"> 自動門狀態</label>
                                <div class="col-md-3 btn-group-vertical" style="margin-top: 36px; padding-left: 26px">
                                    <switch-button v-if="form.door_mgmt.door_status" v-model="form.door_mgmt.door_status.door_status_ctrl" :labels="{checked: '開門', unchecked: '關門'}" :font-size="15" :width="75" :height="33" :disabled="sending || !form.door_mgmt.enable" @change="(event)=>onSwitchButtonChange(form.door_mgmt.door_id, event)"/>
                                </div>
                                <div class="col-md-9" style="font-size: 16px; line-height: 1.9">
                                    <div class="col-md-12">
                                        <label class="full-line">自動門編號：<span> {{ form.door_mgmt.door_id }}</span></label>
                                        <label class="full-line">自動門狀態：
                                            <template v-if="form.door_mgmt.door_status">
                                                <span v-if="form.door_mgmt.door_status.door_status == 'open'">已開門</span>
                                                <span v-else-if="form.door_mgmt.door_status.door_status == 'closed'">已關門</span>
                                                <span v-else-if="form.door_mgmt.door_status.door_status == 'disabled'">已禁用</span>
                                                <span v-else-if="form.door_mgmt.door_status.door_status == 'disconnected'">已離線</span>
                                            </template>
                                        </label>
                                        <label class="full-line">接收時間&emsp;：
                                            <template>
                                                <span v-if="form.door_mgmt.door_status">{{ form.door_mgmt.door_status.update_ts | datetime }}</span>
                                            </template>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <modal v-else v-model="showModal" size="lg" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
            <div v-if="form.id" slot="title">更新軌道</div>
            <div v-else slot="title">創建軌道</div>
            <div slot="default">
                <form novalidate class="form-horizontal" @submit.prevent="submit">
                    <div class="form-group">
                        <label class="col-md-3 control-label">名稱<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <p class="form-control-static">{{ form.name }}</p>
                            <input type="hidden" name="name" v-model="form.name" v-validate="{
                                required: true,
                                edge_duplicate: form
                            }">
                            <span v-show="errors.has('name:edge_duplicate')" class="help is-danger">軌道名稱已重複</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">起點<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <p v-if="vertexTypeId == 2" class="form-control-static">{{ form.start_vertex.name }}</p>
                            <vertex-list-select v-else
                                                v-model="form.start_vertex_id"
                                                v-validate="'required'"
                                                name="start_vertex_id"
                                                :allow-clear="true"
                                                :region-mgmt-id="form.region_mgmt_id"
                                                :filter-value-columns="[form.end_vertex_id]"
                                                :vertex-type-id="vertexTypeId"
                                                :vertex-name.sync="form.start_vertex.name"
                                                :items="vertices"/>
                            <span v-show="errors.has('start_vertex_id:required')" class="help is-danger">請選擇起點</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">終點<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <vertex-list-select v-model="form.end_vertex_id"
                                                v-validate="'required'"
                                                name="end_vertex_id"
                                                :allow-clear="true"
                                                :region-mgmt-id="form.end_vertex.region_mgmt_id"
                                                :filter-value-columns="[form.start_vertex_id]"
                                                :vertex-type-id="vertexTypeId"
                                                :vertex-name.sync="form.end_vertex.name"
                                                :items="vertices"/>
                            <span v-show="errors.has('end_vertex_id:required')" class="help is-danger">請選擇終點</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">方向<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <select class="form-control" v-model="form.direction">
                                <option :value="0">無方向&nbsp;&nbsp;&#8596;</option>
                                <option :value="1">有方向&nbsp;&nbsp;&#8594;</option>
                            </select>
                        </div>
                    </div>
                    <edge-configuration-list-view v-model="form.edge_configurations"
                                                  :vertex-type-id="vertexTypeId"
                                                  :project-id="projectId"
                                                  :region-mgmt-id="regionMgmtId"/>
                    <div class="text-right">
                        <button class="btn btn-light-green">送出</button>
                        <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                    </div>
                </form>
            </div>
        </modal>
    </div>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';
import EdgeConfigurationTypeSelect from '../EdgeConfigurationType/EdgeConfigurationTypeSelect.vue';
import _ from 'lodash';
import VertexListSelect from '../Vertex/VertexListSelect.vue';
import EdgeConfigurationListView from '../EdgeConfiguration/EdgeConfigurationListView.vue';
import SwitchButton from '../VertexConfiguration/SwitchButton.vue';

export default {
    name: "EdgeModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {
        SwitchButton,
        EdgeConfigurationListView,
        VertexListSelect,
        ClipLoader,
        EdgeConfigurationTypeSelect
    },
    props: {
        value: {
            type: Boolean,
            default: false
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        vertexTypeId: {
            type: Number
        },
        vertices: {
            type: Array,
            default() {
                return [];
            }
        },
        edge: {
            type: Object
        },
        projectId: [Number, String],
        regionMgmtId: [Number, String]
    },
    computed: {
        hasConnectionRegion() {
            const hasConnectionRegion = _.find(this.form.edge_configurations, (r) => {
                return r.type == 'connection_region';
            });
            return !!hasConnectionRegion;
        },
        name() {
            if(this.form.start_vertex.name && this.form.end_vertex.name) {
                this.form.name = `(${this.form.start_vertex.name},${this.form.end_vertex.name})`
            } else {
                this.form.name = null;
            }
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
        'form.start_vertex_id': {
            handler(newVal) {
                if(newVal == this.form.end_vertex_id) {
                    this.form.end_vertex_id = null;
                }
            },
            deep: true
        },
        'form.end_vertex_id': {
            handler(newVal) {
                if(newVal == this.form.start_vertex_id) {
                    this.form.start_vertex_id = null;
                }
            },
            deep: true
        },
        name(newVal) {
            this.form.name = newVal;
        }
    },
    data() {
        return {
            sending: false,
            showModal: this.value,
            form: {
                id: null,
                name: null,
                region_mgmt_id: null,
                direction: 0,
                start_vertex_id: null,
                start_vertex: {
                    name: null
                },
                end_vertex_id: null,
                end_vertex: {
                    region_mgmt_id: null,
                    name: null
                },
                enable: 1,
                edge_configurations: [],
                region_mgmt: {
                    project_id: null
                }
            },
            mqttCommandId: null
        }
    },
    destroyed() {
        if(this.isDeploy) {
            window.Echo.leave(`edges.${this.form.id}`);
        }
        if(this.mqttCommandId) {
            window.Echo.leave(`mqttCommands.${this.mqttCommandId}`);
        }
    },
    methods: {
        async onShowModal() {
            this.resetFormValidate();
            if(!this.isDeploy) {
                this.form = _.cloneDeep(this.edge);
            } else {
                this.form.id = this.edge.id;
                await this.fetchData();
            }
            if(this.isDeploy) {
                const that = this;
                window.Echo.private(`edges.${this.form.id}`).listen('DoorMgmtUpdated', (res) => {
                    const autoDoor = _.find(that.form.edge_configurations, ['type', 'auto_door']);
                    if(!autoDoor) {
                        return;
                    }
                    const doorMgmt = res.doorMgmt;
                    if(doorMgmt && doorMgmt.door_status) {
                        doorMgmt.door_status.door_status_ctrl = (doorMgmt.door_status.door_status == 'open') ? 'on' : 'off';
                    }
                    if(doorMgmt.edge_id == that.form.id) {
                        that.$set(that.form, 'door_mgmt', doorMgmt);
                    } else {
                        that.$set(that.form, 'door_mgmt', null);
                    }
                }).listen('DoorStatusUpdated', (res) => {
                    let doorStatus = res.doorStatus;
                    let doorMgmt = doorStatus.door_mgmt;
                    if(doorMgmt) {
                        delete doorStatus.door_mgmt;
                    } else if(that.form.door_mgmt) {
                        doorMgmt = that.form.door_mgmt;
                    } else {
                        return;
                    }
                    doorMgmt.door_status = doorStatus;
                    doorMgmt.door_status.door_status_ctrl = (doorMgmt.door_status.door_status == 'open') ? 'on' : 'off';
                    that.$set(that.form, 'door_mgmt', doorMgmt);
                });
            }
        },
        onHideModal() {
            this.resetForm();
            if(this.isDeploy) {
                window.Echo.leave(`edges.${this.form.id}`);
            }
            this.$emit('input', false);
            this.$emit('close');
        },
        async fetchData() {
            try {
                let res = await axios.get(`/api/edges/${this.form.id}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const edge = _.cloneDeep(data.edge);
                    if(edge.door_mgmt && edge.door_mgmt.door_status) {
                        edge.door_mgmt.door_status.door_status_ctrl = (edge.door_mgmt.door_status.door_status == 'open') ? 'on' : 'off';
                    }
                    this.form = edge;
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
            }
            this.$emit('update', this.form);
            this.$emit('close');
            this.showModal = false;
        },
        resetForm() {
            this.form = {
                id: null,
                name: null,
                region_mgmt_id: null,
                direction: 0,
                start_vertex_id: null,
                end_vertex_id: null,
                start_vertex: {
                    name: null
                },
                end_vertex: {
                    region_mgmt_id: null,
                    name: null
                },
                enable: 1,
                edge_configurations: [],
                region_mgmt: {
                    project_id: null
                }
            };
            this.resetFormValidate();
        },
        addEdgeConfigure() {
            this.form.edge_configurations.push({
                id: null,
                type: null,
                data: null
            });
        },
        initEdgeDefault(row) {
            if(row.type == 'rail_switch') {
                row.data = {
                    switch: null,
                    angle: null
                };
            } else {
                row.data = null;
            }
        },
        deleteRow(vertexConfiguration, index) {
            if(!confirm(`確定要刪除第「${index + 1}」筆資料？`)) {
                return;
            }
            this.form.edge_configurations.splice(index, 1);
        },
        async updateEnable() {
            let res = await axios.patch(`/api/edges/${this.form.id}`, {
                enable: this.form.enable
            });
            res = res.data;
            if(res.status == 0) {
                this.$emit('update', this.form);
            }
        },
        async submitMqttCommand(doorId, action) {
            try {
                this.sending = true;
                let res = await axios.post(`/api/mqttCommands`, {
                    mqtt_command_type_id: 30,
                    door_id: doorId,
                    action: action,
                    region_mgmt_id: this.form.region_mgmt_id
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
                        const condition = receiveCommand.reply.condition;
                        const description = receiveCommand.reply.description;
                        if(condition == 'accepted') {
                            that.$toast.success({
                                title: '成功訊息',
                                message: description
                            });
                        } else {
                            that.form.door_mgmt.door_status.door_status_ctrl = (action == 'open') ? 'off' : 'on';
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
        onSwitchButtonChange(doorId, event) {
            const value = event.value;
            if(value) {
                this.submitMqttCommand(doorId, 'open');
            } else {
                this.submitMqttCommand(doorId, 'close');
            }
        }
    }
}
</script>

<style scoped>
.full-line{
    min-width:   100%;
    min-height:  19px;
    font-weight: normal;
}
</style>
