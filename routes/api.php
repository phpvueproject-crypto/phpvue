<?php

use App\Http\Controllers\AcceptanceGradeController;
use App\Http\Controllers\CarrierClassController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChartColorController;
use App\Http\Controllers\CleanAreaController;
use App\Http\Controllers\CleanStatusController;
use App\Http\Controllers\ClearStatusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DoorMgmtController;
use App\Http\Controllers\EdgeConfigurationTypeController;
use App\Http\Controllers\EdgeController;
use App\Http\Controllers\ElevatorMgmtController;
use App\Http\Controllers\ElevatorStatusController;
use App\Http\Controllers\EquipmentClassController;
use App\Http\Controllers\HookStatusController;
use App\Http\Controllers\LaserDustController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MicroOrganismController;
use App\Http\Controllers\MirController;
use App\Http\Controllers\MirStatusController;
use App\Http\Controllers\MissionBookingController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionQueueController;
use App\Http\Controllers\MqttCommandController;
use App\Http\Controllers\ObjectClassController;
use App\Http\Controllers\ObjectMgmtController;
use App\Http\Controllers\ParkingLotMgmtController;
use App\Http\Controllers\PollutionConditionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectDeployController;
use App\Http\Controllers\ProjectMgmtController;
use App\Http\Controllers\RegionMgmtController;
use App\Http\Controllers\RemoteManagementSystemStatusController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomEnvironmentController;
use App\Http\Controllers\ScheduledMissionController;
use App\Http\Controllers\StationMgmtController;
use App\Http\Controllers\SweepController;
use App\Http\Controllers\SysConfigController;
use App\Http\Controllers\SystemEventController;
use App\Http\Controllers\TransferProcessingController;
use App\Http\Controllers\UniversalEventLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleEventController;
use App\Http\Controllers\VehicleMgmtController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\VendorMgmtController;
use App\Http\Controllers\VertexConfigurationController;
use App\Http\Controllers\VertexController;
use App\Http\Controllers\VertexTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('channelEvents', [ChannelController::class, 'trigger']);
Route::get('projects/{id}', [ProjectController::class, 'show']);
Route::get('projects', [ProjectController::class, 'index']);
Route::get('regionMgmts/{id}', [RegionMgmtController::class, 'show']);
Route::get('regionMgmts', [RegionMgmtController::class, 'index']);
Route::group(['prefix' => '/v2.0.0/'], function() {
    Route::get('registers/{id}', [MirController::class, 'getRegisters']);
    Route::post('registers/{id}', [MirController::class, 'postRegisters']);
    Route::get('status', [MirController::class, 'getStatus']);
    Route::put('status/', [MirController::class, 'putStatus']);
    Route::get('missions', [MirController::class, 'getMissions']);
    Route::get('missions/{id}', [MirController::class, 'getMission']);
    Route::get('mission_queue/', [MirController::class, 'getMissionQueues']);
    Route::post('mission_queue', [MirController::class, 'postMissionQueue']);
    Route::delete('mission_queue', [MirController::class, 'deleteMissionQueue']);
    Route::get('mission_queue/{id}', [MirController::class, 'getMissionQueue']);
    Route::get('maps', [MirController::class, 'getMaps']);
    Route::get('maps/{id}', [MirController::class, 'getMap']);
    Route::get('hook/status', [MirController::class, 'getHookStatus']);
    Route::get('maps/{mapId}/positions', [MirController::class, 'getPositions']);
    Route::get('positions/{id}', [MirController::class, 'getPosition']);
    Route::get('wifi/connections', [MirController::class, 'getWifiConnections']);
    Route::get('log/error_reports/{id}', [MirController::class, 'getErrorReports']);
});
Route::group(['middleware' => ['auth:api']], function() {
    Route::get('chart1', [DashboardController::class, 'chart1']);
    Route::get('chart2', [DashboardController::class, 'chart2']);
    Route::post('regionMgmts/yaml', [RegionMgmtController::class, 'loadYaml']);
    Route::post('regionMgmts/image', [RegionMgmtController::class, 'loadImage']);
    Route::get('user', [UserController::class, 'user']);
    Route::post('regionMgmts', [RegionMgmtController::class, 'store']);
    Route::patch('regionMgmts/{id}', [RegionMgmtController::class, 'update']);
    Route::get('vehicleMgmts', [VehicleMgmtController::class, 'index']);
    Route::get('objectClasses', [ObjectClassController::class, 'index']);
    Route::get('equipmentClasses', [EquipmentClassController::class, 'index']);
    Route::get('vendorMgmts', [VendorMgmtController::class, 'index']);
    Route::get('vendorMgmts/{vendor}', [VendorMgmtController::class, 'show']);
    Route::post('vendorMgmts', [VendorMgmtController::class, 'store']);
    Route::patch('vendorMgmts/{vendor}', [VendorMgmtController::class, 'update']);
    Route::delete('vendorMgmts/{vendor}', [VendorMgmtController::class, 'destroy']);
    Route::get('objectMgmts', [ObjectMgmtController::class, 'index']);
    Route::get('objectMgmts/{objUid}', [ObjectMgmtController::class, 'show']);
    Route::post('objectMgmts', [ObjectMgmtController::class, 'store']);
    Route::patch('objectMgmts/{objUid}', [ObjectMgmtController::class, 'update']);
    Route::delete('objectMgmts/{objUid}', [ObjectMgmtController::class, 'destroy']);
    Route::get('carrierClasses', [CarrierClassController::class, 'index']);
    Route::get('vertices', [VertexController::class, 'index']);
    Route::get('vertices/{id}', [VertexController::class, 'show']);
    Route::post('vertices', [VertexController::class, 'store']);
    Route::patch('vertices/{id}', [VertexController::class, 'update']);
    Route::delete('vertices/{id}', [VertexController::class, 'destroy']);
    Route::get('edges', [EdgeController::class, 'index']);
    Route::post('edges', [EdgeController::class, 'store']);
    Route::get('edges/{id}', [EdgeController::class, 'show']);
    Route::patch('edges/{id}', [EdgeController::class, 'update']);
    Route::delete('edges/{id}', [EdgeController::class, 'destroy']);
    Route::get('vertexTypes', [VertexTypeController::class, 'index']);
    Route::delete('regionMgmts/{id}', [RegionMgmtController::class, 'destroy']);
    Route::get('projectDeploys/{projectName}', [ProjectDeployController::class, 'show']);
    Route::post('projectDeploys', [ProjectDeployController::class, 'store']);
    Route::patch('projectDeploys/{projectName}', [ProjectDeployController::class, 'update']);
    Route::delete('projectDeploys/{projectName}', [ProjectDeployController::class, 'destroy']);
    Route::get('vehicleStatuses', [VehicleStatusController::class, 'index']);
    Route::get('universalEventLogs', [UniversalEventLogController::class, 'index']);
    Route::get('parkingLotMgmts', [ParkingLotMgmtController::class, 'index']);
    Route::get('parkingLotMgmts/{parkingLotId}', [ParkingLotMgmtController::class, 'show']);
    Route::get('transferProcessings', [TransferProcessingController::class, 'index']);
    Route::get('vertexConfigurations', [VertexConfigurationController::class, 'index']);
    Route::post('mqttCommands', [MqttCommandController::class, 'store']);
    Route::get('projectMgmts', [ProjectMgmtController::class, 'index']);
    Route::get('clearStatuses', [ClearStatusController::class, 'index']);
    Route::get('elevatorStatuses', [ElevatorStatusController::class, 'index']);
    Route::get('sweeps', [SweepController::class, 'index']);
    Route::get('laserDusts', [LaserDustController::class, 'index']);
    Route::get('mqttCommands', [MqttCommandController::class, 'index']);
    Route::get('elevatorMgmts', [ElevatorMgmtController::class, 'index']);
    Route::get('elevatorMgmts/{id}', [ElevatorMgmtController::class, 'show']);
    Route::get('microOrganisms', [MicroOrganismController::class, 'index']);
    Route::get('microOrganisms/{id}', [MicroOrganismController::class, 'show']);
    Route::patch('microOrganisms/batch', [MicroOrganismController::class, 'update']);
    Route::delete('microOrganisms/{id}', [MicroOrganismController::class, 'destroy']);
    Route::get('locations', [LocationController::class, 'index']);
    Route::get('acceptanceGrades', [AcceptanceGradeController::class, 'index']);
    Route::patch('acceptanceGrades/batch', [AcceptanceGradeController::class, 'update']);
    Route::delete('acceptanceGrades/resets', [AcceptanceGradeController::class, 'reset']);
    Route::get('stationMgmts', [StationMgmtController::class, 'index']);
    Route::get('doorMgmts', [DoorMgmtController::class, 'index']);
    Route::get('edgeConfigurationTypes', [EdgeConfigurationTypeController::class, 'index']);
    Route::get('pollutionConditions', [PollutionConditionController::class, 'index']);
    Route::get('cleanStatuses/{id}', [CleanStatusController::class, 'show']);
    Route::get('cleanAreas', [CleanAreaController::class, 'index']);
    Route::get('projectMgmts/{projectName}', [ProjectMgmtController::class, 'show']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('scheduledMissions', [ScheduledMissionController::class, 'index']);
    Route::patch('chartColors/{chartColorId}', [ChartColorController::class, 'update']);
    Route::get('roomEnvironments', [RoomEnvironmentController::class, 'index']);
    Route::patch('locations/batch', [LocationController::class, 'updateBatch']);
    Route::post('locations', [LocationController::class, 'store']);
    Route::patch('locations/{id}', [LocationController::class, 'update']);
    Route::patch('vehicleMgmts/{id}', [VehicleMgmtController::class, 'update']);
    Route::post('projectMgmts', [ProjectMgmtController::class, 'store']);
    Route::patch('projectMgmts/{projectName}', [ProjectMgmtController::class, 'update']);
    Route::delete('projectMgmts/{projectName}', [ProjectMgmtController::class, 'destroy']);
    Route::group(['middleware' => ['permission:user-role-manage']], function() {
        Route::post('users', [UserController::class, 'store']);
        Route::patch('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
        Route::get('roles', [RoleController::class, 'index']);
        Route::patch('roles', [RoleController::class, 'update']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy']);
    });
    Route::group(['middleware' => ['permission:cleanup-robot']], function() {
        Route::get('systemEvents', [SystemEventController::class, 'index']);
        Route::get('vehicleEvents', [VehicleEventController::class, 'index']);
        Route::post('elevatorMgmts', [ElevatorMgmtController::class, 'store']);
        Route::patch('elevatorMgmts/{id}', [ElevatorMgmtController::class, 'update']);
        Route::delete('elevatorMgmts/{id}', [ElevatorMgmtController::class, 'destroy']);
        Route::post('parkingLotMgmts', [ParkingLotMgmtController::class, 'store']);
        Route::patch('parkingLotMgmts/{parkingLotId}', [ParkingLotMgmtController::class, 'update']);
        Route::delete('parkingLotMgmts/{parkingLotId}', [ParkingLotMgmtController::class, 'destroy']);
    });
    Route::get('devices', [DeviceController::class, 'index']);
    Route::get('devices/{id}', [DeviceController::class, 'show']);
    Route::patch('devices/{id}/is-connected', [DeviceController::class, 'updateIsConnected']);
    Route::patch('devices/{id}', [DeviceController::class, 'update']);
    Route::post('microOrganisms/batch', [
        MicroOrganismController::class,
        'storeBatch'
    ])->name('microOrganisms.storeBatch');
    Route::get('mirStatuses', [MirStatusController::class, 'index']);
    Route::patch('mir-statuses/{id}/clear-error', [MirStatusController::class, 'clearVehicleErrorTypeId']);
    Route::get('missions', [MissionController::class, 'index']);
    Route::get('missionQueues', [MissionQueueController::class, 'index']);
    Route::get('missionQueues/{id}', [MissionQueueController::class, 'show']);
    Route::post('missionQueues', [MissionQueueController::class, 'store']);
    Route::put('missionQueues/{id}', [MissionQueueController::class, 'update']);
    Route::delete('missionQueues/{id}', [MissionQueueController::class, 'destroy']);
    Route::get('maps', [MapController::class, 'index']);
    Route::get('maps/{id}', [MapController::class, 'show']);
    Route::resource('mission-bookings', MissionBookingController::class)->only([
        'index',
        'show',
        'store',
        'update',
        'destroy'
    ]);
    Route::get('hook-status', [HookStatusController::class, 'hookStatus']);
    Route::get('remote-management-system-statuses', [
        RemoteManagementSystemStatusController::class,
        'index'
    ]);
});
