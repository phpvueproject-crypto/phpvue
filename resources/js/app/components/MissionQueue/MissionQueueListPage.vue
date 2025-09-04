<template>
    <div>
        <section class="content-header clearfix">
            <span>{{ viewModes[viewMode].name }}</span>
            <div v-if="viewMode == 1" class="pull-right">
                <button class="btn btn-default" @click="fetchData(true, true)">
                    <i class="fa fa-refresh"/>
                    同步更新
                </button>
            </div>
        </section>
        <section class="content">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <!--篩選列-->
                        <div v-if="viewMode == 1" class="row">
                            <form class="col-xs-12" @submit.prevent="search">
                                <label>日期範圍：</label>
                                <datetimepicker v-model="form.start_date"
                                                id="start_date"
                                                type="date"
                                                format="yyyy-MM-dd"
                                                input-class="form-control"
                                                picker-class="theme-light-green"
                                                placeholder="請選擇"
                                                style="width: 120px"
                                                :max-datetime="maxDatetime"/>
                                <span> ~ </span>
                                <datetimepicker v-model="form.end_date"
                                                id="end_date"
                                                type="date"
                                                format="yyyy-MM-dd"
                                                input-class="form-control"
                                                picker-class="theme-light-green"
                                                placeholder="請選擇"
                                                style="width: 120px"
                                                :max-datetime="maxDatetime"/>
                                <label>名稱：</label>
                                <div style="display: inline-block">
                                    <input v-model="form.name" type="text" class="form-control" placeholder="搜尋名稱"/>
                                </div>
                                <button type="submit" class="btn btn-default-3d search-btn" @click="search">查詢</button>
                            </form>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div v-if="!loading" class="col-xs-12">
                                <template v-if="viewMode == 0">
                                    <div v-if="!showReload" class="data-list">
                                        <div class="data-row"
                                             v-for="missionQueue in missionQueues">
                                            <div class="data-row-status">
                                                <i v-if="missionQueue.state == 'Executing'" class="fa fa-spinner fa-pulse fa-fw color-start"/>
                                                <i v-else-if="missionQueue.state == 'Pending'" class="fa fa-pause fa-fw"/>
                                            </div>
                                            <div class="data-row-content">
                                                <div class="data-row-title">
                                                    <span v-if="missionQueue.mission">{{ missionQueue.mission.name }}
                                                        <a v-if="missionQueue.locations_count || missionQueue.locations_count == 0" href="javascript:void(0)" @click="showModal(missionQueue.id)">({{ missionQueue.locations_count }})</a>
                                                    </span>
                                                </div>
                                                <div class="data-row-subtitle">
                                                    {{ missionQueue.id }}
                                                    <span v-if="missionQueue.mission && missionQueue.mission.region">/ {{ missionQueue.mission.region }}</span>
                                                    <template v-if="position.position"> / x:({{ position.position.x }}, y:{{ position.position.y }})</template>
                                                </div>
                                            </div>
                                            <div class="data-row-right-block">
                                                <button type="button" class="btn btn-delete-3d op-btn" title="刪除"
                                                        :disabled="sending"
                                                        @click="destroyMissionQueues(missionQueue)">
                                                    <i class="fa fa-trash"/>
                                                </button>
                                                <button type="button" class="btn btn-default-3d op-btn" title="暫停"
                                                        :disabled="sending"
                                                        @click="updateMissionQueues(missionQueue.id, 11)">
                                                    <i class="fa fa-pause"/>
                                                </button>
                                                <button type="button" class="btn btn-light-green-3d op-btn" title="開始"
                                                        :disabled="sending"
                                                        @click="updateMissionQueues(missionQueue.id, 3)">
                                                    <i class="fa fa-play"/>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <reload v-else @click="showReload = false; fetchData(false, true)"/>
                                </template>
                                <template v-else-if="viewMode == 1">
                                    <div class="data-list">
                                        <div class="data-row"
                                             v-for="missionQueue in missionQueues">
                                            <div class="data-row-status">
                                                <i v-if="missionQueue.state == 'Done'" class="fa fa-check fa-fw color-start"/>
                                                <i v-else-if="missionQueue.state == 'Aborted'" class="fa fa-ban color-stop"/>
                                                <i v-else-if="missionQueue.state == 'Failed'" class="fa fa-times-circle color-stop"/>
                                            </div>
                                            <div class="data-row-content">
                                                <div class="data-row-title">
                                                    {{ missionQueue.mission ? missionQueue.mission.name : null }}
                                                </div>
                                                <div class="data-row-subtitle">
                                                    {{ missionQueue.id }}
                                                    <template v-if="missionQueue.mission"> / {{ missionQueue.mission_id }}
                                                        <a v-if="missionQueue.locations_count || missionQueue.locations_count == 0" href="javascript:void(0)" @click="showModal(missionQueue.id)">({{ missionQueue.locations_count }})</a>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="data-row-right-block">
                                                <template v-if="missionQueue.finished">
                                                    <div style="letter-spacing: 0">開始日期：
                                                        <template v-if="missionQueue.started">{{ missionQueue.started | datetime }}</template>
                                                    </div>
                                                    <div style="letter-spacing: 0">結束日期：
                                                        <template v-if="missionQueue.finished">{{ missionQueue.finished | datetime }}</template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="pagination" class="text-center">
                                        <pagination :direction-links="false"
                                                    :boundary-links="true"
                                                    size="sm"
                                                    v-model="pagination.current_page"
                                                    :total-page="pagination.last_page"
                                                    :max-size="10"
                                                    @change="changePage"/>
                                    </div>
                                </template>
                            </div>
                            <clip-loader v-else class="loading" color="gray" size="30px"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <mission-queue-modal v-model="modal.show"
                             :data-id="modal.id"/>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';
