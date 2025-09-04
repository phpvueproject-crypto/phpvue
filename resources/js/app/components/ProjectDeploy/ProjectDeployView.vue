<template>
    <div>
        <div class="vehicle-list">
            <div class="box">
                <div class="box-body">
                    <div v-if="!loading">
                        <table class="table table-bordered table-hover break-table size-sm">
                            <thead class="table-head linear-gradient">
                            <tr>
                                <th class="text-center hide-td">車輛ID</th>
                                <th class="text-center hide-td">狀態</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="vehicleMgmt in vehicleMgmts" class="tr-purple tr-lite-purple">
                                <td data-title="車輛ID" class="text-center">{{ vehicleMgmt.vehicle_id }}</td>
                                <td data-title="狀態" class="text-center">
                                    <template v-if="vehicleMgmt.vehicle_status">
                                        <span v-if="vehicleMgmt.vehicle_status.deploy_status === null">尚未燒入</span>
                                        <span v-else-if="vehicleMgmt.vehicle_status.deploy_status === 0 || vehicleMgmt.vehicle_status.deploy_status === 2">燒入失敗</span>
                                        <span v-else-if="vehicleMgmt.vehicle_status.deploy_status === 1">燒入成功</span>
                                        <span v-else-if="vehicleMgmt.vehicle_status.deploy_status === 3">正在燒入</span>
                                        <div style="position: relative; display: inline-block">
                                            <i class="fa fa-info-circle"
                                               style="cursor: pointer"
                                               :style="`opacity: ${vehicleMgmt.vehicle_status.deploy_fail_reason ? 1 : 0.2}`"
                                               :title="vehicleMgmt.vehicle_status.deploy_fail_reason"/>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span>尚無狀態資料</span>
                                    </template>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                </div>
            </div>
            <div class="button-list">
                <button class="btn btn-warning burn-in" :disabled="!value || writing || sending" @click="submit" title="燒入">
                    <div class="status-div">
                        <span v-if="!projectDeploy || projectDeploy.deploy_status != 2">燒錄</span>
                        <span v-else-if="projectDeploy.deploy_status == 2">發佈中</span>
                        <pulse-loader v-show="writing" class="loading" color="gray" size="4px"/>
                    </div>
                </button>
                <button class="btn btn-delete burn-in" :disabled="!writing" @click="destroy" title="取消">取消</button>
            </div>
        </div>
        <div v-if="projectDeploy && projectDeploy.deploy_fail_desc" class="project-deploy-fail-desc">
            {{ projectDeploy.deploy_fail_desc }}
        </div>
        <vertex-list-modal v-model="modal.show" :vertices="modal.vertices">
            <template slot="head">
                <th class="text-center" style="width: 130px;">區域</th>
                <th class="text-center" style="width: 130px;">站點名稱</th>
                <th>$t('reason')</th>
            </template>
            <template slot="body" slot-scope="slotProps">
                <tr>
                    <td class="text-center">
                        {{ slotProps.vertex.region }}
                    </td>
                    <td class="text-center">
                        {{ slotProps.vertex.name }}
                    </td>
                    <td class="red">{{ $t('notYetSetCustomAttribute') }}</td>
                </tr>
            </template>
        </vertex-list-modal>
    </div>
</template>

<script>
import axios from 'axios';
import PulseLoader from 'vue-spinner/src/PulseLoader';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import VertexListModal from '../Vertex/VertexListModal.vue';
import _ from 'lodash';

