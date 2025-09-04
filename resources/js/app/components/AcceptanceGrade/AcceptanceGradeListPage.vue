<template>
    <div>
        <section class="content-header clearfix">
            <span>參數設定&emsp;</span>
            <template v-if="hasAnyWritePermission">
                <button v-if="mode != 'edit'" class="btn btn-light-green op-btn" @click="mode = 'edit'">
                    <i class="fa fa-pencil" aria-hidden="true"/>&nbsp;編輯
                </button>
                <button v-else-if="mode == 'edit'" class="btn btn-delete op-btn" @click="onClickCancelEditBtn">
                    <i class="fa fa-times-circle" aria-hidden="true"/>&nbsp;取消編輯
                </button>
                <button class="btn btn-delete op-btn reset-btn" @click="resetAcceptanceGrades" :disabled="sending">
                    <i class="fa fa-undo" aria-hidden="true"/>&nbsp;恢復原廠設定
                </button>
            </template>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <h3 class="table-title text-center">環境監控汙染地圖允收標準/級距設定</h3>
                                <table class="table table-bordered table-hover break-table">
                                    <colgroup>
                                        <col span="2" style="background-color: #f0f4fa">
                                        <col v-if="permissions.contains('microparticle-exceeds-read')" span="2" style="background-color: #eef6e8">
                                        <col v-if="permissions.contains(['suspended-exceeds-read', 'falling-exceeds-read', 'contact-exceeds-read'])" :span="microorganismColspanCount" style="background-color: #fff5d6">
                                    </colgroup>
                                    <thead>
                                    <tr class="table-head">
                                        <th class="text-center hide-td vertical-middle" rowspan="2">允收/項目</th>
                                        <th class="text-center hide-td vertical-middle" rowspan="2">汙染狀態</th>
                                        <template v-if="permissions.contains('microparticle-exceeds-read')">
                                            <th class="text-center hide-td vertical-middle" rowspan="2">微粒子<br>(28.3L/0.5µm)
                                            </th>
                                            <th class="text-center hide-td vertical-middle" rowspan="2">微粒子<br>(28.3L/5µm)
                                            </th>
                                        </template>
                                        <th v-if="permissions.contains(['suspended-exceeds-read', 'falling-exceeds-read', 'contact-exceeds-read'])" class="text-center vertical-middle hide-td" :colspan="microorganismColspanCount">微生物(cfu/m<sup>3</sup>)
                                        </th>
                                    </tr>
                                    <tr>
                                        <th v-if="permissions.contains('suspended-exceeds-read')" class="text-center vertical-middle hide-td">懸浮菌</th>
                                        <th v-if="permissions.contains('falling-exceeds-read')" class="text-center vertical-middle hide-td">落下菌</th>
                                        <th v-if="permissions.contains('contact-exceeds-read')" class="text-center vertical-middle hide-td">接觸菌</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" rowspan="2" class="text-center vertical-middle">允收/顏色(定義)</th>
                                        <th v-if="permissions.contains('microparticle-exceeds-read')" class="text-center hide-td th-grade" colspan="5">
                                            Class
                                            <template v-if="form.grade == 'A'">{{ 100 | withComma }}</template>
                                            <template v-else-if="form.grade == 'B'">{{ 1000 | withComma }}</template>
                                            <template v-else-if="form.grade == 'C'">{{ 10000 | withComma }}</template>
                                            <template v-else>{{ 100000 | withComma }}</template>
                                            <br>
                                            (Grade
                                            <select v-model="form.grade" @change="onGradeChange">
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="D">D</option>
                                            </select>)
                                        </th>
                                    </tr>
                                    <tr>
                                        <template v-if="permissions.contains('microparticle-exceeds-read')">
                                            <th class="text-center hide-td">
                                                <span>&nbsp;&nbsp;</span>
                                                <input v-if="mode == 'edit'" v-model="microparticleDot5" class="text-center" :disabled="!permissions.contains('microparticle-exceeds-write')"><span v-else-if="microparticleDot5">{{ microparticleDot5 | withComma }}</span>
                                            </th>
                                            <th class="text-center hide-td">
                                                <span>&nbsp;&nbsp;</span>
                                                <input v-if="mode == 'edit'" v-model="microparticle5" class="text-center" :disabled="!permissions.contains('microparticle-exceeds-write')"><span v-else-if="microparticle5">{{ microparticle5 | withComma }}</span>
                                            </th>
                                        </template>
                                        <th v-if="permissions.contains('suspended-exceeds-read')" class="text-center hide-td">
                                            <span>&nbsp;&nbsp;</span>
                                            <input v-if="mode == 'edit'" v-model="suspended" class="text-center" :disabled="!permissions.contains('suspended-exceeds-write')"><span v-else-if="suspended">{{ suspended | withComma }}</span>
                                        </th>
                                        <th v-if="permissions.contains('falling-exceeds-read')" class="text-center hide-td">
                                            <span>&nbsp;&nbsp;</span>
                                            <input v-if="mode == 'edit'" v-model="falling" class="text-center" :disabled="!permissions.contains('falling-exceeds-write')"><span v-else-if="falling">{{ falling | withComma }}</span>
                                        </th>
                                        <th v-if="permissions.contains('contact-exceeds-read')" class="text-center hide-td">
                                            <span>&nbsp;&nbsp;</span>
                                            <input v-if="mode == 'edit'" v-model="contact" class="text-center" :disabled="!permissions.contains('contact-exceeds-write')"><span v-else-if="contact">{{ contact | withComma }}</span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(acceptanceGrade, i) in acceptanceGradesForTable">
                                        <td data-title="允收/項目" class="text-center">
                                            <span v-if="i == 0">&#62;允收</span>
                                            <span v-else-if="i == (acceptanceGradesForTable.length - 1)">&#60;允收</span>
                                            <span v-else>允收 x {{ i == 1 ? 80 : 50 }} %</span>
                                        </td>
                                        <td data-title="汙染狀態" class="text-center">
                                            <template v-if="pollutionConditions.length == acceptanceGradesForTable.length">
                                                <pollution-condition-color-input class="vertical-middle"
                                                                                 v-model="pollutionConditions[i]" :disabled="mode != 'edit'"/>
                                                <span>{{ pollutionConditions[i].display_name }}</span>
                                            </template>
                                        </td>
                                        <template v-if="permissions.contains('microparticle-exceeds-read')">
                                            <td data-title="微粒子(28.3L/5µm)" class="text-center">
                                                <template v-if="acceptanceGrade.microparticle_dot_5">
                                                    <span v-if="i == 0">&#62;</span>
                                                    <span v-else-if="i == (acceptanceGradesForTable.length - 1)">&#60;</span>
                                                    <span v-else>&nbsp;&nbsp;</span>
                                                </template>
                                                <template v-else-if="mode == 'read'">&nbsp;&nbsp;-</template>
                                                <input v-if="mode == 'edit' && i != 0" class="text-center" v-model.number="acceptanceGrade.microparticle_dot_5" :disabled="!permissions.contains('microparticle-exceeds-write')" @blur="setValue($event, 'microparticle_dot_5', i)"/>
                                                <template v-else-if="acceptanceGrade.microparticle_dot_5">{{ acceptanceGrade.microparticle_dot_5 | withComma }}
                                                    <span v-if="getUpperAcceptanceGrade('microparticle_dot_5', i)">{{ getUpperAcceptanceGrade('microparticle_dot_5', i) | withComma }}</span>
                                                </template>
                                            </td>
                                            <td data-title="微粒子(28.3L/0.5µm)" class="text-center">
                                                <template v-if="acceptanceGrade.microparticle_5">
                                                    <span v-if="i == 0">&#62;</span>
                                                    <span v-else-if="i == (acceptanceGradesForTable.length - 1)">&#60;</span>
                                                    <span v-else>&nbsp;&nbsp;</span>
                                                </template>
                                                <template v-else-if="mode == 'read'">&nbsp;&nbsp;-</template>
                                                <input v-if="mode == 'edit' && i != 0" class="text-center" v-model.number="acceptanceGrade.microparticle_5" :disabled="!permissions.contains('microparticle-exceeds-write')" @blur="setValue($event, 'microparticle_5', i)"/>
                                                <template v-else-if="acceptanceGrade.microparticle_5">{{ acceptanceGrade.microparticle_5 | withComma }}
                                                    <span v-if="getUpperAcceptanceGrade('microparticle_5', i)">{{ getUpperAcceptanceGrade('microparticle_5', i) | withComma }}</span>
                                                </template>
                                            </td>
                                        </template>
                                        <td v-if="permissions.contains('suspended-exceeds-read')" data-title="懸浮菌" class="text-center">
                                            <template v-if="acceptanceGrade.suspended">
                                                <span v-if="i == 0">&#62;</span>
                                                <span v-else-if="i == (acceptanceGradesForTable.length - 1)">&#60;</span>
                                                <span v-else>&nbsp;&nbsp;</span>
                                            </template>
                                            <template v-else-if="mode == 'read'">&nbsp;&nbsp;-</template>
                                            <input v-if="mode == 'edit' && i != 0" class="text-center" v-model.number="acceptanceGrade.suspended" :disabled="!permissions.contains('suspended-exceeds-write')" @blur="setValue($event, 'suspended', i)"/>
                                            <template v-else-if="acceptanceGrade.suspended">{{ acceptanceGrade.suspended | withComma }}
                                                <span v-if="getUpperAcceptanceGrade('suspended', i)">{{ getUpperAcceptanceGrade('suspended', i) | withComma }}</span>
                                            </template>
                                        </td>
                                        <td v-if="permissions.contains('falling-exceeds-read')" data-title="落下菌" class="text-center">
                                            <template v-if="acceptanceGrade.falling">
                                                <span v-if="i == 0">&#62;</span>
                                                <span v-else-if="i == (acceptanceGradesForTable.length - 1)">&#60;</span>
                                                <span v-else>&nbsp;&nbsp;</span>
                                            </template>
                                            <template v-else-if="mode == 'read'">&nbsp;&nbsp;-</template>
                                            <input v-if="mode == 'edit' && i != 0" class="text-center" v-model.number="acceptanceGrade.falling" :disabled="!permissions.contains('falling-exceeds-write')" @blur="setValue($event, 'falling', i)"/>
                                            <template v-else-if="acceptanceGrade.falling">{{ acceptanceGrade.falling | withComma }}
                                                <span v-if="getUpperAcceptanceGrade('falling', i)">{{ getUpperAcceptanceGrade('falling', i) | withComma }}</span>
                                            </template>
                                        </td>
                                        <td v-if="permissions.contains('contact-exceeds-read')" data-title="接觸菌" class="text-center">
                                            <template v-if="acceptanceGrade.contact">
                                                <span v-if="i == 0">&#62;</span>
                                                <span v-else-if="i == (acceptanceGradesForTable.length - 1)">&#60;</span>
                                                <span v-else>&nbsp;&nbsp;</span>
                                            </template>
                                            <template v-else-if="mode == 'read'">&nbsp;&nbsp;-</template>
                                            <input v-if="mode == 'edit' && i != 0" class="text-center" v-model.number="acceptanceGrade.contact" :disabled="!permissions.contains('contact-exceeds-write')" @blur="setValue($event, 'contact', i)"/>
                                            <template v-else-if="acceptanceGrade.contact">{{ acceptanceGrade.contact | withComma }}
                                                <span v-if="getUpperAcceptanceGrade('contact', i)">{{ getUpperAcceptanceGrade('contact', i) | withComma }}</span>
                                            </template>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-right">
                                    <button v-if="mode == 'edit'" type="button" class="btn btn-light-green op-btn" :disabled="sending" @click="submit">
                                        <i class="fa fa-floppy-o"/> 儲存
                                    </button>
                                </div>
                            </div>
                            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import {mapGetters} from 'vuex';
