<template>
    <div>
        <div v-if="isCollapseStyle" class="panel-group" id="accordion" aria-multiselectable="true">
            <template v-for="(project, pIndex) in projects">
                <div class="panel panel-default">
                    <div class="panel-heading" :id="`heading${pIndex}`">
                        <a class="accordion-toggle"
                           data-toggle="collapse"
                           data-parent="#accordion"
                           :class="{'collapsed' : project.isCollapsed}"
                           :href="`#collapse${pIndex}`"
                           :aria-expanded="!project.isCollapsed"
                           :aria-controls="`collapse${pIndex}`">
                            <div>
                                <label class="label label-project">專案</label>&emsp;{{ project.name }}
                            </div>
                        </a>
                        <div class="clearfix"/>
                    </div>
                    <div :id="`collapse${pIndex}`" class="panel-collapse" :class="{'collapse' : project.isCollapsed, 'collapse in' : !project.isCollapsed}" :aria-expanded="!project.isCollapsed" :aria-labelledby="`heading${pIndex}`">
                        <div class="panel-body">
                            <div v-if="project.region_mgmts.length > 0"
                                 v-for="(regionMgmt, rIndex) in project.region_mgmts"
                                 class="col-md-12 region-item"
                                 :class="{'selected-region' : regionMgmt.id == regionMgmtId, 'disabled' : regionMgmt.edit_user_id && regionMgmt.edit_user_id != userId}"
                                 :style="(rIndex + 1) != project.region_mgmts.length ? `border-bottom: 1px solid gray;` : ''"
                                 @click="selectRegion(pIndex, rIndex)">
                                <label class="label label-region">區域</label>&emsp;
                                <template v-if="editMode && regionMgmt.edit_user">
                                    {{ regionMgmt.region }}({{ regionMgmt.edit_user.account }}正在使用中)
                                </template>
                                <template v-else>
                                    {{ regionMgmt.region }}
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div v-else-if="multiple || singleSelect2" :class="[color]">
            <select ref="select2"
                    class="form-control select2"
                    :multiple="multiple"
                    :data-placeholder="placeholder"
                    style="width: 100%;"
                    :disabled="disabled">
                <option v-for="project in projects" :value="project.id">
                    {{ project.name }}
                </option>
            </select>
        </div>
        <select v-else :class="selectClass" v-model="projectId" :disabled="disabled">
            <option v-if="!hasNullOption" :value="null">{{ defaultText }}</option>
            <option v-for="project in projects" :value="project.id">
                {{ project.name }}
            </option>
        </select>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import axios from 'axios';
import {mapState} from 'vuex';

