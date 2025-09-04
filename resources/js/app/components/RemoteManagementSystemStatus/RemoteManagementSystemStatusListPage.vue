<template>
    <div>
        <section class="content-header clearfix">
            <span>採樣任務進度</span>
            <div class="location-select-div">
                <MapListSelect placeholder="請選擇房間"
                               select-style="min-width: 140px;"
                               @change="delay(function(){fetchData();}, 200)"
                               v-model="form.map_id"/>
            </div>
            <div class="title-right-block">
                <router-link :to="exportUrl" target="_blank" type="button" class="btn btn-light-green op-btn">
                    <i class="glyphicon glyphicon-export"/> 匯出
                </router-link>
            </div>
        </section>
        <section class="content">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div v-if="!loading">
                            <table class="flow-chart-remote-management-system">
                                <tbody>
                                <!--第一階-->
                                <tr>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && (!device.mir_status || !device.mir_status.current_status)}">啟動</div>
                                    </td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-right" aria-hidden="true"/>
                                    </td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && device.mir_status.current_status == 'A'}">{{ landmark }}
                                            <div class="second">{{ statusATime }}S</div>
                                        </div>
                                    </td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-right" aria-hidden="true"/>
                                    </td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && (device.mir_status.current_status == 'B' || device.mir_status.current_status == 'C')}">採樣準備
                                            <div class="second">{{ statusBCTime }}S</div>
                                        </div>
                                    </td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-right" aria-hidden="true"/>
                                    </td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && device.mir_status.current_status == 'D'}">空氣採樣
                                            <div class="second">{{ statusDTime }}S</div>
                                        </div>
                                    </td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-right" aria-hidden="true"/>
                                    </td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && device.mir_status.current_status == 'E'}">培養皿回收
                                            <div class="second">{{ statusETime }}S</div>
                                        </div>
                                    </td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-right" aria-hidden="true"/>
                                    </td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && device.mir_status.current_status == 'F'}">回HOME
                                            <div class="second">{{ statusFTime }}S</div>
                                        </div>
                                    </td>
                                </tr>
                                <!--第二階箭頭-->
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-down" aria-hidden="true"/>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <!--第二階-->
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && device.mir_status.current_status == 'B'}">培養皿讀碼
                                            <div class="second">{{ statusBTime }}S</div>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <!--第三階箭頭-->
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center arrow-gap">
                                        <i class="fa fa-long-arrow-down" aria-hidden="true"/>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <!--第三階-->
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <div class="label-node" :class="{'breathing-effect' : device && device.mir_status && device.mir_status.current_status == 'C'}">新培養皿交換
                                            <div class="second">{{ statusCTime }}S</div>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                            <remote-management-system-status-list-view
                                v-if="!showReload"
                                :remote-management-system-statuses="remoteManagementSystemStatuses"
                                :mission-queue="missionQueue"
                                style="margin-top: 20px"/>
                            <reload v-else @click="showReload = false; fetchData()"/>
                        </div>
                        <clip-loader v-else class="loading" color="gray" size="30px"/>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">微生物數據
                            <template v-if="device && device.mir_status && device.mir_status.current_status == 'B'">（掃碼中）</template>
                            <template v-if="device && device.mir_status && device.mir_status.current_status == 'D'">（抽氣中）</template>
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-hover">
                            <tbody>
                            <tr>
                                <td style="width: 220px;">房間編號 / Landmark / x:y</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.location">
                                        {{ remoteManagementSystemStatus.location.map ? remoteManagementSystemStatus.location.map.guid : null }}<br>
                                        {{ remoteManagementSystemStatus.location.device_name }}<br>
                                        {{ remoteManagementSystemStatus.location.x }}:{{ remoteManagementSystemStatus.location.y }}
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>微生物採樣值</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus">
                                        <div v-for="microOrganism in remoteManagementSystemStatus.micro_organisms">
                                            {{ microOrganism.organism_kind_name }}：{{ microOrganism.organism_value }}
                                        </div>
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>條形碼</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.location">{{ remoteManagementSystemStatus.location.bar_code }}</template>
                                </td>
                            </tr>
                            <tr>
                                <td>微生物採樣時間</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.micro_organisms.length > 0">
                                        <span v-if="remoteManagementSystemStatus.micro_organisms[0].Time">{{ remoteManagementSystemStatus.micro_organisms[0].Time | datetime }}</span>
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>紀錄時間</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.micro_organisms.length > 0">
                                        <span v-if="remoteManagementSystemStatus.micro_organisms[0].created_at">{{ remoteManagementSystemStatus.micro_organisms[0].created_at | datetime }}</span>
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>抽氣量平均值(L/S)</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.location">
                                        <div v-for="gasSampling in remoteManagementSystemStatus.gas_samplings">{{ gasSampling.second_mark }}S：{{ gasSampling.average_volume }}</div>
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>抽氣量累計值(L/S)</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.location">
                                        <div v-for="gasSampling in remoteManagementSystemStatus.gas_samplings">{{ gasSampling.second_mark }}S：{{ gasSampling.cumulative_volume }}</div>
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>抽氣時間(S)</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.location">
                                        {{ getLargestSecondMark(remoteManagementSystemStatus.gas_samplings) }}S
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">微粒子數據
                            <template v-if="isParticleWatching">（監控中）</template>
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-hover">
                            <tbody>
                            <tr>
                                <td style="width: 220px;">房間編號 / Landmark / x:y</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.location">
                                        {{ remoteManagementSystemStatus.location.map ? remoteManagementSystemStatus.location.map.guid : null }}<br>
                                        {{ remoteManagementSystemStatus.location.device_name }}<br>
                                        {{ remoteManagementSystemStatus.location.x }}:{{ remoteManagementSystemStatus.location.y }}
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>微粒子採樣值</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus">
                                        <div v-for="particle in remoteManagementSystemStatus.particles">
                                            {{ particle.organism_kind_name }}：{{ particle.organism_value }}
                                        </div>
                                    </template>
                                </td>
                            </tr>
                            <tr>
                                <td>微粒子採樣時間</td>
                                <td>
                                    <template v-if="remoteManagementSystemStatus && remoteManagementSystemStatus.particles.length > 0">
                                        <span v-if="remoteManagementSystemStatus.particles[0].Time">{{ remoteManagementSystemStatus.particles[0].Time | datetime }}</span>
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="clearfix"/>
        </section>
    </div>
