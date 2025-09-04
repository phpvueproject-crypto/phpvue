<template>
    <modal v-model="showModal" @show="onShowModal" @hide="onHideModal" :footer="false" :backdrop="false" :append-to-body="true" :keyboard="false">
        <div slot="title">所有點位</div>
        <div slot="default">
            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
            <table v-else class="table table-bordered table-hover break-table">
                <thead>
                <tr class="table-head">
                    <th class="hide-td">點位名稱</th>
                    <th class="hide-td centered">位置</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="row in missionQueue.locations">
                    <td data-title="點位名稱">{{ row.device_name }}</td>
                    <td data-title="位置" class="centered">
                        <template v-if="row.x !== null && row.y !== null">
                            ({{ row.x }}, {{ row.y }})
                        </template>
                        <template v-else>
                            未設定
                        </template>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </modal>
</template>

<script>
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';

export default {
    name: "MissionQueueModal",
    $_veeValidate: {
        validator: 'new'
    },
    components: {ClipLoader},
    props: {
        value: {
            type: Boolean,
            default: false
        },
        dataId: [Number, String]
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
            missionQueue: {}
        };
    },
    created() {
    },
    methods: {
        async fetchData() {
            try {
                this.loading = true;
                let res = await axios.get(`/api/missionQueues/${this.dataId}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.missionQueue = data.missionQueue;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        onShowModal() {
            if(this.dataId) {
                this.fetchData();
            }
        },
        onHideModal() {
            this.$emit('input', false);
        }
    }
}
</script>

<style scoped lang="scss">

</style>
