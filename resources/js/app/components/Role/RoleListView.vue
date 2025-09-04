<template>
    <div>
        <div v-if="!loading">
            <table class="table table-bordered table-hover break-table">
                <thead>
                <tr class="table-head">
                    <th class="text-center hide-td">ID</th>
                    <th class="hide-td">群組</th>
                    <th class="hide-td">描述</th>
                    <template v-for="(permission, permissionIdx) in permissions">
                        <th v-if="permissionIdx % 2 == 0 && permission.description" :key="permission.id" class="hide-td">{{ permission.display_name }}</th>
                    </template>
                    <th v-if="mode == 'edit'" class="text-center vertical-middle hide-td" rowspan="3">操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(role, roleIdx) in roles" :key="role.id">
                    <td data-title="ID" class="text-center vertical-middle">
                        <template v-if="role.id > 0">{{ role.id }}</template>
                    </td>
                    <td data-title="群組">
                        <template v-if="mode == 'edit'">
                            <input type="text" class="form-control"
                                   v-model="role.name" v-validate="'required'" :name="`role_name_${role.id}`">
                            <span v-show="errors.has(`role_name_${role.id}:required`)" class="help is-danger text-left">請填寫群組</span>
                        </template>
                        <template v-else>{{ role.name }}</template>
                    </td>
                    <td data-title="描述">
                        <template v-if="mode == 'edit'">
                            <input type="text" class="form-control"
                                   v-model="role.description">
                        </template>
                        <template v-else>{{ role.description }}</template>
                    </td>
                    <template v-for="(permission, permissionIdx) in permissions">
                        <td :data-title="permission.display_name"
                            v-if="permissionIdx % 2 == 0 && permission.description" :key="permission.id">
                            <template v-if="mode == 'edit'">
                                <input type="checkbox" class="inline-block" :id="`${permissions[permissionIdx].name}_${role.id}`"
                                       v-model="role.permissions" :value="{id: permissions[permissionIdx].id}" @change="(e) => whenReadPermissionChanged(e, roleIdx, permissions[permissionIdx + 1].id)">
                                <label :for="`${permissions[permissionIdx].name}_${role.id}`">{{ permissions[permissionIdx].description }}</label>
                                <template v-if="(permissionIdx + 1) < permissions.length">
                                    <input type="checkbox" class="inline-block" :id="`${permissions[permissionIdx + 1].name}_${role.id}`"
                                           v-model="role.permissions" :value="{id: permissions[permissionIdx + 1].id}" :disabled="!getIsContains(role.permissions, permissions[permissionIdx].id, 'id')">
                                    <label :for="`${permissions[permissionIdx + 1].name}_${role.id}`">{{ permissions[permissionIdx + 1].description }}</label>
                                </template>
                            </template>
                            <template v-else>
                                <template v-for="rolePermission in role.permissions">
                                    <span v-if="rolePermission.id == permissions[permissionIdx].id">{{ permissions[permissionIdx].description }}</span>
                                    <span v-else-if="(permissionIdx + 1) < permissions.length && rolePermission.id == permissions[permissionIdx + 1].id">&nbsp;/&nbsp;{{ permissions[permissionIdx + 1].description }}</span>
                                </template>
                            </template>
                        </td>
                    </template>
                    <td v-if="mode == 'edit'" data-title="操作" class="text-center vertical-middle">
                        <button type="button" class="btn btn-light-green op-btn" @click="showRolePagePermissionModal(role)" title="編輯頁面權限">
                            <i class="glyphicon glyphicon-pencil"/> 編輯頁面權限
                        </button>
                        <button type="button" class="btn btn-delete op-btn" @click="deleteRow(role)" title="刪除">
                            <i class="glyphicon glyphicon-trash"/> 刪除
                        </button>
                    </td>
                </tr>
                <tr v-if="mode == 'edit'">
                    <td :colspan="pushBtnColspan" class="text-center push-btn" @click="pushRow">
                        <i class="fa fa-plus-circle fa-2x vertical-middle"/>
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
        <role-page-permission-modal v-model="rolePagePermissionModal.show" :role-id="rolePagePermissionModal.role_id" :permissions="rolePagePermissionModal.permissions" :page-permissions="pagePermissions" @update="updateRow"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import {md5} from 'js-md5';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import RolePagePermissionModal from './RolePagePermissionModal.vue';