import moment from 'moment/moment';
import Datetimepicker from '../Module/Datetimepicker.vue';
import Reload from '../Common/Reload.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import MissionQueueModal from './MissionQueueModal.vue';

const FETCH_INTERVAL = 5000;
export default {
    name: "MissionQueueListPage",
    components: {
        MissionQueueModal,
        Reload,
        Datetimepicker,
        ClipLoader
    },
    props: {
        viewMode: {
            type: Number,
            default: 0
        }
    },
    computed: {
        maxDatetime() {// 日期選擇器限定範圍
            return moment().endOf('day').format();
        }
    },
    data() {
        let {start_date, end_date, name} = this.$route.query;
        if(this.viewMode == 0) {
            start_date = null;
            end_date = null;
            name = null;
        } else {
            if(!end_date) {
                end_date = moment().endOf('day').format('YYYY-MM-DD');
            }
        }
        return {
            loading: true,
            sending: false,
            missionQueues: [],
            position: null,
            form: {
                history: this.viewMode,
                start_date: start_date,
                end_date: end_date,
                name: name,
                page: this.$route.query.page && !_.isNaN(this.$route.query.page) ? this.$route.query.page : 1
            },
            viewModes: [
                {value: 0, name: '目前任務列'},
                {value: 1, name: '歷史任務列'}
            ],
            missionQueueTimer: null,
            showReload: false,
            pagination: null,
            modal: {
                id: null,
                show: false
            }
        }
    },
    created() {
        this.fetchData(false, true);
        this.startMissionQueueTimer();
    },
    beforeDestroy() {
        this.stopMissionQueueTimer();
    },
    methods: {
        async fetchData(refresh = false, showLoading = false) {
            if(showLoading) {
                this.loading = true;
            }
            try {
                let res = await axios.get('/api/missionQueues', {
                    params: {
                        ...this.form,
                        refresh: refresh ? 1 : 0
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.missionQueues = data.missionQueues;
                    this.position = data.position;
                    if(data.pagination) {
                        this.pagination = data.pagination;
                    } else {
                        this.pagination = null;
                    }
                } else if(res.status == -21) {
                    this.showReload = true;
                    this.stopMissionQueueTimer();
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        search() {
            const query = _.pickBy(this.form, _.identity);
            if(!this.isDateRangeValid(query.start_date, query.end_date)) {
                this.$toast.warn({
                    title: '警告訊息',
                    message: '無效的日期範圍。'
                });
                return;
            }
            this.changePage(1);
        },
        async updateMissionQueues(missionQueueId, stateId) {
            this.sending = true;
            try {
                let res = await axios.put(`/api/missionQueues/${missionQueueId}`, {
                    state_id: stateId
                });
                res = res.data;
                if(res.status == 0) {
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
        async destroyMissionQueues(missionQueue) {
            if(!confirm(`確定要刪除「${_.get(missionQueue, 'mission.name', '此任務列')}」？`)) {
                return;
            }
            this.sending = true;
            try {
                let res = await axios.delete(`/api/missionQueues/${missionQueue.id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '執行成功！'
                    });
                } else if(res.status == -19) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '任務執行失敗！'
                    });
                } else if(res.status == -20) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '查無任務！'
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.sending = false;
            }
        },
        startMissionQueueTimer() {
            if(this.viewMode === 0) {
                const that = this;
                if(this.missionQueueTimer) {
                    clearInterval(this.missionQueueTimer);
                }
                this.missionQueueTimer = setInterval(() => {
                    that.fetchData(false, false)
                }, FETCH_INTERVAL);
            }
        },
        stopMissionQueueTimer() {
            if(this.missionQueueTimer) {
                clearInterval(this.missionQueueTimer);
                this.missionQueueTimer = null;
            }
        },
        isDateRangeValid(startDate, endDate) {
            if(!startDate || !endDate) {
                return true; // 如果其中一個日期沒有設置，不進行驗證
            }
            const start = moment(startDate);
            const end = moment(endDate);
            return start.isValid() && end.isValid() && (start.isBefore(end) || start.isSame(end));
        },
        changePage(page) {
            this.form.page = page;
            let path = '';
            if(this.viewMode == 0) {
                path = '/missionQueues/current';
            } else {
                path = '/missionQueues/history';
            }
            this.$router.push({
                path: path,
                query: {
                    ...this.form,
                    t: Date.now()
                }
            });
        },
        showModal(id) {
            this.modal.id = id;
            this.modal.show = true;
        }
    }
}
</script>

<style scoped lang="scss">
.data-list{
    margin-top:    16px;
    margin-bottom: 16px;
}
.data-row{
    border:           1px solid #F1F1F1;
    border-radius:    8px;
    background-image: linear-gradient(#FCFCFC, #FAFAFA, #F6F6F6);
}
.data-row:hover{
    background-image: linear-gradient(#F9F9F9, #efefef);
}
.data-row:not(:first-child){
    margin-top: 15px;
}
.data-row-status{
    display:                   inline-block;
    width:                     100px;
    text-align:                center;
    border-right:              1px solid #F1F1F1;
    border-top-left-radius:    8px;
    border-bottom-left-radius: 8px;
    background-image:          linear-gradient(#F9F9F9, #efefef);
    vertical-align:            middle;
    padding:                   20px;
}
.data-row-content{
    display:        inline-block;
    vertical-align: middle;
    padding:        10px;
    .data-row-title{
    }
    .data-row-subtitle{
        color:     #9A9A99;
        font-size: 14px;
    }
}
.data-row-right-block{
    float:          right;
    letter-spacing: 4px;
    padding:        0 20px;
    margin-top:     20px;
}
.color-start{
    color: #5C9B6C;
}
.color-stop{
    color: #DB736A;
}
.data-row-status .fa{
    font-size: 25px;
}
.search-btn{
    vertical-align: bottom;
}
</style>