let timeCounter = null;
export default {
    name: "ProjectDeployView",
    components: {VertexListModal, PulseLoader, ClipLoader},
    props: {
        value: {
            type: String,
            default: null
        },
        unsaved: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        writing() {
            return this.projectDeploy && (this.projectDeploy.deploy_status == 2);
        }
    },
    watch: {
        value(newVal, oldVal) {
            if(oldVal) {
                const projectName = oldVal.replaceAll('-', '');
                window.Echo.leave(`projectDeploys.${projectName}`);
            }
            if(newVal) {
                this.fetchData();
                const that = this;
                const projectName = newVal.replaceAll('-', '');
                window.Echo.private(`projectDeploys.${projectName}`).listen('ProjectDeployUpdated', (e) => {
                    that.$toast.success({
                        title: '成功訊息',
                        message: '燒入成功'
                    });
                    that.projectDeploy.deploy_status = e.projectDeploy.deploy_status;
                    that.projectDeploy.deploy_fail_desc = e.projectDeploy.deploy_fail_desc;
                    that.vehicleMgmts = e.vehicleMgmts;
                });
            }
        }
    },
    data() {
        return {
            loading: true,
            sending: false,
            projectDeploy: null,
            vehicleMgmts: [],
            modal: {
                show: false,
                vertices: []
            }
        }
    },
    async created() {
        this.loading = true;
        if(this.value) {
            await this.fetchData();
            const that = this;
            window.Echo.private(`projectDeploys.${this.value}`).listen('ProjectDeployUpdated', (e) => {
                that.$toast.success({
                    title: '成功訊息',
                    message: '燒入成功'
                });
                that.projectDeploy.deploy_status = e.projectDeploy.deploy_status;
                that.projectDeploy.deploy_fail_desc = e.projectDeploy.deploy_fail_desc;
                that.vehicleMgmts = e.vehicleMgmts;
            });
        }
        this.loading = false;
    },
    destroyed() {
        if(this.value) {
            const projectName = this.value.replaceAll('-', '');
            window.Echo.leave(`projectDeploys.${projectName}`);
        }
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get(`/api/projectDeploys/${this.value}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.projectDeploy = data.projectDeploy;
                    this.vehicleMgmts = _.map(data.vehicleMgmts, (r) => {
                        if(r.vehicle_status) {
                            r.vehicle_status.deploy_status = parseInt(r.vehicle_status.deploy_status);
                        }
                        return r;
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async submit() {
            if(this.unsaved) {
                clearTimeout(timeCounter);
                alert('站點或軌道已被變更，請先儲存！');
                this.$emit('update:highLight', true);
                timeCounter = setTimeout(() => {
                    this.$emit('update:highLight', false);
                }, 4800);
                return;
            }
            if(!confirm('確定要燒錄車輛？')) {
                return;
            }

            try {
                this.sending = true;
                let res = null;
                if(!this.projectDeploy) {
                    res = await axios.post(`/api/projectDeploys`, {
                        project_name: this.value
                    });
                } else {
                    res = await axios.patch(`/api/projectDeploys/${this.value}`, {
                        project_name: this.value
                    });
                }
                res = res.data;
                const data = res.data;
                if(res.status == 0) {
                    this.projectDeploy = data.projectDeploy;
                } else if(res.status == -15) {
                    this.modal.vertices = data.vertices;
                    this.modal.show = true;
                }
                this.sending = false;
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        async destroy() {
            try {
                let res = await axios.delete(`/api/projectDeploys/${this.value}`);
                res = res.data;
                if(res.status == 0) {
                    this.projectDeploy = null;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        }
    }
}
</script>

<style scoped>
.burn-in{
    display:    inline-block;
    min-height: 30px;
    width:      47%;
    max-height: 24px;
    padding:    0;
    transition: all 0.8s ease-in-out;
}
.vehicle-list{
    text-align:       center;
    display:          inline-block;
    width:            306px;
    height:           354px;
    overflow-y:       scroll;
    background-color: white;
}
.button-list{
    display:          block;
    position:         absolute;
    bottom:           0;
    padding:          10px 0 10px 5px;
    left:             0;
    right:            18px;
    background-color: white;
}
.loading{
    margin: 0 0 0 6px;
}
.status-div{
    width:      auto;
    margin:     0 auto;
    height:     16px;
    text-align: center;
    display:    inline-block;
}
.status-div > *{
    float: left;
}
/* 自訂義Scroll bar width */
::-webkit-scrollbar{
    width: 8px;
}
/* Track */
::-webkit-scrollbar-track{
    background:    white;
    border-radius: 2px;
}
/* Handle */
::-webkit-scrollbar-thumb{
    background:    lightgray;
    border-radius: 2px;
}
/* Handle on hover */
::-webkit-scrollbar-thumb:hover{
    background: gray;
}
@media only screen and (max-width: 768px){
    .vehicle-list{
        overflow: auto;
    }
}
thead tr{
    color: white;
}
.tr-purple{
    background-color: rgb(186, 190, 216);
}
.tr-lite-purple{
    background-color: rgb(220, 222, 234);
}
.table{
    margin-bottom: 10px;
}
.size-sm{
    font-size: 12pt;
}
.project-deploy-fail-desc{
    position:         absolute;
    left:             0;
    right:            0;
    bottom:           -26px;
    background-color: red;
    color:            white;
    font-weight:      bold;
    height:           26px;
    text-align:       center;
    opacity:          0.8;
}
</style>
