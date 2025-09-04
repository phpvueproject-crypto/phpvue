<?php

use App\Models\Device;
use App\Models\Edge;
use App\Models\ObjectMgmt;
use App\Models\RegionMgmt;
use App\Models\StationMgmt;
use App\Models\User;
use App\Models\Vertex;
use Carbon\Carbon;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('projectDeploys.{projectName}', function(User $user, $projectName) {
    if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
        $hasPermission = true;
    } else {
        $user = $user->load('regionMgmts.projectMgmt');
        $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $regionMgmt) use ($projectName) {
            $rProjectName = str_replace('-', '', $regionMgmt->project->name);
            return $rProjectName == $projectName;
        });
    }
    return $hasPermission;
});

Broadcast::channel('vertices', function() {
    return true;
});

Broadcast::channel('vertices.{vertex}', function(User $user, Vertex $vertex) {
    if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
        $hasPermission = true;
    } else {
        $user = $user->load('regionMgmts');
        $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $regionMgmt) use ($vertex) {
            return $regionMgmt->region == $vertex->id;
        });
    }
    return $hasPermission;
});

Broadcast::channel('edges', function() {
    return true;
});

Broadcast::channel('edges.{edge}', function(User $user, Edge $edge) {
    if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
        $hasPermission = true;
    } else {
        $user = $user->load('regionMgmts');
        $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $regionMgmt) use ($edge) {
            return $regionMgmt->region == $edge->region;
        });
    }
    return $hasPermission;
});

Broadcast::channel('vehicleMgmts', function() {
    return true;
});

Broadcast::channel('vehicleMgmts.vehicleStatus.{regionMgmtId}', function(User $user, $regionMgmtId) {
    $user = $user->load('regionMgmts');
    if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
        $hasPermission = true;
    } else {
        $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $regionMgmt) use ($regionMgmtId) {
            return $regionMgmt->id == $regionMgmtId;
        });
    }
    return $hasPermission;
});

Broadcast::channel('parkingLotMgmts', function() {
    return true;
});

Broadcast::channel('parkingLotStatuses', function() {
    return true;
});

Broadcast::channel('laserDusts', function() {
    return true;
});

Broadcast::channel('objectPortMgmts.objectPortStatus.{objUid}', function(User $user, $objUid) {
    $user = $user->load('regionMgmts');
    $objectMgmt = ObjectMgmt::whereObjUid($objUid)->first();
    if(!$objectMgmt)
        return false;

    $objectMgmt = $objectMgmt->load('vertex.regionMgmt');
    if(!$objectMgmt->vertex)
        return false;

    if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
        $hasPermission = true;
    } else {
        $regionMgmtId = $objectMgmt->vertex->regionMgmt->id;
        $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $regionMgmt) use ($regionMgmtId) {
            return $regionMgmt->id == $regionMgmtId;
        });
    }
    return $hasPermission;
});

Broadcast::channel('stationMgmts.{id}', function(User $user, $id) {
    $hasPermission = false;
    if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
        $hasPermission = true;
    } else {
        $stationMgmt = StationMgmt::whereStationId($id)->whereRelation('users', 'id', $user->id)->first();
        if($stationMgmt) {
            $hasPermission = true;
        }
    }

    return $hasPermission;
});

Broadcast::channel('stationMgmts', function() {
    return true;
});

Broadcast::channel('regionMgmts', function() {
    return true;
});

Broadcast::channel('regionMgmts.presence.{regionMgmtId}', function(User $user, $regionMgmtId) {
    $regionMgmt = RegionMgmt::find($regionMgmtId);
    if(!$regionMgmt) {
        return false;
    }
    $hasPermission = false;
    if(!$regionMgmt->edit_user_id) {
        if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
            $hasPermission = true;
        } else {
            $user = $user->load('regionMgmts');
            $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $r) use ($regionMgmt) {
                return $r->id == $regionMgmt->id;
            });
        }
    } else if($regionMgmt->edit_user_id == $user->id) {
        $hasPermission = true;
    }

    if($hasPermission) {
        RegionMgmt::whereEditUserId($user->id)->update([
            'edit_user_id' => null,
            'edited_at'    => null
        ]);
        $regionMgmt->edit_user_id = $user->id;
        $regionMgmt->edited_at = Carbon::now();
        $regionMgmt->save();
        return [
            'id'   => $user->id,
            'name' => $user->name
        ];
    } else {
        return false;
    }
});

Broadcast::channel('regionMgmts.{regionMgmtId}', function(User $user, $regionMgmtId) {
    $regionMgmt = RegionMgmt::whereId($regionMgmtId)->first();
    $hasPermission = false;
    if(!$regionMgmt->edit_user_id) {
        if($user->hasRole(['Admin', 'CEO', 'Manage'])) {
            $hasPermission = true;
        } else {
            $user = $user->load('regionMgmts');
            $hasPermission = $user->regionMgmts->contains(function(RegionMgmt $regionMgmt) use ($regionMgmtId) {
                return $regionMgmt->id == $regionMgmtId;
            });
        }
    } else if($regionMgmt->edit_user_id == $user->id) {
        $hasPermission = true;
    }

    if($hasPermission) {
        RegionMgmt::whereEditUserId($user->id)->update([
            'edit_user_id' => null,
            'edited_at'    => null
        ]);
        $regionMgmt->edit_user_id = $user->id;
        $regionMgmt->edited_at = Carbon::now();
        $regionMgmt->save();
        return [
            'id'   => $user->id,
            'name' => $user->name
        ];
    } else {
        return false;
    }
});

Broadcast::channel('clearStatuses', function() {
    return true;
});

Broadcast::channel('elevatorStatuses', function() {
    return true;
});

Broadcast::channel('sweeps', function() {
    return true;
});

Broadcast::channel('projects.{id}', function() {
    return true;
});

Broadcast::channel('mqttCommands', function() {
    return true;
});

Broadcast::channel('rabbitmq', function() {
    return true;
});

Broadcast::channel('mqttCommands.{commandId}', function() {
    return true;
});

Broadcast::channel('vehicleMgmts.{vehicleId}', function() {
    return true;
});

Broadcast::channel('actionNodes', function() {
    return true;
});

Broadcast::channel('pollutionConditions', function() {
    return true;
});

Broadcast::channel('cleanStatuses', function() {
    return true;
});
Broadcast::channel('cleanAreas', function() {
    return true;
});
Broadcast::channel('microOrganisms', function() {
    return true;
});
Broadcast::channel('devices', function() {
    return true;
});
Broadcast::channel('devices.{id}', function(User $user, $deviceId) {
    $device = Device::find($deviceId);
    if($device->is_connected) {
        return true;
    } else {
        return false;
    }
});
Broadcast::channel('mirStatuses', function() {
    return true;
});
Broadcast::channel('remoteManagementSystemStatuses', function() {
    return true;
});
Broadcast::channel('locations', function() {
    return true;
});
