<template>
    <div>
        <section class="content-header">
            <span>專案管理&emsp;</span>
            <button class="btn btn-light-green op-btn" @click="showModal(null)">
                <i class="fa fa-plus" aria-hidden="true"/>&nbsp;創建專案
            </button>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table">
                                    <thead>
                                    <tr class="table-head">
                                        <th class="hide-td">專案名稱</th>
                                        <th class="hide-td text-center">操作</th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="line-height: 5px">
                                            <span class="red font-bold" style="font-size:8pt">
                                                搜尋欄位輸入後請按Enter鍵
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td data-title="搜尋專案名稱" class="hide-td">
                                            <input v-model="form.name" class="form-control" placeholder="搜尋" @keypress.enter="changePage(1)">
                                        </td>
                                        <td data-title="操作" class="hide-td text-center"></td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="project in projects">
                                        <td data-title="專案名稱">{{ project.name }}</td>
                                        <td data-title="操作" class="text-center">
                                            <button class="btn btn-light-green btn-sm" @click="showModal(project.id)" title="修改">
                                                <i class="glyphicon glyphicon-pencil"/>
                                                修改
                                            </button>
                                            <button class="btn btn-delete btn-sm" @click="deleteRow(project)" title="刪除">
                                                <i class="glyphicon glyphicon-trash"/>
                                                刪除
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <pagination size="sm"
                                                v-model="pagination.current_page"
                                                :direction-links="false"
                                                :boundary-links="true"
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
        <project-modal v-model="modal.show"
                       :pid="modal.id"
                       @update="resetRow"/>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import ProjectModal from './ProjectModal.vue';

export default {
    name: "ProjectListPage",
    components: {ClipLoader, ProjectModal},
    data() {
        return {
            loading: false,
            projects: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            modal: {
                id: null,
                show: false
            },
            form: {
                page: this.$route.query.page ? this.$route.query.page : 1,
                name: this.$route.query.name ? this.$route.query.name : null
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
                let res = await axios.get('/api/projects', {
                    params: this.form
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.projects = data.projects;
                    if(data.pagination) {
                        this.pagination = data.pagination;
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        showModal(id) {
            this.modal.id = id;
            this.modal.show = true;
        },
        resetRow(row) {
            const idx = _.findIndex(this.projects, (r) => {
                return r.id == row.id;
            });
            if(idx == -1) {
                this.projects.splice(0, 0, row);
            } else {
                this.projects[idx] = row;
            }
        },
        async deleteRow(project) {
            if(!confirm(`確定要刪除${project.name}?`)) {
                return;
            }
            try {
                let res = await axios.delete(`/api/projects/${project.id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.projects = _.filter(this.projects, (r) => {
                        return r.id != project.id;
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
                path: `/projects`,
                query: query
            });
        }
    }
}
</script>
