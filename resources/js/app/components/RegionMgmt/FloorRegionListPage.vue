<template>
    <div>
        <!--樓層地圖-->
        <floor-region-view :region-mgmt-id="regionMgmtId"/>
        <!--區域名稱-->
        <div class="container-right" :class="showRegion ? '' : 'container-right-collapsed'" :style="showRegion ? 'z-index: 2' : 'z-index: 1'">
            <div class="region-tag" :class="showRegion ? '' : 'burner-collapsed'" @click="showRegion = !showRegion">
                {{ showRegion ? '&nbsp;&gt;' : '&nbsp;&lt;' }}區域名稱
            </div>
            <div class="col-md-12 project-list">
                <div class="text-center project-list-title">專案區域列表</div>
                <project-list-select v-model="form.project_id"
                                     :is-deploy="1"
                                     :disabled="!!form.project_id"/>
                <div class="region-floor-room-collapse-select">
                    <floor-region-mgmt-list-collapse-select v-model="form.id"
                                                            :project-id.sync="form.project_id"
                                                            :is-deploy="1"
                                                            @change="search"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import ProjectListSelect from '../Project/ProjectListSelect.vue';
import FloorRegionMgmtListCollapseSelect from './FloorRegionMgmtListCollapseSelect.vue';
import FloorRegionView from './FloorRegionView.vue';

export default {
    name: "FloorRegionListPage",
    components: {FloorRegionView, FloorRegionMgmtListCollapseSelect, ProjectListSelect},
    data() {
        return {
            loading: false,
            regionMgmtId: this.$route.params.id ? parseInt(this.$route.params.id) : 1,
            showRegion: false,
            form: {}
        }
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/regionMgmts/${this.regionMgmtId}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.regionMgmt;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        search() {
            this.$router.push({
                path: `/floorRegionMgmts/${this.form.id}`
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.container-right{
    position:         fixed;
    right:            0;
    top:              58px;
    width:            320px;
    height:           calc(100vh - 58px);
    padding-top:      15px;
    background-color: white;
    border:           1px #dddddd solid;
    transition:       all 800ms ease-in-out;
}
.container-right-collapsed{
    right: -324px;
}
.region-tag{
    display:          inline-block;
    position:         fixed;
    bottom:           calc(50vh - 200px);
    right:            320px;
    margin-top:       22px;
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
.burner-collapsed{
    position: fixed;
    right:    0;
}
.project-list{
    padding-left:  0;
    padding-right: 0;
}
.project-list-title{
    font-size:     24px;
    margin-bottom: 15px;
    color:         #5c9b6c;
}
.region-floor-room-collapse-select{
    margin-top: 6px;
    overflow:   scroll;
    height:     calc(100vh - 180px);
}
</style>