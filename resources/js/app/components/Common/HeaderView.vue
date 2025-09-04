<template>
    <header class="main-header">
        <router-link to="/regionMgmts?is_deploy=1" class="logo" draggable="false">
            <span class="logo-mini">CD<br>MO</span>
            <span class="logo-lg">GMP廠環境檢測和統計</span>
        </router-link>
        <nav class="navbar navbar-static-top" :class="{'device-connected' : device.is_connected, 'device-disconnected' : !device.is_connected}">
            <a href="#" class="sidebar-toggle" :class="{'device-connected' : device.is_connected, 'device-disconnected' : !device.is_connected}" data-toggle="push-menu" role="button" draggable="false">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <a v-if="device" href="#" class="device-name" :class="{'device-connected' : device.is_connected, 'device-disconnected' : !device.is_connected}" draggable="false">
                <span>{{ device.name }}</span>
            </a>
            <div class="navbar-custom-menu" :class="{'device-connected' : device.is_connected, 'device-disconnected' : !device.is_connected}">
                <ul class="nav navbar-nav">
                    <li v-if="device.is_connected && device.mir_status">
                        <a href="javascript:void(0)" draggable="false" class="no-hover" style="cursor: default">
                            <svg xmlns="http://www.w3.org/2000/svg" class="petri-count"
                                 width="16" height="16" viewBox="8 20 48 32"
                                 fill="none" stroke="currentColor">
                                <ellipse cx="32" cy="28" rx="24" ry="10" stroke-width="3.5" fill="none"/>
                                <ellipse cx="32" cy="36" rx="24" ry="10" stroke-width="3.5" fill="none"/>
                                <line x1="8" y1="28" x2="8" y2="36" stroke-width="3.5"/>
                                <line x1="56" y1="28" x2="56" y2="36" stroke-width="3.5"/>
                                <circle cx="24" cy="32" r="2.5" fill="currentColor"/>
                                <circle cx="34" cy="30" r="2.5" fill="currentColor"/>
                                <circle cx="42" cy="34" r="2.5" fill="currentColor"/>
                            </svg>
                            <span title="目前培養皿數量 / 暫存區培養皿數量">&nbsp;{{ device.mir_status.remaining_petri_count || 0 }} / {{ device.mir_status.initial_petri_count || 0 }}</span>
                        </a>
                    </li>
                    <li><a class="no-hover"><span class="split"></span></a></li>
                    <li v-if="device.is_connected">
                        <div v-if="device.mir_status" class="bar-link">
                            <div class="map-name-div" v-if="device.mir_status.map">{{ device.mir_status.map.name }}</div>
                            <button v-if="[3, 4, 5, 11].contains(device.mir_status.state_id)" class="bar control btn" @click="updateMissionQueues(device.mir_status.state_id == 3 ? 11 : 3, device.mir_status.mission_queue_id)">
                                <i v-if="[3, 5].contains(device.mir_status.state_id)" class="fa fa-pause"></i>
                                <i v-else-if="[4, 11].contains(device.mir_status.state_id)" class="fa fa-play"></i>
                            </button>
                            <div class="bar">
                                <span>
                                    <template v-if="device.mir_status.mission_queue && device.mir_status.mission_queue.mission">
                                       {{ device.mir_status.mission_queue.mission.name }}
                                    </template>
                                    <template v-else>無任務隊列</template>
                                    <template v-if="device.mir_status.location">:{{ device.mir_status.location.device_name }}</template>
                                    <span v-if="device.mir_status.state_text == 'Error'" class="state" style="background-color: #FF001E">
                                        {{ device.mir_status.state_text_zh }}
                                    </span>
                                    <span v-else-if="device.mir_status.state_text == 'ManualControl'" class="state" style="background-color: #FFA500">
                                        {{ device.mir_status.state_text_zh }}
                                    </span>
                                    <span v-else-if="device.mir_status.state_text == 'Ready'" class="state" style="background-color: #008000">
                                        {{ device.mir_status.state_text_zh }}
                                    </span>
                                     <span v-else-if="device.mir_status.state_text == 'Executing'" class="state" style="background-color: #008000">
                                        {{ device.mir_status.state_text_zh }}
                                    </span>
                                    <span v-else-if="device.mir_status.state_text == 'Emergency stop'" class="state" style="background-color: #FF001E">
                                        {{ device.mir_status.state_text_zh }}
                                    </span>
                                    <span v-else-if="device.mir_status.state_text == 'Pause'" class="state" style="background-color: #FFA500">
                                        {{ device.mir_status.state_text_zh }}
                                    </span>
                                    <template v-if="device.mir_status.vehicle_error_type_id">
                                        <i class="fa fa-info-circle is-danger vehicle-error-message-icon"
                                           title="有錯誤訊息，請點擊查看！"
                                           @click="showVehicleErrorTypeModal"/>
                                    </template>
                                </span>
                            </div>
                        </div>
                    </li>
                    <template v-if="device">
                        <li class="dropdown device-menu-2" :class="{'device-connected' : device.is_connected, 'device-disconnected' : !device.is_connected}">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" draggable="false">
                                <template v-if="device.is_connected">已連接</template>
                                <template v-else>已斷開連接</template>
                                <i class="fa fa-caret-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li v-if="device.is_connected">
                                    <a href="#" @click="updateDevice(device.id, 0)"><i class="fa fa-chain-broken"/>斷開連接</a>
                                </li>
                                <li v-else>
                                    <a href="#" @click="updateDevice(device.id, 1)"><i class="fa fa-wifi"/>連接</a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="no-hover"><span class="split"></span></a></li>
                    </template>
                    <li class="dropdown user user-menu">
                        <a href="javascript:void(0)" class="no-hover" style="cursor: default" draggable="false">
                            <i class="fa fa-user" aria-hidden="true"/>
                            <i v-if="permissions.contains('user-role-manage')" class="fa fa-cog icon-addon" aria-hidden="true"/>
                            <span>{{ userAccount }}</span>
                        </a>
                    </li>
                    <li><a class="no-hover"><span class="split"></span></a></li>
                    <!--                    <li class="dropdown">-->
                    <!--                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $t('language') }}<span class="caret"></span></a>-->
                    <!--                        <ul class="dropdown-menu" role="menu">-->
                    <!--                            <li>-->
                    <!--                                <a href="#" @click="setLang('zh')"><img src="/img/icon-zh.png" width=20 height=20> {{ $t('langZh') }}</a>-->
                    <!--                            </li>-->
                    <!--                            <li>-->
                    <!--                                <a href="#" @click="setLang('en')"><img src="/img/icon-us.png" width=20 height=20> {{ $t('langEn') }}</a>-->
                    <!--                            </li>-->
                    <!--                        </ul>-->
                    <!--                    </li>-->
                    <li><a href="javascript:void(0)" draggable="false" @click="logout">{{ $t('logOut') }}</a></li>
                    <li><a class="no-hover"><span class="split"></span></a></li>
                    <li class="battery">
                        <a v-if="device.mir_status" href="javascript:void(0)" class="no-hover" style="cursor: default;" draggable="false">
                            <i id="battery-icon" class="fa fa-map-marker"></i>
                            <span class="battery-level">
                                <template v-if="device.is_connected == 1">
                                    ({{ JSON.parse(device.mir_status.position).x.toFixed(2) }}, {{ JSON.parse(device.mir_status.position).y.toFixed(2) }})
                                </template>
                                <template v-else>
                                    未知
                                </template>
                            </span>
                            <i v-if="device.is_connected == 1" id="battery-icon" :class="[batteryIconClass]"></i>
                            <i v-else id="battery-icon" class="fa fa-battery-empty"></i>
                            <span class="battery-level">
                                <template v-if="device.is_connected == 1">{{ device.mir_status.battery_percentage | integer }}%</template>
                                <template v-else>
                                    未知
                                </template>
                            </span>
                        </a>
                        <a v-else href="javascript:void(0)" class="no-hover" style="cursor: default;" draggable="false">
                            <i id="battery-icon" class="fa fa-map-marker"></i>
                            <span class="battery-level">
                                (0.00, 0.00)
                            </span>
                            <i id="battery-icon" class="fa fa-battery-empty"></i>
                            <span class="battery-level">0%</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <vehicle-error-type-modal v-model="vehicleErrorTypeModal.show" :mir-status="vehicleErrorTypeModal.mir_status"/>
    </header>
