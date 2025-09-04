<template>
    <div class="form-group">
        <label class="col-md-3 control-label">自訂屬性&nbsp;&nbsp;</label>
        <div class="col-md-9">
            <table class="table table-bordered table-hover">
                <thead class="table-head">
                <tr>
                    <th class="text-center" style="width: 60px">#</th>
                    <th class="text-center" style="width: 160px">名稱</th>
                    <th class="text-center">內容</th>
                    <th class="text-center" style="width: 60px">刪除</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(row, index) in edgeConfigurations">
                    <td class="text-center" style="width: 60px">{{ index + 1 }}</td>
                    <td class="text-center" style="width: 160px">
                        <edge-configuration-type-select v-model="row.type"
                                                        v-validate="'required'"
                                                        :name="`type-${index}`"
                                                        :tags="true"
                                                        @change="row.data = null"
                                                        :edge-configuration-types.sync="edgeConfigurationTypes"
                                                        :filter-edge-configuration-types="filterEdgeConfigurationTypes"
                                                        :edge-configuration-type.sync="row.edge_configuration_type"/>
                        <span v-if="errors.has(`type-${index}:required`)" class="help is-danger">請選擇屬性</span>
                    </td>
                    <td>
                        <template v-if="row.edge_configuration_type">
                            <rail-switch-view v-if="row.edge_configuration_type.view_type == 'rail-switch-view'" v-model="row.data" class="switch-btn" :index="index"/>
                            <input v-else-if="row.edge_configuration_type.view_type == 'input-text'" v-model="row.data" class="form-control input-sm" :name="`custom-${index}`" v-validate="validate(row)">
                            <connection-region-select v-else-if="row.edge_configuration_type.view_type == 'connection-region-select'"
                                                      v-model="row.data"
                                                      :name="`custom-${index}`"
                                                      :project-id="projectId"
                                                      v-validate="validate(row)"/>
                            <object-mgmt-list-select v-else-if="row.edge_configuration_type.view_type == 'object-mgmt-list-select'"
                                                     v-model="row.data"
                                                     :name="`custom-${index}`"
                                                     v-validate="validate(row)"
                                                     value-prop="obj_id"
                                                     placeholder="請選擇自動門"
                                                     object-class="DOOR"/>
                        </template>
                        <input v-else v-model="row.data" class="form-control input-sm" :name="`custom-${index}`" v-validate="validate(row)">
                        <span v-show="errors.has(`custom-${index}:required`)" class="help is-danger">
                            <template v-if="row.type == 'connection_region'">請選擇要連接的區域</template>
                            <template v-else-if="row.type == 'auto_door'">請填寫自動門</template>
                            <template v-else>請填寫內容</template>
                        </span>
                        <span v-show="errors.has(`custom-${index}:regex`)" class="help is-danger">請輸入整數</span>
                    </td>
                    <td class="text-center" style="width: 60px">
                        <i class="glyphicon glyphicon-trash cursor-pointer" @click="deleteEdgeConfiguration(index)" title="刪除"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-center cursor-pointer" @click="addEdgeConfiguration">
                        <i class="glyphicon glyphicon-plus-sign cursor-pointer" title="新增"/>
                    </td>
                </tr>
                <tr v-if="vertexTypeId == 2">
                    <td colspan="4">
                        <input type="hidden" name="connection_region" v-validate="'required'" v-model="connectionRegion">
                        <span v-show="errors.has(`connection_region:required`)" class="help is-danger">請至少創建一個欲連接區域</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import EdgeConfigurationTypeSelect from '../EdgeConfigurationType/EdgeConfigurationTypeSelect.vue';
import RailSwitchView from '../EdgeConfigurationType/RailSwitchView.vue';
import ConnectionRegionSelect from '../EdgeConfigurationType/ConnectionRegionSelect.vue';
import ObjectMgmtListSelect from '../ObjectMgmt/ObjectMgmtListSelect.vue';
import _ from 'lodash';

