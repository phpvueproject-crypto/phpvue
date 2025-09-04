<template>
    <div>
        <section class="content-header clearfix">
            <span :style="`color: ${titleText == '停車位管理' ? '#FFFFFF' : '#000000'}`">{{ titleText }}&emsp;</span>
            <button v-if="showCreateBtn" class="btn btn-light-green op-btn" @click="showModal(null)">
                <i class="fa fa-plus" aria-hidden="true"/>&nbsp;創建停車位
            </button>
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
                                        <th class="text-center hide-td" colspan="6">停車位資訊</th>
                                        <th class="text-center hide-td" colspan="4">即時狀態</th>
                                        <th v-if="showEditBtn" class="text-center hide-td" rowspan="2">操作</th>
                                    </tr>
                                    <tr>
                                        <!--停車位資訊-->
                                        <th class="text-center hide-td">停車位編號</th>
                                        <th class="text-center hide-td">站點位置</th>
                                        <th class="text-center hide-td">區域</th>
                                        <th class="text-center hide-td">首選車輛</th>
                                        <th class="text-center hide-td">屬性</th>
                                        <th class="text-center hide-td">啟用狀態</th>
                                        <!--即時狀態-->
                                        <th class="text-center hide-td">預定狀態</th>
                                        <th class="text-center hide-td">預定車輛</th>
                                        <th class="text-center hide-td">目前停放</th>
                                        <th class="text-center hide-td">更新時間</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(parkingLot, index) in parkingLotMgmts" :class="{'tr-gray' : index % 2 == 1}">
                                        <!--停車位資訊-->
                                        <td data-title="停車位編號" class="text-center">{{ parkingLot.parking_lot_id }}</td>
                                        <td data-title="站點位置" class="text-center">{{ parkingLot.vertex_name }}</td>
                                        <td data-title="區域" class="text-center">{{ parkingLot.vertex ? parkingLot.vertex.region_mgmt.region : null }}</td>
                                        <td data-title="首選車輛" class="text-center">{{ parkingLot.prefer_vehicle }}</td>
                                        <td data-title="屬性" class="text-center">{{ parkingLot.attribute }}</td>
                                        <td data-title="啟用狀態" class="text-center">
                                            <b :class="{'red': !parkingLot.enable, 'green': parkingLot.enable}">{{ parkingLot.enable ? '已啟用' : '未啟用' }}</b>
                                        </td>
                                        <!--即時狀態-->
                                        <td data-title="預定狀態" class="text-center">{{ parkingLot.parking_lot_status ? parkingLot.parking_lot_status.parking_lot_status : null }}</td>
                                        <td data-title="預定車輛" class="text-center">{{ parkingLot.parking_lot_status ? parkingLot.parking_lot_status.booking_vehicle_id : null }}</td>
                                        <td data-title="目前停放" class="text-center">{{ parkingLot.parking_lot_status ? parkingLot.parking_lot_status.occupied_vehicle_id : null }}</td>
                                        <td v-if="parkingLot.parking_lot_status" data-title="更新時間" class="text-center">{{ parkingLot.parking_lot_status.update_ts | datetime }}</td>
                                        <td v-else data-title="更新時間" class="text-center">{{ null }}</td>
                                        <td v-if="showEditBtn" data-title="操作" class="text-center">
                                            <button class="btn btn-light-green btn-sm" @click="showModal(parkingLot.parking_lot_id)" title="修改">
                                                <i class="glyphicon glyphicon-pencil"/>
                                                修改
                                            </button>
                                            <button class="btn btn-delete btn-sm" @click="deleteRow(parkingLot)" title="刪除">
                                                <i class="glyphicon glyphicon-trash"/>
                                                刪除
                                            </button>
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
        <parking-lot-mgmt-modal v-model="modal.show"
                                :pid="modal.parking_lot_id"
                                @update="resetRow"
                                @refresh="fetchData"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import ParkingLotMgmtModal from './ParkingLotMgmtModal';

export default {
    name: "ParkingLotMgmtListView",
    components: {ClipLoader, ParkingLotMgmtModal},
    props: {
        showEditBtn: {
            type: Boolean,
            default: false
        },
        showCreateBtn: {
            type: Boolean,
            default: false
        },
        titleText: {
            type: String,
            default() {
                return '停車位狀態';
            }
        },
        tableFontSize: {
            type: Number,
            default: 18
        }
    },
    data() {
        return {
            loading: false,
            parkingLotMgmts: [],
            modal: {
                show: false,
                parking_lot_id: null
            }
        };
    },
    created() {
        this.fetchData();
        const that = this;
        window.Echo.private('parkingLotMgmts').listen('ParkingLotMgmtCreated', (res) => {
            that.parkingLotMgmts.unshift(res.parkingLotMgmt);
        }).listen('ParkingLotMgmtUpdated', (res) => {
            const parkingLotMgmtIdx = _.findIndex(that.parkingLotMgmts, ['parking_lot_id', res.parkingLotMgmt.parking_lot_id]);
            if(parkingLotMgmtIdx != -1) {
                that.parkingLotMgmts.splice(parkingLotMgmtIdx, 1, res.parkingLotMgmt);
            }
        }).listen('ParkingLotMgmtDeleted', (res) => {
            const parkingLotMgmtIdx = _.findIndex(that.parkingLotMgmts, ['parking_lot_id', res.parkingLotMgmt.parking_lot_id]);
            if(parkingLotMgmtIdx != -1) {
                that.parkingLotMgmts.splice(parkingLotMgmtIdx, 1);
            }
        });
        window.Echo.private('parkingLotStatuses').listen('ParkingLotStatusUpdated', (res) => {
            const parkingLotStatus = res.parkingLotStatus;
            const parkingLotMgmt = parkingLotStatus.parking_lot_mgmt;
            delete parkingLotStatus.parking_lot_mgmt;
            parkingLotMgmt.parking_lot_status = parkingLotStatus;
            const parkingLotMgmtIdx = _.findIndex(that.parkingLotMgmts, ['parking_lot_id', parkingLotMgmt.parking_lot_id]);
            if(parkingLotMgmtIdx != -1) {
                that.parkingLotMgmts.splice(parkingLotMgmtIdx, 1, parkingLotMgmt);
            }
        });
    },
    destroyed() {
        window.Echo.leave('parkingLotMgmts');
        window.Echo.leave('parkingLotStatuses');
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/parkingLotMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.parkingLotMgmts = data.parkingLotMgmts;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        resetRow(row) {
            const idx = _.findIndex(this.parkingLotMgmts, (r) => {
                return r.parking_lot_id == row.parking_lot_id;
            });
            if(idx == -1) {
                this.parkingLotMgmts.unshift(row);
            } else {
                this.parkingLotMgmts.splice(idx, 1, row);
            }
        },
        showModal(id) {
            this.modal.parking_lot_id = id;
            this.modal.show = true;
        },
        async deleteRow(parkingLotMgmt) {
            if(!confirm(`確定要刪除${parkingLotMgmt.parking_lot_id}?`)) {
                return;
            }

            try {
                let res = await axios.delete(`/api/parkingLotMgmts/${parkingLotMgmt.parking_lot_id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '刪除成功'
                    });
                    this.parkingLotMgmts = _.filter(this.parkingLotMgmts, (r) => {
                        return r.parking_lot_id != parkingLotMgmt.parking_lot_id;
                    });
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
th{
    vertical-align: middle !important;
}
</style>
