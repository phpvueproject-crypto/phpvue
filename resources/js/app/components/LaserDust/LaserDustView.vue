<template>
    <div v-if="laserDust" class="dust-value">
        <div v-if="laserDust.val_1">&nbsp;Particles > 0.3μm/0.1L &nbsp;&nbsp;air: {{ laserDust.val_1 }}</div>
        <div v-if="laserDust.val_2">&nbsp;Particles > 0.5μm/0.1L &nbsp;&nbsp;air: {{ laserDust.val_2 }}</div>
        <div v-if="laserDust.val_3">&nbsp;Particles > 1.0μm/0.1L &nbsp;&nbsp;air: {{ laserDust.val_3 }}</div>
        <div v-if="laserDust.val_4">&nbsp;Particles > 2.5μm/0.1L &nbsp;&nbsp;air: {{ laserDust.val_4 }}</div>
        <div v-if="laserDust.val_5">&nbsp;Particles > 5.0μm/0.1L &nbsp;&nbsp;air: {{ laserDust.val_5 }}</div>
        <div v-if="laserDust.val_6">&nbsp;Particles > 10.0μm/0.1L air: {{ laserDust.val_6 }}</div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "DustView",
    props: {
        vehicleId: {
            type: String
        }
    },
    watch: {
        vehicleId() {
            this.fetchData();
        }
    },
    data() {
        return {
            loading: true,
            laserDust: null
        }
    },
    created() {
        this.fetchData();
        const that = this;
        window.Echo.private('laserDusts').listen('LaserDustCreated', (res) => {
            const laserDust = res.laserDust;
            if(that.vehicleId != laserDust.vehicle_id) {
                return;
            }

            that.laserDust = laserDust;
        });
    },
    destroyed() {
        window.Echo.leave('laserDusts');
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/laserDusts`, {
                    params: {
                        vehicle_id: this.vehicleId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    if(data.laserDusts.length > 0) {
                        this.laserDust = data.laserDusts[0];
                    } else {
                        this.laserDust = null;
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        }
    }
}
</script>

<style scoped>
.dust-value{
    min-width:         300px;
    min-height:        80px;
    background-image:  linear-gradient(#daf7da, #daf7da),
                       linear-gradient(to bottom right, #5c9b6c, #6db880);
    background-origin: border-box;
    background-clip:   content-box, border-box;
    border:            10px solid transparent;
    border-radius:     30px 0 0 0;
    box-shadow:        -2px -2px 10px #5c9b6c;
    > div{
        margin-left: 10px;
    }
}
</style>