import {md5} from 'js-md5';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import PollutionConditionColorInput from '../PollutionCondition/PollutionConditionColorInput.vue';

export default {
    name: "AcceptanceGradeListPage",
    components: {PollutionConditionColorInput, ClipLoader},
    computed: {
        ...mapGetters({
            permissions: 'user/permissions'
        }),
        microparticle5: {
            get() {
                if(!(this.acceptanceGradesForTable.length > 0) || !this.acceptanceGradesForTable[0].microparticle_5) {
                    return null;
                }
                return this.acceptanceGradesForTable[0].microparticle_5;
            },
            set(value) {
                this.setOtherAcceptanceGrades('microparticle_5', value);
                this.dataColumnToRow();
            }
        },
        microparticleDot5: {
            get() {
                if(!(this.acceptanceGradesForTable.length > 0) || !this.acceptanceGradesForTable[0].microparticle_dot_5) {
                    return null;
                }
                return this.acceptanceGradesForTable[0].microparticle_dot_5;
            },
            set(value) {
                this.setOtherAcceptanceGrades('microparticle_dot_5', value);
                this.dataColumnToRow();
            }
        },
        suspended: {
            get() {
                if(!(this.acceptanceGradesForTable.length > 0) || !this.acceptanceGradesForTable[0].suspended) {
                    return null;
                }
                return this.acceptanceGradesForTable[0].suspended;
            },
            set(value) {
                this.setOtherAcceptanceGrades('suspended', value);
                this.dataColumnToRow();
            }
        },
        falling: {
            get() {
                if(!(this.acceptanceGradesForTable.length > 0) || !this.acceptanceGradesForTable[0].falling) {
                    return null;
                }
                return this.acceptanceGradesForTable[0].falling;
            },
            set(value) {
                this.setOtherAcceptanceGrades('falling', value);
                this.dataColumnToRow();
            }
        },
        contact: {
            get() {
                if(!(this.acceptanceGradesForTable.length > 0) || !this.acceptanceGradesForTable[0].contact) {
                    return null;
                }
                return this.acceptanceGradesForTable[0].contact;
            },
            set(value) {
                this.setOtherAcceptanceGrades('contact', value);
                this.dataColumnToRow();
            }
        },
        newMd5() {
            let hash = md5.create();
            hash.update(JSON.stringify(this.acceptanceGrades));
            return hash.hex();
        },
        hasAnyWritePermission() {
            return this.permissions.contains(['suspended-exceeds-write', 'falling-exceeds-write', 'contact-exceeds-write', 'microparticle-exceeds-write']);
        },
        microorganismColspanCount() {
            const colPermissions = ['suspended-exceeds-read', 'falling-exceeds-read', 'contact-exceeds-read'];
            const that = this;
            let count = 0;
            _.forEach(colPermissions, (r) => {
                if(that.permissions.contains(r)) {
                    count++;
                }
            });
            return count;
        }
    },
    data() {
        return {
            loading: false,
            sending: false,
            mode: 'read',
            acceptanceGrades: [],
            acceptanceGradesForTable: [],
            form: {
                grade: 'A'
            },
            oldGrade: 'A',
            oldMd5: null,
            pollutionConditions: [],
            columns: ['action', 'warn', 'general', 'normal']
        };
    },
    created() {
        this.fetchData();
        this.fetchPollutionConditionData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/acceptanceGrades', {
                    params: {
                        grade: this.form.grade
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.acceptanceGrades = data.acceptanceGrades;
                    this.dataColumnToRow();
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.oldMd5 = this.newMd5;
            this.loading = false;
        },
        async fetchPollutionConditionData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/pollutionConditions');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.pollutionConditions = [];
                    _.forEach(this.columns, (r) => {
                        const pollutionCondition = _.find(data.pollutionConditions, {'name': r});
                        if(!pollutionCondition) {
                            return;
                        }
                        this.pollutionConditions.push(pollutionCondition);
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        async submit() {
            try {
                this.sending = true;
                let res = null;
                res = await axios.patch(`/api/acceptanceGrades/batch`, {
                    acceptance_grades: this.acceptanceGrades,
                    pollution_conditions: this.pollutionConditions
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.acceptanceGrades = data.acceptanceGrades;
                    this.dataColumnToRow();
                    this.$toast.success({
                        title: '成功訊息',
                        message: '儲存成功'
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.oldMd5 = this.newMd5;
            this.sending = false;
        },
        dataColumnToRow() {
            const organismKinds = ['microparticle_dot_5', 'microparticle_5', 'suspended', 'falling', 'contact'];
            const acceptanceGrade = {
                id: null,
                organism_kind: null,
                grade: null,
                action: 0,
                warn: 0,
                general: 0,
                normal: 0
            }

            let microparticleDot5 = _.find(this.acceptanceGrades, {organism_kind: 'microparticle_dot_5'});
            let microparticle5 = _.find(this.acceptanceGrades, {organism_kind: 'microparticle_5'});
            let suspended = _.find(this.acceptanceGrades, {organism_kind: 'suspended'});
            let falling = _.find(this.acceptanceGrades, {organism_kind: 'falling'});
            let contact = _.find(this.acceptanceGrades, {organism_kind: 'contact'});
            let newId = -1;
            if(!microparticleDot5) {
                microparticleDot5 = _.cloneDeep(acceptanceGrade);
                microparticleDot5.id = newId;
                microparticleDot5.organism_kind = 'microparticle_dot_5';
                microparticleDot5.grade = this.form.grade;
                this.acceptanceGrades.push(microparticleDot5);
                newId--;
            }
            if(!microparticle5) {
                microparticle5 = _.cloneDeep(acceptanceGrade);
                microparticle5.id = newId;
                microparticle5.organism_kind = 'microparticle_5';
                microparticle5.grade = this.form.grade;
                this.acceptanceGrades.push(microparticle5);
                newId--;
            }
            if(!suspended) {
                suspended = _.cloneDeep(acceptanceGrade);
                suspended.id = newId;
                suspended.organism_kind = 'suspended';
                suspended.grade = this.form.grade;
                this.acceptanceGrades.push(suspended);
                newId--;
            }
            if(!falling) {
                falling = _.cloneDeep(acceptanceGrade);
                falling.id = newId;
                falling.organism_kind = 'falling';
                falling.grade = this.form.grade;
                this.acceptanceGrades.push(falling);
                newId--;
            }
            if(!contact) {
                contact = _.cloneDeep(acceptanceGrade);
                contact.id = newId;
                contact.organism_kind = 'contact';
                contact.grade = this.form.grade;
                this.acceptanceGrades.push(contact);
                newId--;
            }

            const data = [];
            data.push(microparticleDot5);
            data.push(microparticle5);
            data.push(suspended);
            data.push(falling);
            data.push(contact);
            const acceptanceGradesForTable = [];
            for(let i = 0; i < 4; i++) {
                let obj = {};
                _.forEach(organismKinds, (kind, kIdx) => {
                    obj[kind] = data[kIdx][this.columns[i]];
                });
                acceptanceGradesForTable.push(obj);
            }
            this.acceptanceGradesForTable = acceptanceGradesForTable;
        },
        setOtherAcceptanceGrades(organismKind, value) {
            let acceptanceGrade = _.find(this.acceptanceGrades, (r) => {
                return r.organism_kind == organismKind;
            });
            if(acceptanceGrade) {
                acceptanceGrade.action = value ? parseInt(value) : 0;
                acceptanceGrade.warn = parseInt((value * 0.8).toFixed(0));
                acceptanceGrade.normal = acceptanceGrade.general = parseInt((value * 0.5).toFixed(0));
            }
        },
        checkIsSaved() {
            if(this.oldMd5 != this.newMd5) {
                if(!confirm('資料尚未儲存，確定要切換？')) {
                    return false;
                }
            }
            return true;
        },
        onGradeChange() {
            if(!this.checkIsSaved()) {
                this.form.grade = this.oldGrade;
                return;
            }
            this.oldGrade = this.form.grade;
            this.fetchData();
        },
        onClickCancelEditBtn() {
            if(!this.checkIsSaved()) {
                return;
            }
            this.mode = 'read';
            this.fetchData();
        },
        setValue(e, organismKind, idx) {
            const newValue = e.target._value;
            const acceptanceGrade = _.find(this.acceptanceGrades, ['organism_kind', organismKind]);
            if(acceptanceGrade) {
                acceptanceGrade[this.columns[idx]] = newValue;
            }
        },
        getUpperAcceptanceGrade(column, idx) {
            let upperAcceptanceGrade = null;
            if((idx == 0) || ((idx + 1) == this.acceptanceGradesForTable.length) || ((idx - 2) < 0) || this.acceptanceGradesForTable[idx - 1][column]) {
                return null;
            }
            for(let i = (idx - 2); i >= 0; i--) {
                if(this.acceptanceGradesForTable[i][column]) {
                    upperAcceptanceGrade = this.acceptanceGradesForTable[i][column];
                }
            }
            return upperAcceptanceGrade ? ` ~ ${upperAcceptanceGrade}` : null;
        },
        async resetAcceptanceGrades() {
            if(!confirm('確定要恢復原廠設定嗎？')) {
                return;
            }
            this.sending = true;
            try {
                this.$toast.info({
                    title: '提示訊息',
                    message: '正在進行「恢復原廠設定」　，完成後，將自動重新整理畫面。'
                });
                let res = await axios.delete('/api/acceptanceGrades/resets');
                res = res.data;
                if(res.status == 0) {
                    this.$router.go(0);
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
.table-head{
    background-color: initial;
    color:            initial;
}
.table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td{
    border: 1px solid #808080;
}
.table-title{
    font-weight:    bold;
    border-bottom:  1px solid black;
    padding-bottom: 10px;
    padding-top:    5px;
    margin:         0;
}
.vertical-middle{
    vertical-align: middle;
}
.th-grade{
    background-color: #ffffff;
}
.reset-btn{
    position: absolute;
    right:    15px;
}
/* Hide Arrows from number input */
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button{
    -webkit-appearance: none;
    margin:             0;
}
/* Firefox */
input[type=number]{
    -moz-appearance: textfield;
}
/* Hide Arrows from number input */
input[type="color"]{
    -webkit-appearance: none;
    border:             none;
}
input[type="color"]::-webkit-color-swatch-wrapper{
    padding: 0;
}
input[type="color"]::-webkit-color-swatch{
    border: none;
}
</style>
