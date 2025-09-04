<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ dataId ? '更新連接' : '創建連接' }}</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">名稱<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="name" v-model="form.name" v-validate="'required|max:100'" placeholder="請輸入名稱">
                        <span v-show="errors.has('name:required')" class="help is-danger">請輸入名稱</span>
                        <span v-show="errors.has('name:max')" class="help is-danger">長度不得超過100字元</span>
                        <span v-if="formErrors.name" class="help is-danger">{{ formErrors.name[0] }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">IP連線<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <input type="radio" value="ip"
                                       v-model="allowInputColumn">
                            </span>
                            <input class="form-control" name="ip" placeholder="請輸入IP連線" :disabled="allowInputColumn != 'ip'"
                                   v-model="form.ip" v-validate="{regex: /(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))(\/([0-9]|[12][0-9]|3[0-2]))?$/}">
                        </div>
                        <span v-show="errors.has('ip:regex')" class="help is-danger">請輸入有效的IP連線</span>
                        <span v-if="formErrors.ip" class="help is-danger">{{ formErrors.ip[0] }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">AP連線<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <input type="radio" value="ap"
                                       v-model="allowInputColumn">
                            </span>
                            <input class="form-control" name="ap" placeholder="請輸入AP連線" :disabled="allowInputColumn != 'ap'"
                                   v-model="form.ap">
                        </div>
                        <span v-if="formErrors.ap" class="help is-danger">{{ formErrors.ap[0] }}</span>
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

export default {
    name: "DeviceModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader},
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
        },
        allowInputColumn(newVal) {
            switch(newVal) {
                case 'ip':
                    this.form.ap = null;
                    break;
                case 'ap':
                    this.form.ip = null;
                    break;
            }
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            showModal: this.value,
            form: {},
            formErrors: [],
            nameDuplicate: false,
            allowInputColumn: 'ip'
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/devices/${this.dataId}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.device;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
        },
        async onShowModal() {
            this.resetForm();
            if(this.dataId) {
                this.loading = true;
                await this.fetchData();
            }
            this.loading = false;
        },
        onHideModal() {
            this.formErrors = [];
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
                if(!this.dataId) {
                    res = await axios.post(`/api/devices`, this.form);
                } else {
                    res = await axios.patch(`/api/devices/${this.dataId}`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.device;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.dataId ? '創建' : '更新'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                    this.formErrors = [];
                } else if(res.status == -7) {
                    const data = res.data;
                    this.formErrors = data.errors;
                    this.scrollTo(Object.keys(this.formErrors)[0]);
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                name: null,
                ip: null,
                ap: null
            };
            this.nameDuplicate = false;
            this.resetFormValidate();
        }
    }
}
</script>

<style scoped lang="scss">

</style>
