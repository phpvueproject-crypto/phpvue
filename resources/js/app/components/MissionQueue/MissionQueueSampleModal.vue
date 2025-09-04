<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :size="mode == 'remoteListMode' ? 'lg' : ''" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">{{ mode == 'remoteListMode' ? '查看採樣任務進度' : '編輯備註' }}</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <template v-if="!loading">
                <remote-management-system-status-list-view
                    v-if="mode == 'remoteListMode'"
                    :remote-management-system-statuses="form.remote_management_system_statuses"
                    :mission-queue="form"/>
                <form v-else novalidate class="form-horizontal" @submit.prevent="submit">
                    <div class="form-group">
                        <label for="remarkInput" class="col-md-3 control-label">備註</label>
                        <div class="col-md-9">
                            <input v-model="form.remark"
                                   id="remarkInput"
                                   type="text"
                                   class="form-control"
                                   name="remark"
                                   placeholder="請輸入備註">
                        </div>
                    </div>
                    <div class="text-right">
                        <button class="btn btn-light-green" :disabled="sending">送出</button>
                        <button type="button" class="btn btn-default" @click="showModal = false">取消</button>
                    </div>
                </form>
            </template>
        </div>
    </modal>
</template>

<script>
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import RemoteManagementSystemStatusListView
    from '../RemoteManagementSystemStatus/RemoteManagementSystemStatusListView.vue';

export default {
    name: "MissionQueueSampleModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {RemoteManagementSystemStatusListView, ClipLoader},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        dataId: [Number, String],
        mode: String
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            showModal: this.value,
            loading: false,
            sending: false,
            form: {
                id: null,
                remark: null,
                remote_management_system_statuses: []
            }
        };
    },
    created() {
    },
    methods: {
        getMissionQueue() {
            return {
                id: null,
                remark: null,
                remote_management_system_statuses: []
            }
        },
        onShowModal() {
            if(this.dataId) {
                this.fetchData();
            }
        },
        onHideModal() {
            this.$emit('input', false);
            this.form = this.getMissionQueue();
        },
        async fetchData() {
            try {
                this.loading = true;
                let res = await axios.get(`/api/missionQueues/${this.dataId}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.missionQueue;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        async submit() {
            const isPass = await this.validate();
            if(!isPass) {
                return;
            }
            try {
                this.sending = true;
                let res = null;
                if(!this.dataId) {
                    res = await axios.post(`/api/missionQueues`, this.form);
                } else {
                    res = await axios.put(`/api/missionQueues/${this.dataId}`, this.form);
                }
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `${!this.dataId ? '創建' : '更新'}成功`
                    });
                    this.$emit('update', _.cloneDeep(this.form));
                    this.showModal = false;
                } else if(res.status == -19) {
                    this.$toast.error({
                        title: '錯誤訊息',
                        message: `${!this.dataId ? '創建' : '更新'}失敗`
                    });
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

</style>
