<template>
    <div>
        <div class="panel-group" id="accordion" aria-multiselectable="true">
            <template v-for="(regionMgmt, floorIndex) in regionMgmts">
                <div class="panel panel-default">
                    <div class="panel-heading" :id="`heading${floorIndex}`">
                        <a class="accordion-toggle"
                           data-toggle="collapse"
                           data-parent="#accordion"
                           :class="{'collapsed' : regionMgmt.is_collapsed}"
                           :href="`#collapse${floorIndex}`"
                           :aria-expanded="!regionMgmt.is_collapsed"
                           :aria-controls="`collapse${floorIndex}`">
                            <div @click.self="selectRegion(floorIndex, -1)">
                                <label class="label label-floor">樓層</label>&emsp;{{ regionMgmt.region }}
                            </div>
                        </a>
                        <div class="clearfix"/>
                    </div>
                    <div :id="`collapse${floorIndex}`" class="panel-collapse" :class="{'collapse' : regionMgmt.is_collapsed, 'collapse in' : !regionMgmt.is_collapsed}" :aria-expanded="!regionMgmt.is_collapsed" :aria-labelledby="`heading${floorIndex}`">
                        <div class="panel-body">
                            <div v-for="(roomRegionMgmt, roomIndex) in regionMgmt.room_region_mgmts"
                                 class="col-md-12 region-item"
                                 :class="{'selected-region' : roomRegionMgmt.id == regionMgmtId, 'disabled' : roomRegionMgmt.edit_user_id && roomRegionMgmt.edit_user_id != userId}"
                                 :style="(roomIndex + 1) != regionMgmt.room_region_mgmts.length ? `border-bottom: 1px solid gray;` : ''"
                                 @click="selectRegion(floorIndex, roomIndex)">
                                <label class="label label-room">房間</label>&emsp;
                                <template v-if="!isDeploy && roomRegionMgmt.edit_user">
                                    {{ roomRegionMgmt.region }}({{ roomRegionMgmt.edit_user.account }}正在使用中)
                                </template>
                                <template v-else>
                                    {{ roomRegionMgmt.region }}
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import {mapState} from 'vuex';

export default {
    name: "RegionMgmtListCollapseSelect",
    props: {
        value: {
            type: Number,
            default: null
        },
        isDeploy: {
            type: Number,
            default: 0
        },
        enableConfirm: {
            type: Boolean,
            default: false
        },
        projectId: {
            type: Number,
            default: null
        }
    },
    computed: {
        ...mapState({
            userId: state => state.user.user.id
        })
    },
    watch: {
        async value(newVal) {
            this.regionMgmtId = newVal;
            await this.fetchData();
            if(newVal) {
                this.setActiveRegionMgmt(newVal);
            }
        },
        regionMgmtId(newVal, oldVal) {
            if(!this.isDeploy) {
                window.Echo.leave(`regionMgmts.presence.${oldVal}`);
            }
            this.$emit('input', newVal);
        },
        isDeploy(newVal) {
            this.form.is_deploy = newVal;
        },
        projectId(newVal, oldVal) {
            if(!this.isDeploy) {
                window.Echo.leave(`projects.${oldVal}`);
            }
        }
    },
    data() {
        return {
            regionMgmts: [],
            regionMgmtId: this.value,
            form: {
                is_deploy: this.isDeploy,
                is_write: this.isDeploy ? null : 1,
                is_read: this.isDeploy ? 1 : null
            },
            oldRegionMgmtId: null
        };
    },
    created() {
        if(this.value) {
            this.regionMgmts = [{id: this.value}];
        }
    },
    async mounted() {
        await this.fetchData();
        if(this.value) {
            this.setActiveRegionMgmt(this.value);
        }
        if(!this.isDeploy) {
            const that = this;
            window.Echo.private(`projects.${this.projectId}`).listen('RegionMgmtUpdated', (res) => {
                const regionMgmt = res.regionMgmt;
                const floorIdx = _.findIndex(that.regionMgmts, ['id', regionMgmt.id]);
                if(floorIdx != -1) {
                    const originRoomRegionMgmt = _.cloneDeep(that.regionMgmts[floorIdx].room_region_mgmts);
                    that.regionMgmts.splice(floorIdx, 1, regionMgmt);
                    that.regionMgmts[floorIdx].room_region_mgmts = originRoomRegionMgmt;
                    return;
                }
                _.forEach(this.regionMgmts, (r, rIdx) => {
                    const tempRoomIdx = _.findIndex(r.room_region_mgmts, ['id', regionMgmt.id]);
                    if(tempRoomIdx != -1) {
                        that.regionMgmts[rIdx].room_region_mgmts.splice(tempRoomIdx, 1, regionMgmt);
                    }
                });
            });
        }
    },
    destroyed() {
        if(!this.isDeploy) {
            window.Echo.leave(`regionMgmts.presence.${this.regionMgmtId}`);
            window.Echo.leave(`projects.${this.value}`);
        }
    },
    methods: {
        async fetchData() {
            const that = this;
            this.loading = true;
            try {
                let res = await axios.get('/api/regionMgmts', {
                    params: {
                        project_id: this.projectId,
                        is_write: this.isDeploy ? null : 1,
                        is_read: this.isDeploy ? 1 : null,
                        is_floor: 1
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.regionMgmts = _.map(data.regionMgmts, (r) => {
                        r.is_collapsed = (r.id == that.regionMgmtId);
                        return r;
                    });
                    if(!this.isDeploy && this.regionMgmtId) {
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
        selectRegion(pIndex, rIndex) {
            if(this.enableConfirm) {
                if(!confirm('確定要切換區域？')) {
                    return;
                }
            }
            if(rIndex != -1) {
                this.$emit('input', this.regionMgmts[pIndex].room_region_mgmts[rIndex].id);
                this.$emit('update:projectId', this.regionMgmts[pIndex].project_id);
            } else {
                this.$emit('input', this.regionMgmts[pIndex].id);
                this.$emit('update:projectId', this.regionMgmts[pIndex].project_id);
            }
        },
        setActiveRegionMgmt(id) {
            const regionMgmtIdx = _.findIndex(this.regionMgmts, (r) => {
                return r.id == id;
            });
            if(regionMgmtIdx != -1) {
                this.regionMgmts[regionMgmtIdx].is_collapsed = false;
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
    .label-room{
        color:            #5c9b6c;
        background-color: #ffffff !important;
    }
}
.disabled{
    pointer-events: none;
    cursor:         default;
}
.label-floor{
    font-size:        14px;
    background-color: #5c9b6c !important;
    letter-spacing:   4px;
    padding:          0.2em 0.2em 0.2em 0.4em;
}
.label-room{
    font-size:        14px;
    background-color: #5c9b6c !important;
    letter-spacing:   4px;
    padding:          0.2em 0.2em 0.2em 0.4em;
}
</style>
