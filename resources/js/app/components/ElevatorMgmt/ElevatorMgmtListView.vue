<template>
    <div>
        <section class="content-header clearfix">
            <span :style="`color: ${viewType == 'management' ? '#FFFFFF' : '#000000'}`">{{ viewType == 'management' ? '電梯點管理' : '電梯點狀態' }}&emsp;</span>
            <button v-if="viewType == 'management'" class="btn btn-light-green op-btn" @click="showModal(null)">
                <i class="fa fa-plus" aria-hidden="true"/>&nbsp;創建電梯點
            </button>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table">
                                    <thead class="table-head">
                                    <tr>
                                        <th class="text-center hide-td" colspan="6">電梯點資訊</th>
                                        <th class="text-center hide-td" colspan="4">即時狀態</th>
                                        <th class="text-center hide-td operate-td" rowspan="2">操作</th>
                                    </tr>
                                    <tr>
                                        <!--電梯點資訊-->
                                        <th class="text-center hide-td">電梯點編號</th>
                                        <th class="text-center hide-td">電梯點位置</th>
                                        <th class="text-center hide-td">特定車輛</th>
                                        <th class="text-center hide-td">啟用狀態</th>
                                        <th class="text-center hide-td">電梯MAC</th>
                                        <th class="text-center hide-td">電梯IP</th>
                                        <!--即時狀態-->
                                        <th class="text-center hide-td">狀態</th>
                                        <th class="text-center hide-td">授權狀態</th>
                                        <th class="text-center hide-td">樓層</th>
                                        <th class="text-center hide-td">接收時間</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(elevator, index) in elevatorMgmts" :class="{'tr-gray' : index % 2 == 1}">
                                        <!--電梯點資訊-->
                                        <td data-title="電梯點編號" class="text-center">{{ elevator.elevator_id }}</td>
                                        <td data-title="電梯點位置" class="text-center">{{ elevator.vertex_name }}</td>
                                        <td data-title="特定車輛" class="text-center">{{ elevator.prefer_vehicle }}</td>
                                        <td data-title="啟用狀態" class="text-center vertical-center">
                                            <b :class="{'red': !elevator.enable, 'green': elevator.enable}">{{ elevator.enable ? '已啟用' : '未啟用' }}</b>
                                        </td>
                                        <td data-title="電梯MAC" class="text-center">{{ elevator.macaddr }}</td>
                                        <td data-title="電梯IP" class="text-center">{{ elevator.ipaddr }}</td>
                                        <!--即時狀態-->
                                        <td data-title="狀態" class="text-center">
                                            <template v-if="elevator.elevator_status">
                                                <template v-if="elevator.elevator_status.elevator_status == 'idle'">閒置</template>
                                                <template v-else-if="elevator.elevator_status.elevator_status == 'busy'">運轉</template>
                                                <template v-else-if="elevator.elevator_status.elevator_status == 'disable'">故障</template>
                                                <span v-if="elevator.elevator_status.elevator_status && elevator.elevator_status.elevator_door_status">&nbsp;|&nbsp;</span>
                                                <template v-if="elevator.elevator_status.elevator_door_status == 'open'">開門</template>
                                                <template v-else-if="elevator.elevator_status.elevator_door_status == 'closed'">關門</template>
                                            </template>
                                        </td>
                                        <td data-title="授權狀態" class="text-center">
                                            <template v-if="elevator.elevator_status">
                                                <div :class="{'green' : elevator.elevator_status.elevator_authorization_state, 'gray' : !elevator.elevator_status.elevator_authorization_state}">
                                                    {{ elevator.elevator_status.elevator_authorization_state ? '已授權' : '未授權' }}
                                                </div>
                                            </template>
                                        </td>
                                        <td data-title="樓層" class="text-center vertical-center">
                                            <template v-if="elevator.elevator_status && elevator.elevator_status.elevator_position">{{ elevator.elevator_status.elevator_position }}</template>
                                        </td>
                                        <td data-title="接收時間" class="text-center vertical-center">
                                            <template v-if="elevator.elevator_status">{{ elevator.elevator_status.update_ts | datetime }}</template>
                                        </td>
                                        <td data-title="操作" class="text-center">
                                            <template v-if="viewType == 'management'">
                                                <button class="btn btn-light-green btn-sm" @click="showModal(elevator.elevator_id)" title="修改">
                                                    <i class="glyphicon glyphicon-pencil"/>
                                                    修改
                                                </button>
                                                <button class="btn btn-delete btn-sm" @click="deleteRow(elevator)" title="刪除">
                                                    <i class="glyphicon glyphicon-trash"/>
                                                    刪除
                                                </button>
                                            </template>
                                            <template v-else>
                                                <div class="btn-list">
                                                    <button type="button" class="btn btn-default btn-sm border-gray" :disabled="elevator.sending" @click="submitMqttCommand(31, elevator.elevator_id)">
                                                        要授權
                                                    </button>
                                                    <button type="button" class="btn btn-default btn-sm border-gray" :disabled="elevator.sending" @click="submitMqttCommand(32, elevator.elevator_id)">
                                                        還授權
                                                    </button>
                                                </div>
                                                <div class="btn-list">
                                                    <div class="input-group">
                                                        <input type="text" id="targetFloorInput" class="form-control input-sm input-floor" aria-describedby="basic-addon1" :disabled="elevator.sending" v-model="elevator.elevator_position">
                                                        <span class="input-group-addon" id="basic-addon1">F</span>
                                                        <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default btn-sm border-gray" :disabled="elevator.sending" @click="submitMqttCommand(33, elevator.elevator_id, elevator.elevator_position)">
                                                            <i class="fa fa-arrow-up"/><i class="fa fa-arrow-down"/>
                                                        </button>
                                                    </span>
                                                    </div>
                                                </div>
                                                <div class="btn-list">
                                                    <button type="button" class="btn btn-default btn-sm border-gray" :disabled="elevator.sending" @click="submitMqttCommand(35, elevator.elevator_id)">
                                                        開門
                                                    </button>
                                                    <button type="button" class="btn btn-default btn-sm border-gray" :disabled="elevator.sending" @click="submitMqttCommand(34, elevator.elevator_id)">
                                                        關門
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <elevator-mgmt-modal v-model="modal.show"
                             :eid="modal.id"
                             @update="resetRow"
                             @refresh="fetchData"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import ElevatorMgmtModal from './ElevatorMgmtModal.vue';

