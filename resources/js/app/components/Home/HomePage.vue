<template>
    <div v-if="show" class="container-fluid">
        <!--        <div class="row">-->
        <!--            <button class="btn btn-warning col-xs-4" v-model="$store.state.lang" :value="optionsLang[0].value" @click="setLang(optionsLang[0].value)">{{ $t("langZh") }}</button>-->
        <!--            <button class="btn btn-success col-xs-4 col-xs-offset-4" v-model="$store.state.lang" :value="optionsLang[1].value" @click="setLang(optionsLang[1].value)">{{ $t("langEn") }}</button>-->
        <!--        </div>-->
        <!--        <div class="form-group">-->
        <!--            <label class="col-md-3 control-label">單選select{{ vehicleId }}</label>-->
        <!--            <div class="col-md-9">-->
        <!--                <vehicle-mgmt-list-select v-model="vehicleId" :multiple="false" name="vehicleId"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="form-group">-->
        <!--            <label class="col-md-3 control-label">單選select{{ equipmentClass }}</label>-->
        <!--            <div class="col-md-9">-->
        <!--                <equipment-class-select v-model="equipmentClass" name="equipmentClass"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="form-group">-->
        <!--            <label class="col-md-3 control-label">供應商單選select2{{ vendorMgmt }}</label>-->
        <!--            <div class="col-md-9">-->
        <!--                <vendor-mgmt-select v-model="vendorMgmt" name="vendor"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="form-group">-->
        <!--            <label class="col-md-3 control-label">自訂屬性單選select2{{ vertex }}</label>-->
        <!--            <div class="col-md-9">-->
        <!--                <vertex-configuration-type-select v-model="vertex" name="vertex"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <div class="form-group">
            <label class="col-md-3 control-label">單選region：{{ region }}</label>
            <div class="col-md-9">
                <region-mgmt-list-select v-model="region" :single-select2="true" :min-num-for-search="5"/>
            </div>
        </div>
        <div v-show="region" class="form-group">
            <label class="col-md-3 control-label">單選vertex：{{ vertexId }}</label>
            <div class="col-md-9">
                <vertex-list-select v-model="vertexId" :region="region"/>
            </div>
        </div>
        <div v-show="region" class="form-group">
            <label class="col-md-3 control-label">多選vehicle：{{ vehicleId }}</label>
            <div class="col-md-9">
                <vehicle-mgmt-list-select v-model="vehicleId" :multiple="true"/>
            </div>
        </div>
    </div>
</template>

<script>
import VueI18n from 'vue-i18n';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect';
import EquipmentClassSelect from '../EquipmentClass/EquipmentClassSelect';
import VendorMgmtSelect from '../VendorMgmt/VendorMgmtSelect';
import VertexConfigurationTypeSelect from '../VertexConfigurationType/VertexConfigurationTypeSelect';
import VertexListSelect from '../Vertex/VertexListSelect';
import RegionMgmtListSelect from '../RegionMgmt/RegionMgmtListSelect';

export default {
    name: "HomePage",
    components: {
        VueI18n,
        VehicleMgmtListSelect,
        EquipmentClassSelect,
        VendorMgmtSelect,
        VertexConfigurationTypeSelect,
        VertexListSelect,
        RegionMgmtListSelect
    },
    data() {
        return {
            optionsLang: [
                {text: '中文', value: 'zh'},
                {text: 'English', value: 'en'}
            ],
            vehicleId: [{'vehicle_id': 'MR001'}, {'vehicle_id': 'MR002'}, {'vehicle_id': 'MR003'}],
            equipmentClass: null,
            vendorMgmt: null,
            vertex: null,
            show: false,
            vertexId: '174',
            region: 'ITRIQ01'
        };
    },
    methods: {
        setLang(value) {
            this.$store.commit('UPDATE_LANG', value);
            this.$i18n.locale = value;
            localStorage.setItem('local-lang', value);
        }
    }
}
</script>
