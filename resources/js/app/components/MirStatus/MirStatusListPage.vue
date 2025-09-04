<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';
import _ from 'lodash';

export default {
    name: "MirStatusListPage",
    components: {
        ClipLoader
    },
    computed: {
        deviceId() {
            return this.device ? this.device.id : null;
        }
    },
    watch: {
        deviceId(newVal, oldVal) {
            if(newVal && this.device.is_connected) {
                this.subscribe();
            }
            if(oldVal) {
                this.unsubscribe(oldVal);
            }
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            mirStatuses: [],
            pagination: {
                current_page: 1,
                last_page: 1
            },
            form: {
                pagination: 1,
                page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            device: null
        }
    },
    async created() {
        await this.fetchData();
        this.subscribeDevices();
    },
    destroyed() {
        window.Echo.leave('devices');
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/mirStatuses', {
                    params: this.form
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.mirStatuses = _.map(data.mirStatuses, (r) => {
                        r.position = JSON.parse(r.position);
                        r.velocity = JSON.parse(r.velocity);
                        return r;
                    });
                    this.device = data.device;
                    if(data.pagination) {
                        this.pagination = data.pagination;
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        changePage(page = 1) {
            const query = _.pickBy(this.form, _.identity());
            query.page = page;
            this.$router.push({
                path: '/mirStatuses',
                query: query
            }).catch(() => {
                this.fetchData();
            });
        },
        subscribe() {
            const that = this;
            window.Echo.private(`devices.${this.deviceId}`).listen('MirStatusCreated', (res) => {
                let mirStatus = res.mirStatus;
                let pagination = res.pagination;
                if(mirStatus) {
                    mirStatus.position = JSON.parse(mirStatus.position);
                    mirStatus.velocity = JSON.parse(mirStatus.velocity);
                    if(that.mirStatuses.length == 10) {
                        that.mirStatuses.splice((that.mirStatuses.length - 1), 1);
                    }
                    that.mirStatuses.unshift(mirStatus);
                }
                if(pagination) {
                    that.pagination.last_page = pagination.last_page;
                }
            });
        },
        unsubscribe(deviceId) {
            window.Echo.leave(`devices.${deviceId}`);
        },
        subscribeDevices() {
            const that = this;
            window.Echo.private('devices').listen('DeviceUpdated', (res) => {
                const device = res.device;
                if(device && (device.id == that.deviceId) && !device.is_connected) {
                    that.changePage(1);
                    that.syncUser();
                }
                if(device && (device.id != that.deviceId) && device.is_connected) {
                    that.changePage(1);
                    that.syncUser();
                }
            });
        }
    }
}
</script>

<template>
    <div>
        <div class="col-xs-12 title-block">
            <div class="title-left-block">
                <h3>即時任務列</h3>
            </div>
            <div class="title-right-block"></div>
        </div>
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body">
                    <div v-if="!loading">
                        <table class="table table-bordered table-hover break-table">
                            <thead>
                            <tr class="table-head">
                                <th class="text-center hide-td">編號</th>
                                <th class="hide-td">機器人</th>
                                <th class="hide-td">位置</th>
                                <th class="hide-td">任務文字</th>
                                <th class="hide-td">速度</th>
                                <th class="text-center hide-td">電池百分比</th>
                                <th class="text-center hide-td">採樣時間</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(mirStatus, i) in mirStatuses" :key="mirStatus.id">
                                <td data-title="編號" class="text-center vertical-middle">{{ mirStatus.id }}</td>
                                <td data-title="機器人">{{ mirStatus.robot_model }}</td>
                                <td data-title="位置">
                                    <template v-if="mirStatus.position">
                                        <p>x: {{ mirStatus.position.x }}</p>
                                        <p>y: {{ mirStatus.position.y }}</p>
                                        <p>orientation: {{ mirStatus.position.orientation }}</p>
                                    </template>
                                </td>
                                <td data-title="任務文字">{{ mirStatus.mission_text }}</td>
                                <td data-title="速度">
                                    <template v-if="mirStatus.velocity">
                                        <p>linear: {{ mirStatus.velocity.linear }}</p>
                                        <p>angular: {{ mirStatus.velocity.angular }}</p>
                                    </template>
                                </td>
                                <td data-title="電池百分比" class="text-center">{{ mirStatus.battery_percentage }}&nbsp;%</td>
                                <td data-title="採樣時間" class="text-center">{{ mirStatus.created_at | datetime }}</td>
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
                                        @change="changePage(pagination.current_page)"/>
                        </div>
                    </div>
                    <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.title-block{
    margin-bottom: 30px;
}
.title-left-block{
    display: inline-block;
    h3{
        font-size: 32px;
    }
}
.title-right-block{
    display:    inline-block;
    margin-top: 20px;
    float:      right;
}
.box-body{
    padding: 30px;
}
.table-head th{
    white-space: nowrap;
}
tbody tr:nth-child(even){
    background-color: #F5F5F5;
}
table{
    margin-top: 20px;
}
</style>
