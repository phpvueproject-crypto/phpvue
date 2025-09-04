<template>
    <div>
        <section class="content-header clearfix">
            <span>任務歷史紀錄</span>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table size-sm">
                                    <thead class="table-head">
                                    <tr>
                                        <th class="text-center hide-td">編號</th>
                                        <th class="text-center hide-td">命令類型</th>
                                        <th class="text-center hide-td">命令</th>
                                        <th class="text-center hide-td">更新時間</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="mqttCommand in mqttCommands">
                                        <td data-title="編號" class="text-center">{{ mqttCommand.id }}</td>
                                        <td data-title="命令類型" class="text-center">
                                            <template v-if="mqttCommand.mqtt_command_type">
                                                {{ mqttCommand.mqtt_command_type.name }}
                                            </template>
                                        </td>
                                        <td data-title="命令">
                                            <template v-if="mqttCommand.device_name">
                                                設備名稱：{{ mqttCommand.device_name }}<br>
                                            </template>
                                            <template v-if="mqttCommand.sweep_start_goal_x || mqttCommand.sweep_start_goal_y">
                                                清掃起點：({{ Number.parseFloat(mqttCommand.sweep_start_goal_x).toFixed(2) }}<span v-show="mqttCommand.sweep_start_goal_y">, </span>{{ Number.parseFloat(mqttCommand.sweep_start_goal_y).toFixed(2) }})<br>
                                            </template>
                                            <template v-if="mqttCommand.sweep_end_goal_x || mqttCommand.sweep_end_goal_y">
                                                清掃終點：({{ Number.parseFloat(mqttCommand.sweep_end_goal_x).toFixed(2) }}<span v-show="mqttCommand.sweep_end_goal_y">, </span>{{ Number.parseFloat(mqttCommand.sweep_end_goal_y).toFixed(2) }})<br>
                                            </template>
                                            <template v-if="mqttCommand.goal_x || mqttCommand.goal_y">
                                                目標點&emsp;：({{ Number.parseFloat(mqttCommand.goal_x).toFixed(2) }}<span v-show="mqttCommand.goal_y">, </span>{{ Number.parseFloat(mqttCommand.goal_y).toFixed(2) }})
                                            </template>
                                        </td>
                                        <td data-title="更新時間" class="text-center">{{ mqttCommand.updated_at | datetime }}</td>
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
    </div>
</template>

<script>
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';

export default {
    name: "MqttCommandListView",
    components: {ClipLoader},
    data() {
        return {
            loading: false,
            mqttCommands: [],
            vehicleId: this.$route.query.vehicleId ? this.$route.query.vehicleId : null
        };
    },
    async created() {
        this.loading = true;
        await this.fetchData();
        this.loading = false;
        const that = this;
        window.Echo.private('mqttCommands').listen('MqttCommandUpdated', (res) => {
            that.fetchData();
        });
    },
    destroyed() {
        window.Echo.leave(`mqttCommands`);
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get('/api/mqttCommands', {
                    params: {
                        vehicle_id: this.$route.query.vehicleId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.mqttCommands = data.mqttCommands;
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
.size-sm{
    font-size: 12pt;
}
th, td{
    vertical-align: middle !important;
}
</style>
