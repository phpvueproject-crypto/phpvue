<template>
    <div v-if="vehicleMgmt"
         class="vehicle-mgmt-point"
         :style="`top: ${vehicleMgmt.position_y_px - 1}px;
                  left: ${vehicleMgmt.position_x_px - 1}px;
                  background-color: ${vehicleMgmt.color}80;
                  transition: ${vehicleMgmt.animation ? 'all 1000ms ease' : ''};`"
         :title="vehicleMgmt.vehicle_id">
        <img v-if="vehicleMgmt.theta === null" src="/img/icon-clean-robot-transparent.png?v=202302151731" alt="" style="width: 17px; height: 17px"/>
        <img v-else src="/img/icon-clean-robot-directional-transparent.png?v=202302151731" alt="robot" :style="`transform: rotate(${vehicleMgmt.theta}deg); width: 50px; transform-origin: 9.6px; position: absolute; top: -28px; left: -1px`"/>
    </div>
</template>

<script>
export default {
    name: "VehicleMgmtView",
    props: {
        value: Object,
        index: {
            type: Number
        },
        regionMgmtId: Number
    },
    watch: {
        value(newVal, oldVal) {
            newVal.animation = (newVal.vehicle_id == oldVal.vehicle_id);
            const oldVehicleMgmt = this.vehicleMgmt;
            this.vehicleMgmt = newVal;
            if(oldVehicleMgmt.vehicle_id != newVal.vehicle_id) {
                window.Echo.leave(`vehicleMgmts.${oldVehicleMgmt.vehicle_id}`);
                const that = this;
                setTimeout(() => {
                    that.subscribe();
                }, 500);
            }
        }
    },
    data() {
        return {
            vehicleMgmt: this.value
        }
    },
    created() {
        this.subscribe();
        if(this.vehicleMgmt) {
            this.vehicleMgmt.animation = true;
        }
    },
    destroyed() {
        if(this.vehicleMgmt) {
            window.Echo.leaveChannel(`vehicleMgmts.${this.vehicleMgmt.vehicle_id}`);
        }
    },
    methods: {
        subscribe() {
            const that = this;
            window.Echo.private(`vehicleMgmts.${this.vehicleMgmt.vehicle_id}`).listen('VehicleMgmtUpdated', (e) => {
                let vehicleMgmt = e.vehicleMgmt;
                that.vehicleMgmt.animation = document.hasFocus();
                that.vehicleMgmt.position_x = vehicleMgmt.position_x;
                that.vehicleMgmt.position_y = vehicleMgmt.position_y;
                that.vehicleMgmt.position_x_px = vehicleMgmt.position_x_px;
                that.vehicleMgmt.position_y_px = vehicleMgmt.position_y_px;
                that.vehicleMgmt.theta = vehicleMgmt.theta;
                that.vehicleMgmt.vehicle_status = vehicleMgmt.vehicle_status;
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.vehicle-mgmt-point{
    position:      absolute;
    z-index:       1;
    border-radius: 50%;
    height:        17px;
    width:         17px;
    img{
        position: absolute;
        top:      0;
        left:     0;
    }
}
</style>
