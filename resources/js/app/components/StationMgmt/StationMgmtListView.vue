<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';

export default {
    name: "StationMgmtListView",
    components: {ClipLoader},
    props: {
        titleText: {
            type: String,
            default: '工作站管理'
        },
        tableFontSize: {
            type: Number,
            default: 18
        }
    },
    data() {
        return {
            loading: false,
            stationMgmts: []
        };
    },
    created() {
        this.fetchData();
        const that = this;
        window.Echo.private('stationMgmts').listen('StationMgmtCreated', (res) => {
            that.stationMgmts.unshift(res.stationMgmt);
        }).listen('StationMgmtUpdated', (res) => {
            const stationMgmtIdx = _.findIndex(that.stationMgmts, ['station_id', res.stationMgmt.station_id]);
            if(stationMgmtIdx != -1) {
                that.stationMgmts.splice(stationMgmtIdx, 1, res.stationMgmt);
            }
        }).listen('StationMgmtDeleted', (res) => {
            const stationMgmtIdx = _.findIndex(that.stationMgmts, ['station_id', res.stationMgmt.station_id]);
            if(stationMgmtIdx != -1) {
                that.stationMgmts.splice(stationMgmtIdx, 1);
            }
        });
        window.Echo.private('stationStatuses').listen('StationStatusUpdated', (res) => {
            const stationStatus = res.stationStatus;
            const stationMgmt = stationStatus.station_mgmt;
            delete stationStatus.station_mgmt;
            stationMgmt.station_status = stationStatus;
            const stationMgmtIdx = _.findIndex(that.stationMgmts, ['station_id', stationMgmt.station_id]);
            if(stationMgmtIdx != -1) {
                that.stationMgmts.splice(stationMgmtIdx, 1, stationMgmt);
            }
        });
    },
    destroyed() {
        window.Echo.leave('stationMgmts');
        window.Echo.leave('stationStatuses');
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/stationMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.stationMgmts = data.stationMgmts;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        }
    }
}
</script>

<template>
    <div>
        <section class="content-header clearfix list-page-title">
            <span>{{ titleText }}</span>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table" :style="`font-size: ${tableFontSize}px`">
                                    <thead class="table-head">
                                    <tr>
                                        <th class="text-center hide-td" colspan="7">工作站資訊</th>
                                        <th class="text-center hide-td" colspan="1">即時狀態</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center hide-td">工作站編號</th>
                                        <th class="text-center hide-td">工作站群組</th>
                                        <th class="text-center hide-td">屬性</th>
                                        <th class="text-center hide-td">站點名稱</th>
                                        <th class="text-center hide-td">裝置名稱</th>
                                        <th class="text-center hide-td">區域</th>
                                        <th class="text-center hide-td">啟用狀態</th>
                                        <th class="text-center hide-td">工作站狀態</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(stationMgmt, index) in stationMgmts" :class="{'tr-gray' : index % 2 == 1}">
                                        <td data-title="工作站編號" class="text-center">{{ stationMgmt.station_id }}</td>
                                        <td data-title="工作站群組" class="text-center">{{ stationMgmt.station_group }}</td>
                                        <td data-title="屬性" class="text-center">{{ stationMgmt.attribute }}</td>
                                        <td data-title="站點名稱" class="text-center">{{ stationMgmt.vertex_name }}</td>
                                        <td data-title="裝置名稱" class="text-center">{{ stationMgmt.device_name }}</td>
                                        <td data-title="區域" class="text-center">
                                            <template v-if="stationMgmt.vertex && stationMgmt.vertex.region_mgmt">
                                                {{ stationMgmt.vertex.region_mgmt.region }}
                                            </template>
                                        </td>
                                        <td data-title="啟用狀態" class="text-center">
                                            <b :class="{'red':!stationMgmt.enable, 'green':stationMgmt.enable}">{{ stationMgmt.enable ? '已啟用' : '禁用' }}</b>
                                        </td>
                                        <td data-title="工作站狀態" class="text-center">
                                            <template v-if="stationMgmt.station_status">{{ stationMgmt.station_status.station_status }}</template>
                                        </td>
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

<style scoped>
th{
    vertical-align: middle !important;
}
.table{
    margin-bottom: 10px;
}
</style>
