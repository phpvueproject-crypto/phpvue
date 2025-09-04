<template>
    <div>
        <section class="content-header clearfix">
            <span>手臂資訊</span>
            <div class="title-right-block"></div>
        </section>
        <section class="content">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div v-if="!loading">
                            <table v-if="!showReload" class="table table-bordered table-hover break-table">
                                <thead>
                                <tr class="table-head">
                                    <th class="hide-td">名稱</th>
                                    <th class="hide-td">狀態</th>
                                    <th class="hide-td">資料</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td data-title="" class="vertical-middle">手臂</td>
                                    <td data-title="State String">{{ hookStatus.available ? '可使用的' : '無法使用的' }}</td>
                                    <td data-title="data">角度：{{ hookStatus.angle }}度</td>
                                </tr>
                                <template v-for="(value, key) in hookStatus">
                                    <tr v-if="key !== 'angle' && key !== 'available'">
                                        <td data-title="" class="vertical-middle">
                                            <span v-if="key == 'height'">高度</span>
                                            <span v-else-if="key == 'brake'">剎車</span>
                                            <span v-else-if="key == 'gripper'">夾持器</span>
                                            <span v-else>{{ key }}</span>
                                        </td>
                                        <td data-title="State String">{{ value.state_string == 'Unknown' ? '-' : value.state_string }}</td>
                                        <td data-title="data">
                                            <template v-if="key == 'brake'">
                                                {{ value.braked ? '煞車' : '未煞車' }}
                                            </template>
                                            <template v-if="key == 'gripper'">
                                                {{ value.closed ? '關閉' : '開啟' }}
                                            </template>
                                            <template v-if="key == 'height'">
                                                {{ value.height }}
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                            <reload v-else @click="showReload = false; fetchData()"/>
                        </div>
                        <clip-loader v-else class="loading" color="gray" size="30px"/>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader';
import axios from 'axios';
import Reload from '../Common/Reload.vue';

export default {
    name: "HookStatusListPage",
    components: {Reload, ClipLoader},
    data() {
        return {
            loading: true,
            hookStatus: {},
            showReload: false
        }
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/hook-status');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.hookStatus = data.hookStatus;
                    this.device = data.device;
                } else if(res.status == -21) {
                    this.showReload = true;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style scoped lang="scss">
.title-block{
    margin-bottom: 30px;
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
table{
    margin-top: 20px;
}
</style>
