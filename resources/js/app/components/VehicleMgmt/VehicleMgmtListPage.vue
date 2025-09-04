<template>
    <div>
        <section class="content-header clearfix">
            <span v-if="$route.query.region_mgmt_id" style="color: black">車輛狀態列表</span>
            <span v-else>AMDR管理</span>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <vehicle-mgmt-list-view :table-font-size="tableFontSize"
                                            :show-status="showStatus"
                                            :show-edit-btn="showEditBtn"/>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import VehicleMgmtListView from './VehicleMgmtListView';

export default {
    name: "VehicleMgmtListPage",
    components: {VehicleMgmtListView},
    props: {
        tableFontSize: {
            type: Number,
            default: null
        },
        showStatus: {
            type: Boolean,
            default: false
        },
        showEditBtn: {
            type: Boolean,
            default: false
        }
    }
}
</script>