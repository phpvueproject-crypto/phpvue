<template>
    <div>
        <section class="content-header clearfix">
            <span>載具管理&emsp;</span>
            <button class="btn btn-light-green op-btn" @click="showModal(null)">
                <i class="fa fa-plus" aria-hidden="true"/>&nbsp;創建載具
            </button>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table">
                                    <thead class="table-head">
                                    <tr>
                                        <th class="hide-td th-max-width">裝置編號</th>
                                        <th class="hide-td th-max-width">設備類別</th>
                                        <th class="hide-td th-max-width">位置</th>
                                        <th class="hide-td">更新時間</th>
                                        <th class="text-center hide-td th-min-width">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td colspan="8" style="line-height: 5px">
                                            <span class="red font-bold" style="font-size:8pt">
                                                搜尋欄位輸入後請按Enter鍵
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td data-title="搜尋裝置編號">
                                            <input v-model="form.obj_id" class="form-control" placeholder="搜尋" @keypress.enter="changePage(1)">
                                        </td>
                                        <td data-title="搜尋設備類別">
                                            <object-class-select v-model="form.object_class" class="form-control" name="object_class" @change="changePage(1)"/>
                                        </td>
                                        <td class="hide-td"></td>
                                        <td class="hide-td"></td>
                                        <td class="hide-td"></td>
                                    </tr>
                                    <tr v-for="(objectMgmt,index) in objectMgmts" :class="[index % 2 == 1 ? 'tr-gray' : '']">
                                        <td data-title="裝置編號">{{ objectMgmt.obj_id }}</td>
                                        <td data-title="設備類別">{{ objectMgmt.object_class }}</td>
                                        <td data-title="車輛位置">
                                            <template v-if="objectMgmt.object_class == 'AMDR'">
                                                <template v-if="objectMgmt.vehicle_mgmt && objectMgmt.vehicle_mgmt.vehicle_status">
                                                    {{ objectMgmt.vehicle_mgmt.vehicle_status.vehicle_location }}
                                                    <span v-if="objectMgmt.vehicle_mgmt.vehicle_status.vertex" class="red">
                                                        <i v-if="!objectMgmt.vehicle_mgmt.vehicle_status.vertex.region_mgmt" class="fa fa-exclamation-circle"/>
                                                    </span>
                                                </template>
                                            </template>
                                            <template v-else-if="objectMgmt.object_class == 'DOOR'">
                                                <template v-if="objectMgmt.door_mgmt && objectMgmt.door_mgmt.edge_name">
                                                    {{ objectMgmt.door_mgmt.edge_name }}
                                                    <span v-if="objectMgmt.door_mgmt.edge" class="red">
                                                        <i v-if="!objectMgmt.door_mgmt.edge.region_mgmt" class="fa fa-exclamation-circle"/>
                                                    </span>
                                                </template>
                                            </template>
                                            <template v-else-if="objectMgmt.object_class == 'STATION'">
                                                <template v-if="objectMgmt.station_mgmt && objectMgmt.station_mgmt.vertex_name">
                                                    {{ objectMgmt.station_mgmt.vertex_name }}
                                                    <span v-if="objectMgmt.station_mgmt.vertex" class="red">
                                                        <i v-if="!objectMgmt.station_mgmt.vertex.region_mgmt" class="fa fa-exclamation-circle"/>
                                                    </span>
                                                </template>
                                            </template>
                                            <template v-else-if="objectMgmt.object_class == 'ELEVATOR'">
                                                <template v-if="objectMgmt.elevator_mgmt && (objectMgmt.elevator_mgmt.vertices && objectMgmt.elevator_mgmt.vertices.length > 0)">
                                                    <span v-for="(vertex, index) in objectMgmt.elevator_mgmt.vertices"><span v-if="index != 0">, </span>{{ vertex.name }}</span>
                                                    <span v-if="objectMgmt.elevator_mgmt.vertex" class="red">
                                                        <i v-if="!objectMgmt.elevator_mgmt.vertex.region_mgmt" class="fa fa-exclamation-circle"/>
                                                    </span>
                                                </template>
                                                <template v-else-if="objectMgmt.elevator_mgmt && objectMgmt.elevator_mgmt.vertex_name">
                                                    {{ objectMgmt.elevator_mgmt.vertex_name }}
                                                    <span v-if="objectMgmt.elevator_mgmt.vertex" class="red">
                                                        <i v-if="!objectMgmt.elevator_mgmt.vertex.region_mgmt" class="fa fa-exclamation-circle"/>
                                                    </span>
                                                </template>
                                            </template>
                                        </td>
                                        <td data-title="更新時間">{{ objectMgmt.update_ts | datetime }}</td>
                                        <td data-title="操作" class="text-center">
                                            <button class="btn btn-light-green btn-sm" @click="showModal(objectMgmt.obj_uid)" title="修改">
                                                <i class="glyphicon glyphicon-pencil"/>
                                                修改
                                            </button>
                                            <button class="btn btn-delete btn-sm" @click="deleteRow(objectMgmt)" title="刪除">
                                                <i class="glyphicon glyphicon-trash"/>
                                                刪除
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <pagination :direction-links="false"
                                                :boundary-links="true"
                                                size="sm"
                                                v-model="pagination.current_page"
                                                :total-page="pagination.last_page"
                                                :max-size="10"
                                                @change="changePage(pagination.current_page)"/>
                                </div>
                            </div>
                            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <object-mgmt-modal v-model="modal.show"
                           :obj-uid="modal.obj_uid"
                           @update="resetRow"
                           @refresh="fetchData"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import {mapGetters} from 'vuex';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import ObjectMgmtModal from './ObjectMgmtModal';
