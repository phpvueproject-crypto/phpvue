<template>
    <div>
        <vehicle-route-list-select v-if="routeId"
                                   v-model="dataStr"
                                   value-prop="region_id"
                                   :group="1"
                                   :filter-same-rout-name="false"/>
        <region-mgmt-list-select v-else
                                 v-model="dataStr"
                                 :project-id="projectId"
                                 :single-select2="true"/>
    </div>
</template>

<script>
import VehicleRouteListSelect from '../VehicleRoute/VehicleRouteListSelect.vue';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect.vue';

export default {
    name: "ConnectionRegionSelect",
    components: {VehicleRouteListSelect, RegionMgmtListSelect},
    props: {
        value: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        },
        routeId: {
            type: Number,
            default: null
        },
        projectId: {
            type: Number,
            default: null
        }
    },
    watch: {
        dataStr() {
            this.onChange();
        }
    },
    data() {
        return {
            dataStr: null
        };
    },
    created() {
        if(this.value) {
            this.dataStr = this.value;
        } else {
            this.dataStr = null;
            this.onChange();
        }
    },
    methods: {
        onChange() {
            this.$emit('input', this.dataStr);
        }
    }
}
</script>