</template>

<script>
import {mapGetters} from 'vuex';
import moment from 'moment';
import axios from 'axios';
import _ from 'lodash';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import Reload from '../Common/Reload.vue';
import MapListSelect from '../Map/MapListSelect.vue';
import Datetimepicker from '../Module/Datetimepicker.vue';
import RemoteManagementSystemStatusListView from './RemoteManagementSystemStatusListView.vue';

export default {
    name: "RemoteManagementSystemStatusListPage",
    components: {RemoteManagementSystemStatusListView, Datetimepicker, MapListSelect, Reload, ClipLoader},
    computed: {
        ...mapGetters({
            device: 'device/device'
        }),
        landmark() {
            if(!this.remoteManagementSystemStatus) {
                return '前往採樣點';
            }
            return (this.remoteManagementSystemStatus.location && this.remoteManagementSystemStatus.location.device_name) ? `前往採樣點[${this.remoteManagementSystemStatus.location.device_name}]` : '前往採樣點';
        },
        statusATime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_A_time = 0} = this.remoteManagementSystemStatus || {};
            return status_A_time != null ? status_A_time : 0;
        },
        statusBTime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_B_time = 0} = this.remoteManagementSystemStatus || {};
            return status_B_time != null ? status_B_time : 0;
        },
        statusCTime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_C_time = 0} = this.remoteManagementSystemStatus || {};
            return status_C_time != null ? status_C_time : 0;
        },
        statusBCTime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_B_time = 0, status_C_time = 0} = this.remoteManagementSystemStatus || {};
            return status_B_time + status_C_time;
        },
        statusDTime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_D_time = 0} = this.remoteManagementSystemStatus || {};
            return status_D_time != null ? status_D_time : 0;
        },
        statusETime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_E_time = 0} = this.remoteManagementSystemStatus || {};
            return status_E_time != null ? status_E_time : 0;
        },
        statusFTime() {
            if(!this.remoteManagementSystemStatus) {
                return 0;
            }
            const {status_F_time = 0} = this.remoteManagementSystemStatus || {};
            return status_F_time != null ? status_F_time : 0;
        },
        isParticleWatching() {
            let haveAllParticleValue = false;
            if(this.remoteManagementSystemStatus && this.remoteManagementSystemStatus.location) {
                haveAllParticleValue = !_.find(this.remoteManagementSystemStatus.particles, (r) => {
                    return !r.organism_value;
                });
            }
            return this.device && this.device.mir_status && ['B', 'C', 'D'].contains(this.device.mir_status.current_status) && !haveAllParticleValue;
        },
        exportUrl() {
            let url = '/remoteManagementSystemStatuses.xlsx';
            let kvQueryList = [];
            const form = this.getPureForm(this.form);
            if(form.page) {
                delete form.page;
            }
            _.forOwn(form, function(value, key) {
                if(_.isArray(value)) {
                    _.forEach(value, (r) => {
                        kvQueryList.push(`${key}[]=${r}`);
                    });
                } else {
                    kvQueryList.push(`${key}=${value}`);
                }
            });
            if(kvQueryList.length > 0) {
                url += '?';
                url += kvQueryList.join('&');
            }
            return url;
        }
    },
    data() {
        return {
            loading: true,
            showReload: false,
            remoteManagementSystemStatuses: [],
            form: {
                map_id: null
            },
            delayTimer: null,
            missionQueue: null,
            remoteManagementSystemStatus: null
        }
    },
    created() {
        this.fetchData();
        this.subscribeRemoteManagementSystemStatuses();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/remote-management-system-statuses', {
                    params: this.form
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.remoteManagementSystemStatuses = data.remoteManagementSystemStatuses;
                    this.missionQueue = data.missionQueue;
                    this.remoteManagementSystemStatus = data.remoteManagementSystemStatus;
                } else if(res.status == -21) {
                    this.showReload = true;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        delay(callback, ms) {
            clearTimeout(this.delayTimer);
            this.delayTimer = setTimeout(function() {
                callback.apply();
            }, ms || 0);
        },
        subscribeRemoteManagementSystemStatuses() {
            const that = this;
            window.Echo.private('remoteManagementSystemStatuses').listen('RemoteManagementSystemStatusCreated', (res) => {
                const remoteManagementSystemStatus = _.cloneDeep(res.remoteManagementSystemStatus);

                // 判斷目前列表第一筆的 mission_queue_id 和新進來的是否不同
                const currentQueueId = that.remoteManagementSystemStatuses.length > 0
                    ? that.remoteManagementSystemStatuses[0].mission_queue_id
                    : null;

                if(currentQueueId !== remoteManagementSystemStatus.mission_queue_id) {
                    // 不同，直接清空清單，只塞這一筆
                    that.remoteManagementSystemStatuses = [remoteManagementSystemStatus];
                } else {
                    // 相同，照原本邏輯新增（最多十筆）
                    that.remoteManagementSystemStatuses.push(remoteManagementSystemStatus);
                    if(that.remoteManagementSystemStatuses.length > 10) {
                        that.remoteManagementSystemStatuses.splice(0, 1);
                    }
                }

                that.remoteManagementSystemStatus = remoteManagementSystemStatus;
            }).listen('RemoteManagementSystemStatusUpdated', (res) => {
                const remoteManagementSystemStatus = _.cloneDeep(res.remoteManagementSystemStatus);

                const currentQueueId = that.remoteManagementSystemStatuses.length > 0
                    ? that.remoteManagementSystemStatuses[0].mission_queue_id
                    : null;

                if(currentQueueId !== remoteManagementSystemStatus.mission_queue_id) {
                    // mission_queue_id 已變更，直接清空清單，只留這筆
                    that.remoteManagementSystemStatuses = [remoteManagementSystemStatus];
                } else {
                    // mission_queue_id 一樣，更新舊資料
                    const idx = _.findIndex(that.remoteManagementSystemStatuses, s => s.id == remoteManagementSystemStatus.id);
                    if(idx !== -1) {
                        that.remoteManagementSystemStatuses.splice(idx, 1, remoteManagementSystemStatus);
                    }
                }

                that.remoteManagementSystemStatus = remoteManagementSystemStatus;
            }).listen('MissionQueueCreated', (res) => {
                that.missionQueue = _.cloneDeep(res.missionQueue);
                that.remoteManagementSystemStatuses = [];
            }).listen('MissionQueueUpdated', (res) => {
                const missionQueue = _.cloneDeep(res.missionQueue);
                if(!that.missionQueue || that.missionQueue.id == missionQueue.id) {
                    that.missionQueue = missionQueue;
                }
            });
        },
        getLargestSecondMark(gasSamplings = []) {
            const maxSecondMarkGasSampling = _.maxBy(gasSamplings, 'second_mark');
            return (maxSecondMarkGasSampling && maxSecondMarkGasSampling.second_mark) ? maxSecondMarkGasSampling.second_mark : 0;
        }
    }
}
</script>

<style scoped lang="scss">
.title-right-block{
    float: right;
}
.box-body{
    padding: 30px;
}
.table-striped > tbody tr:nth-child(even){
    background-color: #F5F5F5;
}
table{
    margin-top: 20px;
}
.location-select-div{
    display:     inline-block;
    position:    relative;
    top:         -2px;
    margin-left: 16px;
}
@keyframes breathing{
    0%{
        background-color: #D2D6DE;
    }
    50%{
        background-color: #F0AD4E;
    }
    100%{
        background-color: #D2D6DE;
    }
}
.breathing-effect{
    animation: breathing 1.2s infinite ease-in-out;
}
.flow-chart-remote-management-system{
    font-size: 24px;
    i{
        vertical-align: middle;
    }
    .label-node{
        position:         relative;
        background-color: #D2D6DE;
        padding:          8px 16px;
        border-radius:    8px;
        .second{
            position:  absolute;
            right:     0;
            top:       -24px;
            font-size: 18px;
        }
    }
    .arrow-gap{
        padding: 8px;
    }
}
</style>
