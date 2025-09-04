<template>
    <div>
        <section class="content-header clearfix">
            <span>地圖</span>
            <div class="pull-right">
                <button class="btn btn-default" @click="fetchData(1)">
                    <i class="fa fa-refresh"/>
                    同步更新
                </button>
            </div>
        </section>
        <section class="content">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div v-if="!loading">
                            <table class="table table-bordered table-hover break-table">
                                <thead>
                                <tr class="table-head">
                                    <th class="hide-td">編號</th>
                                    <th class="hide-td">地圖名稱</th>
                                    <th class="hide-td centered">房間號</th>
                                    <th class="hide-td">地圖詳細資訊</th>
                                    <th class="hide-td centered">點位</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="map in maps">
                                    <td data-title="編號">{{ map.guid }}</td>
                                    <td data-title="地圖名稱">
                                        <span>{{ map.name }}</span>
                                        <template v-if="map.is_active">
                                            &nbsp;<span class="label label-success">使用中</span>
                                        </template>
                                    </td>
                                    <td data-title="房間號" class="centered">{{ map.region }}</td>
                                    <td data-title="地圖詳細資訊">
                                        <p>地圖原點&emsp;：({{ map.origin_x }}, {{ map.origin_y }})</p>
                                        <p>地圖解析度：{{ map.resolution }}</p>
                                    </td>
                                    <td data-title="點位" class="centered">
                                        <button type="button" class="btn btn-link" @click="showModal(map.guid)">{{ map.locations_count }}</button>
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
                                            @change="changePage"/>
                            </div>
                        </div>
                        <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                    </div>
                </div>
            </div>
        </section>
        <map-modal v-model="modal.show"
                   :data-id="modal.id"/>
    </div>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';
import MapModal from './MapModal.vue';
import ProjectModal from '../Project/ProjectModal.vue';

export default {
    name: "MapListPage",
    components: {ProjectModal, MapModal, ClipLoader},
    data() {
        return {
            loading: true,
            maps: [],
            pagination: {
                current_page: 1,
                last_page: 1
            },
            form: {
                pagination: 1,
                page: this.$route.query.page ? parseInt(this.$route.query.page, 10) : 1
            },
            modal: {
                id: null,
                show: false
            }
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData(refresh = 0) {
            this.loading = true;
            try {
                const res = await axios.get('/api/maps', {
                    params: {
                        ...this.form,
                        refresh: refresh
                    }
                });
                if(res.data.status === 0) {
                    this.maps = res.data.data.maps;
                    if(res.data.data.pagination) {
                        this.pagination = res.data.data.pagination;
                    }
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        },
        changePage(page) {
            this.form.page = page;
            this.$router.push({path: '/maps', query: {...this.form}});
        },
        showModal(id) {
            this.modal.id = id;
            this.modal.show = true;
        }
    }
};
</script>

<style scoped lang="scss">
.title-block{
    margin-bottom: 30px;
    &:after{ //解決子元件使用float導致高度崩塌問題。
        content: "";
        display: table;
        clear:   both;
    }
}
.title-left-block{
    display: inline-block;
    h3{
        font-size: 32px;
    }
}
.title-right-block{
    margin-top: 20px;
    float:      right;
}
.box-body{
    padding: 30px;
}
.table-head th{
    white-space: nowrap;
}
tbody tr:nth-child(even){
    background-color: #F5F5F5;
}
.table > tbody > tr > td{
    vertical-align: middle;
}
table{
    margin-top: 20px;
}
.label{
    padding:        0.3rem 0.6rem;
    vertical-align: text-top;
    line-height:    16px;
}
</style>
