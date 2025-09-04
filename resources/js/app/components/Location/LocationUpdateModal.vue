<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ dataId ? '編輯' : '新增' }}</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">地圖<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <map-list-select placeholder="請選擇"
                                         select-width="100%"
                                         name="mapId"
                                         v-model="form.map_id"
                                         v-validate="'required'"/>
                        <span v-show="errors.has('mapId:required')" class="help is-danger">請選擇地圖</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">裝置名稱<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="deviceName" placeholder="請輸入" v-model="form.device_name" v-validate="'required'">
                        <span v-show="errors.has('deviceName:required')" class="help is-danger">請輸入裝置名稱</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">條型碼<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="barCode" v-model="form.bar_code" v-validate="'required'" placeholder="請輸入">
                        <span v-show="errors.has('barCode:required')" class="help is-danger">請輸入條型碼</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">採樣時間<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <datetimepicker id="time"
                                        name="time"
                                        type="datetime"
                                        format="yyyy-MM-dd HH:mm"
                                        input-class="form-control"
                                        input-style="height: 34px"
                                        style="width: 100%"
                                        picker-class="theme-light-green"
                                        placeholder="請選擇"
                                        v-model="form.micro_organisms[0].Time" v-validate="'required'"/>
                        <span v-show="errors.has('time:required')" class="help is-danger">請選擇採樣時間</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">記錄時間<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <datetimepicker id="createdAt"
                                        name="createdAt"
                                        type="datetime"
                                        format="yyyy-MM-dd HH:mm"
                                        input-class="form-control"
                                        input-style="height: 34px"
                                        style="width: 100%"
                                        picker-class="theme-light-green"
                                        placeholder="請選擇"
                                        v-model="form.micro_organisms[0].created_at" v-validate="'required'"/>
                        <span v-if="errors.has('createdAt:required')" class="help is-danger">請選擇記錄時間</span>
                        <span v-else-if="(form.micro_organisms[0].Time >= form.micro_organisms[0].created_at)"
                              class="is-danger">採樣時間必須在紀錄時間之前</span>
                    </div>
                </div>
                <template v-for="microOrganism in form.micro_organisms">
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ microOrganism.organism_kind_name }}<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" :name="microOrganism.organism_kind" v-model.number="microOrganism.organism_value" v-validate="'required'" placeholder="請輸入">
                            <span v-show="errors.has(`${microOrganism.organism_kind}:required`)" class="help is-danger">請輸入{{ microOrganism.organism_kind_name }}</span>
                        </div>
                    </div>
                </template>
                <div class="form-group">
                    <label class="col-md-3 control-label">抽氣量&nbsp;&nbsp;</label>
                    <div class="col-md-9">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="centered no-wrap">秒數(S)</th>
                                <th class="centered">抽氣量平均值(L/S)</th>
                                <th class="centered">抽氣量累計值(L/S)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="gasSampling in form.gas_samplings">
                                <td class="centered">{{ gasSampling.second_mark }}</td>
                                <td>
                                    <input type="text" class="form-control text-center" v-model.number="gasSampling.average_volume" placeholder="請輸入">
                                </td>
                                <td>
                                    <input type="text" class="form-control text-center" v-model.number="gasSampling.cumulative_volume" placeholder="請輸入">
                                </td>
                            </tr>
                            </tbody>
                        </table>
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
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import moment from 'moment';
import MapListSelect from '../Map/MapListSelect.vue';
import Datetimepicker from '../Module/Datetimepicker.vue';

