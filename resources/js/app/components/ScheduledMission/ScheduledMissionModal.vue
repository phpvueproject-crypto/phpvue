<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">創建排程</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">任務類型<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="mqtt_command_type_id" v-model="form.mqtt_command_type_id" v-validate="'required'" :style="`color: ${!form.mqtt_command_type_id ? '#a9a9a9' : ''}`">
                            <option :value="null">請選擇任務類型</option>
                            <option v-for="missionType in missionTypes" :value="missionType.value">{{ missionType.display_name }}</option>
                        </select>
                        <span v-show="errors.has('mqtt_command_type_id:required')" class="help is-danger">請選擇任務類型</span>
                    </div>
                </div>
                <div v-if="(form.mqtt_command_type_id == 36 || form.mqtt_command_type_id == 37 || form.mqtt_command_type_id == 38 || form.mqtt_command_type_id == 39)" class="form-group">
                    <label class="col-md-3 control-label">車輛<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <vehicle-mgmt-list-select v-model.lazy="systemIds"
                                                  v-validate="'required'"
                                                  name="system_id"
                                                  placeholder="請選擇車輛"
                                                  :multiple="true"/>
                        <span v-show="errors.has('system_id:required')" class="help is-danger">請選擇車輛</span>
                    </div>
                </div>
                <div v-if="(form.mqtt_command_type_id == 38 || form.mqtt_command_type_id == 39)" class="form-group">
                    <label class="col-md-3 control-label">站點名稱<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <vertex-list-select v-model.lazy="vertexName"
                                            v-validate="'required'"
                                            value-column="name"
                                            name="vertexName"/>
                        <span v-show="errors.has('vertexName:required')" class="help is-danger">請選擇站點名稱</span>
                    </div>
                </div>
                <template v-if="form.mqtt_command_type_id == 41 || form.mqtt_command_type_id == 42">
                    <div class="form-group">
                        <label class="col-md-3 control-label">起點座標X<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="goal1.x"
                                   class="form-control"
                                   v-validate="'required|decimal'"
                                   name="goal1X"/>
                            <span v-show="errors.has('goal1X:required')" class="help is-danger">起點座標X</span>
                            <span v-show="errors.has('goal1X:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">起點座標Y<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="goal1.y"
                                   class="form-control"
                                   v-validate="'required|decimal'"
                                   name="goal1Y"/>
                            <span v-show="errors.has('goal1Y:required')" class="help is-danger">起點座標Y</span>
                            <span v-show="errors.has('goal1Y:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                </template>
                <template v-if="form.mqtt_command_type_id == 41">
                    <div class="form-group">
                        <label class="col-md-3 control-label">終點座標X<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="goal2.x"
                                   class="form-control"
                                   v-validate="'required|decimal'"
                                   name="goal2X"/>
                            <span v-show="errors.has('goal2X:required')" class="help is-danger">終點座標Y</span>
                            <span v-show="errors.has('goal2X:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">終點座標Y<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="goal2.y"
                                   class="form-control"
                                   v-validate="'required|decimal'"
                                   name="goal2Y"/>
                            <span v-show="errors.has('goal2Y:required')" class="help is-danger">終點座標Y</span>
                            <span v-show="errors.has('goal2Y:decimal')" class="help is-danger">請填寫數字</span>
                        </div>
                    </div>
                </template>
                <template v-if="form.mqtt_command_type_id == 42">
                    <div class="form-group">
                        <label class="col-md-3 control-label">角度<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input v-model="theta"
                                   class="form-control"
                                   v-validate="'required|decimal:0|min_value:-360|max_value:360'"
                                   name="theta"/>
                            <span v-show="errors.has('theta:required')" class="help is-danger">角度</span>
                            <span v-show="errors.has('theta:decimal')" class="help is-danger">請填寫整數</span>
                            <span v-show="errors.has('theta:min_value') || errors.has('theta:max_value')" class="help is-danger">請填寫合理角度</span>
                        </div>
                    </div>
                </template>
                <template v-if="form.mqtt_command_type_id == 41 || form.mqtt_command_type_id == 42">
                    <div class="form-group">
                        <label class="col-md-3 control-label">偵測<span class="is-danger">&nbsp;&nbsp;&nbsp;</span></label>
                        <div class="col-md-9">
                            <toggle-button v-model="laserDetection.toggle" :sync="true" @change="laserDetection.value = laserDetection.toggle ? 'on' : 'off'"/>
                        </div>
                    </div>
                </template>
                <div class="form-group">
                    <label class="col-md-3 control-label">時間<span class="is-danger">&nbsp;&nbsp;&nbsp;</span></label>
                    <div class="col-md-9 schedule-time">
                        <select v-model="form.years" class="date-time-selector" style="min-width: 78px" :style="`color: ${!form.years ? '#a9a9a9' : ''}`">
                            <option value="*">年</option>
                            <option v-for="(i, idx) in 3" :value="(thisYear + idx).toString()">{{ thisYear + idx }}</option>
                        </select>
                        <select v-model="form.month" class="date-time-selector" :style="`color: ${!form.month ? '#a9a9a9' : ''}`">
                            <option value="*">月</option>
                            <option v-for="i in 12" :value="i.toString()">{{ i }}</option>
                        </select>
                        <select v-model="form.day" class="date-time-selector" :style="`color: ${!form.day ? '#a9a9a9' : ''}`">
                            <option value="*">日</option>
                            <option v-for="i in 31" :value="i.toString()">{{ i }}</option>
                        </select>
                        <select v-model="form.week" class="date-time-selector" style="min-width: 78px" :style="`color: ${!form.week ? '#a9a9a9' : ''}`">
                            <option value="*">星期</option>
                            <option v-for="i in 7" :value="i.toString()">星期{{ chineseWeek(i) }}</option>
                        </select>
                        <select v-model="form.hours" class="date-time-selector" :style="`color: ${!form.hours ? '#a9a9a9' : ''}`">
                            <option value="*">時</option>
                            <option v-for="(value, index) in 24" :value="index < 10 ? `0${index}` : index.toString()">{{ index < 10 ? `0${index}` : index }}</option>
                        </select>
                        <select v-model="form.minutes" class="date-time-selector" :style="`color: ${!form.minutes ? '#a9a9a9' : ''}`">
                            <option value="*">分</option>
                            <option v-for="(value, index) in 60" :value="index < 10 ? `0${index}` : index.toString()">{{ index < 10 ? `0${index}` : index }}</option>
                        </select>
                    </div>
                </div>
                <div class="text-right">
                    <button class="btn btn-light-green" :disabled="sending">送出</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
        </div>
    </modal>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import moment from 'moment';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect.vue';
