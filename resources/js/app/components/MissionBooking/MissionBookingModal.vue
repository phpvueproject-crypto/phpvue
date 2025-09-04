<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ dataId ? '編輯預約任務' : '預約任務' }}</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">任務<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <mission-list-select v-model="form.mission_id"/>
                        <span v-show="errors.has('mission_id:required')" class="help is-danger">請選擇任務</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">預約時間</label>
                    <div class="col-md-9 schedule-time">
                        <select v-model="form.month" class="date-time-selector" :style="`color: ${!form.month ? '#a9a9a9' : ''}`">
                            <option value="*">月</option>
                            <option v-for="i in 12" :value="i.toString()">{{ i }}</option>
                        </select>
                        <select v-model="form.day" class="date-time-selector" :style="`color: ${!form.day ? '#a9a9a9' : ''}`">
                            <option value="*">日</option>
                            <option v-for="i in 31" :value="i.toString()">{{ i }}</option>
                        </select>
                        <select v-model="form.week" class="date-time-selector" style="min-width: 80px" :style="`color: ${!form.week ? '#a9a9a9' : ''}`">
                            <option value="*">星期</option>
                            <option v-for="i in 7" :value="i.toString()">{{ i }}</option>
                        </select>
                        <select v-model="form.hours" class="date-time-selector" :style="`color: ${!form.hours ? '#a9a9a9' : ''}`">
                            <option value="*">時</option>
                            <option v-for="(value, index) in 24" :value="index.toString()">{{ index }}</option>
                        </select>
                        <select v-model="form.minutes" class="date-time-selector" :style="`color: ${!form.minutes ? '#a9a9a9' : ''}`">
                            <option value="*">分</option>
                            <option v-for="(value, index) in 60" :value="index.toString()">{{ index }}</option>
                        </select>
                    </div>
                </div>
                <div class="text-right">
                    <button class="btn btn-light-green" :disabled="sending">預約</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
        </div>
    </modal>
</template>

<script>
import axios from 'axios';
import moment from 'moment';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import MissionListSelect from '../Mission/MissionListSelect.vue';

export default {
    name: "MissionBookingModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {MissionListSelect, ClipLoader},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        dataId: {
            type: [Number, String],
            default: null
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {}
        };
    },
    created() {
        this.resetForm();
        this.resetFormValidate();
    },
    methods: {
        async onShowModal() {
            this.adjustModalVerticalPosition();
            this.resetForm();
            this.resetFormValidate();
            if(this.dataId) {
                await this.fetchData();
            }
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.resetFormValidate();
            this.$emit('input', false);
        },
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/mission-bookings/${this.dataId}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.missionBooking;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        async submit() {
            const isPass = await this.validate();
            if(!isPass) {
                return;
            }
            this.form.schedule = this.form.minutes.toString()
                + ' ' + this.form.hours.toString()
                + ' ' + this.form.day.toString()
                + ' ' + this.form.month.toString()
                + ' ' + this.form.week.toString();
            this.sending = true;
            try {
                let res = null
                if(this.dataId) {
                    res = await axios.patch(`/api/mission-bookings/${this.dataId}`, this.form);
                } else {
                    res = await axios.post(`/api/mission-bookings`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '預約成功！'
                    });
                    this.$emit('update');
                    this.showModal = false;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.sending = false;
            }
        },
        resetForm() {
            this.form = {
                id: null,
                mission_id: null,
                schedule: null,
                minutes: '*',
                hours: '*',
                day: '*',
                month: '*',
                week: '*'
            };
        },
        adjustModalVerticalPosition() {
            const dialog = $(this.$el).find('.modal-dialog');
            const top = (($(window).height() - dialog.height()) / 6);
            if(top > 30) {
                dialog.css({
                    marginTop: top
                });
            } else {
                dialog.css({
                    marginTop: 30
                });
            }
        }
    }
}
</script>
<style scoped lang="scss">
.schedule-time{
    padding-top:     4px;
    display:         flex;
    justify-content: space-between;
}
.date-time-selector{
    border-color:  #d2d6de;
    outline-color: #d2d6de;
    min-width:     58px;
    font-size:     18px;
    height:        28px;
}
</style>
