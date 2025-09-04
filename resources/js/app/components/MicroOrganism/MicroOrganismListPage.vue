<template>
    <div>
        <section class="content-header clearfix">
            <span>數據輸入&emsp;</span>
            <template v-if="permissions.contains('job-history-write')">
                <button v-if="mode != 'edit'" class="btn btn-light-green op-btn" @click="mode = 'edit'" :disabled="microOrganisms.length <= 0">
                    <i class="fa fa-pencil" aria-hidden="true"/>&nbsp;編輯
                </button>
                <button v-else-if="mode == 'edit'" class="btn btn-delete op-btn" @click="onClickCancelEditBtn">
                    <i class="fa fa-times-circle" aria-hidden="true"/>&nbsp;取消編輯
                </button>
            </template>
        </section>
        <section class="content">
            <div class="box box-top">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4 col-md-offset-2">
                            <div class="form-group">
                                <label for="start_time" class="col-sm-4 control-label">開始時間</label>
                                <div class="col-sm-6">
                                    <datetimepicker v-model="form.start_time"
                                                    id="start_time"
                                                    type="datetime"
                                                    format="yyyy-MM-dd HH:mm:ss"
                                                    input-class="form-control"
                                                    picker-class="theme-light-green"
                                                    placeholder="請選擇"
                                                    style="width: 100%"
                                                    :max-datetime="today"
                                                    :show-clear-btn="true"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="end_time" class="col-sm-4 control-label">結束時間</label>
                                <div class="col-sm-6">
                                    <datetimepicker v-model="form.end_time"
                                                    id="end_time"
                                                    type="datetime"
                                                    format="yyyy-MM-dd HH:mm:ss"
                                                    input-class="form-control"
                                                    picker-class="theme-light-green"
                                                    placeholder="請選擇"
                                                    style="width: 100%"
                                                    :max-datetime="today"
                                                    :show-clear-btn="true"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row next-row">
                        <div class="col-md-4 col-md-offset-2">
                            <div class="form-group">
                                <label for="type" class="col-sm-4 control-label">微生物類別</label>
                                <div class="col-sm-6">
                                    <micro-organism-kind-list-select v-model="form.organism_kinds" id="type" select-class="form-control" select-style="width: 100%" :multiple="true" :items="organismKinds"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="regionMgmtId" class="col-sm-4 control-label">房間</label>
                                <div class="col-sm-6">
                                    <room-environment-select id="regionMgmtId" select-class="form-control" :region-mgmt-id.sync="form.region_mgmt_id" :items="roomEnvironments"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row next-row text-center">
                        <button type="button" class="btn btn-light-green op-btn" :disabled="loading" @click="changePage(1)">
                            <i class="fa fa-search"/> 查詢
                        </button>
                        <button type="button" class="btn btn-delete op-btn" @click="resetSearch">
                            <i class="fa fa-undo"/> 清空
                        </button>
                        <button type="button" class="btn btn-light-green op-btn" @click="showImportModal('數據輸入')">
                            <i class="glyphicon glyphicon-import"/> 匯入
                        </button>
                        <router-link :to="exportUrl" target="_blank" type="button" class="btn btn-light-green op-btn">
                            <i class="glyphicon glyphicon-export"/> 匯出
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-bottom">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table">
                                    <thead>
                                    <tr class="table-head">
                                        <th class="text-center hide-td">編號</th>
                                        <th class="hide-td centered">條形碼</th>
                                        <th class="hide-td">微生物類別</th>
                                        <th class="hide-td">房間</th>
                                        <th class="hide-td">位置代號</th>
                                        <th class="text-center hide-td">測量值
                                            <sort-button v-model="organismValueSortType" column="organism_value" @change="changeSortType"/>
                                        </th>
                                        <th class="text-center hide-td">採樣時間
                                            <sort-button v-model="timeSortType" column="Time" @change="changeSortType"/>
                                        </th>
                                        <th class="text-center hide-td">建檔時間</th>
                                        <th v-if="mode == 'edit'" class="text-center vertical-middle hide-td" rowspan="3">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(microOrganism, i) in microOrganisms" :key="microOrganism.id">
                                        <td data-title="編號" class="text-center vertical-middle">{{ microOrganism.id && microOrganism.id > 0 ? microOrganism.id : null }}</td>
                                        <td data-title="條形碼" class="centered">
                                            <input v-if="(mode == 'edit') && (!microOrganism.id || microOrganism.id < 0)" v-model="microOrganism.bar_code" :name="`bar_code_${microOrganism.id}`" type="text" class="form-control vertical-middle text-center" placeholder="請輸入"/>
                                            <span v-else>{{ microOrganism.bar_code }}</span>
                                        </td>
                                        <td data-title="微生物類別">
                                            <select v-if="(mode == 'edit') && (!microOrganism.id || microOrganism.id < 0)" v-model="microOrganism.organism_kind" v-validate="'required'" :name="`organism_kind_${microOrganism.id}`" class="form-control vertical-middle">
                                                <option :value="null">請選擇</option>
                                                <option v-for="organismKind in organismKinds" :value="organismKind.id">{{ organismKind.name }}</option>
                                            </select>
                                            <template v-else>
                                                <span v-if="microOrganism.organism_kind == 'microparticle_dot_5'">微粒子(0.5µm)</span>
                                                <span v-else-if="microOrganism.organism_kind == 'microparticle_5'">微粒子(5µm)</span>
                                                <span v-else-if="microOrganism.organism_kind == 'suspended'">懸浮微生物</span>
                                                <span v-else-if="microOrganism.organism_kind == 'falling'">落下微生物</span>
                                                <span v-else-if="microOrganism.organism_kind == 'contact'">接觸微生物</span>
                                            </template>
                                            <p v-show="errors.has(`organism_kind_${microOrganism.id}:required`)" class="help is-danger text-left">請選擇微生物類別</p>
                                        </td>
                                        <td data-title="房間">
                                            <room-environment-select v-if="(mode == 'edit') && (!microOrganism.id || microOrganism.id < 0)"
                                                                     v-model="microOrganism.room_name"
                                                                     :region-mgmt-id.sync="microOrganism.location.region_mgmt_id"
                                                                     v-validate="'required'"
                                                                     :name="`room_name_${microOrganism.id}`"
                                                                     :items="roomEnvironments"
                                                                     :locations.sync="microOrganism.location.room_environment.locations"
                                                                     select-class="form-control"
                                                                     select-style="width:100%"/>
                                            <span v-else>{{ microOrganism.room_name }}</span>
                                            <p v-show="errors.has(`room_name_${microOrganism.id}:required`)" class="help is-danger text-left">請選擇房間</p>
                                        </td>
                                        <td data-title="位置代號">
                                            <location-list-select v-if="(mode == 'edit') && (!microOrganism.id || microOrganism.id < 0)"
                                                                  v-model="microOrganism.location_id"
                                                                  v-validate="'required'"
                                                                  select-style="width:100%"
                                                                  :require-region-mgmt-id="true"
                                                                  :name="`location_id_${microOrganism.id}`"
                                                                  :minimum-results-for-search="-1"
                                                                  :items="microOrganism.location.room_environment.locations"
                                                                  :region-mgmt-id="microOrganism.location.region_mgmt_id"
                                                                  :vertex-name.sync="microOrganism.vertex_name"/>
                                            <span v-else>{{ microOrganism.location.device_name }}</span>
                                            <p v-show="errors.has(`location_id_${microOrganism.id}:required`)" class="help is-danger text-left">請選擇位置代號</p>
                                        </td>
                                        <td data-title="測量值" class="text-center">
                                            <input v-if="mode == 'edit'" v-model.number="microOrganism.organism_value" v-validate="(isRequired(microOrganism.organism_kind) ? 'required|' : '') + 'decimal:0|min_value:0|max_value:2000000000'" :name="`organism_value_${microOrganism.id}`" type="text" class="form-control vertical-middle text-center" placeholder="請輸入" @input="(e) => onInputValue(e, microOrganism)"/>
                                            <span v-else>{{ microOrganism.organism_value }}</span>
                                            <p v-show="errors.has(`organism_value_${microOrganism.id}:required`)" class="help is-danger text-left">請填寫測量值</p>
                                            <p v-show="errors.has(`organism_value_${microOrganism.id}:decimal`) || errors.has(`organism_value_${microOrganism.id}:min_value`) || errors.has(`organism_value_${microOrganism.id}:max_value`)" class="help is-danger text-left">請填寫大於 0 且小於等於 2,000,000,000 的整數</p>
                                        </td>
                                        <td data-title="採樣時間" class="text-center">
                                            <datetimepicker v-if="(mode == 'edit') && (!microOrganism.id || microOrganism.id < 0)"
                                                            v-model="microOrganism.Time"
                                                            id="time"
                                                            type="datetime"
                                                            format="yyyy-MM-dd HH:mm"
                                                            class="vertical-middle"
                                                            input-class="form-control text-center"
                                                            picker-class="theme-light-green"
                                                            placeholder="請選擇"
                                                            style="width: 100%"/>
                                            <span v-else>{{ microOrganism.Time | datetime }}</span>
                                        </td>
                                        <td data-title="建檔時間" class="text-center">
                                            <datetimepicker v-if="mode == 'edit'"
                                                            v-model="microOrganism.created_at"
                                                            id="createdAt"
                                                            type="datetime"
                                                            format="yyyy-MM-dd HH:mm"
                                                            class="vertical-middle"
                                                            input-class="form-control text-center"
                                                            picker-class="theme-light-green"
                                                            placeholder="請選擇"
                                                            style="width: 100%"/>
                                            <template v-else-if="microOrganism.created_at">{{ microOrganism.created_at | datetime }}</template>
                                        </td>
                                        <td v-if="mode == 'edit'" data-title="操作" class="text-center vertical-middle">
                                            <button type="button" class="btn btn-delete op-btn" @click="deleteRow(microOrganism)" title="刪除">
                                                <i class="glyphicon glyphicon-trash"/> 刪除
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="mode == 'edit'">
                                        <td colspan="9" class="text-center push-btn" @click="pushRow">
                                            <i class="fa fa-plus-circle fa-2x vertical-middle"/>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <pagination :direction-links="false"
                                                :boundary-links="true"
                                                size="sm"
                                                v-model="pagination.current_page"
                                                :total-page="pagination.last_page"
                                                :max-size="10"
                                                @change="changePage(pagination.current_page)"/>
                                </div>
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
        <ImportModal :file-name="importModal.file_name" :show="importModal.show" api-route="/api/microOrganisms/batch" example-route="/microOrganisms.xlsx" @close="whenImportModalClose" @response="receiveImportResponse"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import moment from 'moment';
