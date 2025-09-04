<template>
    <div class="remote-management-system-status-table">
        <table class="table table-bordered table-striped table-hover break-table">
            <thead>
            <tr>
                <th class="hide-td text-center">時間</th>
                <th class="hide-td text-center">動作</th>
                <th class="hide-td text-center">結果</th>
                <th class="hide-td text-center" style="min-width: 152px">房間</th>
                <th class="hide-td text-center" style="min-width: 152px">地圖</th>
                <th class="hide-td text-center">座標 (x, y)</th>
                <th class="hide-td text-center">條碼</th>
                <th class="hide-td text-center">總計時數</th>
                <th class="hide-td text-center">AMR到達點(A)</th>
                <th class="hide-td text-center">培養皿掃碼(B)</th>
                <th class="hide-td text-center">置入空氣採樣口(C)</th>
                <th class="hide-td text-center">空氣採樣開始(D)</th>
                <th class="hide-td text-center">培養皿歸位(E)</th>
                <th class="hide-td text-center">AMR歸位(F)</th>
            </tr>
            </thead>
            <tbody>

            <tr v-if="missionQueue.start_location">
                <td data-title="時間" class="centered nowrap">
                    <span v-if="missionQueue.started">{{ missionQueue.started | date }}<br>{{ missionQueue.started | time }}</span>
                </td>
                <td data-title="動作" class="centered">任務開始</td>
                <td data-title="結果" class="centered">
                    <template v-if="missionQueue.started"><span class="text-success">成功</span></template>
                    <template v-else><span class="text-danger">失敗</span></template>
                </td>
                <td data-title="房間" class="centered">{{ missionQueue.start_location.room }}</td>
                <td data-title="地圖" class="centered">{{ missionQueue.start_location.map ? missionQueue.start_location.map.name : null }}</td>
                <td data-title="座標 (x, y)" class="centered nowrap">{{ `(${missionQueue.start_location.x ? missionQueue.start_location.x : '?'}, ${missionQueue.start_location.y ? missionQueue.start_location.y : '?'})` }}</td>
                <td data-title="條碼" class="centered nowrap">-</td>
                <td data-title="總計時數" class="centered nowrap">-</td>
                <td data-title="AMR到達點(A)" class="centered">-</td>
                <td data-title="培養皿掃碼(B)" class="centered">-</td>
                <td data-title="置入空氣採樣口(C)" class="centered">-</td>
                <td data-title="空氣採樣開始(D)" class="centered">-</td>
                <td data-title="培養皿歸位(E)" class="centered">-</td>
                <td data-title="AMR歸位(F)" class="centered">-</td>
            </tr>
            <template v-if="remoteManagementSystemStatuses.length > 0">
                <tr v-for="row in remoteManagementSystemStatuses">
                    <td data-title="時間" class="centered nowrap">
                        {{ row.start_time | date }}<br>{{ row.start_time | time }}
                    </td>
                    <td data-title="動作" class="centered">{{ row.location ? row.location.device_name : null }}</td>
                    <td data-title="結果" class="centered">
                        <template v-if="row.result == -1"><span class="text-danger">失敗</span></template>
                        <template v-else-if="row.result == 0"><span>進行中</span></template>
                        <template v-else-if="row.result == 1"><span class="text-success">成功</span></template>
                    </td>
                    <td data-title="房間" class="centered">{{ row.location ? row.location.room : null }}</td>
                    <td data-title="地圖" class="centered">{{ (row.location && row.location.map) ? row.location.map.name : null }}</td>
                    <td data-title="座標 (x, y)" class="centered nowrap">{{ row.location ? `(${row.location.x}, ${row.location.y})` : null }}</td>
                    <td data-title="條碼" class="centered nowrap">{{ row.location ? row.location.bar_code : null }}</td>
                    <td data-title="總計時數" class="centered nowrap">-</td>
                    <td data-title="AMR到達點(A)" class="centered">{{ row.status_A_time ? `${row.status_A_time}S` : null }}</td>
                    <td data-title="培養皿掃碼(B)" class="centered">{{ row.status_B_time ? `${row.status_B_time}S` : null }}</td>
                    <td data-title="置入空氣採樣口(C)" class="centered">{{ row.status_C_time ? `${row.status_C_time}S` : null }}</td>
                    <td data-title="空氣採樣開始(D)" class="centered">{{ row.status_D_time ? `${row.status_D_time}S` : null }}</td>
                    <td data-title="培養皿歸位(E)" class="centered">{{ row.status_E_time ? `${row.status_E_time}S` : null }}</td>
                    <td data-title="AMR歸位(F)" class="centered">{{ row.status_F_time ? `${row.status_F_time}S` : null }}</td>
                </tr>
            </template>
            <tr v-else>
                <td class="centered" colspan="14">暫無資料</td>
            </tr>
            <tr v-if="missionQueue.end_location">
                <td data-title="時間" class="centered nowrap">
                    <span v-if="missionQueue.finished">{{ missionQueue.finished | date }}<br>{{ missionQueue.finished | time }}</span>
                </td>
                <td data-title="動作" class="centered">任務停止</td>
                <td data-title="結果" class="centered">
                    <span class="text-success">成功</span>
                </td>
                <td data-title="房間" class="centered">{{ missionQueue.end_location.room }}</td>
                <td data-title="地圖" class="centered">{{ missionQueue.end_location.map ? missionQueue.end_location.map.name : null }}</td>
                <td data-title="座標 (x, y)" class="centered nowrap">{{ `(${missionQueue.end_location.x ? missionQueue.end_location.x : '?'}, ${missionQueue.end_location.y ? missionQueue.end_location.y : '?'})` }}</td>
                <td data-title="條碼" class="centered nowrap">-</td>
                <td data-title="總計時數" class="centered nowrap">{{ totalTimeSum }}S</td>
                <td data-title="AMR到達點(A)" class="centered">-</td>
                <td data-title="培養皿掃碼(B)" class="centered">-</td>
                <td data-title="置入空氣採樣口(C)" class="centered">-</td>
                <td data-title="空氣採樣開始(D)" class="centered">-</td>
                <td data-title="培養皿歸位(E)" class="centered">-</td>
                <td data-title="AMR歸位(F)" class="centered">-</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: "RemoteManagementSystemStatusListView",
    props: {
        remoteManagementSystemStatuses: Array,
        missionQueue: Object
    },
    computed: {
        totalTimeSum() {
            // 將所有有 total_time 的欄位加總
            return this.remoteManagementSystemStatuses.reduce((sum, row) => {
                const val = parseFloat(row.total_time)
                return sum + (isNaN(val) ? 0 : val)
            }, 0)
        }
    }
}
</script>

<style scoped lang="scss">
.remote-management-system-status-table{
    max-height: calc(100vh - 150px);
    overflow:   auto;
}
.table > thead > tr > th{
    background-color: #5c9b6c;
    color:            white;
    white-space:      nowrap;
}
.nowrap{
    white-space: nowrap;
}
</style>