export default {
    name: "EdgeConfigurationListView",
    inject: ['$validator'],
    components: {
        EdgeConfigurationTypeSelect,
        RailSwitchView,
        ConnectionRegionSelect,
        ObjectMgmtListSelect
    },
    props: {
        value: {
            type: Array,
            default() {
                return [];
            }
        },
        vertexTypeId: {
            type: Number
        },
        projectId: {
            type: Number,
            default: null
        }
    },
    watch: {
        edgeConfigurationTypes(newVal) {
            const edgeConfigurationTypes = newVal;
            let edgeConfigurations = _.cloneDeep(this.edgeConfigurations);
            this.edgeConfigurations = _.map(edgeConfigurations, (r) => {
                const edgeConfigurationType = _.find(edgeConfigurationTypes, (r2) => {
                    return r2.name == r.type;
                });
                r.edge_configuration_type = edgeConfigurationType ? edgeConfigurationType : null;
                return r;
            });
        },
        value(newVal) {
            const edgeConfigurations = _.map(this.edgeConfigurations, (r) => {
                r = _.cloneDeep(r);
                delete r.edge_configuration_type;
                return r;
            });
            if(JSON.stringify(edgeConfigurations) != JSON.stringify(newVal)) {
                const that = this;
                this.edgeConfigurations = _.map(newVal, (r) => {
                    const edgeConfigurationType = _.find(that.edgeConfigurationTypes, (r2) => {
                        return r2.name == r.type;
                    });
                    r.edge_configuration_type = edgeConfigurationType ? edgeConfigurationType : null;
                    return r;
                });
            }
        },
        edgeConfigurations: {
            handler(newVal) {
                const edgeConfigurations = _.map(newVal, (r) => {
                    r = _.cloneDeep(r);
                    delete r.edge_configuration_type;
                    return r;
                });

                if(JSON.stringify(edgeConfigurations) != JSON.stringify(this.value)) {
                    this.$emit('input', edgeConfigurations);
                }
            },
            deep: true
        }
    },
    computed: {
        filterEdgeConfigurationTypes() {
            const types = _.map(this.edgeConfigurations, (r) => {
                return r.type;
            });
            return _.filter(this.edgeConfigurationTypes, (r) => {
                return r.is_unique == 0 || (r.is_unique == 1 && !types.contains(r.name));
            });
        },
        connectionRegion() {
            const edgeConfiguration = _.find(this.edgeConfigurations, (r) => {
                return r.type == 'connection_region';
            });
            return edgeConfiguration ? edgeConfiguration.data : null;
        }
    },
    data() {
        return {
            edgeConfigurations: [],
            edgeConfigurationTypes: []
        };
    },
    created() {
        const that = this;
        this.edgeConfigurations = _.map(this.value, (r) => {
            const edgeConfigurationType = _.find(that.edgeConfigurationTypes, (r2) => {
                return r2.name == r.type;
            });
            r.edge_configuration_type = edgeConfigurationType ? edgeConfigurationType : null;
            return r;
        });
    },
    methods: {
        addEdgeConfiguration() {
            this.edgeConfigurations.push({
                id: this.generateUUID(),
                type: null,
                data: null,
                edge_configuration_type: null
            });
        },
        deleteEdgeConfiguration(index) {
            if(!confirm(`確定要刪除第「${index + 1}」筆？`)) {
                return;
            }
            this.edgeConfigurations.splice(index, 1);
        },
        validate(row, customRules = {}) {
            let rules = {};
            if(row && row.edge_configuration_type && row.edge_configuration_type.validate) {
                rules = JSON.parse(row.edge_configuration_type.validate);
            }

            rules['required'] = true;
            for(const [key, value] of Object.entries(customRules)) {
                rules[key] = value;
            }
            return rules;
        }
    }
}
</script>

<style scoped>
.switch-btn{
    margin-top: 5px;
}
.glyphicon-plus-sign{
    font-size: 31px;
}
</style>