export default {
    name: "LocationUpdateModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {Datetimepicker, MapListSelect, ClipLoader},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        dataId: Number
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            loading: false,
            sending: false,
            showModal: this.value,
            form: {}
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        onShowModal() {
            this.resetForm();
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async submit() {
            const isPass = await this.validate();
            if(!isPass) {
                return;
            }
            _.forEach(this.form.micro_organisms, (r, i) => {
                if(i == 0) {
                    r.bar_code = this.form.bar_code;
                    r.device_name = this.form.device_name;
                    r.Time = moment(r.Time).startOf('minute').format('yyyy-MM-DD HH:mm:ss');
                    r.created_at = moment(r.created_at).startOf('minute').format('yyyy-MM-DD HH:mm:ss');
                } else {
                    r.bar_code = this.form.micro_organisms[0].bar_code;
                    r.device_name = this.form.micro_organisms[0].device_name;
                    r.Time = this.form.micro_organisms[0].Time;
                    r.created_at = this.form.micro_organisms[0].created_at;
                }
            });
            if(!this.checkTimeIsPass()) {
                return;
            }
            const form = _.cloneDeep(this.form);
            form.gas_samplings = _.filter(form.gas_samplings, (r) => {
                return r.average_volume != null && r.cumulative_volume != null;
            });
            try {
                this.sending = true;
                let res = null;
                if(!this.dataId) {
                    res = await axios.post(`/api/locations`, form);
                } else {
                    res = await axios.patch(`/api/locations/${this.dataId}`, form);
                }
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.dataId ? '新增' : '儲存'}成功`
                    });
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
                bar_code: null,
                build: null,
                device_name: null,
                floors: null,
                gas_samplings: this.getGasSamplings(),
                id: null,
                map_id: null,
                micro_organisms: this.getMicroOrganisms(),
                room: null,
                x: null,
                x_px: null,
                y: null,
                y_px: null
            };
            this.resetFormValidate();
        },
        getMicroOrganisms() {
            return [{
                Time: moment().format('yyyy-MM-DD HH:mm:ss'),
                created_at: moment().add(3, 'days').format('yyyy-MM-DD HH:mm:ss'),
                device_name: null,
                bar_code: null,
                organism_kind: "suspended",
                organism_kind_name: "懸浮微生物",
                organism_value: null,
                id: null,
                location_id: this.dataId,
                source: 2
            }, {
                Time: moment().format('yyyy-MM-DD HH:mm:ss'),
                created_at: moment().add(3, 'days').format('yyyy-MM-DD HH:mm:ss'),
                device_name: null,
                bar_code: null,
                organism_kind: "contact",
                organism_kind_name: "接觸微生物",
                organism_value: null,
                id: null,
                location_id: this.dataId,
                source: 2
            }, {
                Time: moment().format('yyyy-MM-DD HH:mm:ss'),
                created_at: moment().add(3, 'days').format('yyyy-MM-DD HH:mm:ss'),
                device_name: null,
                bar_code: null,
                organism_kind: "falling",
                organism_kind_name: "落下微生物",
                organism_value: null,
                id: null,
                location_id: this.dataId,
                source: 2
            }];
        },
        getGasSamplings() {
            return [{
                id: null,
                location_id: this.dataId,
                average_volume: null,
                cumulative_volume: null,
                second_mark: 60,
                is_latest: 1,
                created_at: null,
                updated_at: null
            }, {
                id: null,
                location_id: this.dataId,
                average_volume: null,
                cumulative_volume: null,
                second_mark: 120,
                is_latest: 1,
                created_at: null,
                updated_at: null
            }, {
                id: null,
                location_id: this.dataId,
                average_volume: null,
                cumulative_volume: null,
                second_mark: 180,
                is_latest: 1,
                created_at: null,
                updated_at: null
            }, {
                id: null,
                location_id: this.dataId,
                average_volume: null,
                cumulative_volume: null,
                second_mark: 240,
                is_latest: 1,
                created_at: null,
                updated_at: null
            }, {
                id: null,
                location_id: this.dataId,
                average_volume: null,
                cumulative_volume: null,
                second_mark: 300,
                is_latest: 1,
                created_at: null,
                updated_at: null
            }]
        },
        checkTimeIsPass() {
            return !_.find(this.form.micro_organisms, (r) => {
                return r.Time >= r.created_at;
            });
        }
    }
}
</script>

<style scoped lang="scss">
.no-wrap{
    white-space: nowrap;
}
</style>
