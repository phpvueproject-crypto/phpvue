<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MicroOrganismController;
use App\Http\Controllers\MissionQueueController;
use App\Http\Controllers\RegionMgmtController;
use App\Http\Controllers\RemoteManagementSystemStatusController;
use App\Http\Controllers\TransferProcessingController;
use App\Models\RegionMgmt;

$projectNames = RegionMgmt::with('project')->get()->pluck('project.name')->unique()->toArray();
$regions = RegionMgmt::all()->pluck('region')->toArray();

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('login', [LoginController::class, 'login']);
Route::get('refreshCsrfToken', [LoginController::class, 'refreshCsrfToken']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('images/{projectName}/cad_{region}.png', [
    RegionMgmtController::class,
    'displayCad'
])->whereIn('projectName', $projectNames)->whereIn('region', $regions);
Route::middleware(['auth'])->group(function() use ($projectNames, $regions) {
    Route::get('images/{projectName}/background_{region}{type}.png', [
        RegionMgmtController::class,
        'display'
    ])->whereIn('projectName', $projectNames)->whereIn('region', $regions)->whereIn('type', [
        '_preview',
        '_original',
        ''
    ]);
    Route::get('transferProcessings', [TransferProcessingController::class, 'index']);
    Route::get('{projectName}/{filename}.json', [RegionMgmtController::class, 'download']);
    Route::post('uploadChart', [DashboardController::class, 'uploadChart']);
    Route::get('chart.pdf', [DashboardController::class, 'downloadChart']);
    Route::get('microOrganisms.xlsx', [
        MicroOrganismController::class,
        'download'
    ])->name('microOrganisms.download');
    Route::get('remoteManagementSystemStatuses.xlsx', [
        RemoteManagementSystemStatusController::class,
        'index'
    ])->defaults('format', 'xlsx');
    Route::get('missionQueues.xlsx', [
        MissionQueueController::class,
        'index'
    ]);
    Route::get('missionQueueDetails.xlsx', [
        MissionQueueController::class,
        'exportDetails'
    ])->where('format', 'xlsx');
    Route::get('missionQueueDetails/{id}.xlsx', [
        MissionQueueController::class,
        'show'
    ]);
});
Route::get('/{vue_capture?}', function() {
    return view('welcome');
})->where('vue_capture', '[\/\w\.-]*')->name('login');
