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
                <tr v-for="(row, index) in vertexConfigurations">
                    <td class="text-center" style="width: 60px">{{ index + 1 }}</td>
                    <td class="text-center" style="width: 160px">
                        <vertex-configuration-type-select :name="`type-${index}`"
                                                          v-model.lazy="row.type"
                                                          v-validate="{required: true}"
                                                          :tags="true"
                                                          @change="row.data = null"
                                                          :vertex-configuration-types="vertexConfigurationTypes"
                                                          :filter-vertex-configuration-types="filterVertexConfigurationTypes"
                                                          :disabled="vertexConfigurationTypeDisabled(row.type)"
                                                          :vertex-configurations="vertexConfigurations"
                                                          :vertex-configuration-type.sync="row.vertex_configuration_type"/>
                        <span v-if="errors.has(`type-${index}:required`)" class="help is-danger">請選擇名稱</span>
                    </td>
                    <td>
                        <template v-if="row.vertex_configuration_type">
                            <switch-button v-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'switch-button'" v-model="row.data" class="switch-btn" :name="`custom-${index}`" v-validate="validate(row)"/>
                            <input v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'input-text'" v-model="row.data" class="form-control input-sm" :name="`custom-${index}`" v-validate="validate(row, row.vertex_configuration_type.vertex_configuration_column.name == 'vertex_name' ? {
                                vertex_duplicate: vertex
                            } : {})">
                            <remapping-view v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'remapping-view'" v-model="row.data" :index="index" v-validate="validate(row)"/>
                            <intersection-view v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'intersection-view'" v-model="row.data" :index="index" v-validate="validate(row)"/>
                            <group-name-view v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'group-name-view'" v-model="row.data" :index="index" v-validate="validate(row)"/>
                            <object-mgmt-list-select v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'object-mgmt-list-select'" v-model="row.data" :name="`custom-${index}`" object-class="STATION" value-prop="obj_id" v-validate="validate(row)"/>
                            <alignment-view v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'alignment-view'" v-model="row.data" :name="`custom-${index}`" :index="index" v-validate="validate(row)"/>
                            <speed-view v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'speed-view'" v-model="row.data" :name="`custom-${index}`" :index="index" v-validate="validate(row)"/>
                            <rotation-condition v-else-if="row.vertex_configuration_type.vertex_configuration_column.view_type == 'rotation-condition-view'" v-model="row.data" :name="`custom-${index}`" :index="index"/>
                        </template>
                        <input v-else v-model="row.data" class="form-control input-sm" :name="`custom-${index}`" v-validate="validate(row)">
                        <span v-show="errors.has(`custom-${index}:required`)" class="help is-danger">請填寫內容</span>
                        <span v-show="errors.has(`custom-${index}:numeric`)" class="help is-danger">請填寫整數</span>
                        <span v-show="errors.has(`custom-${index}:min_value`)" class="help is-danger">請填寫大於0的數值</span>
                        <span v-show="errors.has(`custom-${index}:max_value`)" class="help is-danger">請填寫小於等於360的數值</span>
                        <span v-show="errors.has(`custom-${index}:max`)" class="help is-danger">請填寫15字以內的內容</span>
                        <span v-show="errors.has(`custom-${index}:vertex_duplicate`)" class="help is-danger">已存在相同站點名稱的站點</span>
                        <span v-show="errors.has(`custom-${index}:unique_type`)" class="help is-danger">已存在相同站點名稱的站點</span>
                    </td>
                    <td class="text-center" style="width: 60px">
                        <i v-show="!row.vertex_configuration_type || row.vertex_configuration_type.is_default == 0 || frequency(row) > 1" class="glyphicon glyphicon-trash cursor-pointer" @click="deleteVertexConfiguration(index)" title="刪除"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-center cursor-pointer" @click="addVertexConfiguration">
                        <i class="glyphicon glyphicon-plus-sign cursor-pointer" title="新增"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import VertexConfigurationTypeSelect from '../VertexConfigurationType/VertexConfigurationTypeSelect.vue';
import _ from 'lodash';
import SwitchButton from './SwitchButton.vue';
import RemappingView from './RemappingView.vue';
import IntersectionView from './IntersectionView.vue';
import GroupNameView from './GroupNameView.vue';
import ObjectMgmtListSelect from '../ObjectMgmt/ObjectMgmtListSelect.vue';
import AlignmentView from './AlignmentView.vue';
import SpeedView from './SpeedView.vue';
import RotationCondition from './RotationConditionView.vue';

