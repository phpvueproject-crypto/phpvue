<template>
    <div class="region" id="region">
        <!--底圖內容-->
        <div v-if="regionMgmt" id="baseMap" class="base-map" :style="`scale: ${scale}`">
            <img :src="`/images/${regionMgmt.project.name}/cad_${regionMgmt.region}.png`" alt="" draggable="false"/>
            <location-list-view ref="locations" map-type="cad" view-type="pollution" v-model="locations" :region-mgmt="regionMgmt" :region-mgmt-id="regionMgmt.id" :is-deploy="1" :acceptance-grades="acceptanceGrades" :pollution-conditions="pollutionConditions" :scale="scale" @dbclick="showMicroOrganism"/>
        </div>
        <!--微生物圖例-->
        <div class="micro-organism-legend">
            <div>
                <span><i class="fa fa-play fa-fw fa-rotate-270" aria-hidden="true"/> 懸浮微生物</span>
                <span><i class="fa fa-square fa-fw" aria-hidden="true"/> 落下微生物</span>
                <span><i class="fa fa-circle fa-fw" aria-hidden="true"/> 接觸微生物</span>
                <span><i class="fa fa-star fa-fw" aria-hidden="true"/> 微粒子(5µm)</span>
                <span><i class="fa fa-square fa-fw fa-rotate-45" aria-hidden="true"/> 微粒子(0.5µm)</span>
            </div>
            <div>
                <template v-for="pollutionCondition in pollutionConditions">
                    <span><i class="fa fa-square" :style="`color: ${pollutionCondition.color}`" aria-hidden="true"/> {{ pollutionCondition.display_name }}</span>
                </template>
            </div>
        </div>
        <!--現在位置-->
        <div class="room-name-block">
            <router-link to="/floorRegionMgmts/1" class="back-btn">&#60;返回</router-link>
            <span class="room-name-title">現在位置：</span>
            <span class="room-name">{{ roomName ? roomName : '尚無房間名稱' }}</span>
            &nbsp;
            <div class="btn-group shadow" role="group" style="display: inline-block">
                <button class="btn btn-default" :disabled="scale >= 3" @click="scaleMap(true)">+</button>
                <button class="btn btn-default" @click="scale = 1">{{ parseInt(scale * 100) }}%</button>
                <button class="btn btn-default" :disabled="scale <= 0" @click="scaleMap(false)">-</button>
            </div>
        </div>
        <!--微生物資訊-->
        <div class="micro-organism" :class="microOrganismModal.show ? '' : 'micro-organism-collapsed'">
            <div class="micro-organism-tag" :class="microOrganism && microOrganismModal.show ? '' : 'tag-collapsed'" @click="showMicroOrganism">
                {{ microOrganismModal.show ? '&nbsp;&gt;' : '&nbsp;&lt;' }}微生物資訊
            </div>
            <div class="micro-organism-info">
                <form v-if="microOrganism" novalidate class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-4 control-label">時間</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{ microOrganism.Time | datetime }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">位置</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{ microOrganism.device_name }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">座標</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                <span style="font-weight: bold">X: </span>{{ microOrganism.location.x_px }}</p>
                            <p class="form-control-static">
                                <span style="font-weight: bold">Y: </span>{{ microOrganism.location.y_px }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">微生物類別</label>
                        <div class="col-md-8">
                            <p class="form-control-static">
                                <span v-if="microOrganism.organism_kind == 'suspended'">懸浮微生物</span>
                                <span v-else-if="microOrganism.organism_kind == 'falling'">落下微生物</span>
                                <span v-else-if="microOrganism.organism_kind == 'contact'">接觸微生物</span>
                                <span v-else-if="microOrganism.organism_kind == 'microparticle_dot_5'">微粒子(0.5µm)</span>
                                <span v-else-if="microOrganism.organism_kind == 'microparticle_5'">微粒子(5µm)</span>
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">微生物數據</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{ microOrganism.organism_value }}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">房間名稱</label>
                        <div class="col-md-8">
                            <p class="form-control-static">{{ microOrganism.room_name }}</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';
import LocationListView from '../Location/LocationListView.vue';

export default {
    name: "RegionMgmtPage",
    components: {LocationListView},
    computed: {
        roomName() {
            if(!this.regionMgmt || !this.regionMgmt.room_environment || !this.regionMgmt.room_environment.room_name) {
                return null;
            }
            return this.regionMgmt.room_environment.room_name;
        }
    },
    data() {
        return {
            regionMgmtId: this.$route.params.id ? parseInt(this.$route.params.id) : null,
            regionMgmt: null,
            locations: null,
            microOrganismModal: {
                show: false
            },
            microOrganism: null,
            acceptanceGrades: [],
            pollutionConditions: [],
            scale: 1
        }
    },
    async mounted() {
        if(this.regionMgmtId) {
            await Promise.all([
                this.fetchData(),
                this.fetchAcceptanceGradesData(),
                this.fetchPollutionConditionData()
            ]);
            await this.$refs.locations.fetchData();
        }
        this.slider();
        this.subScribe();
    },
    beforeDestroy() {
        this.unSubscribe();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/regionMgmts/${this.regionMgmtId}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.regionMgmt = data.regionMgmt;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        async fetchAcceptanceGradesData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/acceptanceGrades', {
                    params: {
                        region_mgmt_id: this.regionMgmtId
                    }
                });
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.acceptanceGrades = data.acceptanceGrades;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        async fetchPollutionConditionData() {
            this.loading = true;
            try {
                let res = await axios.get('/api/pollutionConditions');
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.pollutionConditions = [];
                    const columns = ['action', 'warn', 'general', 'normal'];
                    _.forEach(columns, (r) => {
                        const pollutionCondition = _.find(data.pollutionConditions, {'name': r});
                        if(!pollutionCondition) {
                            return;
                        }
                        this.pollutionConditions.push(pollutionCondition);
                    });
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        slider() {
            let isDown = false;
            let startX, startY;
            let scrollLeft, scrollTop;
            const $baseMap = document.getElementById('baseMap');
            if(!$baseMap) {
                return;
            }
            $baseMap.addEventListener('mousedown', (e) => {
                if(e.target.className.contains('micro-organism') || e.target.className == 'ui-widget-content ui-draggable ui-draggable-handle' || e.target.classList.contains('disable-drag')) {
                    return;
                }
                isDown = true;
                $baseMap.classList.add("active");
                startX = e.pageX;
                startY = e.pageY - $baseMap.offsetTop;
                scrollLeft = $baseMap.scrollLeft;
                scrollTop = $baseMap.scrollTop;
            });
            $baseMap.addEventListener('mouseleave', () => {
                isDown = false;
                $baseMap.classList.remove("active");
            });
            $baseMap.addEventListener('mouseup', () => {
                isDown = false;
                $baseMap.classList.remove("active");
            });
            $baseMap.addEventListener('mousemove', (e) => {
                e.preventDefault();
                if(!isDown) return;
                const x = e.pageX - $baseMap.offsetLeft;
                const walkX = x - startX;
                $baseMap.scrollLeft = scrollLeft - walkX;

                const y = e.pageY - $baseMap.offsetTop;
                const walkY = y - startY;
                $baseMap.scrollTop = scrollTop - walkY;
            });
        },
        showMicroOrganism(microOrganism) {
            if(microOrganism && microOrganism.device_name) {
                this.microOrganism = microOrganism;
            }
            if(this.microOrganism) {
                if(microOrganism && microOrganism.device_name) {
                    this.microOrganismModal.show = true;
                } else {
                    this.microOrganismModal.show = !this.microOrganismModal.show;
                }
            } else {
                alert('請雙擊微生物。');
            }
        },
        subScribe() {
            const that = this;
            window.Echo.private('pollutionConditions').listen('PollutionConditionUpdated', (res) => {
                const pollutionCondition = res.pollutionCondition;
                const originPollutionConditionIdx = _.findIndex(that.pollutionConditions, {'id': pollutionCondition.id});
                if(originPollutionConditionIdx != -1) {
                    that.pollutionConditions.splice(originPollutionConditionIdx, 1, pollutionCondition);
                }
            });
        },
        unSubscribe() {
            window.Echo.leave('pollutionConditions');
        },
        scaleMap(zoom) {
            if(zoom) {
                this.scale += 0.1;
            } else {
                this.scale -= 0.1;
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.region{
    position: relative;
}
.base-map{
    position: relative;
    height:   calc(100vh - 60px);
    overflow: hidden;
    img{
        user-select: none;
    }
}
.region-link{
    position: absolute;
    z-index:  1;
}
.micro-organism{
    position:         fixed;
    right:            0;
    top:              48px;
    min-width:        450px;
    height:           calc(100vh - 60px);
    margin:           10px 0 0 26px;
    background-color: white;
    transition:       all 800ms ease-in-out;
    overflow-y:       scroll;
    overflow-x:       hidden;
    box-shadow:       0 0 10px gray;
}
.micro-organism-collapsed{
    position: fixed;
    right:    -450px;
}
.micro-organism-tag{
    display:          inline-block;
    position:         fixed;
    bottom:           calc(50vh - 20px);
    right:            450px;
    width:            40px;
    padding:          4px 0 8px 10px;
    border-left:      1px solid #d3d3d3;
    border-top:       2px solid #d3d3d3;
    border-bottom:    2px solid #d3d3d3;
    border-radius:    20px 0 0 20px;
    font-size:        16pt;
    font-weight:      bold;
    word-break:       break-all;
    color:            #5c9b6c;
    background-color: white;
    box-shadow:       -1px 0 4px 2px #d3d3d3;
    cursor:           pointer;
    transition:       all 800ms ease-in-out;
}
.tag-collapsed{
    position: fixed;
    right:    0;
}
.micro-organism-info{
    margin-top: 30px;
}
.fa-rotate-45{
    transform: rotate(45deg);
}
.fa-24{
    font-size: 24px;
}
.micro-organism-legend{
    position:         absolute;
    top:              15px;
    left:             15px;
    border-top:       8px solid #bfc2e3;
    background-color: white;
    box-shadow:       0 1px 4px 2px #d3d3d3;
    letter-spacing:   2px;
    font-size:        14px;
    padding:          5px 15px;
    user-select:      none;
    span:not(:first-child){
        margin-left: 10px;
    }
    div{
        line-height: 3;
    }
}
.room-name-block{
    position:         absolute;
    top:              15px;
    right:            15px;
    border-top:       8px solid #bfc2e3;
    background-color: white;
    box-shadow:       0 1px 4px 2px #d3d3d3;
    letter-spacing:   2px;
    font-size:        14px;
    padding:          15px;
    transition:       top 0.5s ease-in-out;
    .room-name{
        font-weight: bold;
    }
    .room-name-title{
        user-select: none;
    }
}
.back-btn{
    border-right:  1px solid #808080;
    padding-right: 4px;
}
</style>
