<template>
    <div>
        <section class="content-header clearfix list-page-title">
            <span>事件列表</span>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <ul class="nav nav-tabs">
                        <li :class="{'active' : eventType == 'system'}">
                            <a href="javascript:void(0)" @click="changePage(1, 'system', false)">系統事件</a>
                        </li>
                        <li :class="{'active' : eventType == 'vehicle'}">
                            <a href="javascript:void(0)" @click="changePage(1, 'vehicle', false)">車輛事件</a>
                        </li>
                    </ul>
                    <div class="box">
                        <div class="box-body">
                            <div v-if="!loading">
                                <div class="scroll">
                                    <table class="table table-bordered table-hover break-table">
                                        <thead class="table-head">
                                        <tr>
                                            <th class="hide-td">接收時間</th>
                                            <th class="hide-td text-center">系統類型</th>
                                            <th class="hide-td text-center">系統編號</th>
                                            <th class="hide-td">事件編號</th>
                                            <th class="hide-td">事件名稱</th>
                                            <th class="hide-td text-center" style="min-width: 140px">事件等級</th>
                                            <!--only vehicle_event-->
                                            <template v-if="eventType == 'vehicle'">
                                                <th class="hide-td">車輛狀態</th>
                                                <th class="hide-td">車輛位置</th>
                                                <th class="hide-td">車輛座標</th>
                                                <th class="hide-td">車輛任務編號</th>
                                            </template>
                                            <th class="hide-td">備註</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td colspan="11" style="line-height: 5px">
                                                <span class="red font-bold" style="font-size:8pt">搜尋欄位輸入後請按Enter鍵</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="hide-td"></td>
                                            <td class="hide-td"></td>
                                            <td data-title="系統編號">
                                                <input class="form-control" placeholder="請輸入" v-model="form.system_id" @change="changePage(1, eventType, true)"/>
                                            </td>
                                            <td data-title="事件編號">
                                                <input class="form-control" placeholder="請輸入" v-model="form.event_code" @change="changePage(1, eventType, true)"/>
                                            </td>
                                            <td data-title="事件名稱" style="max-width: 100px; min-width: 100%">
                                                <input class="form-control" placeholder="請輸入" v-model="form.event_name" @change="changePage(1, eventType, true)"/>
                                            </td>
                                            <td data-title="事件等級" style="max-width: 100px; min-width: 100%">
                                                <select class="form-control" v-model="form.event_level" @change="changePage(1, eventType, true)">
                                                    <option :value="null">請選擇</option>
                                                    <option value="ERROR">ERROR</option>
                                                    <option value="WARNING">WARNING</option>
                                                    <option value="INFO">INFO</option>
                                                </select>
                                            </td>
                                            <template v-if="eventType == 'vehicle'">
                                                <td class="hide-td"></td>
                                                <td class="hide-td"></td>
                                                <td class="hide-td"></td>
                                                <td class="hide-td"></td>
                                            </template>
                                            <td class="hide-td"></td>
                                        </tr>
                                        <tr v-for="(eventLog, index) in events" :class="[index%2 == 1 ? 'tr-gray' : '']">
                                            <td data-title="接收時間">{{ eventLog.receive_time | datetime }}</td>
                                            <td data-title="系統類型" class="text-center">{{ eventLog.system_type }}</td>
                                            <td data-title="系統編號" class="text-center">{{ eventLog.system_id }}</td>
                                            <td data-title="事件編號">{{ eventLog.event_code }}</td>
                                            <td data-title="事件名稱">{{ eventLog.event_name }}</td>
                                            <td data-title="事件等級" class="text-center">{{ eventLog.event_level }}</td>
                                            <template v-if="eventType == 'vehicle'">
                                                <td data-title="車輛狀態">{{ eventLog.vehicle_status }}</td>
                                                <td data-title="車輛位置">{{ eventLog.vehicle_location }}</td>
                                                <td data-title="車輛座標">{{ eventLog.vehicle_coordinate }}</td>
                                                <td data-title="車輛任務編號">{{ eventLog.vehicle_mission_uuid }}</td>
                                            </template>
                                            <td data-title="備註">{{ eventLog.comment }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <pagination :direction-links="false"
                                                :boundary-links="true"
                                                size="sm"
                                                v-model="pagination.current_page"
                                                :total-page="pagination.last_page"
                                                :max-size="10"
                                                @change="changePage(pagination.current_page, eventType, true)"/>
                                </div>
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
import _ from 'lodash';
import ClipLoader from 'vue-spinner/src/ClipLoader';

export default {
    name: "EventListPage",
    components: {ClipLoader},
    data() {
        return {
            loading: false,
            events: [],
            pagination: {
                last_page: 1,
                current_page: this.$route.query.page ? parseInt(this.$route.query.page) : 1
            },
            form: {
                event_level: this.$route.query.event_level ? this.$route.query.event_level : null,
                event_name: this.$route.query.event_name ? this.$route.query.event_name : null,
                system_id: this.$route.query.system_id ? this.$route.query.system_id : null,
                event_code: this.$route.query.event_code ? this.$route.query.event_code : null
            },
            eventType: this.$route.query.event_type ? this.$route.query.event_type : 'system'
        };
    },
    async created() {
        await this.fetchData();
        if(this.pagination.current_page > this.pagination.last_page) {
            this.changePage(1, this.eventType, true);
        }
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/${this.eventType}Events`, {
                    params: {
                        page: this.pagination.current_page,
                        event_name: this.form.event_name,
                        event_level: this.form.event_level,
                        system_id: this.form.system_id,
                        event_code: this.form.event_code
                    }
                });
                res = res.data;
                this.events = res[`${this.eventType}Events`].data;
                this.pagination.current_page = res[`${this.eventType}Events`].current_page;
                this.pagination.last_page = res[`${this.eventType}Events`].last_page;
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        changePage(page, eventType, isKeepSearch) {
            const query = _.pickBy({
                event_type: eventType,
                event_name: isKeepSearch ? this.form.event_name : null,
                event_level: isKeepSearch ? this.form.event_level : null,
                system_id: isKeepSearch ? this.form.system_id : null,
                event_code: isKeepSearch ? this.form.event_code : null,
                page: page
            }, _.identity());
            this.$router.push({
                path: `/events`,
                query: query
            });
        }
    }
}
</script>
<style lang="scss" scoped>
thead tr{
    color: white;
}
td, th{
    white-space: nowrap;
}
th{
    vertical-align: middle !important;
}
.nav-tabs{
    background-color: white;
    li.active a{
        color: #5c9b6c;
    }
    a{
        color: #000000;
    }
}
.box{
    border-top: none;
}
.scroll{
    overflow-x: auto;
}
</style>
