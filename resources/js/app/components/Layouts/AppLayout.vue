<template>
    <div>
        <div v-if="!pageLoading" class="app-layout">
            <header-view/>
            <sidebar-view/>
            <div class="content-wrapper">
                <router-view/>
            </div>
        </div>
        <clip-loader v-else class="loading" color="gray" size="30px"/>
        <div class="alarms">
            <template v-for="(alarm, index) in alarms">
                <alarm-view v-model="alarms[index]" :title="alarm.title" :sub-title="alarm.sub_title" :content="alarm.content" :alarm-at="alarm.alarm_at" :border-top-color="alarm.border_top_color" :key="alarm.uuid" @close="clearAlarm(index)"/>
            </template>
        </div>
    </div>
</template>

<script>
import moment from 'moment';
import {mapState} from 'vuex';
import _ from 'lodash';
import axios from 'axios';
import HeaderView from "../Common/HeaderView";
import SidebarView from '../Common/SidebarView.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import AlarmView from '../Module/AlarmView.vue';

export default {
    components: {
        HeaderView,
        SidebarView,
        ClipLoader,
        AlarmView
    },
    name: "AppLayout",
    computed: {
        ...mapState({
            pageLoading: state => state.pageLoading
        }),
        pollutionConditionActionColor() {
            const pollutionConditionAction = _.find(this.pollutionConditions, {'name': 'action'});
            if(!pollutionConditionAction) {
                return null;
            }
            return pollutionConditionAction.color;
        }
    },
    watch: {
        'modal.show'(newVal) {
            this.$store.commit('user/UPDATE_USER_MODAL', newVal);
        }
    },
    data() {
        return {
            alarms: [],
            microOrganisms: [
                {id: 'microparticle_dot_5', name: '微粒子(0.5µm)'}, {id: 'microparticle_5', name: '微粒子(5µm)'},
                {id: 'suspended', name: '懸浮微生物'}, {id: 'falling', name: '落下微生物'},
                {id: 'contact', name: '接觸微生物'}
            ],
            pollutionConditions: []
        }
    },
    created() {
        document.body.className = 'skin-green-light sidebar-mini fixed wysihtml5-supported';
        this.fetchPollutionConditionData();
    },
    mounted() {
        const that = this;
        setInterval(() => {
            that.resizeContentHeight();
        }, 100);
        this.subScribe();
    },
    beforeDestroy() {
        this.unSubscribe();
    },
    methods: {
        resizeContentHeight() {
            const $contentWrapper = document.getElementsByClassName('content-wrapper')[0];
            if($contentWrapper) {
                $contentWrapper.style.minHeight = window.innerHeight + 'px';
            }
        },
        subScribe() {
            const that = this;
            window.Echo.private('microOrganisms').listen('MicroOrganismCreated', (res) => {
                const microOrganism = res.microOrganism;
                if(microOrganism.score <= 100) {
                    return;
                }
                that.newMicroOrganismAlarm(microOrganism);
            }).listen('MicroOrganismUpdated', (res) => {
                const microOrganism = res.microOrganism;
                if(microOrganism.score <= 100) {
                    return;
                }
                that.newMicroOrganismAlarm(microOrganism);
            });
            window.Echo.private('pollutionConditions').listen('PollutionConditionUpdated', (res) => {
                const pollutionCondition = res.pollutionCondition;
                const originPollutionConditionIdx = _.findIndex(that.pollutionConditions, {'id': pollutionCondition.id});
                if(originPollutionConditionIdx != -1) {
                    that.pollutionConditions.splice(originPollutionConditionIdx, 1, pollutionCondition);
                }
            });
        },
        unSubscribe() {
            window.Echo.leave('microOrganisms');
            window.Echo.leave('pollutionConditions');
        },
        getEmptyAlarm() {
            return {
                uuid: null,
                title: '行動值',
                sub_title: null,
                content: null,
                alarm_at: null,
                maintain_seconds: 5,
                border_top_color: this.pollutionConditionActionColor
            }
        },
        clearAlarm(index) {
            this.alarms[index].alarm_at = null;
            const that = this;
            setTimeout(() => {
                that.alarms.splice(index, 1);
            }, 800);
        },
        async fetchPollutionConditionData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/pollutionConditions');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.pollutionConditions = [];
                    const columns = ['action', 'warn', 'general', 'normal'];
                    _.forEach(columns, (r) => {
                        const pollutionCondition = _.find(data.pollutionConditions, {'name': r});
                        if(!pollutionCondition) {
                            return;
                        }
                        this.pollutionConditions.push(pollutionCondition);
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        newMicroOrganismAlarm(data) {
            const microOrganism = _.find(this.microOrganisms, {'id': data.organism_kind});
            const alarm = this.getEmptyAlarm();
            alarm.sub_title = microOrganism ? `微生物類別：${microOrganism.name}` : null;
            alarm.content = `測量值：${data.organism_value}`;
            alarm.alarm_at = moment().format('yyyy-MM-DD HH:mm:ss');
            alarm.uuid = this.generateUUID();
            alarm.maintain_seconds = (alarm.maintain_seconds + this.alarms.length) * 1000;
            this.alarms.push(alarm);
            setTimeout(() => {
                const alarmIdx = _.findIndex(this.alarms, {'uuid': alarm.uuid});
                this.alarms[alarmIdx].alarm_at = null;
                setTimeout(() => {
                    const alarmIdx = _.findIndex(this.alarms, {'uuid': alarm.uuid});
                    this.alarms.splice(alarmIdx, 1);
                }, 800);
            }, alarm.maintain_seconds);
        }
    }
}
</script>

<style lang="scss" scoped>
.app-layout{
    position:         relative;
    background-color: #222d32;
    .main-header .logo{
        height: 52px;
    }
}
.content-wrapper{
    padding-top:      58px;
    background-color: #ecf0f5;
}
.alarms{
    position:   fixed;
    right:      10px;
    top:        60px;
    z-index:    1;
    transition: all 1000ms ease-in-out;
    max-height: calc(100vh - 64px);
    overflow-y: auto;
}
</style>