export default {
    name: "ElevatorMgmtListView",
    components: {ClipLoader, ElevatorMgmtModal},
    props: {
        viewType: {type: String, default: 'management'}
    },
    data() {
        return {
            loading: false,
            elevatorMgmts: [],
            mqttCommandId: null,
            modal: {
                show: false,
                id: null
            }
        };
    },
    async created() {
        this.loading = true;
        await this.fetchData();
        this.loading = false;
        const that = this;
        window.Echo.private('elevatorMgmts').listen('ElevatorMgmtCreated', (res) => {
            that.elevatorMgmts.unshift(res.elevatorMgmt);
        }).listen('ElevatorMgmtUpdated', (res) => {
            const elevatorMgmtIdx = _.findIndex(that.elevatorMgmts, ['elevator_id', res.elevatorMgmt.elevator_id]);
            if(elevatorMgmtIdx != -1) {
                that.elevatorMgmts.splice(elevatorMgmtIdx, 1, res.elevatorMgmt);
            }
        }).listen('ElevatorMgmtDeleted', (res) => {
            const elevatorMgmtIdx = _.findIndex(that.elevatorMgmts, ['elevator_id', res.elevatorMgmt.elevator_id]);
            if(elevatorMgmtIdx != -1) {
                that.elevatorMgmts.splice(elevatorMgmtIdx, 1);
            }
        });
        window.Echo.private('elevatorStatuses').listen('ElevatorStatusUpdated', (res) => {
            const elevatorStatus = res.elevatorStatus;
            const elevatorMgmt = elevatorStatus.elevator_mgmt;
            delete elevatorStatus.elevator_mgmt;
            elevatorMgmt.elevator_status = elevatorStatus;
            const elevatorMgmtIdx = _.findIndex(that.elevatorMgmts, ['elevator_id', elevatorMgmt.elevator_id]);
            if(elevatorMgmtIdx != -1) {
                that.elevatorMgmts.splice(elevatorMgmtIdx, 1, elevatorMgmt);
            }
        });
    },
    destroyed() {
        if(this.mqttCommandId) {
            window.Echo.leave(`mqttCommands.${this.mqttCommandId}`);
        }
        window.Echo.leave('elevatorMgmts');
        window.Echo.leave('elevatorStatuses');
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get('/api/elevatorMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.elevatorMgmts = _.map(data.elevatorMgmts, (r) => {
                        r.sending = false;
                        return r;
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        async submitMqttCommand(mqttCommandTypeId, elevatorId, targetFloor = null) {
            if(mqttCommandTypeId == 33) {
                if(!targetFloor) {
                    alert('請先輸入樓層。');
                    document.getElementById('targetFloorInput').focus();
                    return;
                }
                targetFloor = targetFloor + 'F';
            }
            try {
                const elevatorMgmtIdx = _.findIndex(this.elevatorMgmts, (r) => {
                    return r.elevator_id == elevatorId;
                });
                if(elevatorMgmtIdx != -1) {
                    this.elevatorMgmts[elevatorMgmtIdx].sending = true;
                }
                let res = await axios.post(`/api/mqttCommands`, {
                    mqtt_command_type_id: mqttCommandTypeId,
                    target_floor: targetFloor,
                    elevator_id: elevatorId
                });
                res = res.data;
                if(res.status == 0) {
                    if(this.mqttCommandId) {
                        window.Echo.leave(`mqttCommands.${this.mqttCommandId}`);
                    }
                    const data = res.data;
                    this.mqttCommandId = data.mqttCommand.id;
                    const that = this;
                    window.Echo.private(`mqttCommands.${this.mqttCommandId}`).listen('MqttCommandUpdated', (e) => {
                        that.$toast.success({
                            title: '成功訊息',
                            message: '命令送出成功！'
                        });
                        if(elevatorMgmtIdx != -1) {
                            that.elevatorMgmts[elevatorMgmtIdx].sending = false;
                        }
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        resetRow(row) {
            const idx = _.findIndex(this.elevatorMgmts, (r) => {
                return r.elevator_id == row.elevator_id;
            });
            if(idx == -1) {
                this.elevatorMgmts.splice(0, 0, row);
            } else {
                this.elevatorMgmts[idx] = row;
            }
        },
        showModal(id) {
            this.modal.id = id;
            this.modal.show = true;
        },
        async deleteRow(elevatorMgmt) {
            if(!confirm(`確定要刪除${elevatorMgmt.elevator_id}?`))
                return;
            try {
                let res = await axios.delete(`/api/elevatorMgmts/${elevatorMgmt.elevator_id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.elevatorMgmts = _.filter(this.elevatorMgmts, (r) => {
                        return r.elevator_id != elevatorMgmt.elevator_id;
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>

<style scoped>
.table{
    margin-bottom: 10px;
}
th{
    vertical-align: middle !important;
}
.border-gray{
    border: 1px solid #adadad;
}
.border-gray:focus{
    outline: none !important;
    border:  1px solid #adadad;
}
.input-group{
    width:  100px;
    margin: 0 auto;
    button{
        position: relative;
    }
}
.btn-list{
    margin-bottom: 10px;
}
.operate-td{
    min-width: 180px;
}
.input-floor{
    width:     42px;
    font-size: 14px;
}
</style>