export default {
    name: "ProjectListSelect",
    props: {
        value: {
            type: [Array, Number]
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: '請選擇'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        color: {
            type: String,
            default: 'theme-blue'
        },
        defaultText: {
            type: String,
            default: '請選擇'
        },
        singleSelect2: {
            type: Boolean,
            default: false
        },
        minNumForSearch: {
            type: Number,
            default: -1
        },
        tags: {
            type: Boolean,
            default: false
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        enableConfirm: {
            type: Boolean,
            default: false
        },
        isCollapseStyle: {
            type: Boolean,
            default: false
        },
        regionMgmtId: {
            type: Number,
            default: null
        },
        editMode: {
            type: Boolean,
            default: false
        },
        selectClass: {
            type: String,
            default: 'form-control'
        }
    },
    computed: {
        hasNullOption() {
            return _.find(this.projects, (r) => {
                return r.name == null;
            });
        },
        ...mapState({
            userId: state => state.user.user.id
        })
    },
    watch: {
        async value(newVal) {
            const that = this;
            if(this.multiple) {
                this.$nextTick(() => {
                    $(that.$refs.select2).select2('data', _.map(newVal, (r) => {
                        return r.id;
                    }));
                });
            } else if(this.singleSelect2) {
                this.$nextTick(() => {
                    $(that.$refs.select2).select2('data', [newVal]);
                });
            } else {
                this.projectId = newVal;
                await this.fetchData();
                if(newVal && this.isCollapseStyle) {
                    this.setActiveProject(newVal);
                }
            }
            if(this.isDeploy) {
                if(this.projects.length > 0) {
                    this.$emit('input', this.projects[0].id);
                }
            }
            if(this.singleSelect2 || this.multiple) {
                this.refresh();
            }
        },
        projectId(newVal, oldVal) {
            if(this.enableConfirm && !this.isRollback) {
                if(!confirm('確定要切換專案？')) {
                    this.isRollback = true;
                    const that = this;
                    this.$nextTick(() => {
                        that.projectId = oldVal;
                    });
                    return;
                }
            }

            this.isRollback = false;
            this.$emit('input', newVal);
            this.$emit('change');
        },
        isDeploy(newVal) {
            this.form.is_deploy = newVal;
        }
    },
    data() {
        return {
            projects: [],
            projectId: this.value,
            form: {
                is_deploy: this.isDeploy,
                is_write: this.isDeploy ? null : 1,
                is_read: this.isDeploy ? 1 : null
            },
            oldRegionMgmtId: null,
            isRollback: false
        };
    },
    created() {
        if(this.value && !this.multiple && !this.singleSelect2) {
            this.projects = [{id: this.value}];
        }
    },
    async mounted() {
        await this.fetchData();
        if(this.value && this.isCollapseStyle) {
            this.setActiveProject(this.value);
        }
        if(this.multiple || this.singleSelect2) {
            this.init();
            this.setSelect2Val();
        } else if(this.editMode && !this.multiple) {
            const that = this;
            window.Echo.private(`projects.${this.value}`).listen('RegionMgmtUpdated', (e) => {
                const regionMgmt = e.regionMgmt;
                const projectIdx = _.findIndex(that.projects, (r) => {
                    return r.id == regionMgmt.project_id;
                });
                if(projectIdx == -1) {
                    return;
                }
                const regionMgmtIdx = _.findIndex(that.projects[projectIdx].region_mgmts, (r) => {
                    return r.id == regionMgmt.id;
                });
                if(regionMgmtIdx == -1) {
                    return;
                }
                that.projects[projectIdx].region_mgmts.splice(regionMgmtIdx, 1, regionMgmt);
            });
        }
    },
    destroyed() {
        if(this.editMode && !this.multiple) {
            window.Echo.leave(`regionMgmts.presence.${this.regionMgmtId}`);
            window.Echo.leave(`projects.${this.value}`);
        }
    },
    methods: {
        init() {
            const $el = $(this.$refs.select2);
            const that = this;
            $el.select2({
                minimumResultsForSearch: that.minNumForSearch,
                closeOnSelect: !that.multiple,
                tags: that.tags,
                language: {
                    noResults: function() {
                        return '查無資料';
                    }
                }
            });
            $el.on("select2:select", (e) => {
                that.setVal();
            });
            $el.on("select2:unselect", (e) => {
                that.setVal();
            });
        },
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/projects', {
                    params: this.form
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    if(this.isCollapseStyle) {
                        const that = this;
                        this.projects = _.map(data.projects, (project) => {
                            const regionMgmt = _.find(project.region_mgmts, (region) => {
                                return region.id == that.regionMgmtId;
                            });
                            project.isCollapsed = !regionMgmt;
                            return project;
                        });
                    } else {
                        this.projects = data.projects;
                    }
                    if(this.isDeploy) {
                        if(this.projects.length > 0) {
                            this.$emit('input', this.projects[0].id);
                        }
                    }
                    if(this.editMode && !this.multiple && this.regionMgmtId) {
                        if(this.$route.path == '/regionMgmts') {
                            window.Echo.join(`regionMgmts.presence.${this.regionMgmtId}`);
                        }
                        this.oldRegionMgmtId = this.regionMgmtId;
                    }
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        setSelect2Val() {
            const that = this;
            this.$nextTick(() => {
                if(that.multiple && !that.singleSelect2) {
                    $(that.$refs.select2).val(_.map(that.value, (r) => {
                        return r.id;
                    }));
                } else {
                    $(that.$refs.select2).val([that.value]);
                }
                $(that.$refs.select2).trigger('change');
            });
        },
        setVal() {
            const arr = $(this.$refs.select2).select2('data');
            if(this.multiple) {
                this.$emit('input', _.map(arr, (r) => {
                    return {
                        id: r.id
                    };
                }));
            } else {
                if(arr.length != 0) {
                    this.$emit('input', arr[0].id);
                } else {
                    this.$emit('input', null);
                }
            }
        },
        refresh() {
            $(this.$refs.select2).val(null).trigger('change');
            $(this.$refs.select2).select2('destroy');
            this.init();
            this.setSelect2Val();
        },
        selectRegion(pIndex, rIndex) {
            this.$emit('input', this.projects[pIndex].id);
            this.$emit('update:regionMgmtId', this.projects[pIndex].region_mgmts[rIndex].id);
        },
        setActiveProject(id) {
            const projectIdx = _.findIndex(this.projects, (r) => {
                return r.id == id;
            });
            if(projectIdx != -1) {
                this.projects[projectIdx].isCollapsed = false;
            }
        }
    }
}
</script>
<style lang="scss" scoped>
.panel{
    border: 0;
}
.panel-default > .panel-heading{
    background-color: #deecdf;
}
.panel-heading .accordion-toggle:after{
    font-family: Glyphicons Halflings, serif;
    content:     "\E114";
    float:       right;
    color:       #5c9b6c;
    line-height: 1.6;
    padding:     10px 15px 10px 0;
}
.panel-heading .accordion-toggle.collapsed:after{
    content: "\E080";
}
.panel-heading{
    border-radius: 0;
    padding:       0;
    a{
        div{
            float:       left;
            width:       88%;
            color:       #5c9b6c;
            font-weight: bold;
            font-size:   14pt;
            padding:     10px 0 10px 15px;
        }
    }
}
.panel-body{
    padding: 0;
}
.panel-group .panel + .panel{
    margin-top: 0;
    border-top: 1px #5c9b6c solid;
}
.region-item{
    line-height: 2;
    color:       #5c9b6c;
}
.region-item:hover, .selected-region{
    color:            white;
    background-color: #5c9b6c;
    cursor:           pointer;
    .label-region{
        color:            #5c9b6c;
        background-color: #ffffff !important;
    }
}
.disabled{
    pointer-events: none;
    cursor:         default;
}
.label-project{
    font-size:        14px;
    background-color: #5c9b6c !important;
    letter-spacing:   4px;
    padding:          0.2em 0.2em 0.2em 0.4em;
}
.label-region{
    font-size:        14px;
    background-color: #5c9b6c !important;
    letter-spacing:   4px;
    padding:          0.2em 0.2em 0.2em 0.4em;
}
.select-only-read{
    border:     0;
    background: white;
    appearance: none;
}
</style>
