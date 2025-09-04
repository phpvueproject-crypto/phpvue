<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title" v-if="!pid">創建專案</div>
        <div slot="title" v-else>更新專案</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                <div class="form-group">
                    <label class="col-md-3 control-label">專案名稱<span class="is-danger">&nbsp;*</span></label>
                    <div class="col-md-9">
                        <input class="form-control" name="project_name" v-model="form.name" v-validate="'required|max:100'" placeholder="請填寫專案名稱" @input="idDuplicate = false" :disabled="pid">
                        <span v-show="errors.has('project_name:required')" class="help is-danger">請填寫專案名稱</span>
                        <span v-show="errors.has('project_name:max')" class="help is-danger">長度不得超過100字元</span>
                        <span v-show="idDuplicate" class="help is-danger">該專案名稱已存在！</span>
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
    name: "ProjectModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        pid: Number
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
            idDuplicate: false
        };
    },
    created() {
        this.resetForm();
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/projects/${this.pid}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.project;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async onShowModal() {
            this.resetForm();
            if(this.pid) {
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
                if(!this.pid) {
                    res = await axios.post(`/api/projects`, this.form);
                } else {
                    res = await axios.patch(`/api/projects/${this.pid}`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.project;
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.pid ? '創建' : '更新'}成功`
                    });
                    this.$emit('update', this.form);
                    this.showModal = false;
                } else if(res.status == -7) {
                    this.idDuplicate = true;
                    this.scrollTo('project_name');
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetForm() {
            this.form = {
                name: null
            };
            this.idDuplicate = false;
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
