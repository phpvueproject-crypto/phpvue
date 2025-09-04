<template>
    <aside class="main-sidebar">
        <section class="sidebar" style="height:auto">
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">主選單</li>
                <li v-if="permissions.contains('user-role-manage')" :class="{active: $route.fullPath.match(/\/users/)}">
                    <router-link to="/users">
                        <i class="fa fa-user-circle-o"></i> <span>{{ $t('AAPM') }}</span>
                    </router-link>
                </li>
                <li v-if="permissions.contains('sampling-robot')"
                    class="treeview"
                    :class="{active: $route.fullPath.match(/\/devices/)||
                                     $route.fullPath.match(/\/mirStatuses/) ||
                                     $route.fullPath.match(/\/missions/) ||
                                     $route.fullPath.match(/\/missionQueues/) ||
                                     $route.fullPath.match(/\/regionMgmts/) ||
                                     $route.fullPath.match(/\/maps/) ||
                                     $route.fullPath.match(/\/microOrganisms/) && $route.query.source === '2' ||
                                     $route.fullPath.match(/\/locations/) ||
                                     $route.fullPath.match(/\/hookStatuses/) ||
                                     $route.fullPath.match(/\/missionBookings/) ||
                                     $route.fullPath.match(/\/remoteManagementSystemStatuses/)}">
                    <a href="javascript:void(0)">
                        <i class="fa fa-car"></i>
                        <span>採樣機器人</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview"
                            :class="{active: $route.fullPath.match(/\/mirStatuses/) ||
                                             $route.fullPath.match(/\/missions/) ||
                                             $route.fullPath.match(/\/missionQueues/) ||
                                             $route.fullPath.match(/\/missionBookings/)}">
                            <a href="javascript:void(0)">
                                <i class="fa fa-circle-o"></i>
                                <span>任務</span>
                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                            </a>
                            <ul class="treeview-menu">
                                <li :class="{active: $route.fullPath.match(/\/missions/) && !$route.fullPath.match(/\/missionQueues\/sample/)}">
                                    <router-link to="/missions">
                                        <i class="fa fa-circle-o"></i> <span>發送任務</span>
                                    </router-link>
                                </li>
                                <li :class="{active: $route.fullPath.match(/\/missionQueues\/current/)}">
                                    <router-link :to="{path:'/missionQueues/current'}">
                                        <i class="fa fa-circle-o"></i> <span>目前任務列</span>
                                    </router-link>
                                </li>
                                <li :class="{active: $route.fullPath.match(/\/missionQueues\/history/)}">
                                    <router-link :to="{path:'/missionQueues/history'}">
                                        <i class="fa fa-circle-o"></i> <span>歷史任務列</span>
                                    </router-link>
                                </li>
                                <li :class="{active: $route.fullPath.match(/\/missionBookings/)}">
                                    <router-link to="/missionBookings">
                                        <i class="fa fa-circle-o"></i> <span>預約任務</span>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <li :class="{active: $route.fullPath.match(/\/regionMgmts/) && parseInt($route.query.is_deploy) == 0}">
                            <router-link :to="`/regionMgmts?is_deploy=0&${regionQuery}`">
                                <i class="fa fa-circle-o"></i> <span>{{ $t('GDC') }}</span>
                            </router-link>
                        </li>
                        <li :class="{active: $route.fullPath.match(/\/regionMgmts/) && parseInt($route.query.is_deploy) == 1}">
                            <router-link :to="`/regionMgmts?is_deploy=1&${regionQuery}`">
                                <i class="fa fa-circle-o"></i> <span>{{ $t('SR') }}</span>
                            </router-link>
                        </li>
                        <li :class="{active: $route.fullPath.match(/\/maps/)}">
                            <router-link to="/maps">
                                <i class="fa fa-circle-o"></i> <span>地圖列</span>
                            </router-link>
                        </li>
                        <li :class="{active: $route.fullPath.match(/\/missionQueues\/sample/)}">
                            <router-link to="/missionQueues/sample">
                                <i class="fa fa-circle-o"></i> <span>歷史採樣任務進度</span>
                            </router-link>
                        </li>
                        <li :class="{active: $route.path === '/hookStatuses'}">
                            <router-link to="/hookStatuses">
                                <i class="fa fa-circle-o"></i> <span>手臂資訊</span>
                            </router-link>
                        </li>
                        <li :class="{active: $route.path === '/remoteManagementSystemStatuses'}">
                            <router-link to="/remoteManagementSystemStatuses">
                                <i class="fa fa-circle-o"></i> <span>採樣任務進度</span>
                            </router-link>
                        </li>
                        <li :class="{active: $route.fullPath.match(/\/devices/)}">
                            <router-link to="/devices">
                                <i class="fa fa-circle-o"></i> <span>AP連線 / IP連線</span>
                            </router-link>
                        </li>
                        <li v-if="device && (device.ip || device.ap)">
                            <a :href="`${device.ip ? ('http://' + device.ip) : device.ap}`" target="_blank">
                                <i class="fa fa-circle-o"></i> <span>MIR 軟體</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!--                <li v-if="permissions.contains('cleanup-robot')" class="treeview" :class="{active: (-->
                <!--                            $route.path.match(/\/regionMgmts/) && parseInt($route.query.is_deploy) == 0 ||-->
                <!--                            $route.path.match(/\/regionMgmts/) && parseInt($route.query.is_deploy) == 1 ||-->
                <!--                            $route.path.match(/\/objectMgmts/) ||-->
                <!--                            $route.path.match(/\/projects/) ||-->
                <!--                            $route.path.match(/\/vehicleMgmts/) ||-->
                <!--                            $route.path.match(/\/parkingLotMgmts/) ||-->
                <!--                            $route.path.match(/\/events/) ||-->
                <!--                            $route.path.match(/\/scheduledMissions/)-->
                <!--                        )}">-->
                <!--                    <a href="javascript:void(0)">-->
                <!--                        <i class="fa fa-car"></i>-->
                <!--                        <span>清消機器人</span>-->
                <!--                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>-->
                <!--                    </a>-->
                <!--                    <ul class="treeview-menu">-->
                <!--                        <li :class="{active: $route.fullPath.match(/\/regionMgmts/) && parseInt($route.query.is_deploy) == 0}">-->
                <!--                            <router-link :to="`/regionMgmts?is_deploy=0&${regionQuery}`">-->
                <!--                                <i class="fa fa-circle-o"></i> <span>{{ $t('GDC') }}</span>-->
                <!--                            </router-link>-->
                <!--                        </li>-->
                <!--                        <li :class="{active: $route.fullPath.match(/\/regionMgmts/) && parseInt($route.query.is_deploy) == 1}">-->
                <!--                            <router-link :to="`/regionMgmts?is_deploy=1&${regionQuery}`">-->
                <!--                                <i class="fa fa-circle-o"></i> <span>{{ $t('SR') }}</span>-->
                <!--                            </router-link>-->
                <!--                        </li>-->
                <!--                        <li :class="{active: $route.fullPath.match(/\/objectMgmts/)}">-->
                <!--                            <router-link to="/objectMgmts">-->
                <!--                                <i class="fa fa-circle-o"></i> <span>{{ $t('VM') }}</span>-->
                <!--                            </router-link>-->
                <!--                        </li>-->
                <!--                        <li class="treeview" :class="{active: (-->
                <!--                            $route.path.match(/\/projects/) ||-->
                <!--                            $route.path.match(/\/vehicleMgmts/) ||-->
                <!--                            $route.path.match(/\/parkingLotMgmts/) ||-->
                <!--                            $route.path.match(/\/events/) ||-->
                <!--                            $route.path.match(/\/scheduledMissions/)-->
                <!--                        )}">-->
                <!--                            <a href="javascript:void(0)">-->
                <!--                                <i class="fa fa-circle-o"></i>-->
                <!--                                <span>其他</span>-->
                <!--                                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>-->
                <!--                            </a>-->
                <!--                            <ul class="treeview-menu">-->
                <!--                                <li :class="{active: $route.fullPath.match(/\/projects/)}">-->
                <!--                                    <router-link to="/projects">-->
                <!--                                        <i class="fa fa-circle-o"></i>-->
                <!--                                        <span>{{ $t('projectManagement') }}</span>-->
                <!--                                    </router-link>-->
                <!--                                </li>-->
                <!--                                <li :class="{active: $route.fullPath.match(/\/vehicleMgmts/)}">-->
                <!--                                    <router-link to="/vehicleMgmts">-->
                <!--                                        <i class="fa fa-circle-o"></i>-->
                <!--                                        <span>{{ $t('amdrManagement') }}</span>-->
                <!--                                    </router-link>-->
                <!--                                </li>-->
                <!--                                <li :class="{active: $route.fullPath.match(/\/parkingLotMgmts/)}">-->
                <!--                                    <router-link to="/parkingLotMgmts">-->
                <!--                                        <i class="fa fa-circle-o"></i>-->
                <!--                                        <span>{{ $t('parkingManagement') }}</span>-->
                <!--                                    </router-link>-->
                <!--                                </li>-->
                <!--                                <li :class="{active: $route.fullPath.match(/\/events/)}">-->
                <!--                                    <router-link to="/events">-->
                <!--                                        <i class="fa fa-circle-o"></i>-->
                <!--                                        <span>事件列表</span>-->
                <!--                                    </router-link>-->
                <!--                                </li>-->
                <!--                                <li :class="{active: $route.fullPath.match(/\/scheduledMissions/)}">-->
                <!--                                    <router-link to="/scheduledMissions">-->
                <!--                                        <i class="fa fa-circle-o"></i>-->
                <!--                                        <span>排程任務</span>-->
                <!--                                    </router-link>-->
                <!--                                </li>-->
                <!--                            </ul>-->
                <!--                        </li>-->
                <!--                    </ul>-->
                <!--                </li>-->
                <li v-if="permissions.contains('pollution-map')" :class="{active: $route.fullPath.match(/\/floorRegionMgmts/) || $route.fullPath.match(/\/regionMgmts\/[1-9]/)}">
                    <router-link to="/floorRegionMgmts/1">
                        <i class="fa fa-map"></i>
                        <span>汙染地圖</span>
                    </router-link>
                </li>
                <li v-if="permissions.contains('parameter-settings') && permissions.contains(['suspended-exceeds-read', 'falling-exceeds-read', 'contact-exceeds-read', 'microparticle-exceeds-read'])"
                    :class="{active: $route.fullPath.match(/\/acceptanceGrades/)}">
                    <router-link to="/acceptanceGrades">
                        <i class="fa fa-cog"></i>
                        <span>參數設定</span>
                    </router-link>
                </li>
                <li v-if="permissions.contains('microbiological-data-entry') && permissions.contains('job-history-read')" :class="{active: $route.fullPath.match(/\/microOrganisms/) && $route.query.source !== '2'}">
                    <router-link to="/microOrganisms">
                        <i class="fa fa-tag"></i>
                        <span>數據輸入</span>
                    </router-link>
                </li>
                <li v-if="permissions.contains('data-analysis-table')" :class="{active: $route.fullPath.match(/\/dashboard/)}">
                    <router-link to="/dashboard">
                        <i class="fa fa-area-chart"></i>
                        <span>數據分析表</span>
                    </router-link>
                </li>
            </ul>
        </section>
    </aside>
</template>

<script>
import {mapGetters} from 'vuex';
import _ from 'lodash';

export default {
    name: "sidebar-view",
    computed: {
        ...mapGetters({
            roles: 'user/roles',
            permissions: 'user/permissions',
            device: 'device/device'
        }),
        regionQuery() {
            if(this.$route.path.contains('/regionMgmts')) {
                const query = _.pickBy(this.$route.query, _.identity(null));
                const params = _.pickBy(this.$route.params, _.identity(null));
                delete query.is_deploy;
                delete params.is_deploy;
                if(_.isEmpty(query)) {
                    if(_.isEmpty(params)) {
                        return '';
                    }
                    return new URLSearchParams(params).toString();
                }
                return new URLSearchParams(query).toString();
            } else {
                return '';
            }
        }
    },
    data() {
        return {}
    },
    mounted() {
        const trees = $('[data-widget="tree"]');
        trees.tree();

        setInterval(() => {
            const height = $('.admin-layout').innerHeight() - $('.main-header').innerHeight();
            $('.main-sidebar').height(height);
        }, 100);
    },
    methods: {
        showUserModal() {
            this.$store.commit('user/UPDATE_USER_MODAL', true);
        }
    }
}
</script>
