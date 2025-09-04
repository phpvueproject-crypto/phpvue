<template>
    <div>
        <template v-if="showVehiclePoints">
            <template v-for="(vehicleMgmt, index) in vehicleMgmts">
                <template v-if="vehicleMgmt.vehicle_status">
                    <vehicle-mgmt-view v-show="vehicleMgmt.vehicle_status.region_mgmt && vehicleMgmt.vehicle_status.region_mgmt.id == regionMgmtId" v-model="vehicleMgmts[index]"
                                       :region-mgmt-id="regionMgmtId"
                                       @delete="deleteVehicle(vehicleMgmts[index].vehicle_id)"/>
                    <template v-if="vehicleMgmt.clean_area && vehicleMgmt.clean_area.region_mgmt_id == regionMgmtId">
                        <div class="clean-area" :style="`
                            top: ${vehicleMgmt.clean_area.start_goal_y_px < vehicleMgmt.clean_area.end_goal_y_px ? vehicleMgmt.clean_area.start_goal_y_px : vehicleMgmt.clean_area.end_goal_y_px}px;
                            left: ${vehicleMgmt.clean_area.start_goal_x_px < vehicleMgmt.clean_area.end_goal_x_px ? vehicleMgmt.clean_area.start_goal_x_px : vehicleMgmt.clean_area.end_goal_x_px}px;
                            height: ${abs(vehicleMgmt.clean_area.start_goal_y_px - vehicleMgmt.clean_area.end_goal_y_px)}px;
                            width: ${abs(vehicleMgmt.clean_area.start_goal_x_px - vehicleMgmt.clean_area.end_goal_x_px)}px;`"/>
                        <draw-line v-for="(route, i) in vehicleMgmt.clean_area.routes"
                                   v-model="vehicleMgmt.clean_area.routes[i]"
                                   :key="`cleanRoute_${i}`"
                                   :scale="scale"/>
                        <draw-point v-for="(turning_point,i) in vehicleMgmt.clean_area.turning_points"
                                    v-model="vehicleMgmt.clean_area.turning_points[i]"
                                    :key="`inflectionPoint_${i}`"
                                    :scale="scale"/>
                    </template>
                </template>
            </template>
        </template>
        <template v-else>
            <div class="box">
                <div class="box-body">
                    <div v-if="!loading">
                        <table class="table table-bordered table-hover break-table" :style="`font-size: ${tableFontSize}px`">
                            <thead class="table-head">
                            <tr v-if="showStatus">
                                <th class="text-center hide-td" colspan="4">車輛資訊</th>
                                <th class="text-center hide-td" colspan="3">即時狀態</th>
                            </tr>
                            <tr>
                                <th class="text-center hide-td">車輛編號</th>
                                <th class="text-center hide-td">載貨物種類</th>
                                <th class="text-center hide-td">車輛MAC</th>
                                <th class="text-center hide-td">車輛IP</th>
                                <template v-if="showStatus">
                                    <th class="text-center hide-td">車輛位置</th>
                                    <th class="text-center hide-td">車輛狀態</th>
                                    <th class="text-center hide-td">接收時間</th>
                                </template>
                                <th v-if="showEditBtn" class="text-center hide-td" :colspan="showStatus ? 1 : 2">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-for="(vehicleMgmt, index) in vehicleMgmts">
                                <vehicle-mgmt-tr-view v-model="vehicleMgmts[index]"
                                                      :index="index"
                                                      :show-status="showStatus"
                                                      :show-edit-btn="showEditBtn"/>
                            </template>
                            </tbody>
                        </table>
                    </div>
                    <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import VehicleMgmtView from './VehicleMgmtView.vue';
import VehicleMgmtTrView from './VehicleMgmtTrView.vue';
import DrawPoint from '../Module/DrawPoint.vue';
import DrawLine from '../Module/DrawLine.vue';

export default {
    name: "VehicleMgmtListView",
    components: {DrawLine, DrawPoint, ClipLoader, VehicleMgmtView, VehicleMgmtTrView},
    props: {
        value: {
            type: Array
        },
        tableFontSize: {
            type: Number,
            default: null
        },
        showStatus: {
            type: Boolean,
            default: false
        },
        showEditBtn: {
            type: Boolean,
            default: false
        },
        showVehiclePoints: {
            type: Boolean,
            default: false
        },
        regionMgmtId: Number,
        mapType: {
            type: String,
            default: 'radar'
        },
        scale: {type: Number, default: 1}
    },
    watch: {
        value(newVal) {
            this.vehicleMgmts = newVal;
        }
    },
    data() {
        return {
            loading: false,
            vehicleMgmts: [],
            selectedVehicle: []
        };
    },
    async created() {
        await this.fetchData();

        const that = this;
        window.Echo.private('vehicleMgmts').listen('VehicleStatusUpdated', (res) => {
            const vehicleStatus = _.cloneDeep(res.vehicleStatus);
            const vehicleMgmt = _.cloneDeep(vehicleStatus.vehicle_mgmt);
            delete vehicleStatus.vehicle_mgmt;
            vehicleMgmt.vehicle_status = vehicleStatus;
            const vehicleMgmtIdx = _.findIndex(that.vehicleMgmts, (r) => {
                return r.vehicle_id == vehicleMgmt.vehicle_id;
            });
            if(vehicleMgmt.clean_area && vehicleMgmt.clean_area.turning_points) {
                vehicleMgmt.clean_area.routes = [];
                for(let i = 1; i < vehicleMgmt.clean_area.turning_points.length; i++) {
                    vehicleMgmt.clean_area.routes.push({
                        start: vehicleMgmt.clean_area.turning_points[i - 1],
                        end: vehicleMgmt.clean_area.turning_points[i]
                    });
                }
            }
            if(vehicleMgmtIdx == -1) {
                return;
            }
            if(vehicleMgmt.mqtt_command) {
                vehicleMgmt.mqtt_command.remain_stop_second = that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.remain_stop_second;
            }
            that.vehicleMgmts.splice(vehicleMgmtIdx, 1, vehicleMgmt);
            that.$emit('input', that.vehicleMgmts);
        });
    },
    destroyed() {
        window.Echo.leave('vehicleMgmts');
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/vehicleMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.vehicleMgmts = _.map(data.vehicleMgmts, (vehicle) => {
                        vehicle.selected = 0;
                        vehicle.animation = false;
                        if(vehicle.mqtt_command) {
                            vehicle.mqtt_command.sending = false;
                            vehicle.mqtt_command.stop_disabled = false;
                            vehicle.mqtt_command.remain_stop_second = vehicle.stoppable_second + 1;
                        }
                        if(vehicle.clean_area && _.isArray(vehicle.clean_area.turning_points)) {
                            vehicle.clean_area.routes = [];
                            for(let i = 1; i < vehicle.clean_area.turning_points.length; i++) {
                                vehicle.clean_area.routes.push({
                                    start: vehicle.clean_area.turning_points[i - 1],
                                    end: vehicle.clean_area.turning_points[i]
                                });
                            }
                        }
                        return vehicle;
                    });
                    this.$emit('input', this.vehicleMgmts);
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        deleteVehicle(vehicleId) {
            this.vehicleMgmts = _.filter(this.vehicleMgmts, (r) => {
                return r.vehicle_id != vehicleId;
            });
            this.$emit('input', this.vehicleMgmts);
        }
    }
}
</script>

<style scoped>
.table{
    margin-bottom: 10px;
}
.clean-area{
    background-color: rgba(231, 245, 222, 0.5);
    position:         absolute;
    border-radius:    10px;
}
</style>
