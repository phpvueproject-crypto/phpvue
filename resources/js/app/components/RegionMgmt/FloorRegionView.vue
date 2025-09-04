<template>
    <div class="project">
        <!--底圖內容-->
        <div id="baseMap" class="base-map" :style="`height: ${isLogin ? 'calc(100vh - 60px)' : 'calc(100vh - 66px)'}`">
            <img v-if="regionMgmt && regionMgmt.project" :src="`/images/${regionMgmt.project.name}/cad_${regionMgmt.region}.png`" alt="" draggable="false"/>
            <template v-for="(roomRegionMgmt, idx) in regionMgmt.room_region_mgmts">
                <template v-if="roomRegionMgmt.room_environment">
                    <RoomEnvironmentView v-model="regionMgmt.room_region_mgmts[idx].room_environment"
                                         :x-px="roomRegionMgmt.x_px"
                                         :y-px="roomRegionMgmt.y_px"
                                         :room-name="roomRegionMgmt.room_environment.room_name"
                                         :micro-organism="roomRegionMgmt.micro_organism"
                                         @click="toRegionMgmt(roomRegionMgmt.id)"/>
                </template>
            </template>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import {mapGetters} from 'vuex';
import RoomEnvironmentView from '../RoomEnvironment/RoomEnvironmentView.vue';

export default {
    name: "FloorRegionView",
    components: {RoomEnvironmentView},
    computed: {
        ...mapGetters({
            isLogin: 'user/isLogin'
        })
    },
    props: {
        regionMgmtId: {
            type: Number,
            default: 1
        }
    },
    data() {
        return {
            loading: true,
            project: {region_mgmts: []},
            regionMgmt: {}
        }
    },
    mounted() {
        this.fetchData();
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
                this.guestRedirectHome(err);
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
                if(e.target.className == 'vertex-point' || e.target.className == 'edge-point' || e.target.className == 'ui-widget-content ui-draggable ui-draggable-handle' || e.target.classList.contains('disable-drag')) {
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
        toRegionMgmt(regionMgmtId) {
            if(this.isLogin) {
                this.$router.push(`/regionMgmts/${regionMgmtId}`);
            } else {
                this.$emit('needLogin', regionMgmtId);
            }
        },
        subScribe() {
            const that = this;
            window.Echo.private('microOrganisms').listen('MicroOrganismCreated', (res) => {
                const microOrganism = res.microOrganism;
                const regionMgmt = res.microOrganism.region_mgmt;
                if(!microOrganism.is_serious) {
                    return;
                }
                const regionMgmtIdx = _.findIndex(that.project.region_mgmts, ['id', regionMgmt.id]);
                if(regionMgmtIdx != -1) {
                    that.$set(that.project.region_mgmts[regionMgmtIdx], 'micro_organism', microOrganism);
                }
            }).listen('MicroOrganismUpdated', (res) => {
                const microOrganism = res.microOrganism;
                const regionMgmt = res.microOrganism.region_mgmt;
                if(!microOrganism.is_serious) {
                    return;
                }
                const regionMgmtIdx = _.findIndex(that.project.region_mgmts, ['id', regionMgmt.id]);
                if(regionMgmtIdx != -1) {
                    that.project.region_mgmts[regionMgmtIdx].micro_organism = microOrganism;
                }
            }).listen('MicroOrganismDeleted', (res) => {
                const microOrganism = res.microOrganism;
                const regionMgmt = res.microOrganism.region_mgmt;
                const regionMgmtIdx = _.findIndex(that.project.region_mgmts, ['id', regionMgmt.id]);
                if(regionMgmtIdx == -1 || !microOrganism.is_serious || microOrganism.id != that.project.region_mgmts[regionMgmtIdx].micro_organism.id) {
                    return;
                }
                that.project.region_mgmts[regionMgmtIdx].micro_organism = null;
            });
        },
        unSubscribe() {
            window.Echo.leave('microOrganisms');
        }
    }
}
</script>

<style lang="scss" scoped>
.project{
    position:   relative;
    text-align: left;
}
.base-map{
    position: relative;
    overflow: hidden;
    img{
        user-select: none;
    }
}
</style>