<template>
    <div>
        <!--登入按鈕-->
        <button type="button" class="btn btn-light-green op-btn login-btn" @click="showLoginModal = true">
            <i class="fa fa-sign-in"/> 登入
        </button>
        <!--底圖內容-->
        <floor-region-view @needLogin="needLogin"/>
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
                                                            :is-select-first="true"
                                                            @change="fetchData"/>
                </div>
            </div>
        </div>
        <!--登入視窗-->
        <div class="login">
            <LoginModal v-if="showLoginModal" v-model="showLoginModal" :region-id="regionId"/>
        </div>
        <!--頁尾-->
        <div class="bottom-row"/>
    </div>
</template>

<script>
import LoginModal from './LoginModal.vue';
import FloorRegionView from '../RegionMgmt/FloorRegionView.vue';
import FloorRegionMgmtListCollapseSelect from '../RegionMgmt/FloorRegionMgmtListCollapseSelect.vue';
import ProjectListSelect from '../Project/ProjectListSelect.vue';
import axios from 'axios';

export default {
    name: "LoginPage",
    components: {ProjectListSelect, FloorRegionMgmtListCollapseSelect, FloorRegionView, LoginModal},
    data() {
        return {
            regionId: null,
            showLoginModal: false,
            showRegion: false,
            form: {}
        }
    },
    created() {
        if(this.form.id) {
            this.fetchData();
        }
    },
    methods: {
        needLogin(regionId) {
            this.regionId = regionId;
            this.showLoginModal = true;
        },
        async fetchData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/regionMgmts/${this.form.id}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.form = data.regionMgmt;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
        }
    }
}
</script>

<style scoped lang="scss">
.bottom-row{
    position:          fixed;
    bottom:            0;
    left:              0;
    right:             0;
    width:             100%;
    height:            66px;
    background-color:  #b1b3b2;
    background-size:   contain;
    background-repeat: no-repeat;
    background-image:  url(/img/login-bottom-row.jpg);
}
.login-btn{
    position: fixed;
    top:      15px;
    left:     15px;
    z-index:  1;
}
.login{
    position: absolute;
    top:      0;
    left:     0;
    width:    100%;
}
.container-right{
    position:         fixed;
    right:            0;
    top:              0;
    width:            320px;
    height:           calc(100vh - 66px);
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