export default {
    name: "VertexConfigurationListView",
    inject: ['$validator'],
    components: {
        RotationCondition,
        SpeedView,
        AlignmentView,
        ObjectMgmtListSelect,
        GroupNameView,
        IntersectionView,
        RemappingView,
        SwitchButton,
        VertexConfigurationTypeSelect
    },
    props: {
        value: {
            type: Array,
            default() {
                return [];
            }
        },
        vertexConfigurationTypes: {
            type: Array,
            default() {
                return [];
            }
        },
        vertex: {
            type: Object
        }
    },
    watch: {
        vertexConfigurationTypes(newVal) {
            const vertexConfigurationTypes = newVal;
            let vertexConfigurations = _.cloneDeep(this.vertexConfigurations);
            vertexConfigurations = this.initDefaultVertexConfigurations(vertexConfigurations, vertexConfigurationTypes);
            vertexConfigurations = this.clearNoUsedVertexConfigurations(vertexConfigurations, vertexConfigurationTypes);

            this.vertexConfigurations = vertexConfigurations;
        },
        value(newVal) {
            const vertexConfigurations = _.map(this.vertexConfigurations, (r) => {
                r = _.cloneDeep(r);
                delete r.vertex_configuration_type;
                return r;
            });
            if(JSON.stringify(vertexConfigurations) != JSON.stringify(newVal)) {
                const that = this;
                this.vertexConfigurations = _.map(newVal, (r) => {
                    const vertexConfigurationType = _.find(that.vertexConfigurationTypes, (r2) => {
                        return r2.vertex_configuration_column.name == r.type;
                    });
                    r.vertex_configuration_type = vertexConfigurationType ? vertexConfigurationType : null;
                    return r;
                });
            }
        },
        vertexConfigurations: {
            handler(newVal) {
                const vertexConfigurations = _.map(newVal, (r) => {
                    r = _.cloneDeep(r);
                    delete r.vertex_configuration_type;
                    return r;
                });

                if(JSON.stringify(vertexConfigurations) != JSON.stringify(newVal)) {
                    this.$emit('input', vertexConfigurations);
                }
            },
            deep: true
        }
    },
    computed: {
        filterVertexConfigurationTypes() {
            const types = _.map(this.vertexConfigurations, (r) => {
                return r.type;
            });
            return _.filter(this.vertexConfigurationTypes, (r) => {
                return r.vertex_configuration_column.is_unique == 0 || (r.vertex_configuration_column.is_unique == 1 && !types.contains(r.vertex_configuration_column.name));
            });
        }
    },
    data() {
        return {
            vertexConfigurations: []
        };
    },
    created() {
        const that = this;
        this.vertexConfigurations = _.map(this.value, (r) => {
            const vertexConfigurationType = _.find(that.vertexConfigurationTypes, (r2) => {
                return r2.vertex_configuration_column.name == r.type;
            });
            r.vertex_configuration_type = vertexConfigurationType ? vertexConfigurationType : null;
            return r;
        });

        let vertexConfigurations = this.initDefaultVertexConfigurations(this.vertexConfigurations, this.vertexConfigurationTypes);
        vertexConfigurations = this.clearNoUsedVertexConfigurations(vertexConfigurations, this.vertexConfigurationTypes);
        this.vertexConfigurations = vertexConfigurations;
    },
    methods: {
        vertexConfigurationTypeDisabled(type) {
            const vertexConfigurationType = _.find(this.vertexConfigurationTypes, (r) => {
                return r.vertex_configuration_column.name == type;
            });
            if(vertexConfigurationType) {
                return !!vertexConfigurationType.is_default == 1;
            } else {
                return false;
            }
        },
        addVertexConfiguration() {
            this.vertexConfigurations.push({
                id: this.generateUUID(),
                type: null,
                data: null,
                vertex_configuration_type: null
            });
        },
        deleteVertexConfiguration(index) {
            if(!confirm(`確定要刪除第「${index + 1}」筆資料？`)) {
                return;
            }
            this.vertexConfigurations.splice(index, 1);
        },
        validate(row, customRules = {}) {
            let rules = {};
            if(row && row.vertex_configuration_type && row.vertex_configuration_type.vertex_configuration_column.validate) {
                rules = JSON.parse(row.vertex_configuration_type.vertex_configuration_column.validate);
            }

            rules['required'] = true;
            for(const [key, value] of Object.entries(customRules)) {
                rules[key] = value;
            }
            return rules;
        },
        initDefaultVertexConfigurations(vertexConfigurations, vertexConfigurationTypes) {
            const defaultVertexConfigurationTypes = _.filter(vertexConfigurationTypes, (r) => {
                return r.is_default == 1;
            });
            for(let i = 0; i < defaultVertexConfigurationTypes.length; i++) {
                let data = null;
                const type = defaultVertexConfigurationTypes[i].vertex_configuration_column.name;
                const vertexConfiguration = _.find(vertexConfigurations, (r) => {
                    return r.type == type;
                });
                if(!vertexConfiguration) {
                    vertexConfigurations.push({
                        id: this.generateUUID(),
                        type: type,
                        data: data
                    });
                }
            }
            return vertexConfigurations;
        },
        clearNoUsedVertexConfigurations(vertexConfigurations, vertexConfigurationTypes) {
            vertexConfigurations = _.map(vertexConfigurations, (r) => {
                const vertexConfigurationType = _.find(vertexConfigurationTypes, (r2) => {
                    return r2.vertex_configuration_column.name == r.type;
                });
                r.vertex_configuration_type = vertexConfigurationType ? vertexConfigurationType : null;
                return r;
            });
            return vertexConfigurations;
        },
        frequency(vertexConfiguration) {
            let f = 0;
            for(let i = 0; i < this.vertexConfigurations.length; i++) {
                const row = this.vertexConfigurations[i];
                if(row.type == vertexConfiguration.type) {
                    f++;
                }
                if(vertexConfiguration.id == row.id) {
                    break;
                }
            }
            return f;
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
