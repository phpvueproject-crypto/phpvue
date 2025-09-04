<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title" v-if="!form.vendor">創建供應商</div>
        <div slot="title" v-else>更新供應商</div>
        <div slot="default">
            <form v-if="!loading" novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">供應商名稱</label>
                    <div class="col-md-9">
                        <input class="form-control" name="vendor" v-model="form.vendor" v-validate="'required'" placeholder="請填寫供應商名稱" @input="vendorDuplicate = false">
                        <span v-show="errors.has('vendor:required')" class="help is-danger">請填寫供應商名稱</span>
                        <span v-show="vendorDuplicate" class="help is-danger">該供應商已存在！</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">供應商統編</label>
                    <div class="col-md-9">
                        <input class="form-control" name="vendor_vat" v-model="form.vendor_vat" v-validate="'required'" placeholder="請填寫供應商統編">
                        <span v-show="errors.has('vendor_vat:required')" class="help is-danger">請填寫供應商統編</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">供應商電話</label>
                    <div class="col-md-9">
                        <input class="form-control" name="vendor_support" v-model="form.vendor_support" v-validate="'required'" placeholder="請填寫供應商電話">
                        <span v-show="errors.has('vendor_support:required')" class="help is-danger">請填寫供應商電話</span>
                    </div>
                </div>
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

export default {
    name: "VendorMgmtModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        vendor: {
            type: String
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
            vendorDuplicate: false
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/vendorMgmts/${this.vendor}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.vendorMgmt;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async onShowModal() {
            this.resetForm();
            if(this.vendor) {
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
                if(!isPass)
                    return;

                this.sending = true;
                let res = null;
                if(!this.vendor)
                    res = await axios.post(`/api/vendorMgmts`, this.form);
                else
                    res = await axios.patch(`/api/vendorMgmts/${this.vendor}`, this.form);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.vendorMgmt;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.vendor ? '創建' : '更新'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                } else if(res.status == -7) {
                    this.vendorDuplicate = true;
                    this.scrollTo('vendor');
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                vendor: null,
                vendor_vat: null,
                vendor_support: null
            };
            this.vendorDuplicate = false;
            this.resetFormValidate();
        }
    }
}
</script>
