<template>
    <div>
        <section class="content-header clearfix">
            <template v-if="tabIdx == 0">{{ $t('AAPM') }}&emsp;
                <button class="btn btn-light-green op-btn" @click="showModal(null, true, true)" title="創建使用者">
                    <img src="/img/icon-create-user.png" class="icon-user">&nbsp;{{ $t('createUser') }}
                </button>
            </template>
            <template v-else-if="tabIdx == 1">群組管理&emsp;
                <button v-if="roleMode != 'edit'" class="btn btn-light-green op-btn" @click="roleMode = 'edit'">
                    <i class="fa fa-pencil" aria-hidden="true"/>&nbsp;編輯
                </button>
                <button v-else-if="roleMode == 'edit'" class="btn btn-delete op-btn" @click="roleMode = 'read'">
                    <i class="fa fa-times-circle" aria-hidden="true"/>&nbsp;取消編輯
                </button>
            </template>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <tabs v-model="tabIdx" @change="refreshData">
                                <tab title="帳號管理">
                                    <user-list-view ref="userList" :user-modal.sync="modal"/>
                                </tab>
                                <tab title="群組管理">
                                    <role-list-view ref="roleList" :role-mode.sync="roleMode"/>
                                </tab>
                            </tabs>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import {mapGetters} from 'vuex';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import UserListView from './UserListView.vue';
import RoleListView from '../Role/RoleListView.vue';

export default {
    name: "UserListPage",
    components: {RoleListView, UserListView, ClipLoader},
    computed: {
        ...mapGetters({
            LoginUser: 'user/user'
        })
    },
    data() {
        return {
            tabIdx: 0,
            modal: {
                id: null,
                show: false,
                showProfile: true,
                showPassword: true
            },
            roleMode: 'read'
        };
    },
    methods: {
        showModal(id, showProfile, showPassword) {
            this.modal.id = id;
            this.modal.showProfile = showProfile;
            this.modal.showPassword = showPassword;
            this.modal.show = true;
        },
        refreshData(index) {
            if(index == 0) {
                this.$refs.userList.fetchData();
            } else if(index == 1) {
                this.$refs.roleList.fetchData();
            }
        }
    }
}
</script>

<style scoped>
.icon-user{
    width: 14px;
}
</style>
