<template>
    <div class="container-fluid">
        <div class="region-area">
            <div ref="region" id="region">
                <div v-if="form.project.name && form.id" class="div-region" style="-webkit-transform-origin: 0 0">
                    <img v-if="mapType == 'radar'" id="imgMap" :src="`/images/${form.project.name}/background_${form.region}_preview.png?v=${updatedAt}`" alt="地圖" @load="onImageLoad">
                    <img v-else-if="mapType == 'cad'" id="imgMap" :src="`/images/${form.project.name}/cad_${form.region}.png?v=${updatedAt}`" alt="地圖" @load="onImageLoad">
                    <template v-if="isDeploy">
                        <div v-show="mqttCommand.start_goal_x_px && mqttCommand.start_goal_y_px" class="goal" :style="`top: ${mqttCommand.start_goal_y_px - 5}px; left: ${mqttCommand.start_goal_x_px - 5}px`"/>
                        <div v-show="mqttCommand.end_goal_x_px && mqttCommand.end_goal_y_px" class="goal" :style="`top: ${mqttCommand.end_goal_y_px - 5}px; left: ${mqttCommand.end_goal_x_px - 5}px`"/>
                        <div v-show="mqttCommand.goal_x_px && mqttCommand.goal_y_px" class="goal" :style="`top: ${mqttCommand.goal_y_px - 5}px; left: ${mqttCommand.goal_x_px - 5}px`"/>
                    </template>
                    <div v-if="form.origin_x_px && form.origin_y_px" class="origin-x-y" :style="`top: ${form.origin_y_px - 5}px; left: ${form.origin_x_px - 5}px`"/>
                    <vehicle-mgmt-list-view v-if="isDeploy" v-model="vehicleMgmts" :region-mgmt-id="form.id" :show-vehicle-points="true"/>
                    <vertex-list-view v-if="mapType == 'radar'"
                                      ref="vertices"
                                      v-model="form.vertices"
                                      v-show="!hideVertexAndEdge"
                                      :region-mgmt-id="form.id"
                                      :selected-ids.sync="selectedVertexIds"
                                      :is-all-unselected.sync="isAllVertexUnselected"
                                      :drag-pos.sync="dragVertexPos"
                                      :drag-edge-pos="dragEdgePos"
                                      :nearby-vertex.sync="nearbyVertex"
                                      :modal.sync="vertexModal"
                                      :scale="scale"
                                      :delete-id.sync="deleteVertexId"
                                      :region-mgmt="form"
                                      :search-vertex="searchVertex"
                                      :disabled="locked"
                                      :is-deploy="isDeploy"
                                      :loading.sync="vertexLoading"
                                      :map-type="mapType"/>
                    <location-list-view v-else-if="mapType == 'cad'"
                                        ref="locations"
                                        v-model="form.locations"
                                        v-show="!hideVertexAndEdge"
                                        :region-mgmt-id="form.id"
                                        :selected-ids.sync="selectedVertexIds"
                                        :is-all-unselected.sync="isAllVertexUnselected"
                                        :drag-pos.sync="dragVertexPos"
                                        :drag-edge-pos="dragEdgePos"
                                        :nearby-location.sync="nearbyLocation"
                                        :modal.sync="locationModal"
                                        :scale="scale"
                                        :delete-id.sync="deleteVertexId"
                                        :region-mgmt="form"
                                        :search-location="searchVertex"
                                        :disabled="locked"
                                        :is-deploy="isDeploy"
                                        :loading.sync="vertexLoading"
                                        :map-type="mapType"/>
                    <edge-list-view ref="edges"
                                    v-model="form.edges"
                                    v-show="!hideVertexAndEdge"
                                    :project-id="form.project_id"
                                    :region-mgmt-id="form.id"
                                    :drag-pos.sync="dragEdgePos"
                                    :drag-vertex-pos="dragVertexPos"
                                    :reset-vertex-pos-list="resetVertexPosList"
                                    :selected-vertex-ids.sync="selectedVertexIds"
                                    :is-all-vertex-unselected.sync="isAllVertexUnselected"
                                    :nearby-vertex="nearbyVertex"
                                    :delete-vertex-id="deleteVertexId"
                                    :scale="scale"
                                    :disabled="locked"
                                    :is-deploy="isDeploy"
                                    :loading.sync="edgeLoading"
                                    :modal.sync="edgeModal"
                                    :vertices="form.vertices"
                                    :vehicle-mgmts="vehicleMgmts"
                                    :map-type="mapType"/>
                </div>
            </div>
            <div class="container-left-top">
                <button v-show="!isDeploy" class="btn btn-lock" :disabled="(scale < 1) || !(form.project_id && form.id)" @click="locked = !locked">
                    <img :src="locked ? '/img/icon-locked.png' : '/img/icon-unlock.png'" alt="鎖定狀態" height="22">
                    <span class="pull-right" style="font-size: 13pt">{{ locked ? '&nbsp;已鎖定' : '&nbsp;未鎖定' }}</span>
                </button>
            </div>
            <div class="container-right-top">
                <div class="form-inline">
                    <template v-if="!isDeploy">
                        <button type="button" v-if="mapType == 'radar'" class="btn btn-light-green shadow" :class="{'animation-fade' : showBlackMask}" :disabled="sending || !(form.project_id && form.id) || (oldRegionMd5 == newRegionMd5) || loading || vertexLoading || edgeLoading" @click="submit(form.id)">儲存</button>
                        <button type="button" v-else-if="mapType == 'cad'" class="btn btn-light-green shadow" :class="{'animation-fade' : showBlackMask}" :disabled="sending || !(form.project_id && form.id) || (oldRegionMd5 == newRegionMd5) || loading || vertexLoading || edgeLoading" @click="submitLocation()">儲存</button>
                    </template>
                    <input class="shadow form-control" style="max-width: 120px" v-model="searchVertex" placeholder="搜尋站點" :disabled="!form.id"/>
                    <div v-if="!isDeploy" class="dropdown" style="display: inline-block">
                        <button class="btn btn-default shadow dropdown-toggle" type="button" id="dropdownMenuExport" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" :disabled="!form.id">
                            匯出
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuExport">
                            <li>
                                <a :href="`/${form.project_id}/graph_${form.id}.json`" target="_blank">下載&nbsp;Graph.json</a>
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="btn shadow" style="display: inline-block"
                            :class="{'btn-default' : hideVertexAndEdge, 'btn-light-green' : !hideVertexAndEdge}"
                            @click="hideVertexAndEdge = !hideVertexAndEdge" title="地圖路線">
                        地圖路線
                    </button>
                    <div class="btn-group shadow" role="group" style="display: inline-block">
                        <button class="btn btn-default" :disabled="scale >= 3" @click="scaleMap(true)">+</button>
                        <button class="btn btn-default" @click="scale = 1">{{ parseInt(scale * 100) }}%</button>
                        <button class="btn btn-default" :disabled="scale <= 0" @click="scaleMap(false)">-</button>
                    </div>
                    <!--房間名稱-->
                    <div v-if="roomName" class="room-name-block shadow">
                        <span class="room-name-title">房間名稱：</span>
                        <span class="room-name">{{ roomName }}</span>
                    </div>
                </div>
            </div>
            <div class="container-tag" v-if="isDeploy && form.id">
                <div class="tag-self" @click="openNewWidow(1000, 300,'parkingLotMgmts/window')" title="停車位狀態">停車位狀態</div>
                <div class="tag-self" @click="openNewWidow(1000, 300,'vehicleMgmts/window')" title="車輛狀態列表">車輛狀態列表</div>
                <div class="tag-self" @click="openNewWidow(1000, 300,'cleanAreas/window')" title="清潔區域狀態列表" style="width: 160px">清潔區域狀態列表</div>
                <div class="tag-self" @click="openNewWidow(1000, 300,'elevatorMgmts/window')" title="電梯狀態列表">電梯狀態列表</div>
                <div class="tag-self" @click="openNewWidow(1000, 300,'mqttCommands/window')" title="任務歷史紀錄">任務歷史紀錄</div>
            </div>
            <div v-if="form.id && form.project_id && isFloor" class="btn-group btn-group-sm bottom-bar-map-type">
                <button type="button" class="btn btn-default" :class="{'active' : mapType == 'radar'}" :disabled="imgNotFound" @click="onChangeMapType('radar')">雷達圖</button>
                <button type="button" class="btn btn-default" :class="{'active' : mapType == 'cad'}" :disabled="!form.is_exist_cad" @click="onChangeMapType('cad')">CAD圖</button>
            </div>
            <div class="container-bottom">
                <div class="row bottom-bar">
                    <div class="col-xs-4 text-left">
                        <button v-if="!isDeploy && mapType != 'cad'" class="btn btn-clean-all-edge" @click="deleteEdges" title="清除所有軌道">
                            <i class="glyphicon glyphicon-trash" style="font-size: 10pt"/>
                            清除所有軌道
                        </button>
                        <div v-if="form.id" class="file-md5">
                            graph: {{ isDeploy ? form.deploy_background_file_md5 : form.undeploy_background_file_md5 }}<br/>
                            route: {{ isDeploy ? form.deploy_route_file_md5 : form.undeploy_route_file_md5 }}
                        </div>
                        <div v-if="form.id && isDeploy && cleanStatus && isFloor" class="clean-station-status">
                            自淨站(自動門): {{ cleanStatus.door_status == 'open' ? '已開門' : '已關門' }}<br/>
                            自淨站(電動缸): {{ cleanStatus.cylinder_status == 'stretch' ? '已伸出' : '已縮回' }}
                        </div>
                    </div>
                    <div class="col-xs-8 text-right">
                        <template v-if="form.id">
                            <div class="scale-refer-block">
                                <div class="scale-refer" style="width:70px"/>
                                <span style="color: #5c9b6c; margin:0 14px 0 0">{{ (form.mm ? parseInt(form.mm) / 1000 * 70 / scale : 0.264583 * 70 / scale).toFixed(0) }}&nbsp;m&lt;米&gt;</span>
                            </div>
                        </template>
                        <template v-if="isDeploy && isFloor">
                            <vehicle-mgmt-list-select v-model="vehicleId"
                                                      class="vehicle-mgmt-list-select"
                                                      :disabled="!form.id"/>
                            <span v-if="vehicleMgmt.vehicle_status && vehicleMgmt.vehicle_status.battery_status" class="battery">電量：
                                {{ vehicleMgmt.vehicle_status.battery_status }}
                            </span>
                            <div class="dropup">
                                <button class="btn btn-light-green toolbar-btn dropdown-toggle" type="button" id="cleanStationDropUpMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        :style="`background: ${([43, 44].contains(mqttCommand.mqtt_command_type_id)) ? 'orange': ''}`" :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id">
                                    <template v-if="mqttCommand.mqtt_command_type_id == 43 && mqttCommandSendCommand.data.action == 'stretch'">
                                        <i class="fa fa-long-arrow-right"/> 伸出電動缸
                                    </template>
                                    <template v-else-if="mqttCommand.mqtt_command_type_id == 43 && mqttCommandSendCommand.data.action == 'retract'">
                                        <i class="fa fa-long-arrow-left"/> 縮回電動缸
                                    </template>
                                    <template v-else-if="mqttCommand.mqtt_command_type_id == 44 && mqttCommandSendCommand.data.action == 'open'">
                                        <i class="fa fa-window-maximize" style="width: 16px"/> 開門
                                    </template>
                                    <template v-else-if="mqttCommand.mqtt_command_type_id == 44 && mqttCommandSendCommand.data.action == 'close'">
                                        <i class="fa fa-window-close" style="width: 16px"/> 關門
                                    </template>
                                    <template v-else>
                                        <i class="fa fa-recycle" style="width: 16px"/> 自淨站
                                    </template>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="cleanStationDropUpMenu">
                                    <li>
                                        <mqtt-command-button ref="mqtt43Stretch"
                                                             element-type="a"
                                                             :element-style="`${(mqttCommand.mqtt_command_type_id == 43) ? 'background: orange; color: white;': ''}`"
                                                             title="伸出電動缸"
                                                             action="stretch"
                                                             :vehicle-id="vehicleId"
                                                             :mqtt-command-type-id="43"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="directSubmit($refs.mqtt43Stretch)"
                                                             @reset="reset">
                                            <i class="fa fa-long-arrow-right" style="width: 16px"/> 伸出電動缸
                                        </mqtt-command-button>
                                    </li>
                                    <li>
                                        <mqtt-command-button ref="mqtt43Retract"
                                                             element-type="a"
                                                             :element-style="`${(mqttCommand.mqtt_command_type_id == 43) ? 'background: orange; color: white;': ''}`"
                                                             title="縮回電動缸"
                                                             action="retract"
                                                             :vehicle-id="vehicleId"
                                                             :mqtt-command-type-id="43"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="directSubmit($refs.mqtt43Retract)"
                                                             @reset="reset">
                                            <i class="fa fa-long-arrow-left" style="width: 16px"/> 縮回電動缸
                                        </mqtt-command-button>
                                    </li>
                                    <li>
                                        <mqtt-command-button ref="mqtt44Open"
                                                             element-type="a"
                                                             :element-style="`${mqttCommand.mqtt_command_type_id == 44 ? 'background: orange; color: white;': ''}`"
                                                             title="開門"
                                                             action="open"
                                                             :vehicle-id="vehicleId"
                                                             :mqtt-command-type-id="44"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="directSubmit($refs.mqtt44Open)"
                                                             @reset="reset">
                                            <i class="fa fa-window-maximize" style="width: 16px"/> 開門
                                        </mqtt-command-button>
                                    </li>
                                    <li>
                                        <mqtt-command-button ref="mqtt44Close"
                                                             element-type="a"
                                                             :element-style="`${mqttCommand.mqtt_command_type_id == 44 ? 'background: orange; color: white;': ''}`"
                                                             title="關門"
                                                             action="close"
                                                             :vehicle-id="vehicleId"
                                                             :mqtt-command-type-id="44"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="directSubmit($refs.mqtt44Close)"
                                                             @reset="reset">
                                            <i class="fa fa-window-close" style="width: 16px"/> 關門
                                        </mqtt-command-button>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropup">
                                <button class="btn btn-light-green toolbar-btn dropdown-toggle" type="button" id="cleanDropUpMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        :style="`background: ${(mqttCommand.mqtt_command_type_id == 2 || mqttCommand.mqtt_command_type_id == 4 || mqttCommand.mqtt_command_type_id == 13) ? 'orange': ''}`" :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id">
                                    <template v-if="mqttCommand.mqtt_command_type_id == 2">
                                        <i class="glyphicon glyphicon-search"/> 偵測
                                    </template>
                                    <template v-else-if="mqttCommand.mqtt_command_type_id == 4 && mqttCommand.laser_detection == 1">
                                        <i class="fa fa-paint-brush" style="width: 16px"/> 清掃 +
                                        <i class="glyphicon glyphicon-search"/> 偵測
                                    </template>
                                    <template v-else-if="mqttCommand.mqtt_command_type_id == 13">
                                        <i class="fa fa-paint-brush" style="width: 16px"/> 全域清掃
                                    </template>
                                    <template v-else><i class="fa fa-paint-brush" style="width: 16px"/> 清掃</template>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="cleanDropUpMenu">
                                    <li>
                                        <mqtt-command-button ref="mqtt4on"
                                                             element-type="a"
                                                             :element-style="`${(mqttCommand.mqtt_command_type_id == 4 && mqttCommand.laser_detection == 1) ? 'background: orange; color: white;': ''}`"
                                                             title="清掃+偵測"
                                                             laser-detection="on"
                                                             :vehicle-id="vehicleId"
                                                             :mqtt-command-type-id="4"
                                                             :x="mqttCommand.start_goal_x_px"
                                                             :y="mqttCommand.start_goal_y_px"
                                                             :x2="mqttCommand.end_goal_x_px"
                                                             :y2="mqttCommand.end_goal_y_px"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="generateGoal($refs.mqtt4on, true, 1, 4)"
                                                             @reset="reset">
                                            <i class="fa fa-paint-brush" style="width: 16px"/> 清掃 +
                                            <i class="glyphicon glyphicon-search"/> 偵測
                                        </mqtt-command-button>
                                    </li>
                                    <li>
                                        <mqtt-command-button ref="mqtt4off"
                                                             element-type="a"
                                                             :element-style="`${mqttCommand.mqtt_command_type_id == 4 && mqttCommand.laser_detection === 0 ? 'background: orange; color: white;': ''}`"
                                                             title="清掃"
                                                             :vehicle-id="vehicleId"
                                                             :mqtt-command-type-id="4"
                                                             :x="mqttCommand.start_goal_x_px"
                                                             :y="mqttCommand.start_goal_y_px"
                                                             :x2="mqttCommand.end_goal_x_px"
                                                             :y2="mqttCommand.end_goal_y_px"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="generateGoal($refs.mqtt4off, true, 0, 4)"
                                                             @reset="reset">
                                            <i class="fa fa-paint-brush" style="width: 16px"/> 清掃
                                        </mqtt-command-button>
                                    </li>
                                    <li>
                                        <mqtt-command-button ref="mqtt2"
                                                             element-type="a"
                                                             :element-style="`${mqttCommand.mqtt_command_type_id == 2 && mqttCommand.laser_detection == 1 ? 'background: orange; color: white;': ''}`"
                                                             title="偵測"
                                                             :vehicle-id="vehicleId"
                                                             laser-detection="on"
                                                             :mqtt-command-type-id="2"
                                                             :x="mqttCommand.goal_x_px"
                                                             :y="mqttCommand.goal_y_px"
                                                             :region-mgmt-id="form.id"
                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                             @click="generateGoal($refs.mqtt2, false, 1, 2)"
                                                             @reset="reset">
                                            <i class="glyphicon glyphicon-search"/> 偵測
                                        </mqtt-command-button>
                                    </li>
                                    <!--                                    TODO Open in the next stage.-->
                                    <!--                                    <li role="separator" class="divider"></li>-->
                                    <!--                                    <li>-->
                                    <!--                                        <mqtt-command-button ref="mqtt13"-->
                                    <!--                                                             element-type="a"-->
                                    <!--                                                             :element-style="`${mqttCommand.mqtt_command_type_id == 13 ? 'background: orange; color: white;': ''}`"-->
                                    <!--                                                             title="全域清掃"-->
                                    <!--                                                             :vehicle-id="vehicleId"-->
                                    <!--                                                             :mqtt-command-type-id="13"-->
                                    <!--                                                             :x="mqttCommand.start_goal_x_px"-->
                                    <!--                                                             :y="mqttCommand.start_goal_y_px"-->
                                    <!--                                                             :region-mgmt-id="form.id"-->
                                    <!--                                                             :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"-->
                                    <!--                                                             @click="generateGoal($refs.mqtt13, false, 0, 13)"-->
                                    <!--                                                             @reset="reset">-->
                                    <!--                                            <i class="fa fa-paint-brush" style="width: 16px"/> 全域清掃-->
                                    <!--                                        </mqtt-command-button>-->
                                    <!--                                    </li>-->
                                </ul>
                            </div>
                            <template v-if="vehicleMgmt.chargeable">
                                <div class="dropup">
                                    <button class="btn btn-light-green toolbar-btn dropdown-toggle" type="button" id="chargeDropUpMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            :style="`background: ${(mqttCommand.mqtt_command_type_id == 5 || mqttCommand.mqtt_command_type_id == 6) ? 'orange': ''}`" :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id">
                                        <template v-if="mqttCommand.mqtt_command_type_id == 6">
                                            <i class="fa fa-bolt" style="position: relative; width: 16px"><i class="fa fa-remove" style="position: absolute; top: 6px; left: 10px; font-size: 8px"/></i>&nbsp;&nbsp;停止充電
                                        </template>
                                        <template v-else><i class="fa fa-bolt" style="width: 16px"/> 充電</template>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="chargeDropUpMenu">
                                        <li>
                                            <mqtt-command-button ref="mqtt5"
                                                                 element-type="a"
                                                                 :element-style="`${mqttCommand.mqtt_command_type_id == 5 ? 'background: orange; color: white;': ''}`"
                                                                 title="充電"
                                                                 :vehicle-id="vehicleId"
                                                                 :mqtt-command-type-id="5"
                                                                 :x="mqttCommand.goal_x_px"
                                                                 :y="mqttCommand.goal_y_px"
                                                                 :region-mgmt-id="form.id"
                                                                 :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                                 @click="generateGoal($refs.mqtt5, false, 0, 5)"
                                                                 @reset="reset">
                                                <i class="fa fa-bolt" style="width: 16px"/> 充電
                                            </mqtt-command-button>
                                        </li>
                                        <li>
                                            <mqtt-command-button ref="mqtt6"
                                                                 element-type="a"
                                                                 :element-style="`${mqttCommand.mqtt_command_type_id == 6 ? 'background: orange; color: white;': ''}`"
                                                                 title="停止充電"
                                                                 :vehicle-id="vehicleId"
                                                                 :mqtt-command-type-id="6"
                                                                 :region-mgmt-id="form.id"
                                                                 :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                                 @click="stopCharge($refs.mqtt6)">
                                                <i class="fa fa-bolt" style="position: relative; width: 16px"><i class="fa fa-remove" style="position: absolute; top: 6px; left: 10px; font-size: 8px"/></i>&nbsp;&nbsp;停止充電
                                            </mqtt-command-button>
                                        </li>
                                    </ul>
                                </div>
                            </template>
                            <mqtt-command-button ref="mqtt2off"
                                                 root-element-class="inline-block"
                                                 element-class="btn btn-light-green init-btn"
                                                 :element-style="`background: ${mqttCommand.mqtt_command_type_id == 11 ? 'orange': ''}`"
                                                 title="移動"
                                                 :vehicle-id="vehicleId"
                                                 :laser-detection="mqttCommand.laser_detection"
                                                 :mqtt-command-type-id="11"
                                                 :x="mqttCommand.goal_x_px"
                                                 :y="mqttCommand.goal_y_px"
                                                 :region-mgmt-id="form.id"
                                                 :theta="mqttCommand.theta"
                                                 unit="m"
                                                 :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                 @click="mqttCmdModal.show = true"
                                                 @reset="reset">
                                <i class="glyphicon glyphicon-move"/> 移動
                            </mqtt-command-button>
                            <mqtt-command-button ref="mqtt3"
                                                 root-element-class="inline-block"
                                                 element-class="btn btn-light-green init-btn"
                                                 :element-style="`background: ${mqttCommand.mqtt_command_type_id == 3 ? 'orange': ''}`"
                                                 title="補水"
                                                 :vehicle-id="vehicleId"
                                                 :mqtt-command-type-id="3"
                                                 :device-name="mqttCommand.device_name"
                                                 :region-mgmt-id="form.id"
                                                 :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                 @click="selectVertex($refs.mqtt3)"
                                                 @reset="reset">
                                <i class="glyphicon glyphicon-tint"/> 補水
                            </mqtt-command-button>
                            <mqtt-command-button ref="mqtt12"
                                                 root-element-class="inline-block"
                                                 element-class="btn btn-light-green init-btn"
                                                 title="重設"
                                                 :vehicle-id="vehicleId"
                                                 :mqtt-command-type-id="12"
                                                 :region-mgmt-id="form.id"
                                                 :disabled="!vehicleId || mqttCommand.sending || !!mqttCommand.command_id"
                                                 @click="directSubmit($refs.mqtt12)">
                                <i class="glyphicon glyphicon-refresh"/> 重設
                            </mqtt-command-button>
                            <mqtt-command-button ref="mqtt7"
                                                 root-element-class="inline-block"
                                                 element-class="btn btn-light-green init-btn"
                                                 title="停止"
                                                 :vehicle-id="vehicleId"
                                                 :mqtt-command-type-id="7"
                                                 :uuid="mqttCommand.command_id"
                                                 :region-mgmt-id="form.id"
                                                 :disabled="mqttCommand.stop_disabled"
                                                 @click="directSubmit($refs.mqtt7)">
                                <i class="glyphicon glyphicon-ban-circle"/> 停止
                                <template v-if="mqttCommand.remain_stop_second > 0 && mqttCommand.remain_stop_second < vehicleMgmt.stoppable_second + 1">
                                    ({{ mqttCommand.remain_stop_second }})
                                </template>
                            </mqtt-command-button>
                        </template>
                        <template v-else>
                            <div class="div-background">
                                <vertex-type-select class="list-select" style="width: 164px" title="請選擇站點類型"
                                                    v-model="vertexTypeId" defaultText="請選擇" :vertex-type-id="allowVertexTypeId"/>
                            </div>
                            <vertex-point v-if="vertexTypeId" class="vertex-point-container" :vertexTypeId="vertexTypeId" :scale="scale" @stop="checkVertexPosition" @mouseup="onMouseUp"/>
                        </template>
                    </div>
                </div>
            </div>
            <div class="container-right" :class="showRegion ? '' : 'container-right-collapsed'" :style="showRegion ? 'z-index: 2' : 'z-index: 1'">
                <div class="region-tag" :class="showRegion ? '' : 'burner-collapsed'" @click="showRegion = !showRegion">
                    {{ showRegion ? '&nbsp;&gt;' : '&nbsp;&lt;' }}區域名稱
                </div>
                <div class="col-md-12 project-list">
                    <div class="text-center project-list-title">專案區域列表</div>
                    <project-list-select v-model="form.project_id"
                                         :is-deploy="isDeploy"
                                         :disabled="!!(isDeploy && form.project_id)"
                                         @change="onProjectChange"/>
                    <div class="region-floor-room-collapse-select">
                        <region-mgmt-list-collapse-select v-if="form.project_id"
                                                          v-model="form.id"
                                                          :project-id.sync="form.project_id"
                                                          :is-deploy="isDeploy"
                                                          :enable-confirm="unsaved"/>
                    </div>
                </div>
                <template v-if="!isDeploy">
                    <div class="region-operate">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-light-green" title="更新區域" :disabled="!form.id" @click="showModal(form.id)">
                                <i class="glyphicon glyphicon-pencil"/> 更新
                            </button>
                            <button class="btn btn-light-green" title="刪除區域" :disabled="!form.id" @click="deleteRegion">
                                <i class="glyphicon glyphicon-trash"/> 刪除
                            </button>
                            <button class="btn btn-light-green" @click="showModal(null)" title="新增區域">
                                <i class="glyphicon glyphicon-upload"/> 新增
                            </button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </template>
            </div>
            <div v-if="!isDeploy && mapType == 'radar'" class="container-right-project-deploy" :class="showProjectDeploy ? '' : 'container-right-project-deploy-collapsed'" :style="showProjectDeploy ? 'z-index: 2' : 'z-index: 1'">
                <div class="burner-tag" :class="showProjectDeploy ? '' : 'burner-collapsed'" @click="showProjectDeployList">
                    {{ showProjectDeploy ? '&gt;' : '&lt;' }}燒入車輛
                </div>
                <project-deploy-view ref="projectDeploy" v-model="form.project.name" :unsaved="unsaved" :show-guide.sync="showBlackMask"/>
            </div>
            <div v-if="isDeploy" class="container-right-display" :class="(vertexModal.show || locationModal.show) ? '' : 'container-right-display-collapsed'" :style="(vertexModal.show || locationModal.show) ? 'z-index: 2' : 'z-index: 1'">
                <div class="show-info" :style="`${(vertexModal.show || locationModal.show) ? '' : 'right: -40px'}`" @click="vertexModal.id ? vertexModal.show = !vertexModal.show : locationModal.id ? locationModal.show = !locationModal.show : vertexModal.show = locationModal.show = false">
                    {{ (vertexModal.show || locationModal.show) ? '&nbsp;&gt;' : '&nbsp;&lt;' }}站點資訊
                </div>
                <vertex-modal v-if="mapType == 'radar'" v-model="vertexModal.show"
                              :vertex.sync="vertexModal.vertex"
                              :nearby-vertices="vertexModal.nearbyVertices"
                              :region-mgmt-id="form.id"
                              :is-deploy="isDeploy"/>
                <location-modal v-else-if="mapType == 'cad'" v-model="locationModal.show"
                                :location.sync="locationModal.location"
                                :nearby-vertices="locationModal.nearbyVertices"
                                :room="roomName"
                                :is-deploy="isDeploy"/>
            </div>
            <div v-if="isDeploy" class="container-right-display" :class="edgeModal.show ? '' : 'container-right-display-collapsed'" :style="edgeModal.show ? 'z-index: 2' : 'z-index: 1'">
                <div class="show-info" :style="`${edgeModal.show ? '' : 'right: -40px'}`" @click="edgeModal.show = !edgeModal.show">
                    {{ edgeModal.show ? '&nbsp;&gt;' : '&nbsp;&lt;' }}軌道資訊
                </div>
                <edge-modal v-model="edgeModal.show"
                            :edge="edgeModal.edge"
                            :is-deploy="isDeploy"
                            :vertices="edgeModal.vertices"
                            @update="updateEdge"/>
            </div>
            <mqtt-command-modal v-model="mqttCmdModal.show"
                                @submit="(mqttCmdForm)=>generateGoal($refs.mqtt2off, false, $refs.mqtt2off.form.laser_detection, $refs.mqtt2off.form.mqtt_command_type_id, mqttCmdForm)"/>
            <div v-if="showBlackMask" class="view-mask">
                <div v-if="showBlackMaskMsg" class="route-planing">路線規劃中，請稍候......</div>
            </div>
            <div v-if="(!vertexModal.show || !locationModal.show) && isDeploy && isFloor" class="dust-view">
                <laser-dust-view :vehicle-id="vehicleId"/>
            </div>
        </div>
        <region-mgmt-modal v-model="modal.show"
                           :rid="modal.id"
                           @update="resetRegion"/>
        <vertex-list-modal v-model="vertexListModal.show" :vertices="vertexListModal.vertices">
            <template v-slot:head>
                <th class="text-center" style="width: 130px"> 區域</th>
                <th class="text-center" style="width: 130px"> 站點名稱</th>
                <th>原因</th>
            </template>
            <template v-slot:body="slotProps">
                <tr>
                    <td class="text-center">
                        {{ slotProps.vertex.region_mgmt.region }}
                    </td>
                    <td class="text-center">
                        {{ slotProps.vertex.name }}
                    </td>
                    <td class="red">站點已出現在其他區域</td>
                </tr>
            </template>
        </vertex-list-modal>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';
