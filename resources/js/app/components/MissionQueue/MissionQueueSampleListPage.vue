<template>
    <div>
        <section class="content-header clearfix">
            <span>歷史採樣任務進度</span>
            <div class="pull-right">
                <button type="button" class="btn btn-default" @click="fetchData(1, true)">
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
                        <div class="row">
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
                                <button type="submit" class="btn btn-default-3d search-btn">查詢</button>
                                <div class="pull-right">
                                    <router-link :to="exportUrlMissionQueueDetails" target="_blank" type="button" class="btn btn-light-green op-btn" title="匯出歷史採樣任務進度（含明細）">
                                        <i class="fa fa-download"/>&nbsp;匯出（含明細）
                                    </router-link>
                                    <router-link :to="exportUrlMissionQueue" target="_blank" type="button" class="btn btn-light-green op-btn" title="匯出歷史採樣任務進度（不含明細）">
                                        <i class="fa fa-download"/>&nbsp;匯出（不含明細）
                                    </router-link>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="box-body">
                        <!--資料列-->
                        <div class="row">
                            <template v-if="!loading">
                                <div class="col-xs-12">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-head">
                                        <tr>
                                            <th class="hide-td text-center">時間</th>
                                            <th class="hide-td text-center">名稱</th>
                                            <th class="hide-td text-center">結果</th>
                                            <th class="hide-td text-center">起始點</th>
                                            <th class="hide-td text-center">結束點</th>
                                            <th class="hide-td text-center">採樣點</th>
                                            <th class="hide-td text-center">總計時數</th>
                                            <th class="hide-td text-center">備註</th>
                                            <th class="hide-td text-center">操作</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template v-for="row in missionQueues">
                                            <tr>
                                                <td data-title="時間" class="centered">{{ row.started | datetime }}</td>
                                                <td data-title="名稱" class="centered">{{ row.mission ? row.mission.name : null }}
                                                    <div class="pull-right">
                                                        <button type="button" class="btn btn-light-green op-btn" title="查看採樣任務進度" @click="showModal(row.id, 'remoteListMode')">
                                                            <i class="fa fa-file-text" aria-hidden="true"/>
                                                        </button>
                                                        <router-link :to="`/missionQueueDetails/${row.id}.xlsx`" target="_blank" type="button" class="btn btn-light-green op-btn" title="匯出該歷史採樣任務進度明細">
                                                            <i class="fa fa-download"/>
                                                        </router-link>
                                                    </div>
                                                </td>
                                                <td data-title="結果" class="centered">{{ row.state }}</td>
                                                <td data-title="起始點" class="centered">{{ row.start_location ? row.start_location.device_name : null }}</td>
                                                <td data-title="結束點" class="centered">{{ row.end_location ? row.end_location.device_name : null }}</td>
                                                <td data-title="採樣點" class="centered">{{ row.remote_management_system_statuses_count }}</td>
                                                <td data-title="總計時數" class="centered">{{ row.remote_management_system_statuses_sum_total_time }}</td>
                                                <td data-title="備註" class="centered">{{ row.remark }}</td>
                                                <td data-title="操作" class="centered">
                                                    <button type="button" class="btn btn-light-green op-btn" title="編輯備註" @click="showModal(row.id)">
                                                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"/>
                                                    </button>
                                                    <button type="button" class="btn btn-delete op-btn" title="刪除" :disabled="sending" @click="deleteRow(row)">
                                                        <span class="glyphicon glyphicon-trash" aria-hidden="true"/>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="pagination" class="col-xs-12 text-center">
                                    <pagination v-model="pagination.current_page"
                                                size="sm"
                                                :total-page="pagination.last_page"
                                                :direction-links="false"
                                                :boundary-links="true"
                                                :max-size="10"
                                                @change="changePage"/>
                                </div>
                            </template>
                            <template v-else>
                                <clip-loader class="loading" color="gray" size="30px"/>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <mission-sample-modal v-model="modal.show"
                              :data-id="modal.id"
                              :mode="modal.mode"
                              @update="updateRow"/>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';
import moment from 'moment/moment';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import Datetimepicker from '../Module/Datetimepicker.vue';
import MissionSampleModal from './MissionQueueSampleModal.vue';

export default {
    name: "MissionQueueSampleListPage",
    components: {
        MissionSampleModal,
        Datetimepicker,
        ClipLoader
    },
    props: {},
    computed: {
        maxDatetime() {
            return moment().endOf('day').format();
        },
        exportUrlMissionQueue() {
            let url = '/missionQueues.xlsx';
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
        },
        exportUrlMissionQueueDetails() {
            let url = '/missionQueueDetails.xlsx';
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
        },
    },
    data() {
        let {start_date, end_date, name} = this.$route.query;
        if(!end_date) {
            end_date = moment().endOf('day').format('YYYY-MM-DD');
        }
        return {
            loading: true,
            sending: false,
            missionQueues: [],
            form: {
                start_date: start_date,
                end_date: end_date,
                name: name,
                page: this.$route.query.page && !_.isNaN(this.$route.query.page) ? this.$route.query.page : 1
            },
            pagination: {current_page: 1, last_page: 1},
            modal: {id: null, mode: null, show: false}
        }
    },
    created() {
        this.fetchData(0, true);
    },
    methods: {
        showModal(id, mode) {
            this.modal.id = id;
            this.modal.mode = mode;
            this.modal.show = true;
        },
        isDateRangeValid(startDate, endDate) {
            if(!startDate || !endDate) {
                return true;
            }
            const start = moment(startDate);
            const end = moment(endDate);
            return start.isValid() && end.isValid() && (start.isBefore(end) || start.isSame(end));
        },
        updateRow(row) {
            const idx = _.findIndex(this.missionQueues, (r) => {
                return r.id == row.id;
            });
            if(idx != -1) {
                this.missionQueues[idx].remark = row.remark;
            }
        },
        async fetchData(refresh = 0, showLoading = false) {
            if(showLoading) {
                this.loading = true;
            }
            try {
                let res = await axios.get('/api/missionQueues', {
                    params: {
                        ...this.form,
                        refresh: refresh,
                        pagination: 1,
                        history: 1
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.missionQueues = _.cloneDeep(data.missionQueues);
                    Object.assign(this.pagination, data.pagination);
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
        changePage(page) {
            this.form.page = page;
            let path = '/missionQueues/sample';
            this.$router.push({
                path: path,
                query: {
                    ...this.form,
                    t: Date.now()
                }
            });
        },
        async deleteRow(row) {
            if(!confirm(`確定要刪除「${_.get(row, 'mission.name', '此筆資料')}」?`)) {
                return;
            }

            try {
                this.sending = true;
                let res = await axios.delete(`/api/missionQueues/${row.id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.missionQueues = _.filter(this.missionQueues, (r) => {
                        return r.id != row.id;
                    });
                } else if(res.status == -19) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: `刪除失敗`
                    });
                } else if(res.status == -20) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: `資料不存在，可能已被刪除`
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.sending = false;
            }
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
    float:       right;
    padding:     0 20px;
    margin-top:  12px;
    line-height: 1.5;
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
.op-btn{
    vertical-align: middle;
}
</style>