</template>

<script>
import axios from 'axios';
import {mapState, mapGetters} from 'vuex';
import VueI18n from 'vue-i18n';
import VehicleErrorTypeModal from '../VehicleErrorType/VehicleErrorTypeModal.vue';
import MapModal from '../Map/MapModal.vue';

export default {
    name: "HeaderView",
    components: {MapModal, VehicleErrorTypeModal, VueI18n},
    computed: {
        ...mapState({
            userAccount: state => state.user.user.account,
            userId: state => state.user.user.id
        }),
        ...mapGetters({
            user: 'user/user',
            roles: 'user/roles',
            permissions: 'user/permissions',
            device: 'device/device'
        }),
        batteryIconClass() {
            let icon = 'fa-battery-empty';
            if(this.device.mir_status) {
                if(this.device.mir_status.battery_percentage > 75) {
                    icon = 'fa-battery-full';
                } else if(this.device.mir_status.battery_percentage > 50) {
                    icon = 'fa-battery-three-quarters';
                } else if(this.device.mir_status.battery_percentage > 25) {
                    icon = 'fa-battery-half';
                } else if(this.device.mir_status.battery_percentage > 10) {
                    icon = 'fa-battery-quarter';
                }
            }

            return `fa ${icon}`;
        }
    },
    data() {
        return {
            vehicleErrorTypeModal: {
                show: false,
                mir_status: null
            }
        };
    },
    created() {
        this.subscribe();
    },
    methods: {
        async logout() {
            try {
                let res = await axios.post('/logout');
                let data = res.data;
                if(data.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '登出成功'
                    });
                    await this.syncCsrfToken();
                    this.$store.commit('user/UPDATE_USER', null);
                    this.$router.push('/');
                }
            } catch(err) {
                this.$store.commit('user/UPDATE_USER', null);
            }
        },
        setLang(value) {
            this.$store.commit('UPDATE_LANG', value);
            this.$i18n.locale = value;
            localStorage.setItem('local-lang', value);
        },
        async updateDevice(deviceId, isConnected) {
            try {
                let res = await axios.patch(`/api/devices/${deviceId}/is-connected`, {
                    is_connected: isConnected
                });
                res = res.data;
                if(res.status == 0) {
                    await this.syncUser();
                    const data = res.data;
                    const device = data.device;
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
        },
        async updateMissionQueues(stateId, missionQueueId) {
            this.sending = true;
            try {
                let res = await axios.put(`/api/missionQueues/${missionQueueId}`, {
                    state_id: stateId
                });
                res = res.data;
                if(res.status == 0) {
                    this.device.mir_status.state_id = stateId;
                    if(stateId == 3) {
                        this.$toast.success({
                            title: '成功訊息',
                            message: '開始成功！'
                        });
                    } else {
                        this.$toast.success({
                            title: '成功訊息',
                            message: '暫停成功！'
                        });
                    }
                } else if(res.status == -19) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '任務執行失敗！'
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.sending = false;
            }
        },
        subscribe() {
            const that = this;
            window.Echo.private('mirStatuses').listen('MirStatusCreated', (res) => {
                that.device.mir_status = res.mirStatus;
                if(that.vehicleErrorTypeModal.show) {
                    that.vehicleErrorTypeModal.mir_status = that.device.mir_status;
                    that.vehicleErrorTypeModal.show = !!that.device.mir_status.vehicle_error_type_id;
                }
            }).listen('MirStatusUpdated', (res) => {
                that.device.mir_status = res.mirStatus;
                if(that.vehicleErrorTypeModal.show) {
                    that.vehicleErrorTypeModal.mir_status = that.device.mir_status;
                    that.vehicleErrorTypeModal.show = !!that.device.mir_status.vehicle_error_type_id;
                }
            }).listen('DeviceUpdated', (res) => {
                that.$store.commit('device/UPDATE_DEVICE', res.device);
            });
        },
        showVehicleErrorTypeModal() {
            this.vehicleErrorTypeModal.mir_status = (this.device && this.device.mir_status) ? this.device.mir_status : null;
            this.vehicleErrorTypeModal.show = true;
        }
    }
}
</script>