import ObjectClassSelect from '../ObjectClass/ObjectClassSelect.vue';

export default {
    name: "ObjectMgmtListPage",
    components: {ClipLoader, ObjectMgmtModal, ObjectClassSelect},
    computed: {
        ...mapGetters({
            roles: 'user/roles'
        })
    },
    data() {
        return {
            loading: false,
            objectMgmts: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            modal: {
                obj_uid: null,
                show: false
            },
            form: {
                obj_id: this.$route.query.obj_id ? this.$route.query.obj_id : null,
                object_class: this.$route.query.object_class ? this.$route.query.object_class : null
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
                let res = await axios.get('/api/objectMgmts', {
                    params: {
                        page: this.pagination.current_page,
                        obj_id: this.form.obj_id,
                        object_class: this.form.object_class
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.objectMgmts = _.map(data.objectMgmts, (objectMgmt) => {
                        objectMgmt.selected = 0;
                        return objectMgmt;
                    });
                    this.pagination = data.pagination;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        showModal(obj_uid) {
            this.modal.obj_uid = obj_uid;
            this.modal.show = true;
        },
        resetRow(row) {
            const idx = _.findIndex(this.objectMgmts, (r) => {
                return r.obj_uid == row.obj_uid;
            });
            if(idx == -1) {
                this.objectMgmts.splice(0, 0, row);
            } else {
                this.objectMgmts[idx] = row;
            }
        },
        async deleteRow(objectMgmt) {
            if(!confirm(`確定要刪除${objectMgmt.obj_id}?`))
                return;
            try {
                let res = await axios.delete(`/api/objectMgmts/${objectMgmt.obj_uid}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.objectMgmts = _.filter(this.objectMgmts, (r) => {
                        return r.obj_uid != objectMgmt.obj_uid;
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        changePage(page) {
            const form = _.cloneDeep(this.form);
            form.page = page;
            const query = this.getPureForm(form);
            this.$router.push({
                path: `/objectMgmts`,
                query: query
            });
        }
    }
}
</script>

<style scoped>
.th-max-width{
    max-width: 130px;
}
.th-min-width{
    min-width: 150px;
}
</style>
