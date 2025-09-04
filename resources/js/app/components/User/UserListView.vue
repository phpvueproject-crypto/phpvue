<template>
    <div class="row">
        <div v-if="!loading" class="col-xs-10 table-block">
            <table class="table table-bordered table-hover break-table">
                <thead class="table-head">
                <tr>
                    <th class="hide-td no-wrap text-center">{{ $t('id') }}</th>
                    <th class="hide-td no-wrap">{{ $t('account') }}</th>
                    <th class="hide-td no-wrap">{{ $t('role') }}</th>
                    <th class="hide-td no-wrap">管理區域</th>
                    <th class="hide-td no-wrap">監看區域</th>
                    <th class="hide-td no-wrap text-center">{{ $t('enable') }}</th>
                    <th class="hide-td no-wrap">{{ $t('createdAt') }}</th>
                    <th class="hide-td no-wrap">{{ $t('updatedAt') }}</th>
                    <th class="hide-td no-wrap text-center th-operate">{{ $t('operate') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="8" style="line-height: 5px;">
                                            <span class="red font-bold" style="font-size:8pt">
                                                {{ $t('PEATITSF') }}
                                            </span>
                    </td>
                </tr>
                <tr>
                    <td class="hide-td no-wrap text-center"></td>
                    <td :data-title="$t('account')" class="th-search-text">
                        <input v-model="form.account" class="form-control" :placeholder="$t('search')" @keypress.enter="fetchData">
                    </td>
                    <td class="hide-td"></td>
                    <td class="hide-td"></td>
                    <td class="hide-td"></td>
                    <td :data-title="$t('enable')" class="th-search-enable">
                        <select class="form-control" v-model="form.enable" @change="fetchData">
                            <option :value="null">{{ $t('all') }}</option>
                            <option value="1">{{ $t('enabled') }}</option>
                            <option value="0">{{ $t('disabled') }}</option>
                        </select>
                    </td>
                    <td class="hide-td"></td>
                    <td class="hide-td"></td>
                    <td class="hide-td"></td>
                </tr>
                <tr v-for="(user, userIdx) in users" :class="[userIdx % 2 == 0 ? 'tr-gray' : '']">
                    <td class="no-wrap vertical-middle text-center" :data-title="$t('id')">{{ user.id }}</td>
                    <td class="no-wrap vertical-middle" :data-title="$t('account')">{{ user.account }}</td>
                    <td class="no-wrap vertical-middle" :data-title="$t('role')">{{ user.roles[0].display_name }}</td>
                    <td data-title="管理區域" @dragover="(e) => dragOver(e)" @drop="drop(userIdx, 'write_region_mgmts')">
                        <div>
                            <button type="button" class="btn btn-default btn-sm op-btn" :disabled="sending" @click="selectAllRegion(userIdx, 'write_region_mgmts')">
                                <i class="fa fa-check-square-o" aria-hidden="true"/>
                                全選
                            </button>
                        </div>
                        <div class="region-label-div">
                            <div v-for="(writeRegionMgmt, writeRegionMgmtIdx) in user.write_region_mgmts"
                                 :key="writeRegionMgmt.id"
                                 class="label label-success region-label">{{ writeRegionMgmt.region }}
                                <i class="fa fa-times-circle region-label-close-btn" :class="{'cursor-disable' : sending}" @click="deleteRegion(userIdx, 'write_region_mgmts', writeRegionMgmtIdx)"/>
                            </div>
                        </div>
                    </td>
                    <td data-title="監看區域" @dragover="(e) => dragOver(e)" @drop="drop(userIdx, 'read_region_mgmts')">
                        <div>
                            <button type="button" class="btn btn-default btn-sm op-btn" :disabled="sending" @click="selectAllRegion(userIdx, 'read_region_mgmts')">
                                <i class="fa fa-check-square-o" aria-hidden="true"/>
                                全選
                            </button>
                        </div>
                        <div class="region-label-div">
                            <div v-for="(readRegionMgmt, readRegionMgmtIdx) in user.read_region_mgmts"
                                 :key="readRegionMgmt.id"
                                 class="label label-success region-label">{{ readRegionMgmt.region }}
                                <i class="fa fa-times-circle region-label-close-btn" :class="{'cursor-disable' : sending}" @click="deleteRegion(userIdx, 'read_region_mgmts', readRegionMgmtIdx)"/>
                            </div>
                        </div>
                    </td>
                    <td class="no-wrap vertical-middle text-center" :data-title="$t('enable')" :class="[user.enable ? 'green' : 'red']">
                        {{ user.enable ? $t('enabled') : $t('disabled') }}
                    </td>
                    <td class="no-wrap vertical-middle" :data-title="$t('createdAt')">{{ user.created_at | datetime }}</td>
                    <td class="no-wrap vertical-middle" :data-title="$t('updatedAt')">{{ user.updated_at | datetime }}</td>
                    <td class="no-wrap vertical-middle text-center" :data-title="$t('operate')">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuOperate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                操作
                                <span class="caret"/>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuOperate">
                                <li>
                                    <a href="javascript:void(0)" @click="showModal(user.id, true, false)" title="修改個資">
                                        <i class="glyphicon glyphicon-pencil"/>
                                        {{ $t('editData') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" @click="showModal(user.id, false, true)" title="修改密碼">
                                        <i class="glyphicon glyphicon-lock"/>
                                        {{ $t('editPassword') }}</a>
                                </li>
                                <li :class="{'disabled' : LoginUser.id == user.id}">
                                    <a href="javascript:void(0)" @click="deleteRow(user)" title="刪除">
                                        <i class="glyphicon glyphicon-trash"/>
                                        {{ $t('delete') }}</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-2">
            <h3 class="region-title">區域：</h3>
            <ul class="region-list">
                <li v-for="(regionMgmt, regionMgmtIdx) in regionMgmts"
                    :key="regionMgmt.id"
                    class="region-item"
                    :class="{'cursor-disable' : sending}"
                    :draggable="!sending"
                    @dragstart="dragStart(regionMgmtIdx)" @dragend="dragEnd">{{ regionMgmt.region }}
                </li>
            </ul>
        </div>
        <div class="col-xs-10">
            <div class="text-center">
                <pagination :direction-links="false"
                            :boundary-links="true"
                            size="sm"
                            v-model="pagination.current_page"
                            :total-page="pagination.last_page"
                            :max-size="10"
                            @change="fetchData"/>
            </div>
        </div>
        <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
        <user-modal v-model="modal.show"
                    :uid="modal.id"
                    :show-profile="modal.showProfile"
                    :show-password="modal.showPassword"
                    @update="resetRow"
                    @refresh="fetchData"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import {mapGetters} from 'vuex';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import UserModal from './UserModal';

export default {
    name: "UserListView",
    components: {UserModal, ClipLoader},
    computed: {
        ...mapGetters({
            LoginUser: 'user/user'
        })
    },
    props: {
        userModal: {
            type: Object,
            default: () => {
                return {
                    id: null,
                    show: false,
                    showProfile: true,
                    showPassword: true
                }
            }
        }
    },
    watch: {
        userModal: {
            handler(newVal) {
                if(newVal.show) {
                    this.showModal(newVal.id, newVal.showProfile, newVal.showPassword);
                }
            },
            deep: true
        },
        modal: {
            handler() {
                this.$emit('update:userModal', this.modal);
            },
            deep: true
        }
    },
    data() {
        return {
            loading: false,
            sending: false,
            users: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            modal: {
                id: null,
                show: false,
                showProfile: true,
                showPassword: true
            },
            form: {
                account: null,
                enable: null
            },
            regionMgmts: [],
            draggedItem: null
        };
    },
    created() {
        this.fetchData();
        this.fetchRegionMgmtData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/users', {
                    params: {
                        page: this.pagination.current_page,
                        account: this.form.account,
                        enable: this.form.enable
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.users = _.map(data.users, (user) => {
                        user.selected = 0;
                        if(user.roles.length == 0) {
                            user.roles.push({
                                id: 4,
                                display_name: 'operator'
                            });
                        }
                        return user;
                    });
                    this.pagination = data.pagination;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        async fetchRegionMgmtData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/regionMgmts', {
                    params: {
                        all: 1
                    }
                });
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
        showModal(id, showProfile, showPassword) {
            this.modal.id = id;
            this.modal.showProfile = showProfile;
            this.modal.showPassword = showPassword;
            this.modal.show = true;
        },
        resetRow(row) {
            const idx = _.findIndex(this.users, (r) => {
                return r.id == row.id;
            });
            if(idx == -1) {
                this.users.splice(0, 0, row);
            } else {
                this.users[idx] = row;
            }
        },
        async deleteRow(row) {
            if(this.LoginUser.id == row.id) {
                return;
            }
            if(!confirm(`確定要刪除「${row.name}」?`)) {
                return;
            }

            try {
                let res = await axios.delete(`/api/users/${row.id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.users = _.filter(this.users, (r) => {
                        return r.id != row.id;
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        async updateRow(row) {
            try {
                this.sending = true;
                let res = await axios.patch(`/api/users/${row.id}`, row);
                res = res.data;
                if(res.status != 0) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: '資料尚未儲存！'
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        dragStart(regionMgmtIdx) {
            this.draggedItem = this.regionMgmts[regionMgmtIdx];
        },
        dragEnd() {
            this.draggedItem = null;
        },
        dragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            e.cancelBubble = true;
        },
        drop(userIdx, column) {
            const region = _.find(this.users[userIdx][column], {'id': this.draggedItem.id});
            if(!region) {
                this.users[userIdx][column].push(this.draggedItem);
                this.updateRow(this.users[userIdx]);
            }
        },
        deleteRegion(userIdx, column, userRegionMgmtIdx) {
            if(this.sending) {
                return;
            }
            if(!userIdx && userIdx != 0) {
                return;
            }
            this.users[userIdx][column].splice(userRegionMgmtIdx, 1);
            this.updateRow(this.users[userIdx]);
        },
        selectAllRegion(userIdx, column) {
            if(this.sending) {
                return;
            }
            if(!userIdx && userIdx != 0) {
                return;
            }
            this.users[userIdx][column] = _.cloneDeep(this.regionMgmts);
            this.updateRow(this.users[userIdx]);
        }
    }
}
</script>

<style scoped>
.th-search-text{
    min-width: 80px;
    max-width: 100px;
}
.th-search-enable{
    min-width: 100px;
}
.table-block{
    height:        calc(100vh - 305px);
    padding-right: 0;
    overflow:      auto;
}
.region-title{
    font-size:    18px;
    font-weight:  bold;
    margin-top:   14px;
    padding-left: 7px;
}
.region-list{
    border:          1px solid #f4f4f4;
    height:          calc(100vh - 350px);
    overflow:        auto;
    list-style-type: none;
    padding-left:    0;
}
.region-item{
    user-select: none;
    cursor:      pointer;
    padding:     8px;
}
.region-item:hover{
    background-color: #e3e3e3;
}
.region-item:active{
    cursor: grabbing;
}
.region-item:not(:first-child){
    margin-top: 10px;
}
.label-success{
    background-color: #6db880 !important;
}
.region-label{
    display:       inline-block;
    position:      relative;
    font-size:     14px;
    margin-right:  15px;
    user-select:   none;
    margin-top:    10px;
    margin-bottom: 10px;
}
.region-label-close-btn{
    position: absolute;
    top:      -10px;
    right:    -10px;
    color:    #6db880;
    cursor:   pointer;
}
.no-wrap{
    white-space: nowrap;
}
.vertical-middle{
    vertical-align: middle;
}
.cursor-disable, .cursor-disable:hover, .cursor-disable:active{
    cursor: not-allowed;
}
.th-operate{
    width: 170px;
}
.region-label-div{
    margin-top:  10px;
    padding-top: 5px;
    border-top:  1px solid lightgray;
}
@media only screen and (max-width: 768px){
    .th-search-text{
        max-width: 100%;
    }
    .th-search-enable{
        max-width: 100%;
    }
}
</style>
