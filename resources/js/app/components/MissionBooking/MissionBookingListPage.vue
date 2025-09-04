<template>
    <div>
        <section class="content-header clearfix">
            <span>預約任務</span>
            <div class="pull-right">
                <button class="btn btn-light-green" style="margin-left: 4px" @click="showModal(null)">
                    <i class="fa fa-plus"/>
                    <span style="margin-left: 4px">創建預約任務</span>
                </button>
            </div>
        </section>
        <section class="content">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div v-if="!loading">
                            <div class="data-list">
                                <div class="data-row"
                                     v-for="item in missionBookings">
                                    <div class="data-row-content">
                                        <div class="data-row-title">
                                            {{ item.mission.name }} / {{ item.month }}月{{ item.day }}日&nbsp;星期{{ item.week }}&nbsp;{{ item.hours }}時{{ item.minutes }}分
                                        </div>
                                        <div class="data-row-subtitle">
                                            {{ item.mission_id }}
                                        </div>
                                    </div>
                                    <div class="data-row-right-block">
                                        <button type="button" class="btn btn-light-green-3d op-btn" :disabled="sending" @click="showModal(item.id)">
                                            <i class="fa fa-pencil"/>
                                        </button>
                                        <button type="button" class="btn btn-delete-3d op-btn" :disabled="sending" @click="deleteRow(item)">
                                            <i class="fa fa-trash"/>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                    </div>
                </div>
            </div>
        </section>
        <mission-booking-modal v-model="modal.show"
                               :data-id="modal.id"
                               @update="fetchData"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import MissionBookingModal from './MissionBookingModal.vue';

export default {
    name: "MissionBookingListPage",
    components: {ClipLoader, MissionBookingModal},
    data() {
        return {
            loading: false,
            sending: false,
            missionBookings: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            modal: {
                show: false,
                id: null
            }
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/mission-bookings');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.missionBookings = data.missionBookings;
                    if(data.pagination) {
                        this.pagination = data.pagination;
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        async deleteRow(row) {
            if(!confirm('確定要刪除此筆預約任務？')) {
                return;
            }
            this.sending = true;
            try {
                let res = await axios.delete(`/api/mission-bookings/${row.id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.missionBookings = _.filter(this.missionBookings, (r) => {
                        return r.id != row.id;
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.sending = false;
            }
        },
        changePage() {
            this.$router.push({
                path: `/missionBookings`,
                query: {
                    page: this.pagination.current_page
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

<style scoped>
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
    margin-top:     20px;
    float:          right;
    letter-spacing: 10px;
}
.box-body{
    padding: 30px;
}
.data-list{
    margin-top: 20px;
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
.data-row-content{
    display:        inline-block;
    vertical-align: middle;
    padding:        10px;
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
</style>
