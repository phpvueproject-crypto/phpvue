<template>
    <div>
        <section class="content-header clearfix list-page-title">
            <span>排程任務&nbsp;</span>
            <button class="btn btn-light-green op-btn" @click="modal.show = true">
                <i class="fa fa-plus" aria-hidden="true"/>&nbsp;創建排程任務
            </button>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table">
                                    <thead>
                                    <tr class="table-head">
                                        <th class="text-center">任務編號</th>
                                        <th class="text-center">任務類型</th>
                                        <th class="text-center">車輛ID
                                            <sort-button v-model="systemIdSortType" column="system_id" @change="changeSortType"/>
                                        </th>
                                        <th class="text-center">時間
                                            <sort-button v-model="timeSortType" column="time" @change="changeSortType"/>
                                        </th>
                                        <th class="text-center">參數</th>
                                        <th class="text-center">創建日期</th>
                                        <th class="text-center">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(scheduledMission, index) in scheduledMissions" :class="[index % 2 == 0 ? 'tr-gray' : '']">
                                        <td data-title="任務編號" class="text-center">{{ scheduledMission.mission_id }}</td>
                                        <td data-title="任務類型" class="text-center">{{ scheduledMission.mission_type }}</td>
                                        <td data-title="車輛ID" class="text-center">{{ scheduledMission.system_id }}</td>
                                        <td data-title="時間" class="text-center">
                                            {{ scheduledMission.years ? scheduledMission.years : '年' }}年{{ scheduledMission.month ? scheduledMission.month : '每' }}月{{ scheduledMission.day ? scheduledMission.day : '每' }}日{{ scheduledMission.week ? '每週' + chineseNumber(scheduledMission.week) : '' }}{{ scheduledMission.hours ? (scheduledMission.hours + '點') : '每小時' }}{{ scheduledMission.minutes ? (scheduledMission.minutes + '分') : '每分鐘' }}
                                        </td>
                                        <td data-title="參數" class="text-center">{{ scheduledMission.parameter }}</td>
                                        <td data-title="創建日期" class="text-center">{{ scheduledMission.create_ts | datetime }}</td>
                                        <td data-title="操作" class="text-center">
                                            <button class="btn btn-delete btn-sm" @click="deleteRow(scheduledMission.mission_id)" title="刪除">
                                                <i class="glyphicon glyphicon-trash"/>
                                                刪除
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <pagination size="sm"
                                                v-model="pagination.current_page"
                                                :direction-links="false"
                                                :boundary-links="true"
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
        </section>
        <scheduled-mission-modal v-model="modal.show"
                                 @update="fetchData"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import ScheduledMissionModal from './ScheduledMissionModal.vue';
import SortButton from '../Module/SortButton.vue';

export default {
    name: "ScheduledMissionListPage",
    components: {SortButton, ClipLoader, ScheduledMissionModal},
    data() {
        return {
            loading: false,
            scheduledMissions: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            modal: {
                show: false
            },
            form: {
                mission_id: this.$route.query.mission_id ? this.$route.query.mission_id : null,
                order_by: this.$route.query.order_by ? this.$route.query.order_by : null,
                direction: this.$route.query.direction ? this.$route.query.direction : null
            },
            systemIdSortType: null,
            timeSortType: null
        };
    },
    created() {
        this.fetchData();
        this.initSortButton();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/scheduledMissions', {
                    params: {
                        page: this.pagination.current_page,
                        mission_id: this.form.mission_id,
                        order_by: this.form.order_by,
                        direction: this.form.direction
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.scheduledMissions = data.scheduledMissions;
                    if(data.pagination) {
                        this.pagination = data.pagination;
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        async deleteRow(missionId) {
            if(!confirm(`確定要刪除任務編號「${missionId}」？`)) {
                return;
            }
            try {
                let res = await axios.post(`/api/mqttCommands`, {
                    mqtt_command_type_id: 40,
                    mission_id: missionId
                });
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '已送出命令'
                    });
                    this.scheduledMissions = _.filter(this.scheduledMissions, (r) => {
                        return r.mission_id != missionId;
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        changePage(page) {
            const form = _.cloneDeep(this.form);
            form.page = page;
            const query = this.getPureForm(form);
            this.$router.push({
                path: `/scheduledMissions`,
                query: query
            });
        },
        changeSortType(column, sortType) {
            switch(column) {
                case 'system_id':
                    this.systemIdSortType = sortType;
                    this.timeSortType = null;
                    break;
                case 'time':
                    this.systemIdSortType = null;
                    this.timeSortType = sortType;
                    break;
                default:
                    return;
            }
            if(column && sortType) {
                this.form.order_by = column;
                this.form.direction = sortType;
            } else {
                this.form.order_by = null;
                this.form.direction = null;
            }

            this.changePage(1);
        },
        initSortButton() {
            switch(this.$route.query.order_by) {
                case 'system_id':
                    this.systemIdSortType = this.$route.query.direction;
                    break;
                case 'time':
                    this.timeSortType = this.$route.query.direction;
                    break;
            }
        },
        chineseNumber(number) {
            let ch = '';
            if(number == 1) {
                ch = '一';
            } else if(number == 2) {
                ch = '二';
            } else if(number == 3) {
                ch = '三';
            } else if(number == 4) {
                ch = '四';
            } else if(number == 5) {
                ch = '五';
            } else if(number == 6) {
                ch = '六';
            } else if(number == 7) {
                ch = '日';
            }
            return ch;
        }
    }
}
</script>

<style scoped>
.op-btn{
    font-weight:    bold;
    vertical-align: top;
    border-radius:  6px;
    padding:        5px 7px 2px 6px;
}
</style>
