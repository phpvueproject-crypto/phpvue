<template>
    <div>
        <section class="content-header clearfix">
            <span>發送任務</span>
            <div class="pull-right">
                <button class="btn btn-default" @click="fetchData(1)">
                    <i class="fa fa-refresh"/>
                    同步更新
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
                                     v-for="mission in missions">
                                    <div class="data-row-content">
                                        <div class="data-row-title">
                                            {{ mission.name }} / {{ mission.region ? mission.region : '其他' }}
                                        </div>
                                        <div class="data-row-subtitle">
                                            {{ mission.guid }}
                                        </div>
                                    </div>
                                    <div class="data-row-right-block">
                                        <button type="button" class="btn btn-light-green-3d op-btn" :disabled="sending" @click="storeMissionQueues(mission.guid)">
                                            <i class="fa fa-play"/>
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
    </div>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';

export default {
    name: "MissionListPage",
    components: {ClipLoader},
    data() {
        return {
            loading: true,
            sending: false,
            missions: []
        }
    },
    async created() {
        await this.fetchData();
    },
    methods: {
        async fetchData(refresh = 0) {
            this.loading = true;
            try {
                let res = await axios.get('/api/missions', {
                    params: {
                        refresh: refresh
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.missions = data.missions;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        async storeMissionQueues(missionId) {
            if(!confirm('確定要執行此任務？')) {
                return;
            }
            this.sending = true;
            try {
                let res = await axios.post('/api/missionQueues', {
                    mission_id: missionId
                });
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
                } else if(res.status == -21) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: 'MIR車輛未開機，請稍後再試，或點擊同步更新！'
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
