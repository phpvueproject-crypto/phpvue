<template>
    <div>
        <section class="content-header clearfix">
            <span>AP連線 / IP連線&emsp;</span>
        </section>
        <section class="content">
            <div class="box box-top">
                <div class="box-body">
                    <div v-if="!loading" class="device-block">
                        <template v-if="devices.length > 0 && devices[0]">
                            <div>
                                <div class="status-grid">
                                    <i class="fa fa-wifi" :class="{'green' : devices[0].is_connected}"/>
                                </div>
                                <div class="status-grid">
                                    <template v-if="devices[0].is_connected">
                                        <span class="green">當前已連接</span>
                                    </template>
                                    <template v-else-if="devices[0].connected_at">上次連接</template>
                                    <template v-else>未曾連接</template>
                                </div>
                                <div class="status-grid">
                                    <template v-if="devices[0].connected_at">{{ devices[0].connected_at | datetime }}</template>
                                </div>
                            </div>
                            <div class="device-info-block">
                                <h3 class="device-name">{{ devices[0].name }}</h3>
                                <div class="device-info">
                                    <i class="fa fa-cog green"/>
                                    IP連線
                                    <div class="device-info-right">{{ devices[0].ip ? devices[0].ip : '不可用' }}</div>
                                </div>
                                <div class="device-info">
                                    <i class="fa fa-cog green"/>
                                    AP連線
                                    <div class="device-info-right">{{ devices[0].ap ? devices[0].ap : '不可用' }}</div>
                                </div>
                            </div>
                            <div class="device-button-bar">
                                <button v-if="devices[0].is_connected" type="button" class="btn btn-danger"
                                        :disabled="sending"
                                        @click="updateIsConnected(devices[0].id, 0)">
                                    <i class="fa fa-chain-broken"/>
                                    斷開連接
                                </button>
                                <button v-else type="button" class="btn btn-success"
                                        :disabled="sending"
                                        @click="updateIsConnected(devices[0].id, 1)">
                                    <i class="fa fa-wifi"/>
                                    連接
                                </button>
                                <button type="button" class="btn btn-success"
                                        :disabled="sending"
                                        @click="showModal(devices[0].id)">
                                    <i class="fa fa-pencil"/>
                                    編輯
                                </button>
                            </div>
                        </template>
                    </div>
                    <clip-loader v-else class="loading" color="gray" size="30px"/>
                </div>
            </div>
        </section>
        <device-modal v-model="modal.show"
                      :data-id="modal.id"
                      @update="resetRow"/>
    </div>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import DeviceModal from '../Device/DeviceModal';
import axios from 'axios';
import _ from 'lodash';
import {mapGetters} from 'vuex';

export default {
    name: "DeviceListPage",
    components: {DeviceModal, ClipLoader},
    computed: {
        ...mapGetters({
            device: 'device/device'
        })
    },
    watch: {
        device(newVal) {
            this.fetchData();
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            devices: [],
            modal: {
                id: null,
                show: false
            }
        }
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/devices');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.devices = data.devices;
                    if(data.pagination) {
                        this.pagination = data.pagination;
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        showModal(id) {
            this.modal.id = id;
            this.modal.show = true;
        },
        resetRow(row) {
            const idx = _.findIndex(this.devices, (r) => {
                return r.id == row.id;
            });
            if(idx != -1) {
                this.devices.splice(idx, 1, row);
            } else {
                this.devices.unshift(row);
            }
        },
        async updateIsConnected(deviceId, isConnected) {
            this.sending = true;
            try {
                let res = await axios.patch(`/api/devices/${deviceId}/is-connected`, {
                    is_connected: isConnected
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const device = data.device;
                    const deviceIdx = _.findIndex(this.devices, ['id', device.id]);
                    if(deviceIdx != -1) {
                        this.devices.splice(deviceIdx, 1, device);
                    }
                    await this.syncUser();
                    if(device.is_connected == isConnected) {
                        this.$toast.success({
                            title: '成功訊息',
                            message: `${isConnected ? '已連接' : '已斷開連接'}`
                        });
                    } else {
                        this.$toast.error({
                            title: '錯誤訊息',
                            message: `MIR車輛未開機，請稍後再試！`
                        });
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.sending = false;
        }
    }
}
</script>

<style scoped lang="scss">
.box-header{
    padding: 30px;
}
.box-body{
    padding: 30px;
}
.device-block{
    display:          inline-block;
    width:            calc(50% - 14px);
    background-color: #FCFCFC;
    border:           1px solid #EBEBEB;
    margin-right:     10px;
    vertical-align:   top;
}
.device-block:nth-child(n+3){
    margin-top: 10px;
}
.status-grid{
    display: inline-block;
    padding: 15px;
}
.status-grid:not(:last-child){
    border-right: 1px solid #EBEBEB;
}
.title-right-block{
    margin-top: 20px;
    float:      right;
}
.divider{
    display:      inline-block;
    color:        gray;
    margin-left:  15px;
    margin-right: 15px;
}
.device-info-block{
    padding:    15px 30px;
    border-top: 1px solid #EBEBEB;
}
.device-name{
    margin-bottom: 30px;
}
.device-info{
    margin-top: 15px;
}
.device-info-right{
    float: right;
}
.device-button-bar{
    padding: 15px 30px;
}
</style>