import {md5} from 'js-md5';
import moment from 'moment';
import ResizeSensor from 'resize-sensor';
import RegionMgmtModal from './RegionMgmtModal';
import RegionMgmtListCollapseSelect from './RegionMgmtListCollapseSelect.vue';
import ProjectDeployView from '../ProjectDeploy/ProjectDeployView.vue';
import ProjectListSelect from '../Project/ProjectListSelect.vue';
import VertexTypeSelect from '../VertexType/VertexTypeSelect';
import VertexPoint from '../Vertex/VertexPoint';
import VertexListView from '../Vertex/VertexListView';
import VertexModal from '../Vertex/VertexModal';
import VertexListModal from '../Vertex/VertexListModal.vue';
import EdgeListView from '../Edge/EdgeListView';
import EdgeModal from '../Edge/EdgeModal.vue';
import VehicleMgmtListView from '../VehicleMgmt/VehicleMgmtListView.vue';
import VehicleMgmtListSelect from '../VehicleMgmt/VehicleMgmtListSelect.vue';
import MqttCommandButton from '../MqttCommand/MqttCommandButton';
import MqttCommandModal from '../MqttCommand/MqttCommandModal.vue';
import LaserDustView from '../LaserDust/LaserDustView.vue';
import LocationListView from '../Location/LocationListView.vue';
import LocationModal from '../Location/LocationModal.vue';