import VertexListSelect from '../Vertex/VertexListSelect.vue';
import {ToggleButton} from 'vue-js-toggle-button';

export default {
    name: "ScheduledMissionModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader, VehicleMgmtListSelect, VertexListSelect, ToggleButton},
    props: {
        value: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        thisYear() {
            return parseInt(moment().format('YYYY'));
        }
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        },
        systemIds(newVal) {
            let systemIds = [];
            _.forEach(newVal, (r) => {
                systemIds.push(r.vehicle_id);
            });
            this.form.system_id = systemIds;
        },
        'form.mqtt_command_type_id': {
            handler(newVal) {
                if(newVal != 38 || newVal != 39) {
                    this.vertexName = null;
                }
                this.resetFormValidate();
            }, deep: true
        },
        vertexName(newVal) {
            let parameter = [];
            if(newVal) {
                parameter.push({
                    typename: 'vertex_name',
                    data: newVal
                });
            }
            this.form.parameter = parameter;
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {},
            systemIds: [],
            missionTypes: [
                {value: 36, display_name: 'go breaking'},
                {value: 37, display_name: 'breaking completed'},
                {value: 38, display_name: 'go charge'},
                {value: 39, display_name: 'go injection'},
                {value: 41, display_name: 'go sweep'},
                {value: 42, display_name: 'go location'}
            ],
            vertexName: null,
            goal1: {x: null, y: null},
            goal2: {x: null, y: null},
            laserDetection: {toggle: false, value: 'off'},
            theta: null
        };
    },
    created() {
        this.resetForm();
        this.resetFormValidate();
    },
    methods: {
        async onShowModal() {
            this.resetForm();
            this.resetFormValidate();
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.resetFormValidate();
            this.$emit('input', false);
        },
        async submit() {
            const form = _.pickBy(this.form, _.identity());
            if(form.system_id.length == 0) {
                form.system_id.push("acs");
            }
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }
                if(this.form.mqtt_command_type_id == 41) {
                    const obj = {};
                    obj.start_goal = {x: this.goal1.x, y: this.goal1.y};
                    obj.end_goal = {x: this.goal2.x, y: this.goal2.y};
                    obj.laser_detection = this.laserDetection.value;
                    this.form.parameter = [];
                    this.form.parameter.push(obj);
                }
                if(this.form.mqtt_command_type_id == 42) {
                    const obj = {};
                    obj.goal = {x: this.goal1.x, y: this.goal1.y, theta: this.theta};
                    obj.laser_detection = this.laserDetection.value;
                    this.form.parameter = [];
                    this.form.parameter.push(obj);
                }
                return;

                this.sending = true;
                let res = await axios.post(`/api/mqttCommands`, form);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '命令發送成功'
                    });
                    this.$emit('update');
                    this.showModal = false;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                mqtt_command_type_id: null,
                system_id: [],
                years: '*',
                month: '*',
                week: '*',
                day: '*',
                hours: '*',
                minutes: '*',
                parameter: []
            };
            this.systemIds = [];
        },
        chineseWeek(number) {
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
.schedule-time{
    padding-top: 4px
}
.date-time-selector{
    border-color:  #d2d6de;
    outline-color: #d2d6de;
    min-width:     60px;
    font-size:     18px;
    height:        28px;
}
</style>
