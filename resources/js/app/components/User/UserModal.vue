<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">
            <template v-if="!form.id">創建帳號</template>
            <template v-else-if="showPassword">修改密碼</template>
            <template v-else>修改個資</template>
        </div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <template v-if="showProfile">
                    <div v-if="form.id" class="form-group">
                        <label class="col-md-3 control-label">編號&nbsp;&nbsp;</label>
                        <div class="col-md-9">{{ form.id }}</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">身份<span class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <role-list-select v-model="form.roles[0].id" v-validate="'required'" name="role_id"/>
                            <span v-show="errors.has('role_id:required')" class="help is-danger">請選擇身份</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">啟用狀態</label>
                        <div class="col-md-9">
                            <input name="enable" type="checkbox" v-model="form.enable">
                        </div>
                    </div>
                </template>
                <hr v-if="showProfile && showPassword">
                <template v-if="showPassword">
                    <div class="form-group">
                        <label class="col-md-3 control-label">帳號<span v-if="!form.id" class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input class="form-control" name="account" v-model="form.account" v-validate="'required'" placeholder="請填寫帳號" :disabled="form.id" @input="showAccountRepeatMsg = false">
                            <span v-show="errors.has('account:required')" class="help is-danger">請填寫帳號</span>
                            <span v-show="showAccountRepeatMsg" class="help is-danger">已有使用者使用這組帳號！</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">密碼<span v-if="!form.id" class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input type="password" name="password" class="form-control" v-model="form.password" v-validate="'required|min:8|max:20'" placeholder="請填寫8~20碼之間" ref="password">
                            <span v-show="errors.has('password:required')" class="help is-danger">請填寫密碼</span>
                            <span v-show="errors.has('password:min')" class="help is-danger">請填寫8~20碼之間</span>
                            <span v-show="errors.has('password:max')" class="help is-danger">請填寫8~20碼之間</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">確認密碼<span v-if="!form.id" class="is-danger">&nbsp;*</span></label>
                        <div class="col-md-9">
                            <input type="password" name="password_confirmation" class="form-control" v-model="form.password_confirmation" v-validate="'required|confirmed:password'" placeholder="請填寫8~20碼之間">
                            <span v-show="errors.has('password_confirmation:required')" class="help is-danger">請填寫確認密碼</span>
                            <span v-show="errors.has('password_confirmation:confirmed')" class="help is-danger">請與密碼一致</span>
                        </div>
                    </div>
                </template>
                <div class="text-right">
                    <button class="btn btn-light-green" :disabled="sending">送出</button>
                    <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                </div>
            </form>
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
        </div>
    </modal>
</template>

<script>
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import RoleListSelect from '../Role/RoleListSelect';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect';

export default {
    name: "UserModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader, RoleListSelect, RegionMgmtListSelect, VehicleMgmtListSelect},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        uid: {
            type: [String, Number]
        },
        showProfile: {
            type: Boolean,
            default: false
        },
        showPassword: {
            type: Boolean,
            default: false
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
            form: {},
            showAccountRepeatMsg: false
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/users/${this.uid}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const form = data.user;
                    if(form.roles.length == 0) {
                        form.roles.push({
                            id: 4,
                            name: 'operate'
                        });
                    }
                    this.form = form;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async onShowModal() {
            this.resetForm();
            if(this.uid) {
                this.loading = true;
                await this.fetchData();
            }
            this.loading = false;
        },
        onHideModal() {
            this.resetForm();
            this.$emit('input', false);
        },
        async submit() {
            try {
                const isPass = await this.validate();
                if(!isPass) {
                    return;
                }

                this.sending = true;
                let res = null;
                if(!this.uid) {
                    res = await axios.post(`/api/users`, this.form);
                } else {
                    res = await axios.patch(`/api/users/${this.uid}`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.user;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.uid ? '創建' : '修改'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                } else if(res.status == -3) {
                    this.showAccountRepeatMsg = true;
                    this.scrollTo('account');
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                id: null,
                account: null,
                password: null,
                password_confirmation: null,
                enable: 1,
                roles: [
                    {id: null}
                ]
            };
            this.showAccountRepeatMsg = false;
            this.resetFormValidate();
        }
    }
}
</script>

<style scoped>
hr{
    margin-bottom: 20px;
}
</style>
