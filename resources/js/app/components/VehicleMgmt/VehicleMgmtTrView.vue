<script>
import axios from 'axios';

export default {
    name: "VehicleMgmtTrView",
    props: {
        value: {
            type: Object
        },
        index: {
            type: Number
        },
        showStatus: {
            type: Boolean,
            default: false
        },
        showEditBtn: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        value(newVal) {
            newVal.animation = true;
            this.vehicleMgmt = newVal;
        }
    },
    created() {
        const that = this;
        this.vehicleMgmt.animation = true;
        window.Echo.private(`vehicleMgmts.${this.vehicleMgmt.vehicle_id}`).listen('VehicleMgmtUpdated', (e) => {
            that.vehicleMgmt.animation = document.hasFocus();
            let vehicleMgmt = e.vehicleMgmt;
            that.vehicleMgmt.position_x = vehicleMgmt.position_x;
            that.vehicleMgmt.position_y = vehicleMgmt.position_y;
            that.vehicleMgmt.theta = vehicleMgmt.theta;
            that.vehicleMgmt.vehicle_status = vehicleMgmt.vehicle_status;
        });
    },
    beforeDestroy() {
        window.Echo.leave(`vehicleMgmts.${this.vehicleMgmt.vehicle_id}`);
    },
    data() {
        return {
            vehicleMgmt: this.value
        }
    },
    methods: {
        async submit(vehicleMgmt) {
            try {
                this.sending = true;
                let res = await axios.patch(`/api/vehicleMgmts/${vehicleMgmt.vehicle_id}`, vehicleMgmt);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '更新成功！'
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        }
    }
}
</script>

<template>
    <tr :class="{'tr-gray' : index % 2 == 1}">
        <td data-title="車輛編號" class="text-center">{{ vehicleMgmt.vehicle_id }}</td>
        <td data-title="載貨物種類" class="text-center">{{ vehicleMgmt.carrier_class }}</td>
        <td data-title="車輛MAC" class="text-center">{{ vehicleMgmt.macaddr }}</td>
        <td data-title="車輛IP" class="text-center">{{ vehicleMgmt.ipaddr }}</td>
        <template v-if="showStatus">
            <td data-title="車輛位置" class="text-center">
                <template v-if="vehicleMgmt.vehicle_status">
                    {{ vehicleMgmt.vehicle_status.vehicle_location }}
                </template>
            </td>
            <td data-title="車輛狀態">
                <template v-if="vehicleMgmt.vehicle_status">
                    <div v-if="vehicleMgmt.vehicle_status.water_box_status">water box：{{ vehicleMgmt.vehicle_status.water_box_status }}</div>
                    <div v-if="vehicleMgmt.vehicle_status.spray_status">spray：{{ vehicleMgmt.vehicle_status.spray_status }}</div>
                    <div v-if="vehicleMgmt.vehicle_status.mopping_motor_status">mopping motor：{{ vehicleMgmt.vehicle_status.mopping_motor_status }}</div>
                    <div v-if="vehicleMgmt.vehicle_status.air_laser_sensor_status">air laser sensor：{{ vehicleMgmt.vehicle_status.air_laser_sensor_status }}</div>
                    <div v-if="vehicleMgmt.vehicle_status.depth_camera_status">depth camera：{{ vehicleMgmt.vehicle_status.depth_camera_status }}</div>
                    <div v-if="vehicleMgmt.vehicle_status.pipe_import_status">pipe import：{{ vehicleMgmt.vehicle_status.pipe_import_status }}</div>
                    <div v-if="vehicleMgmt.vehicle_status.sweep_mode_status">sweep mode：{{ vehicleMgmt.vehicle_status.sweep_mode_status }}</div>
                </template>
            </td>
            <td v-if="vehicleMgmt.vehicle_status" data-title="接收時間" class="text-center">{{ vehicleMgmt.vehicle_status.update_ts }}</td>
            <td v-else data-title="接收時間" class="text-center">{{ null }}</td>
        </template>
        <template v-if="showEditBtn">
            <td data-title="操作" class="text-center">
                <div class="text-right" style="display: inline-block">
                    <label for="vehicle_id" style="vertical-align: bottom">{{ vehicleMgmt.color }}</label>
                    <input id="vehicle_id" type="color" v-model="vehicleMgmt.color">
                </div>
                <div class="text-left" style="display: inline-block; vertical-align: top">
                    <button class="btn btn-light-green btn-xs" title="儲存" @click="submit(vehicleMgmt)">
                        <i class="glyphicon glyphicon-pencil"/>
                        儲存
                    </button>
                </div>
            </td>
        </template>
    </tr>
</template>

<style scoped>
.btn-xs{
    height: 27px;
}
input[type="color"]{
    -webkit-appearance: none;
    border:             none;
}
input[type="color"]::-webkit-color-swatch-wrapper{
    padding: 0;
}
input[type="color"]::-webkit-color-swatch{
    border: none;
}
</style>