<style lang="scss" scoped>
.main-header{
    user-select:       none;
    -webkit-user-drag: none;
}
.skin-green-light .main-header .navbar{
    background-color: #5c9b6c;
}
.skin-green-light .main-header .logo{
    background-color: #5c9b6c;
}
.skin-green-light .main-header .navbar .sidebar-toggle:hover{
    background: rgba(0, 0, 0, 0.1);
}
.logo{
    height: 55px;
    .logo-mini{
        margin-top: 3px
    }
    .logo-lg{
        font-size:      18px;
        line-height:    55px;
        font-weight:    bold;
        letter-spacing: 1px;
    }
}
.sidebar-toggle, .nav > li > a, .logo{
    line-height: 25px;
}
.icon-addon{
    margin-left: -12px;
    font-size:   10pt;
}
.split{
    border-right: #FFFFFF solid;
}
.no-hover:hover, .no-hover:focus{
    background: none !important;
}
.device-name{
    float:       left;
    color:       #FFFFFF;
    font-weight: bold;
    font-family: NotoSansTC, system-ui;
    line-height: 25px;
    padding:     15px;
    cursor:      default;
    transition:  all 500ms ease-in-out;
}
.device-menu{
    width:      144px;
    text-align: right;
    cursor:     default;
    transition: all 500ms ease-in-out;
}
.sidebar-toggle{
    transition: all 500ms ease-in-out;
}
.device-connected{
    background-color: #5C9B6C !important;
}
.device-disconnected{
    background-color: #DB736A !important;
}
.battery{
    color: white;
    .fa-battery-half{
        color: white;
    }
}
.fa.fa-battery-full{
    color: limegreen;
}
.fa.fa-battery-three-quarters{
    color: limegreen;
}
.fa.fa-battery-half{
    color: gold;
}
.fa.fa-battery-quarter{
    color: orange;
}
.fa.fa-battery-empty{
    color: grey;
}
.fa.fa-map-marker.blue{
    color: blue;
}
.fa.fa-map-marker.red{
    color: red;
}
.fa.fa-map-marker.orange{
    color: orange;
}
.fa.fa-map-marker.gold{
    color: gold;
}
.fa.fa-map-marker.limegreen{
    color: limegreen;
}
.battery-level{
    margin-right: 10px;
}
.device-menu-2{
    padding-left: 20px;
}
.bar{
    background-color: rgba(0, 0, 0, 0.5);
    padding:          5px;
    border-radius:    5px;
    float:            left;
}
.control{
    padding:      5px 10px;
    margin-right: 3px;
    cursor:       pointer;
}
.bar-link{
    padding-top:    10px;
    padding-bottom: 10px;
    cursor:         default;
    color:          white;
}
.state{
    font-size:     12px;
    padding:       3px;
    border-radius: 3px;
    margin-left:   10px;
}
.map-name-div{
    float:         left;
    font-weight:   bold;
    font-family:   NotoSansTC, system-ui;
    padding:       5px;
    border-radius: 5px;
    margin-right:  3px;
}
.vehicle-error-message-icon{
    cursor:      pointer;
    margin-left: 8px;
}
.petri-count{
    vertical-align: text-bottom;
}
</style>
