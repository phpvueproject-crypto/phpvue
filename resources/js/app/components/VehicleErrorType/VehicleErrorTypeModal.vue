<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :backdrop="false" :append-to-body="true" :keyboard="false" cancel-text="取消" ok-text="確定">
        <div slot="title">
            <template v-if="mirStatus && mirStatus.vehicle_error_type">{{ mirStatus.vehicle_error_type.name }}</template>
        </div>
        <div slot="default">
            <div class="is-danger">
                <template v-if="mirStatus">
                    {{ mirStatus.vehicle_error_message }}
                </template>
            </div>
        </div>
        <div slot="footer">
            <button type="button" class="btn btn-delete" :disabled="sending" @click="submit">重置</button>
            <button type="button" class="btn btn-default" @click="showModal = false">關閉</button>
        </div>
    </modal>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';

export default {
    name: "VehicleErrorTypeModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader},
    props: {
        value: {type: Boolean, default: false},
        mirStatus: Object
    },
    watch: {
        value(newVal) {
            this.showModal = newVal;
        }
    },
    data() {
        return {
            showModal: this.value,
            sending: false
        };
    },
    created() {
    },
    methods: {
        onShowModal() {
        },
        onHideModal() {
            this.$emit('input', false);
        },
        async submit() {
            if(!confirm('確定要重置？')) {
                return;
            }
            try {
                this.sending = true;
                let res = await axios.patch(`/api/mir-statuses/${this.mirStatus.id}/clear-error`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '重置成功'
                    });
                    this.showModal = false;
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