let afterSubmitDisabledTimer;
let moved, unwatchSelectedVertexIds, moveListener;
let downListener = () => {
    moved = false;
};
let upListener = () => {
    if(!moved) {
        alert('請選擇站點！');
    }
};
let mousemoveCb, cb, intervalCb;

export default {
    name: "RegionMgmtListPage",
    components: {
        LocationModal,
        LocationListView,
        LaserDustView,
        MqttCommandModal,
        MqttCommandButton,
        VehicleMgmtListSelect,
        VehicleMgmtListView,
        EdgeModal,
        EdgeListView,
        VertexListModal,
        VertexModal,
        VertexListView,
        VertexPoint,
        VertexTypeSelect,
        ProjectListSelect,
        ProjectDeployView,
        RegionMgmtListCollapseSelect,
        RegionMgmtModal
    },
    computed: {
        updatedAt() {
            if(this.form && this.form.updated_at) {
                return moment(this.form.updated_at).format('YYYYMMDDHHmmss');
            } else {
                return '';
            }
        },
        vehicleMgmt() {
            const that = this;
            const vehicleMgmt = _.find(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            if(!vehicleMgmt) {
                return this.emptyVehicleMgmt();
            }
            if(!vehicleMgmt.mqtt_command) {
                vehicleMgmt.mqtt_command = this.emptyMqttCommand();
            }

            return vehicleMgmt;
        },
        mqttCommand() {
            if(!this.vehicleMgmt) {
                return this.emptyMqttCommand();
            }
            if(!this.vehicleMgmt.mqtt_command) {
                return this.emptyMqttCommand();
            }

            return this.vehicleMgmt.mqtt_command;
        },
        mqttCommandSendCommand() {
            if(!this.mqttCommand || !this.mqttCommand.send_command) {
                return this.emptyMqttCommandSendCommand();
            }
            return JSON.parse(this.mqttCommand.send_command);
        },
        urlState() {
            let urlState = `?is_deploy=${this.isDeploy}`;
            if(this.form.project_id) {
                urlState += `&project_id=${this.form.project_id}`;
                if(this.form.id) {
                    urlState += `&region_mgmt_id=${this.form.id}`;
                }
            }
            return urlState;
        },
        isDeploy() {
            return this.$route.query.is_deploy ? parseInt(this.$route.query.is_deploy) : 1;
        },
        sendForm() {
            return {
                vertices: _.map(this.form.vertices, (r) => {
                    delete r.is_selected;
                    delete r.is_nearby;
                    delete r.nearby_vertex_length;
                    delete r.vertex_type;
                    delete r.is_show;
                    return r;
                }),
                edges: _.map(this.form.edges, (r) => {
                    delete r.x1;
                    delete r.y1;
                    delete r.dragging_x1;
                    delete r.dragging_y1;
                    delete r.x2;
                    delete r.y2;
                    delete r.dragging_x2;
                    delete r.dragging_y2;
                    delete r.degrees;
                    delete r.length;
                    delete r.xMid;
                    delete r.yMid;
                    return r;
                }),
                locations: _.map(this.form.locations, (r) => {
                    return r;
                })
            }
        },
        newRegionMd5() {
            let hash = md5.create();
            hash.update(JSON.stringify(this.sendForm));
            return hash.hex();
        },
        unsaved() {
            return !this.isDeploy && this.oldRegionMd5 && this.oldRegionMd5 != this.newRegionMd5;
        },
        imgNotFound() {
            if(this.isDeploy) {
                return !this.form.is_exist_background;
            } else {
                return !this.form.is_exist_preview_background;
            }
        },
        isFloor() {
            return !!this.form.floor_region_mgmt_id;
        },
        allowVertexTypeId() {
            return this.mapType == 'cad' ? 4 : null;
        },
        roomName() {
            if(!this.form || !this.form.room_environment || !this.form.room_environment.room_name) {
                return null;
            }
            return this.form.room_environment.room_name;
        }
    },
    beforeRouteLeave(to, from, next) {
        if(this.unsaved) {
            if(confirm('資料尚未儲存，確定要離開此畫面？')) {
                next();
            }
        } else {
            next();
        }
    },
    watch: {
        'form.id': {
            async handler(newVal, oldVal) {
                this.hideVertexAndEdge = newVal == null;
                if(this.vertexModal.show) {
                    this.vertexModal.show = false;
                    this.vertexModal.id = null;
                }
                if(this.locationModal.show) {
                    this.locationModal.show = false;
                    this.locationModal.id = null;
                }
                window.history.replaceState(null, null, this.urlState);
                const route = Object.assign({}, this.$route);
                route.fullPath = `${this.$route.path}${this.urlState}`;
                route.params = {
                    is_deploy: this.isDeploy,
                    project_id: this.form.project_id,
                    region_mgmt_id: this.form.id
                };
                this.$router.history.cb(route);
                localStorage.redirect = this.$route.fullPath;
                await this.fetchData(oldVal);
            }
        },
        scale(newVal) {
            this.locked = newVal < 1;
            this.resizeContentHeight();
        },
        locked(newVal) {
            if(newVal) {
                this.showBlackMask = false;
            }
        },
        'vertexModal.show': {
            handler(newVal) {
                if(!this.isDeploy || !newVal) {
                    return;
                }
                this.showProjectDeploy = false;
                this.showRegion = false;
                this.edgeModal.show = false;
            }, deep: true
        },
        'locationModal.show': {
            handler(newVal) {
                if(!this.isDeploy || !newVal) {
                    return;
                }
                this.showProjectDeploy = false;
                this.showRegion = false;
                this.edgeModal.show = false;
            }, deep: true
        },
        'edgeModal.show': {
            handler(newVal) {
                if(!this.isDeploy || !newVal) {
                    return;
                }
                this.showProjectDeploy = false;
                this.showRegion = false;
                this.vertexModal.show = false;
                this.locationModal.show = false;
            }, deep: true
        },
        vehicleId() {
            this.showBlackMask = false;
            this.showBlackMaskMsg = false;
        }
    },
    data() {
        return {
            loading: false,
            sending: false,
            form: {
                id: this.$route.query.region_mgmt_id ? parseInt(this.$route.query.region_mgmt_id) : null,
                region: null,
                project_id: this.$route.query.project_id ? parseInt(this.$route.query.project_id) : null,
                vertices: [],
                locations: [],
                edges: [],
                mm: 0,
                resolution: 0,
                img_width: 0,
                img_height: 0,
                updated_at: null,
                origin_x: null,
                origin_y: null,
                origin_x_px: null,
                origin_y_px: null,
                mqtt_command: {
                    command_id: null
                },
                project: {
                    name: null
                }
            },
            regionMgmts: [],
            modal: {
                id: null,
                show: false
            },
            selectedVertexIds: [],
            isAllVertexUnselected: false,
            dragVertexPos: {
                x: -1,
                y: -1,
                vertex_id: null
            },
            resetVertexPosList: [],
            dragEdgePos: {
                x: -1,
                y: -1
            },
            nearbyVertex: null,
            nearbyLocation: null,
            scale: 1,
            edgeModal: {
                show: false,
                vertices: [],
                edge: null
            },
            vertexModal: {
                show: false,
                id: null,
                nearbyVertices: []
            },
            vertexListModal: {
                show: false,
                vertices: []
            },
            locationModal: {
                show: false,
                id: null,
                nearbyVertices: []
            },
            showProjectDeploy: false,
            showRegion: false,
            hideVertexAndEdge: false,
            deleteVertexId: null,
            vertexTypeId: null,
            locked: false,
            showBlackMask: false,
            showBlackMaskMsg: false,
            searchVertex: null,
            vertexLoading: false,
            edgeLoading: false,
            oldRegionMd5: null,
            vehicleMgmts: [],
            vehicleId: null,
            mqttCmdModal: {
                show: false
            },
            cleanStatus: {},
            mapType: 'radar'
        };
    },
    created() {
        const that = this;
        if(this.$route.query.region_mgmt_id) {
            this.fetchData();
            this.fetchCleanStatusData();
            window.Echo.private('rabbitmq').listen('MqttCommandUpdated', (res) => {
                const mqttCommand = res.mqttCommand;
                const receiveCommand = JSON.parse(mqttCommand.receive_command);
                const vehicleMgmtIdx = _.findIndex(that.vehicleMgmts, (r) => {
                    return r.vehicle_id == mqttCommand.vehicle_id;
                });
                if(!receiveCommand) {
                    return;
                }
                switch(receiveCommand.typename) {
                    case 'on_vehicle_update_afs_path':
                        if(!mqttCommand.vehicle_mgmt || !mqttCommand.vehicle_mgmt.clean_area || !mqttCommand.vehicle_mgmt.clean_area.turning_points) {
                            return;
                        }
                        that.$set(that.vehicleMgmts[vehicleMgmtIdx], 'clean_area', mqttCommand.vehicle_mgmt.clean_area);
                        that.$set(that.vehicleMgmts[vehicleMgmtIdx].clean_area, 'routes', []);
                        for(let i = 1; i < that.vehicleMgmts[vehicleMgmtIdx].clean_area.turning_points.length; i++) {
                            that.vehicleMgmts[vehicleMgmtIdx].clean_area.routes.push({
                                start: that.vehicleMgmts[vehicleMgmtIdx].clean_area.turning_points[i - 1],
                                end: that.vehicleMgmts[vehicleMgmtIdx].clean_area.turning_points[i]
                            });
                        }
                        that.showBlackMask = false;
                        that.showBlackMaskMsg = false;
                        break;
                    case 'on_transfer_completed':
                    case 'on_vehicle_discharged':
                        if(vehicleMgmtIdx == -1) {
                            return;
                        }
                        that.vehicleMgmts[vehicleMgmtIdx].stoppable_second = mqttCommand.stoppable_second;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command = that.emptyMqttCommand();
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.remain_stop_second = mqttCommand.stoppable_second + 1;
                        that.vehicleMgmts[vehicleMgmtIdx].clean_area = null;
                        that.$nextTick(() => {
                            that.vehicleMgmts[vehicleMgmtIdx].theta = null;
                        });
                        break;
                    case 'set_door_completed':
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command = that.emptyMqttCommand();
                        this.$toast.success({
                            title: '成功訊息',
                            message: '自動門設定完成'
                        });
                        break;
                    case 'set_cylinder_completed':
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command = that.emptyMqttCommand();
                        this.$toast.success({
                            title: '成功訊息',
                            message: '電動缸設定完成'
                        });
                        break;
                }
            });
            window.Echo.private(`cleanStatuses`).listen('CleanStatusUpdated', (res) => {
                const cleanStatus = res.cleanStatus;
                if(cleanStatus.cleanstation_ID == that.cleanStatus.cleanstation_ID) {
                    that.cleanStatus = cleanStatus;
                }
            });
        }
        window.onbeforeunload = function(event) {
            if(that.unsaved) {
                event.preventDefault();
                event.returnValue = '';
                return '';
            }
        };
    },
    mounted() {
        const that = this;
        new ResizeSensor($('html'), function() {
            that.resizeContentHeight();
        });
    },
    destroyed() {
        window.Echo.leave(`regionMgmts.display.${this.form.id}`);
        window.Echo.leave('rabbitmq');
        window.Echo.leave(`cleanStatuses`);
    },
    methods: {
        async fetchData(oldRegionMgmtId = null) {
            if(this.isDeploy) {
                if(oldRegionMgmtId) {
                    window.Echo.leave(`regionMgmts.${oldRegionMgmtId}`);
                }
            } else {
                this.$store.commit('UPDATE_REGION_MGMT_ID', this.form.id);
            }

            if(!this.form.id) {
                this.resetForm();
                this.form.is_exist_preview_background = false;
                this.form.is_exist_background = false;
                return;
            }

            this.oldRegionMd5 = this.newRegionMd5;
            const that = this;
            this.loading = true;
            try {
                let res = await axios.get(`/api/regionMgmts/${this.form.id}`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    const form = data.regionMgmt;
                    if(this.form.id != form.id) {
                        return;
                    }
                    form.mm = parseInt(form.mm);
                    form.project_id = parseInt(form.project_id);
                    form.edit_user = null;
                    if(!form.project) {
                        form.project = {
                            name: null
                        };
                    }
                    this.form = form;
                    this.locked = (this.scale < 1);
                    this.showBlackMask = false;

                    if(this.isDeploy) {
                        if(oldRegionMgmtId) {
                            window.Echo.leave(`regionMgmts.${oldRegionMgmtId}`);
                        }
                        window.Echo.private(`regionMgmts.${this.form.id}`).listen('RegionMgmtUpdated', (e) => {
                            that.fetchData();
                            that.$nextTick(() => {
                                if(that.$refs.vehicles) {
                                    that.$refs.vehicles.fetchData();
                                }
                            });
                        });
                    }

                    if(this.isFloor) {
                        this.mapType = 'radar';
                    } else if(this.form.floors) {
                        this.mapType = 'cad';
                    }

                    this.$nextTick(async() => {
                        if(that.mapType == 'radar') {
                            await Promise.all([
                                that.$refs.vertices.fetchData(),
                                that.$refs.edges.fetchData()
                            ]);
                        } else if(that.mapType == 'cad') {
                            await Promise.all([
                                that.$refs.locations.fetchData(),
                                that.$refs.edges.fetchData()
                            ]);
                        }
                        that.oldRegionMd5 = that.newRegionMd5;
                        that.sending = false;
                        that.loading = false;
                    });
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.loading = false;
            this.$nextTick(() => {
                that.slider();
            });
        },
        showModal(id) {
            if(this.unsaved) {
                if(confirm('尚未儲存，確定要更新區域？')) {
                    this.modal.id = id;
                    this.modal.show = true;
                }
            } else {
                this.modal.id = id;
                this.modal.show = true;
            }
        },
        resetForm() {
            this.form = {
                id: null,
                region: null,
                project_id: null,
                vertices: [],
                locations: [],
                edges: [],
                mm: 0,
                resolution: 0,
                img_width: 0,
                img_height: 0,
                updated_at: null,
                origin_x: null,
                origin_y: null,
                origin_x_px: null,
                origin_y_px: null,
                origin_start_direction: 1,
                is_exist_preview_background: false,
                is_exist_background: false,
                project: {
                    name: null
                },
                deploy_background_file_md5: null,
                undeploy_background_file_md5: null,
                deploy_route_file_md5: null,
                undeploy_route_file_md5: null
            };
            this.oldRegionMd5 = null;
            this.showBlackMask = false;
            this.showBlackMaskMsg = false;
        },
        checkVertexPosition(position) {
            if(!(this.form.project_id && this.form.id)) {
                this.$toast.error({
                    title: '無法新增站點',
                    message: '請先選擇區域'
                });
                return;
            }

            if(!this.isDeploy && this.locked) {
                this.$toast.error({
                    title: '無法新增站點',
                    message: '請先解除鎖定'
                });
                return;
            }

            if(this.mapType == 'cad' && this.vertexTypeId != 4) {
                this.$toast.error({
                    title: '無法新增站點',
                    message: 'CAD圖僅限新增「工作站」'
                });
                return;
            }

            if(this.mapType == 'radar') {
                const that = this;
                let vertexConfigurationTypes = [];
                let vertexConfigurations = [];
                const vertexType = _.find(this.vertexTypes, (r) => {
                    return r.id == that.vertexTypeId;
                });
                if(vertexType) {
                    vertexConfigurationTypes = vertexType.vertex_configuration_types;
                    vertexConfigurations = this.initDefaultVertexConfigurations(vertexConfigurationTypes);
                }
                this.vertexModal = {
                    show: true,
                    vertex: {
                        id: null,
                        region_mgmt_id: this.form.id,
                        tag: null,
                        vertex_type_id: this.vertexTypeId,
                        vertex_type: {
                            vertex_configuration_types: vertexConfigurationTypes
                        },
                        x: this.imgCoordToQuadrantCoordX(this.form.resolution, position.x, this.form.origin_x),
                        y: this.imgCoordToQuadrantCoordY(this.form.resolution, position.y, this.form.img_height, this.form.origin_y, this.form.origin_start_direction),
                        x_px: position.x,
                        y_px: position.y,
                        z: 0,
                        enable: 1,
                        vertex_configurations: vertexConfigurations,
                        region_mgmt: {
                            project_id: this.form.project_id,
                            resolution: this.form.resolution,
                            origin_x: this.form.origin_x,
                            img_height: this.form.img_height,
                            origin_y: this.form.origin_y,
                            origin_start_direction: this.form.origin_start_direction
                        },
                        undeploy_location: {
                            name: null
                        },
                        edges: []
                    },
                    nearbyVertices: []
                };
            } else if(this.mapType == 'cad') {
                this.locationModal = {
                    show: true,
                    location: {
                        id: null,
                        build: this.form.project ? this.form.project.name : null,
                        floors: this.form.floors ? this.form.floors : null,
                        room: this.roomName,
                        vertex_id: null,
                        vertex_name: null,
                        device_name: null,
                        copy_id: null,
                        x: null,
                        y: null,
                        x_px: position.x,
                        y_px: position.y
                    },
                    nearbyVertices: []
                };
            }
        },
        initDefaultVertexConfigurations(vertexConfigurationTypes) {
            const defaultVertexConfigurationTypes = _.filter(vertexConfigurationTypes, (r) => {
                return r.is_default == 1;
            });
            const vertexConfigurations = [];
            for(let i = 0; i < defaultVertexConfigurationTypes.length; i++) {
                let data = null;
                const type = defaultVertexConfigurationTypes[i].vertex_configuration_column.name;
                const vertexConfigurationType = _.find(vertexConfigurationTypes, (r) => {
                    return r.vertex_configuration_column.name == type;
                });
                vertexConfigurations.push({
                    id: this.generateUUID(),
                    type: type,
                    data: data,
                    vertex_configuration_type: vertexConfigurationType ? vertexConfigurationType : null
                });
            }
            return vertexConfigurations;
        },
        scaleMap(zoom) {
            if(zoom) {
                this.scale += 0.1;
            } else {
                this.scale -= 0.1;
            }
        },
        async deleteEdges() {
            if(!confirm('確定要清除所有的軌道？')) {
                return;
            }
            this.form.edges = [];
        },
        onMouseUp() {
            alert('請拖曳icon以創建站點！');
        },
        async deleteRegion() {
            if(!confirm(`確定要刪除「${this.form.region}」?`)) {
                return;
            }

            try {
                let res = await axios.delete(`/api/regionMgmts/${this.form.id}`);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: `刪除成功`
                    });
                    this.regionMgmts = _.filter(this.regionMgmts, (r) => {
                        return r.id != this.form.id;
                    });
                    this.form.id = null;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
        },
        resizeContentHeight() {
            const $region = document.getElementById('region');
            const $imgMap = document.getElementById('imgMap');
            const $divRegion = document.getElementsByClassName('div-region');
            const sidebarVertexInfoWidth = 452;
            if($imgMap) {
                const width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                if($divRegion.length > 0) {
                    $divRegion[0].style.width = $imgMap.width + 'px';
                    $divRegion[0].style.height = $imgMap.height + 'px';
                    $divRegion[0].style.transform = `scale(${this.scale},${this.scale})`;
                }
                if(($imgMap.width * this.scale) < width) {
                    if(this.isDeploy && (this.vertexModal.show || this.locationModal.show) && (width - sidebarVertexInfoWidth) < ($imgMap.width * this.scale)) {
                        $region.style.width = (($imgMap.width * this.scale) - ($imgMap.width - (width - sidebarVertexInfoWidth))) + 'px';
                    } else {
                        $region.style.width = ($imgMap.width * this.scale) + 'px';
                    }
                } else {
                    if(this.isDeploy && (this.vertexModal.show || this.locationModal.show)) {
                        $region.style.width = (width - sidebarVertexInfoWidth) + 'px';
                    } else {
                        $region.style.width = width + 'px';
                    }
                }
                const height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
                if(($imgMap.height * this.scale) < (height - 106)) {
                    $region.style.height = ($imgMap.height * this.scale) + 'px';
                } else {
                    $region.style.height = (height - 106) + 'px';
                }
            }
        },
        slider() {
            let isDown = false;
            let startX, startY;
            let scrollLeft, scrollTop;
            const $region = document.getElementById('region');
            if(!$region) {
                return;
            }
            $region.addEventListener('mousedown', (e) => {
                if(e.target.className == 'location-point' || e.target.className == 'vertex-point' || e.target.className == 'edge-point' || e.target.className == 'ui-widget-content ui-draggable ui-draggable-handle') {
                    return;
                }

                isDown = true;
                $region.classList.add("active");
                startX = e.pageX;
                startY = e.pageY - $region.offsetTop;
                scrollLeft = $region.scrollLeft;
                scrollTop = $region.scrollTop;
            });
            $region.addEventListener('mouseleave', () => {
                isDown = false;
                $region.classList.remove("active");
            });
            $region.addEventListener('mouseup', () => {
                isDown = false;
                $region.classList.remove("active");
            });
            $region.addEventListener('mousemove', (e) => {
                e.preventDefault();
                if(!isDown) return;
                const x = e.pageX - $region.offsetLeft;
                const walkX = x - startX;
                $region.scrollLeft = scrollLeft - walkX;

                const y = e.pageY - $region.offsetTop;
                const walkY = y - startY;
                $region.scrollTop = scrollTop - walkY;
            });
        },
        openNewWidow(w, h, route) {
            let url = `/${route}`;

            const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
            const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

            const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

            const systemZoom = width / window.screen.availWidth;
            const left = (width - w) / 2 / systemZoom + dualScreenLeft
            const top = (height - h) / 2 / systemZoom + dualScreenTop
            const newWindow = window.open(url, '狀態顯示', `scrollbars=yes,width=${w / systemZoom},height=${h / systemZoom},top=${top},left=${left}`)

            if(window.focus) newWindow.focus();
        },
        showProjectDeployList() {
            if(this.form.project_id && this.form.id) {
                this.showProjectDeploy = !this.showProjectDeploy;
                this.showRegion = false;
            } else {
                alert('請先選擇專案區域！');
            }
        },
        async submit(id = this.form.id) {
            this.sending = true;
            try {
                let res = null;
                res = await axios.patch(`/api/regionMgmts/${id}`, this.sendForm);
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '儲存成功！'
                    });
                    const data = res.data;
                    if(this.mapType == 'radar') {
                        const regionMgmt = data.regionMgmt;
                        this.form.undeploy_route_file_md5 = regionMgmt.undeploy_route_file_md5;
                        this.form.updated_at = regionMgmt.updated_at;
                        if(!this.imgNotFound) {
                            await Promise.all([
                                this.$refs.vertices.fetchData(),
                                this.$refs.edges.fetchData()
                            ]);
                        }
                    } else if(this.mapType == 'cad') {
                        if(!this.imgNotFound) {
                            await Promise.all([
                                this.$refs.locations.fetchData(),
                                this.$refs.edges.fetchData()
                            ]);
                        }
                    }
                    this.showBlackMask = false;
                    this.showBlackMaskMsg = false;
                    this.oldRegionMd5 = this.newRegionMd5;
                } else if(res.status == -9) {
                    this.vertexListModal.vertices = res.data;
                    this.vertexListModal.show = true;
                }
            } catch(err) {
                this.guestRedirectHome(err);
            }
            this.sending = false;
        },
        resetRegion(regionForm) {
            window.history.replaceState(null, null, this.urlState);
            const route = Object.assign({}, this.$route);
            route.fullPath = `${this.$route.path}${this.urlState}`;
            route.params = {
                is_deploy: this.isDeploy,
                project_id: this.form.project_id,
                region_mgmt_id: this.form.id
            };
            this.$router.history.cb(route);
            this.form.project_id = regionForm.project_id;
            this.form.id = regionForm.id;
            if(this.form.id == regionForm.id) {
                this.fetchData();
            }
        },
        async onChangeMapType(mapType) {
            if(this.unsaved) {
                if(!confirm('資料尚未儲存，確定要切換地圖類型？')) {
                    return;
                }
            }
            this.loading = true;
            this.vertexModal.show = false;
            this.vertexModal.id = null;
            this.locationModal.show = false;
            this.locationModal.id = null;
            this.mapType = mapType;
            if(mapType != 'radar') {
                this.showProjectDeploy = false;
            }
            if(!this.form.id) {
                return;
            }
            const that = this;
            this.$nextTick(async() => {
                if(mapType == 'radar') {
                    await Promise.all([
                        that.$refs.vertices.fetchData(),
                        that.$refs.edges.fetchData()
                    ]);
                } else if(mapType == 'cad') {
                    await Promise.all([
                        that.$refs.locations.fetchData(),
                        that.$refs.edges.fetchData()
                    ]);
                }
                that.oldRegionMd5 = that.newRegionMd5;
                that.loading = false;
            });
        },
        updateEdge(row) {
            const edgeIdx = _.findIndex(this.form.edges, (r) => {
                return row.id == r.id;
            });
            this.form.edges[edgeIdx].enable = row.enable;
        },
        async onProjectChange() {
            if(!this.form.id) {
                return;
            }
            if(this.mapType == 'radar') {
                await Promise.all([
                    this.$refs.vertices.fetchData(),
                    this.$refs.edges.fetchData()
                ]);
            } else if(this.mapType == 'cad') {
                await Promise.all([
                    this.$refs.locations.fetchData(),
                    this.$refs.edges.fetchData()
                ]);
            }
            this.oldRegionMd5 = this.newRegionMd5;
        },
        async fetchCleanStatusData() {
            this.loading = true;
            try {
                let res = await axios.get(`/api/cleanStatuses/clean001`);
                res = res.data;
                if(res.status == 0) {
                    const data = res.data;
                    this.cleanStatus = data.cleanStatus;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.loading = false;
        },
        generateGoal(ref, isDouble, laserDetection, mqttCommandTypeId, mqttCmdForm) {
            const $map = document.getElementById('imgMap');
            const that = this;
            const vehicleMgmtIdx = _.findIndex(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command = _.cloneDeep(this.emptyMqttCommand());
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = mqttCommandTypeId;
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.laser_detection = laserDetection;
            this.vehicleMgmts = _.cloneDeep(this.vehicleMgmts);

            intervalCb = setInterval(() => {
                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.remain_stop_second -= 1;
                if(that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.remain_stop_second == 0) {
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.stop_disabled = false;
                    clearInterval(intervalCb);
                }
            }, 1000);
            this.mqttCommand.sending = true;
            cb = async(e) => {
                if(!isDouble) {
                    if(that.mqttCommand.goal_x_px === null && that.mqttCommand.goal_y_px === null) {
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_x_px = e.offsetX;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_y_px = e.offsetY;
                        $map.removeEventListener('mousemove', mousemoveCb);
                        $map.removeEventListener('click', cb);
                        that.$nextTick(async() => {
                            const mqttCommand = await ref.submit();
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = false;
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_x_px = null;
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_y_px = null;
                            if(mqttCommand) {
                                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = mqttCommand.command_id;
                                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = mqttCommand.mqtt_command_type_id;
                                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.laser_detection = mqttCommand.laser_detection;
                            }
                        });
                    }
                } else {
                    if(that.mqttCommand.start_goal_x_px === null && that.mqttCommand.start_goal_y_px === null) {
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.start_goal_x_px = e.offsetX;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.start_goal_y_px = e.offsetY;
                        $map.removeEventListener('mousemove', mousemoveCb);
                    } else if(that.mqttCommand.end_goal_x_px === null && that.mqttCommand.end_goal_y_px === null) {
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.end_goal_x_px = e.offsetX;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.end_goal_y_px = e.offsetY;
                        $map.removeEventListener('click', cb);
                        that.showBlackMask = true;
                        that.showBlackMaskMsg = true;
                        that.$nextTick(async() => {
                            const mqttCommand = await ref.submit();
                            if(!that.vehicleMgmts[vehicleMgmtIdx].clean_area) {
                                that.$set(that.vehicleMgmts[vehicleMgmtIdx], 'clean_area', {
                                    start_goal_x_px: that.mqttCommand.start_goal_x_px,
                                    start_goal_y_px: that.mqttCommand.start_goal_y_px,
                                    end_goal_x_px: that.mqttCommand.end_goal_x_px,
                                    end_goal_y_px: that.mqttCommand.end_goal_y_px
                                });
                            } else {
                                that.vehicleMgmts[vehicleMgmtIdx].clean_area.start_goal_x_px = that.mqttCommand.start_goal_x_px;
                                that.vehicleMgmts[vehicleMgmtIdx].clean_area.start_goal_y_px = that.mqttCommand.start_goal_y_px;
                                that.vehicleMgmts[vehicleMgmtIdx].clean_area.end_goal_x_px = that.mqttCommand.end_goal_x_px;
                                that.vehicleMgmts[vehicleMgmtIdx].clean_area.end_goal_y_px = that.mqttCommand.end_goal_y_px;
                            }
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.start_goal_x_px = null;
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.start_goal_y_px = null;
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.end_goal_x_px = null;
                            that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.end_goal_y_px = null;
                            if(mqttCommand) {
                                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = mqttCommand.command_id;
                                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = mqttCommand.mqtt_command_type_id;
                                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.laser_detection = mqttCommand.laser_detection;
                            } else {
                                that.showBlackMask = false;
                                that.showBlackMaskMsg = false;
                            }
                        });
                    }
                }
            };
            mousemoveCb = async(e) => {
                const theta = that.calculateDegrees(e.layerX, e.layerY, that.vehicleMgmts[vehicleMgmtIdx].position_x_px, that.vehicleMgmts[vehicleMgmtIdx].position_y_px);
                that.vehicleMgmts[vehicleMgmtIdx].theta = theta - 180;
                if(that.vehicleMgmts[vehicleMgmtIdx].vehicle_id != that.vehicleId) {
                    $map.removeEventListener('mousemove', mousemoveCb);
                    $map.removeEventListener('click', cb);
                    clearInterval(intervalCb);
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command = null;
                    that.vehicleMgmts[vehicleMgmtIdx].theta = null;
                }
            }
            if(mqttCmdForm) {
                this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_x_px = parseFloat(mqttCmdForm.x);
                this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_y_px = parseFloat(mqttCmdForm.y);
                this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.theta = parseInt(mqttCmdForm.theta);
                this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.laser_detection = mqttCmdForm.laser_detection;
                this.$nextTick(async() => {
                    const mqttCommand = await ref.submit();
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = false;
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_x_px = null;
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.goal_y_px = null;
                    if(mqttCommand) {
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = mqttCommand.command_id;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = mqttCommand.mqtt_command_type_id;
                    }
                });
            } else {
                $map.addEventListener('click', cb);
                $map.addEventListener('mousemove', mousemoveCb);
            }
        },
        selectVertex(ref) {
            const that = this;
            this.isAllVertexUnselected = true;
            const vehicleMgmtIdx = _.findIndex(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command = _.cloneDeep(this.emptyMqttCommand());
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = 3;
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = true;
            intervalCb = setInterval(() => {
                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.remain_stop_second -= 1;
                if(that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.remain_stop_second == 0) {
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.stop_disabled = false;
                    clearInterval(intervalCb);
                }
            }, 1000);
            const $map = document.getElementById('imgMap');
            unwatchSelectedVertexIds = this.$watch('selectedVertexIds', function(newVal) {
                if(newVal.length == 0) {
                    return;
                }

                const selectedVertexId = newVal[0];
                const vertex = _.find(that.form.vertices, (r) => {
                    return r.id == selectedVertexId;
                });
                if(!vertex) {
                    return;
                }
                const vertexConfiguration = _.find(vertex.vertex_configurations, (r) => {
                    return r.type == 'device_name';
                });
                if(!vertexConfiguration) {
                    that.isAllVertexUnselected = true;
                    alert('該站點沒有設立設備名稱，請重新選擇！');
                    return;
                }

                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.device_name = vertexConfiguration.data;
                that.$nextTick(async() => {
                    const mqttCommand = await ref.submit();
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = false;
                    if(mqttCommand) {
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = mqttCommand.command_id;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = mqttCommand.mqtt_command_type_id;
                        that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.laser_detection = mqttCommand.laser_detection;
                    }
                    unwatchSelectedVertexIds();
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.device_name = null;
                    that.isAllVertexUnselected = true;
                    $map.removeEventListener('mousedown', downListener);
                    $map.removeEventListener('mousemove', moveListener);
                    $map.removeEventListener('mouseup', upListener);
                });
            });
            moveListener = () => {
                if(that.vehicleMgmts[vehicleMgmtIdx].vehicle_id != that.vehicleId) {
                    const $map = document.getElementById('imgMap');
                    $map.removeEventListener('mousedown', downListener);
                    $map.removeEventListener('mousemove', moveListener);
                    $map.removeEventListener('mouseup', upListener);
                    if(unwatchSelectedVertexIds) {
                        unwatchSelectedVertexIds();
                    }
                    clearInterval(intervalCb);
                    that.vehicleMgmts[vehicleMgmtIdx].mqtt_command = null;
                }
                moved = true;
            };

            $map.addEventListener('mousedown', downListener);
            $map.addEventListener('mousemove', moveListener);
            $map.addEventListener('mouseup', upListener);
        },
        async directSubmit(ref) {
            const that = this;
            const vehicleMgmtIdx = _.findIndex(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            if(vehicleMgmtIdx == -1) {
                return;
            }
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = true;
            if(ref.form.mqtt_command_type_id == 7) {
                if(this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id) {
                    this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = null;
                    await ref.submit();
                }
                this.reset();
            } else if([43, 44].contains(ref.form.mqtt_command_type_id)) {
                const mqttCommand = await ref.submit();
                const vehicleIdx = _.findIndex(this.vehicleMgmts, {'vehicle_id': this.vehicleId});
                if(vehicleIdx != -1) {
                    this.vehicleMgmts[vehicleIdx].mqtt_command = mqttCommand;
                }
            } else {
                await ref.submit();
                afterSubmitDisabledTimer = setTimeout(() => {
                    that.reset();
                }, 5000);
            }
        },
        async stopCharge(ref) {
            const that = this;
            const vehicleMgmtIdx = _.findIndex(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = true;
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = 6;
            this.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = null;
            const mqttCommand = await ref.submit();
            afterSubmitDisabledTimer = setTimeout(() => {
                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.sending = false;
            }, 5000);
            if(mqttCommand) {
                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.command_id = mqttCommand.command_id;
                that.vehicleMgmts[vehicleMgmtIdx].mqtt_command.mqtt_command_type_id = mqttCommand.mqtt_command_type_id;
            }
        },
        emptyVehicleMgmt() {
            return {
                vehicle_id: null,
                stoppable_second: 5,
                chargeable: true,
                vehicle_status: {
                    battery_status: null
                },
                mqtt_command: this.emptyMqttCommand()
            };
        },
        emptyMqttCommandSendCommand() {
            return {
                data: {action: null}
            }
        },
        emptyMqttCommand() {
            const that = this;
            const vehicleMgmtIdx = _.findIndex(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            let vehicleMgmt = null;
            if(vehicleMgmtIdx != -1) {
                vehicleMgmt = this.vehicleMgmts[vehicleMgmtIdx];
            }
            return {
                sending: false,
                command_id: null,
                command_type_id: null,
                laser_detection: 0,
                device_name: null,
                start_goal_x_px: null,
                start_goal_y_px: null,
                end_goal_x_px: null,
                end_goal_y_px: null,
                goal_x_px: null,
                goal_y_px: null,
                stop_disabled: true,
                remain_stop_second: (vehicleMgmt && vehicleMgmt.stoppable_second) ? (vehicleMgmt.stoppable_second + 1) : 6
            };
        },
        reset() {
            const that = this;
            const vehicleMgmtIdx = _.findIndex(this.vehicleMgmts, (r) => {
                return r.vehicle_id == that.vehicleId;
            });
            if(vehicleMgmtIdx == -1) {
                return;
            }
            const mqttCommand = _.cloneDeep(this.emptyMqttCommand());
            mqttCommand.remain_stop_second = 0;
            this.$set(this.vehicleMgmts[vehicleMgmtIdx], 'mqtt_command', mqttCommand);
            this.vehicleMgmts[vehicleMgmtIdx].clean_area = null;
            this.vehicleMgmts[vehicleMgmtIdx].theta = null;
            this.showBlackMask = false;
            this.showBlackMaskMsg = false;
            this.isAllVertexUnselected = true;

            const $map = document.getElementById('imgMap');
            $map.removeEventListener('mousedown', downListener);
            $map.removeEventListener('mousemove', moveListener);
            $map.removeEventListener('mouseup', upListener);
            if(unwatchSelectedVertexIds) {
                unwatchSelectedVertexIds();
            }
            $map.removeEventListener('mousemove', mousemoveCb);
            $map.removeEventListener('click', cb);
            clearInterval(intervalCb);
        },
        onImageLoad() {
            this.resizeContentHeight();
            this.slider();
        },
        async submitLocation() {
            this.sending = true;
            const locations = _.map(this.sendForm.locations, (r) => {
                if(r.id.toString().contains('-')) {
                    r.id = null;
                }
                return r;
            });
            try {
                let res = null;
                res = await axios.patch(`/api/locations/batch`, {
                    locations: locations
                });
                res = res.data;
                if(res.status == 0) {
                    this.$toast.success({
                        title: '成功訊息',
                        message: '儲存成功！'
                    });
                    this.showBlackMask = false;
                    this.showBlackMaskMsg = false;
                    this.oldRegionMd5 = this.newRegionMd5;
                }
            } catch(err) {
                await this.guestRedirectHome(err);
            }
            this.sending = false;
        }
    }
}
</script>

<style lang="scss" scoped>
.container-fluid{
    padding: 0;
}
.region-area{
    position: relative;
    height:   calc(100vh - 58px);
}
#imgMap{
    object-fit:        contain;
    -webkit-user-drag: none;
}
.div-region{
    position:                 relative;
    -webkit-user-select:      none;
    -moz-user-select:         none;
    -webkit-transform-origin: 0 0;
}
.container-left-top{
    position: absolute;
    top:      15px;
    left:     20px;
    z-index:  1;
}
.container-right-top{
    position: absolute;
    right:    20px;
    top:      15px;
}
.container-bottom{
    position: absolute;
    left:     0;
    right:    0;
    bottom:   0;
    padding:  0 10px 0 10px;
}
.container-tag{
    display:  inline-block;
    position: fixed;
    bottom:   48px;
}
.bottom-bar{
    background-image:    url("/img/map_bottom_background.png?v=20221216");
    background-repeat:   no-repeat;
    background-size:     cover;
    background-position: center;
    min-height:          49px;
}
.vertex-point-container{
    display: inline-block;
}
.list-select{
    display:          inline;
    width:            170px;
    padding:          0 0 2px 20px;
    margin:           0 6px 8px 0;
    font-size:        11pt;
    font-weight:      bold;
    color:            darkgreen;
    background-color: transparent;
    appearance:       none;
    border:           none;
    cursor:           pointer;
}
.container-right{
    position:         fixed;
    right:            0;
    top:              58px;
    width:            320px;
    height:           calc(100vh - 106px);
    padding-top:      15px;
    background-color: white;
    border:           1px #dddddd solid;
    transition:       all 800ms ease-in-out;
}
.container-right-collapsed{
    right: -324px;
}
.container-right-project-deploy{
    position:         fixed;
    right:            0;
    bottom:           calc(50vh - 204px);
    width:            306px;
    height:           354px;
    background-color: white;
    border:           1px #dddddd solid;
    transition:       all 800ms ease-in-out;
    z-index:          1;
}
.container-right-project-deploy-collapsed{
    right: -306px;
}
.container-right-display{
    position:         fixed;
    right:            0;
    top:              48px;
    min-width:        450px;
    height:           calc(100vh - 108px);
    margin:           10px 0 0 26px;
    background-color: white;
    transition:       all 800ms ease-in-out;
    overflow-y:       scroll;
    overflow-x:       hidden;
    box-shadow:       0 0 10px gray;
}
.container-right-display-collapsed{
    position: fixed;
    right:    -490px;
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
.burner-tag{
    display:          inline-block;
    position:         fixed;
    bottom:           calc(50vh - 20px);
    right:            306px;
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
.show-info{
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
.burner-collapsed{
    position: fixed;
    right:    0;
}
.shadow{
    box-shadow: 5px 5px 5px gray;
    margin:     0 5px 5px 0;
}
.demarcation{
    border-right: 2px solid white;
    margin:       0 10px 0 6px;
}
.btn{
    font-weight: bold;
}
.toolbar-btn{
    padding:       4px 8px 4px 8px;
    margin:        8px 0;
    border-radius: 10px 10px 10px 10px;
    font-size:     16px;
}
.div-background{
    display:           inline-block;
    position:          relative;
    margin:            7px 0 0 0;
    width:             254px;
    background-image:  url('/img/vertex_type_dropdownlist_border.png?v=20231108');
    background-repeat: no-repeat;
    background-size:   contain;
}
.div-background:after{
    width:        0;
    height:       0;
    border-left:  6px solid transparent;
    border-right: 6px solid transparent;
    border-top:   6px solid white;
    position:     absolute;
    top:          14px;
    right:        10px;
    content:      "";
    z-index:      98;
}
.tag-self{
    display:             inline-block;
    margin:              0 -12px -2px -3px;
    width:               120px;
    height:              40px;
    line-height:         40px;
    font-size:           12pt;
    text-align:          center;
    color:               white;
    font-weight:         bold;
    background-image:    url(/img/tag-blank.png?v=202212161533);
    background-color:    transparent;
    background-repeat:   no-repeat;
    background-size:     cover;
    background-position: center;
    cursor:              pointer;
}
.tag-self:hover{
    background-image: url(/img/tag-blank-s.png?v=202212161533);
}
.scale-refer-block{
    display:     inline-block;
    line-height: 49px;
}
.scale-refer{
    display:    inline-block;
    height:     4px;
    border:     2px solid #5c9b6c;
    border-top: none;
}
.btn-lock{
    background-color: rgba(0, 0, 0, 0.5);
    color:            white;
    width:            108px;
    border-radius:    8px 8px 8px 8px;
}
.view-mask{
    position:         fixed;
    top:              0;
    left:             0;
    right:            0;
    height:           calc(100vh - 48px);
    background-color: rgba(0, 0, 0, 0.2);
}
.animation-fade{
    animation:        fade 1200ms infinite;
    background-color: gold;
    position:         relative;
    z-index:          1;
}
.file-md5{
    position:    relative;
    top:         8px;
    display:     inline-block;
    color:       #333333;
    font-size:   12px;
    margin-left: 8px;
}
.clean-station-status{
    position:    relative;
    top:         8px;
    display:     inline-block;
    color:       #333333;
    font-size:   12px;
    margin-left: 8px;
}
.btn-clean-all-edge{
    border-radius:    10px;
    background-color: #5b3c44;
    color:            white;
    margin:           7px 0;
}
.btn-clean-all-edge:hover{
    background-color: #bb7c8c;
}
#region{
    width:    100%;
    overflow: hidden;
}
.goal{
    background-color: gold;
    position:         absolute;
    height:           10px;
    width:            10px;
    border-radius:    50%;
}
.dust-view{
    position: absolute;
    bottom:   48px;
    right:    10px;
}
.origin-x-y{
    position:         absolute;
    height:           10px;
    width:            10px;
    border-radius:    50%;
    background-image: radial-gradient(circle at 10px 10px, yellow, orange);
    box-shadow:       1px 1px 4px grey;
}
.region-operate{
    position:      absolute;
    bottom:        0;
    left:          0;
    right:         0;
    margin-bottom: 15px;
}
.region-floor-room-collapse-select{
    margin-top: 6px;
    overflow:   scroll;
    height:     calc(100vh - 270px);
}
.project-list-title{
    font-size:     24px;
    margin-bottom: 15px;
    color:         #5c9b6c;
}
.vehicle-mgmt-list-select{
    display: inline-block;
}
.project-list{
    padding-left:  0;
    padding-right: 0;
}
.dropup{
    display: inline-block;
}
.bottom-bar-map-type{
    position: absolute;
    left:     calc(50% - 58px);
    bottom:   54px;
    button{
        font-weight: 500;
    }
    button:focus{
        outline: none;
    }
}
.room-name-block{
    display:          inline-block;
    border-top:       6px solid #6db880;
    background-color: white;
    letter-spacing:   2px;
    font-size:        14px;
    padding:          4px 15px;
    .room-name{
        font-weight: bold;
    }
    .room-name-title{
        user-select: none;
    }
}
.route-planing{
    line-height: calc(100vh - 48px);
    text-align:  center;
    color:       white;
    font-size:   40px;
}
@keyframes fade{
    from{
        opacity: 1.0;
    }
    50%{
        opacity: 0.5;
    }
    to{
        opacity: 1.0;
    }
}
@media only screen and (max-width: 520px){
    .container-tag{
        bottom: 144px;
    }
}
@media only screen and (max-width: 768px){
    .container-right{
        top: 24vh;
    }
}
@media only screen and (max-width: 786px){
    .container-tag{
        bottom: 96px;
    }
}
@media only screen and (max-width: 1066px){
    .div-region{
        padding: 0 0 80px 0;
    }
}
@media only screen and (max-width: 552px){
    .div-region{
        padding: 0 0 120px 0;
    }
}
@media only screen and (max-width: 520px){
    .container-tag{
        bottom: 122px;
    }
}
@media only screen and (max-width: 460px){
    .div-region{
        padding: 0 0 154px 0;
    }
}
.battery{
    font-size: 16px;
    color:     rgb(77, 135, 142);
    margin:    0 10px;
}
</style>