import {md5} from 'js-md5';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import Datetimepicker from '../Module/Datetimepicker.vue';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect.vue';
import VertexListSelect from '../Vertex/VertexListSelect.vue';
import LocationListSelect from '../Location/LocationListSelect.vue';
import MicroOrganismKindListSelect from './MicroOrganismKindListSelect.vue';
import RoomEnvironmentView from '../RoomEnvironment/RoomEnvironmentView.vue';
import RoomEnvironmentSelect from '../RoomEnvironment/RoomEnvironmentSelect.vue';
import {mapGetters} from 'vuex';
import SortButton from '../Module/SortButton.vue';
import ImportModal from '../Module/ImportModal.vue';

export default {
    name: "MicroOrganismListPage",
    components: {
        ImportModal,
        SortButton,
        RoomEnvironmentSelect,
        RoomEnvironmentView,
        ClipLoader,
        Datetimepicker,
        RegionMgmtListSelect,
        VertexListSelect,
        LocationListSelect,
        MicroOrganismKindListSelect
    },
    computed: {
        ...mapGetters({
            permissions: 'user/permissions'
        }),
        newMd5() {
            let hash = md5.create();
            hash.update(JSON.stringify(_.map(this.microOrganisms, (r) => {
                return {
                    bar_code: r.bar_code,
                    organism_kind: r.organism_kind,
                    room_name: r.room_name,
                    location_id: r.location_id,
                    organism_value: r.organism_value,
                    Time: r.Time,
                    created_at: r.created_at
                }
            })));
            return hash.hex();
        },
        today() {
            return moment().format();
        },
        exportUrl() {
            let url = '/microOrganisms.xlsx';
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
        }
    },
    inject: ['$validator'],
    data() {
        return {
            loading: false,
            sending: false,
            mode: 'read',
            microOrganisms: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            roomEnvironments: [],
            organismKinds: [
                {id: 'microparticle_dot_5', name: '微粒子(0.5µm)'}, {id: 'microparticle_5', name: '微粒子(5µm)'},
                {id: 'suspended', name: '懸浮微生物'}, {id: 'falling', name: '落下微生物'},
                {id: 'contact', name: '接觸微生物'}
            ],
            regionMgmts: [],
            locations: [],
            form: {
                start_time: this.$route.query.start_time ? this.$route.query.start_time : null,
                end_time: this.$route.query.end_time ? this.$route.query.end_time : null,
                organism_kinds: this.$route.query.organism_kinds && _.isArray(this.$route.query.organism_kinds) ? this.$route.query.organism_kinds : this.$route.query.organism_kinds ? [this.$route.query.organism_kinds] : [],
                region_mgmt_id: this.$route.query.region_mgmt_id ? parseInt(this.$route.query.region_mgmt_id) : null,
                page: this.$route.query.page ? parseInt(this.$route.query.page) : 1,
                order_by: this.$route.query.order_by ? this.$route.query.order_by : null,
                direction: this.$route.query.direction ? this.$route.query.direction : null
            },
            oldMd5: null,
            organismValueSortType: null,
            timeSortType: null,
            importModal: {
                show: false,
                file_name: null
            }
        };
    },
    created() {
        Promise.all([
            this.fetchData(),
            this.fetchRegionMgmtData(),
            this.fetchLocationsData()
        ]);
        this.initSortButton();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/microOrganisms', {
                    params: this.form
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const that = this;
                    this.roomEnvironments = data.roomEnvironments;
                    if(_.keys(this.getPureForm(this.form)).length > 1) {
                        this.microOrganisms = _.map(data.microOrganisms, (r) => {
                            r.Time = moment(r.Time).tz('Asia/Taipei').toISOString();
                            r.created_at = moment(r.created_at).tz('Asia/Taipei').toISOString();
                            let roomEnvironment = null;
                            let regionMgmtId = null;
                            if(r.location && r.location.room_environment) {
                                roomEnvironment = _.find(that.roomEnvironments, ['region_mgmt_id', r.location.room_environment.region_mgmt_id]);
                                regionMgmtId = r.location.room_environment.region_mgmt_id;
                            }
                            r.location = {
                                device_name: r.location.device_name,
                                id: r.location.id,
                                room_environment: {
                                    region_mgmt_id: regionMgmtId,
                                    locations: roomEnvironment ? roomEnvironment.locations : []
                                }
                            };
                            return r;
                        });
                        this.pagination = data.pagination;
                    } else {
                        this.microOrganisms = [];
                        this.pagination.current_page = 1;
                        this.pagination.last_page = 1;
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.oldMd5 = this.newMd5;
            this.loading = false;
        },
        onDateTimePickerChange(column, value) {
            this.form[column] = value;
        },
        pushRow() {
            const minIdRow = _.minBy(this.microOrganisms, 'id');
            let newId = -1;
            if(minIdRow && minIdRow.id < 0) {
                newId = minIdRow.id - 1;
            }
            const roomEnvironment = _.find(this.roomEnvironments, (r) => r.region_mgmt_id == this.form.region_mgmt_id);
            this.microOrganisms.push({
                id: newId,
                organism_kind: null,
                organism_value: null,
                location: {
                    room_environment: {
                        region_mgmt_id: this.form.region_mgmt_id,
                        locations: []
                    }
                },
                location_id: null,
                Time: null,
                created_at: moment().format('yyyy-MM-DD HH:mm'),
                room_name: roomEnvironment ? roomEnvironment.room_name : null
            });
        },
        async deleteRow(microOrganism) {
            if(!confirm(`確定要刪除${microOrganism.id > 0 ? ' ID:' + microOrganism.id + ' ' : ''}此筆資料？`)) {
                return;
            }
            if(microOrganism.id > 0) {
                try {
                    this.sending = true;
                    let res = await axios.delete(`/api/microOrganisms/${microOrganism.id}`);
                    res = res.data;
                    if(res.status == 0) {
                        this.$toast.success({
                            title: '成功訊息',
                            message: '刪除成功'
                        });
                    }
                } catch(err) {
                    await this.guestRedirectHome(err);
                }
            }
            this.microOrganisms = _.filter(this.microOrganisms, (r) => {
                return r.id != microOrganism.id;
            });
            this.sending = false;
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }

                this.sending = true;
                let res = await axios.patch(`/api/microOrganisms/batch`, {
                    micro_organisms: this.microOrganisms
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.oldMd5 = this.newMd5;
                    this.microOrganisms = data.microOrganisms;
                    this.$toast.success({
                        title: '成功訊息',
                        message: '儲存成功'
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.mode = 'read';
            this.oldMd5 = this.newMd5;
            this.sending = false;
        },
        changePage(page = 1) {
            if(!this.checkIsSaved()) {
                return;
            }
            if(this.form.start_time && this.form.end_time && !moment(this.form.start_time).isSameOrBefore(this.form.end_time)) {
                this.$toast.error({
                    title: '錯誤訊息',
                    message: '「結束時間」不得早於「開始時間」'
                });
                return;
            }
            const query = _.pickBy(this.form, _.identity());
            query.page = page;
            this.$router.push({
                path: '/microOrganisms',
                query: query
            }).catch(() => {
                this.fetchData();
            });
        },
        resetSearch() {
            if(!this.checkIsSaved()) {
                return;
            }
            this.oldMd5 = this.newMd5;
            this.form = {
                start_time: null,
                end_time: null,
                organism_kinds: null,
                region_mgmt_id: null
            };
            this.changePage(1);
        },
        async fetchRegionMgmtData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/regionMgmts');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.regionMgmts = data.regionMgmts;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        async fetchLocationsData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/locations', {
                    params: {
                        region_mgmt_id: this.form.region_mgmt_id
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.locations = data.locations;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        checkIsSaved() {
            if(this.oldMd5 != this.newMd5) {
                if(!confirm('資料尚未儲存，確定要切換？')) {
                    return false;
                }
            }
            return true;
        },
        onClickCancelEditBtn() {
            if(!this.checkIsSaved()) {
                return;
            }
            this.mode = 'read';
            this.fetchData();
        },
        getRegionMgmtId(region) {
            const regionMgmt = _.find(this.regionMgmts, (r) => {
                return r.region == region;
            });
            if(regionMgmt) {
                return regionMgmt.id;
            }
            return null;
        },
        changeSortType(column, sortType) {
            switch(column) {
                case 'organism_value':
                    this.organismValueSortType = sortType;
                    this.timeSortType = null;
                    break;
                case 'Time':
                    this.organismValueSortType = null;
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
                case 'organism_value':
                    this.organismValueSortType = this.$route.query.direction;
                    break;
                case 'Time':
                    this.timeSortType = this.$route.query.direction;
                    break;
            }
        },
        showImportModal(fileName) {
            this.importModal.file_name = fileName;
            this.importModal.show = true;
        },
        whenImportModalClose() {
            this.importModal.show = false;
            this.importModal.file_name = null;
        },
        receiveImportResponse(res) {
            const that = this;
            this.microOrganisms = _.map(res, (r) => {
                r.Time = moment(r.Time).tz('Asia/Taipei').toISOString();
                r.created_at = moment(r.created_at).tz('Asia/Taipei').toISOString();
                let roomEnvironment = null;
                let regionMgmtId = null;
                if(r.location && r.location.room_environment) {
                    roomEnvironment = _.find(that.roomEnvironments, ['region_mgmt_id', r.location.room_environment.region_mgmt_id]);
                    regionMgmtId = r.location.room_environment.region_mgmt_id;
                }
                r.location = {
                    device_name: r.location.device_name,
                    id: r.location.id,
                    room_environment: {
                        region_mgmt_id: regionMgmtId,
                        locations: roomEnvironment ? roomEnvironment.locations : []
                    }
                };
                return r;
            });
            this.oldMd5 = this.newMd5;
        },
        isRequired(organismKind) {
            return !(organismKind == 'suspended' || organismKind == 'falling' || organismKind == 'contact');
        },
        onInputValue(e, microOrganism) {
            microOrganism.organism_value = parseInt(e.target.value.replace(/\,/g, ''));
            if(isNaN(microOrganism.organism_value)) {
                microOrganism.organism_value = null;
            }
        }
    }
}
</script>

<style scoped>
.vertical-middle{
    vertical-align: middle;
}
.push-btn{
    color:            #696969;
    background-color: white;
    cursor:           pointer;
}
.push-btn:hover{
    background-color: #f5f5f5;
}
.next-row{
    margin-top: 15px;
}
.box{
    box-shadow: 5px 5px 15px 0 #808080;
}
.box-top{
    border-radius: 15px 15px 0 0;
}
.box-bottom{
    border-radius: 0 0 15px 15px;
}
/* Hide Arrows from number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button{
    -webkit-appearance: none;
    margin:             0;
}
input[type=number]{
    -moz-appearance: textfield;
}
/* Hide Arrows from number input */
/* Hide Border from color input */
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
#excel-container{
    width:  100%;
    height: 400px;
}
/* Hide Border from color input */
</style>