export default {
    name: "RoleListView",
    components: {
        RolePagePermissionModal,
        ClipLoader
    },
    inject: ['$validator'],
    props: {
        roleMode: {type: String, default: 'read'}
    },
    computed: {
        newMd5() {
            let hash = md5.create();
            hash.update(JSON.stringify(this.roles));
            return hash.hex();
        },
        pushBtnColspan() {
            if(_.isArray(this.permissions) && this.permissions.length > 0) {
                return (4 + Math.ceil(this.permissions.length / 2));
            }
            return 4;
        },
        pagePermissions() {
            return _.filter(this.permissions, (r) => {
                return !r.description;
            });
        },
        pagePermissionIds() {
            const pagePermissions = _.filter(this.permissions, (r) => {
                return !r.description;
            });
            return _.map(pagePermissions, (r) => {
                return r.id;
            });
        }
    },
    watch: {
        roleMode(newVal) {
            switch(newVal) {
                case 'edit':
                    this.mode = newVal;
                    break;
                case 'read':
                    this.onClickCancelEditBtn();
                    break;
            }
        },
        mode(newVal) {
            this.$emit('update:roleMode', newVal);
        }
    },
    data() {
        return {
            loading: false,
            sending: false,
            mode: 'read',
            roles: [],
            permissions: [],
            oldMd5: null,
            rolePagePermissionModal: {
                show: false,
                role_id: null,
                permissions: []
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
                let res = await axios.get('/api/roles');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.roles = _.map(data.roles, (r) => {
                        if(_.isArray(r.permissions) && r.permissions.length > 0) {
                            r.permissions = _.map(r.permissions, (r) => {
                                return {id: r.id};
                            });
                        } else {
                            r.permissions = [];
                        }
                        return r;
                    });
                    this.permissions = data.permissions;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.oldMd5 = this.newMd5;
            this.loading = false;
        },
        pushRow() {
            if(this.roles.length >= 20) {
                alert('數量已達上限，無法繼續新增。');
                return;
            }
            const minIdRow = _.minBy(this.roles, 'id');
            let newId = -1;
            if(minIdRow && minIdRow.id < 0) {
                newId = minIdRow.id - 1;
            }

            this.roles.push({
                id: newId,
                name: null,
                display_name: null,
                description: null,
                permissions: []
            });
        },
        async deleteRow(role) {
            if(!confirm(`確定要刪除${role.id > 0 ? ' ID:' + role.id + ' ' : ''}此筆資料？`)) {
                return;
            }
            if(role.id > 0) {
                try {
                    this.sending = true;
                    let res = await axios.delete(`/api/roles/${role.id}`);
                    res = res.data;
                    if(res.status == 0) {
                        this.$toast.success({
                            title: '成功訊息',
                            message: '刪除成功'
                        });
                        this.roles = _.filter(this.roles, (r) => {
                            return r.id != role.id;
                        });
                    }
                } catch(err) {
                    this.guestRedirectHome(err);
                }
                this.sending = false;
            } else if(role.id < 0) {
                this.$toast.success({
                    title: '成功訊息',
                    message: '刪除成功'
                });
                this.roles = _.filter(this.roles, (r) => {
                    return r.id != role.id;
                });
            }
        },
        updateRow(row) {
            const that = this;
            if(!row.role_id) {
                return;
            }
            const roleIdx = _.findIndex(this.roles, (r) => {
                return r.id == row.role_id;
            });
            if(roleIdx != -1) {
                this.roles[roleIdx].permissions = _.filter(this.roles[roleIdx].permissions, (r) => {
                    return !that.pagePermissionIds.contains(r.id);
                });
                _.forEach(row.permissions, (r) => {
                    that.roles[roleIdx].permissions.push(r);
                });
            }
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }
                const roles = _.map(this.roles, (r) => {
                    if(r.id < 0) {
                        r.id = null;
                    }
                    return r;
                });

                this.sending = true;
                let res = await axios.patch(`/api/roles`, {
                    roles: roles
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.roles = _.cloneDeep(data.roles);
                    await this.syncUser();
                    this.$toast.success({
                        title: '成功訊息',
                        message: '儲存成功'
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.mode = 'read';
            this.oldMd5 = this.newMd5;
            this.sending = false;
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
        getIsContains(array, value, column) {
            return !!_.find(array, {[column]: value});
        },
        whenReadPermissionChanged(e, roleIdx, deletePermissionId) {
            const checked = e.target.checked;
            if(!checked) {
                this.roles[roleIdx].permissions = _.filter(this.roles[roleIdx].permissions, (r) => {
                    return r.id != deletePermissionId;
                });
            }
        },
        showRolePagePermissionModal(role) {
            const that = this;
            this.rolePagePermissionModal.role_id = role.id;
            this.rolePagePermissionModal.permissions = _.filter(role.permissions, (r) => {
                return that.pagePermissionIds.contains(r.id);
            });
            this.rolePagePermissionModal.show = true;
        }
    }
}
</script>

<style scoped>
.vertical-middle{
    vertical-align: middle;
}
.inline-block{
    display: inline-block;
}
.push-btn{
    color:            #696969;
    background-color: white;
    cursor:           pointer;
}
.push-btn:hover{
    background-color: #f5f5f5;
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
/* Hide Border from color input */
</style>
