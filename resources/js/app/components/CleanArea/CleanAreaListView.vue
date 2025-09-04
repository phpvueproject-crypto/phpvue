<template>
    <div>
        <section class="content-header">
            <h1>清潔區域狀態列表</h1>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <table class="table table-bordered table-hover break-table size-sm">
                                    <thead class="table-head">
                                    <tr>
                                        <th class="text-center hide-td">清潔區域起點 (x, y)</th>
                                        <th class="text-center hide-td">清潔區域終點 (x, y)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="cleanArea in cleanAreas">
                                        <td data-title="清潔區域起點 (x, y)" class="text-center">
                                            {{ `(${cleanArea.start_goal_x}, ${cleanArea.start_goal_y})` }}
                                        </td>
                                        <td data-title="清潔區域終點 (x, y)" class="text-center">
                                            {{ `(${cleanArea.end_goal_x}, ${cleanArea.end_goal_y})` }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <clip-loader v-if="loading" class="loading" color="gray" size="30px"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import axios from 'axios';
import ClipLoader from 'vue-spinner/src/ClipLoader';
import _ from 'lodash';

export default {
    name: "CleanAreaListView",
    components: {ClipLoader},
    data() {
        return {
            loading: false,
            cleanAreas: []
        };
    },
    async created() {
        this.loading = true;
        await this.fetchData();
        this.loading = false;
        this.subScribe();
    },
    destroyed() {
        window.Echo.leave('cleanAreas');
    },
    methods: {
        async fetchData() {
            try {
                let res = await axios.get('/api/cleanAreas');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.cleanAreas = data.cleanAreas;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        subScribe() {
            const that = this;
            window.Echo.private('cleanAreas').listen('CleanAreaCreated', (res) => {
                const cleanArea = res.cleanArea;
                if(!cleanArea.enable) {
                    return;
                }
                that.cleanAreas.push(cleanArea);
            }).listen('CleanAreaUpdated', (res) => {
                const cleanArea = res.cleanArea;
                if(cleanArea.enable) {
                    const cleanAreaIdx = _.findIndex(that.cleanAreas, ['id', cleanArea.id]);
                    if(cleanAreaIdx != -1) {
                        that.cleanAreas.splice(cleanAreaIdx, 1, cleanArea);
                    } else {
                        that.cleanAreas.push(cleanArea);
                    }
                } else {
                    that.cleanAreas = _.filter(that.cleanAreas, (r) => {
                        return r.id != cleanArea.id;
                    });
                }
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.content-header{
    color: black;
}
.size-sm{
    font-size: 12pt;
}
.table{
    margin-bottom: 10px;
}
th{
    vertical-align: middle !important;
}
</style>
