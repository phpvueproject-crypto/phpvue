<template>
    <div>
        <section class="content-header clearfix">
            <span>搬運命令清單</span>
        </section>

        <div class="box-header with-border" style="padding-bottom: 0;">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon">搬運狀態</span>
                    <select class="form-control" v-model="form.transfer_state">
                        <option :value="null">請選擇搬運狀態</option>
                        <option v-for="row in transferStates" :value="row">{{ row }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon">命令編號</span>
                    <input v-model="form.command_id" class="form-control" placeholder="請輸入命令編號">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon">關鍵字</span>
                    <input v-model="form.query" class="form-control" placeholder="請輸入關鍵字">
                    <span class="btn input-group-addon" @click="resetSearch('query')">清除</span>
                </div>
            </div>
            <div class="col-md-6" style="margin-top: 10px;">
                <div class="input-group">
                    <span class="input-group-addon">開始時間</span>
                    <input type="datetime-local" v-model="form.receive_start_ts" class="form-control">
                </div>
            </div>
            <div class="col-md-6" style="margin-top: 10px;">
                <div class="input-group">
                    <span class="input-group-addon">結束時間</span>
                    <input type="datetime-local" v-model="form.receive_stop_ts" class="form-control">
                </div>
            </div>
            <div class="col-md-6 col-md-offset-6 text-right" style="margin-top: 10px;">
                <button type="button" class="btn btn-light-green" @click="changePage">送出</button>
                <button type="button" class="btn btn-delete" @click="resetSearch">重設</button>
                <a :href="exportUrl" target="_blank" class="btn btn-light-green">匯出CSV檔</a>
            </div>
        </div>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table">
                                    <thead class="table-head">
                                    <tr>
                                        <th class="text-center hide-td" colspan="8">搬運命令清單</th>
                                        <th class="text-center hide-td" colspan="10">進行中</th>
                                    </tr>
                                    <tr>
                                        <!--搬運命令清單-->
                                        <th class="text-center hide-td">任務編號</th>
                                        <th class="text-center hide-td">接收任務時間</th>
                                        <th class="text-center hide-td">命令ID</th>
                                        <th class="text-center hide-td">來源槽</th>
                                        <th class="text-center hide-td">目標槽</th>
                                        <th class="text-center hide-td">任務優先</th>
                                        <th class="text-center hide-td">派任務人員</th>
                                        <th class="text-center hide-td">載貨ID</th>
                                        <!--進行中-->
                                        <th class="text-center hide-td">任務併單ID</th>
                                        <th class="text-center hide-td">車輛ID</th>
                                        <th class="text-center hide-td">派任務狀態</th>
                                        <th class="text-center hide-td">備註</th>
                                        <th class="text-center hide-td">Deploy地圖更新</th>
                                        <th class="text-center hide-td">更新時間</th>
                                        <th class="text-center hide-td">任務併單時間</th>
                                        <th class="text-center hide-td">任務分派時間</th>
                                        <th class="text-center hide-td">任務開始時間</th>
                                        <th class="text-center hide-td">任務結束時間</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="transfer in transferProcessings">
                                        <!--搬運命令清單-->
                                        <td data-title="任務編號" class="text-center">{{ transfer.serial_num }}</td>
                                        <td v-if="transfer" data-title="接收任務時間" class="text-center">{{ transfer.receive_ts | datetime }}</td>
                                        <td v-else data-title="接收任務時間" class="text-center">{{ null }}</td>
                                        <td data-title="命令ID" class="text-center">{{ transfer.command_id }}</td>
                                        <td data-title="來源槽" class="text-center">{{ transfer.source_port }}</td>
                                        <td data-title="目標槽" class="text-center">{{ transfer.dest_port }}</td>
                                        <td data-title="任務優先" class="text-center">{{ transfer.priority }}</td>
                                        <td data-title="派任務人員" class="text-center">{{ transfer.operator_id }}</td>
                                        <td data-title="載貨ID" class="text-center">{{ transfer.carrier_id }}</td>
                                        <!--進行中-->
                                        <td data-title="任務併單ID" class="text-center">{{ transfer.merged_command_id }}</td>
                                        <td data-title="車輛ID" class="text-center">{{ transfer.vehicle_id }}</td>
                                        <td data-title="派任務狀態" class="text-center">{{ transfer.transfer_state }}</td>
                                        <td data-title="備註" class="text-center">{{ transfer.comment }}</td>
                                        <td data-title="Deploy地圖更新" class="text-center">{{ transfer.magic }}</td>
                                        <td v-if="transfer" data-title="更新時間" class="text-center">{{ transfer.update_ts | datetime }}</td>
                                        <td v-else data-title="更新時間" class="text-center">{{ null }}</td>
                                        <td v-if="transfer" data-title="任務併單時間" class="text-center">{{ transfer.merged_ts | datetime }}</td>
                                        <td v-else data-title="任務併單時間" class="text-center">{{ null }}</td>
                                        <td v-if="transfer" data-title="任務分派時間" class="text-center">{{ transfer.assigned_ts | datetime }}</td>
                                        <td v-else data-title="任務分派時間" class="text-center">{{ null }}</td>
                                        <td v-if="transfer" data-title="任務開始時間" class="text-center">{{ transfer.delivery_start_ts | datetime }}</td>
                                        <td v-else data-title="任務開始時間" class="text-center">{{ null }}</td>
                                        <td v-if="transfer" data-title="任務結束時間" class="text-center">{{ transfer.delivery_stop_ts | datetime }}</td>
                                        <td v-else data-title="任務結束時間" class="text-center">{{ null }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <pagination :direction-links="false"
                                                :boundary-links="true"
                                                size="sm"
                                                v-model="pagination.current_page"
                                                :total-page="pagination.last_page"
                                                :max-size="10"
                                                @change="changePage"/>
                                </div>
                            </div>
                            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import _ from 'lodash';

export default {
    name: "TransferListView",
    components: {ClipLoader},
    computed: {
        exportUrl() {
            return '/transferProcessings?format=csv&' + this.serialize(this.form);
        }
    },
    data() {
        return {
            loading: false,
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            transferProcessings: [],
            form: {
                command_id: this.$route.query.command_id ? this.$route.query.command_id : null,
                transfer_state: this.$route.query.transfer_state ? this.$route.query.transfer_state : null,
                query: this.$route.query.transfer_state ? this.$route.query.transfer_state : null,
                receive_start_ts: this.$route.query.receive_start_ts ? this.$route.query.receive_start_ts : null,
                receive_stop_ts: this.$route.query.receive_stop_ts ? this.$route.query.receive_stop_ts : null
            },
            transferStates: ['QUEUED', 'WAITING', 'TRANSFERRING', 'DONE', 'PAUSED', 'CANCELING', 'ABORTING', 'CANCELED', 'ABORTED', 'REJECTED', 'PENDING']
        };
    },
    created() {
        this.fetchData();
        const that = this;
        window.Echo.private(`transferProcessings`).listen('TransferProcessingCreated', async(e) => {
            const transferProcessing = e.transferProcessing;
            if(transferProcessing.mqtt_command) {
                transferProcessing.mqtt_command.priority = 1
                transferProcessing.mqtt_command.confirm_sending = false;
            } else {
                transferProcessing.mqtt_command = {
                    priority: 1,
                    confirm_sending: false
                }
            }
            that.transferProcessings.push(transferProcessing);
        }).listen('TransferProcessingUpdated', (e) => {
            const transferProcessing = e.transferProcessing;
            if(transferProcessing.mqtt_command) {
                transferProcessing.mqtt_command.priority = 1
                transferProcessing.mqtt_command.confirm_sending = false;
            } else {
                transferProcessing.mqtt_command = {
                    priority: 1,
                    confirm_sending: false
                }
            }
            const tpIdx = _.findIndex(that.transferProcessings, (r) => {
                return r.serial_num == transferProcessing.serial_num;
            });
            if(tpIdx != -1) {
                that.transferProcessings.splice(tpIdx, 1, transferProcessing);
            }
        });
    },
    destroyed() {
        window.Echo.leave(`transferProcessings`);
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/transferProcessings', {
                    params: {
                        page: this.pagination.current_page,
                        command_id: this.form.command_id,
                        transfer_state: this.form.transfer_state,
                        query: this.form.query,
                        receive_start_ts: this.form.receive_start_ts,
                        receive_stop_ts: this.form.receive_stop_ts
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.transferProcessings = data.transferProcessings;
                    this.pagination = data.pagination;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        changePage() {
            this.$router.push({
                path: `/transfers/window`,
                query: {
                    page: this.pagination.current_page,
                    command_id: this.form.command_id,
                    transfer_state: this.form.transfer_state,
                    query: this.form.query,
                    receive_start_ts: this.form.receive_start_ts,
                    receive_stop_ts: this.form.receive_stop_ts
                }
            });
            this.fetchData();
        },
        resetSearch(keyWord) {
            if(keyWord == 'query') {
                this.form.query = null;
            } else {
                this.form = {
                    command_id: null,
                    transfer_state: null,
                    query: null,
                    receive_start_ts: null,
                    receive_stop_ts: null
                }
                this.changePage();
            }
        },
        serialize(obj) {
            const str = [];
            for(let p in obj)
                if(obj.hasOwnProperty(p)) {
                    if(obj[p])
                        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
            return str.join("&");
        }
    }
}
</script>

<style scoped>
.table{
    margin-bottom: 10px;
}
.size-sm{
    font-size: 10pt;
}
th{
    vertical-align: middle !important;
}
</style>
