<?php

namespace App\Jobs;

use App\Events\LaserDustCreated;
use App\Events\MicroOrganismCreated;
use App\Events\MqttCommandUpdated;
use App\Events\ProjectDeployUpdated;
use App\Events\VehicleMgmtUpdated;
use App\Models\AcceptanceGrade;
use App\Models\CleanArea;
use App\Models\CleanStatus;
use App\Models\LaserDust;
use App\Models\Location;
use App\Models\MicroOrganism;
use App\Models\MqttCommand;
use App\Models\ObjectMgmt;
use App\Models\PollutionCondition;
use App\Models\ProjectDeploy;
use App\Models\TurningPoint;
use App\Models\VehicleMgmt;
use App\Repositories\ProjectRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use function app\getColor;

class MqttReceiver implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RabbitMQJob $job): void {
        $payload = $job->payload();
        $res = $payload['data'];
        $typename = $res['typename'];
        if(isset($res['id']['uuid'])) {
            $uuid = $res['id']['uuid'];
            $mqttCommand = MqttCommand::whereCommandId($uuid)->first();
            if($mqttCommand) {
                $mqttCommand->is_completed = 1;
                $mqttCommand->receive_command = json_encode($res);
                $mqttCommand->save();
            } else {
                $this->destroyWork($job);
                return;
            }

            $command = json_decode($mqttCommand->send_command, true);
            $reqData = $command['data'];
            $status = 0;
            $desc = null;
            if(isset($res['reply'])) {
                $reply = $res['reply'];
                $status = $reply['condition'] == 'accepted' ? 0 : -1;
                $desc = $reply['description'];
            }

            switch($typename) {
                case 'deploy_graph':
                    $projectName = $reqData['project_name'];
                    $projectDeploy = ProjectDeploy::whereProjectName($projectName)->first();
                    $projectDeploy->deploy_status = $status == 0 ? 1 : 0;
                    $projectDeploy->deploy_fail_desc = $desc;
                    $projectDeploy->save();

                    $updateFailAccountList = $res['data']['update_fail_account_list'];
                    $updateFailVehicleIds = collect($updateFailAccountList)->map(function($r) {
                        return $r['account']['name'];
                    });
                    $vehicleMgmts = VehicleMgmt::with('vehicleStatus')->get();
                    foreach($updateFailAccountList as $updateFailAccount) {
                        /** @var VehicleMgmt $vehicleMgmt */
                        $vehicleMgmt = $vehicleMgmts->where('vehicle_id', $updateFailAccount['account']['name'])->first();
                        if(!$vehicleMgmt) {
                            continue;
                        }
                        $vehicleStatus = $vehicleMgmt->vehicleStatus;
                        if(!$vehicleStatus) {
                            continue;
                        }

                        $vehicleStatus->deploy_status = 0;
                        $vehicleStatus->deploy_fail_reason = $updateFailAccount['reason'];
                        $vehicleStatus->save();
                    }

                    $updateSuccessfullyAccountList = $res['data']['update_successfully_account_list'];
                    $updateSuccessfullyVehicleIds = collect($updateSuccessfullyAccountList)->map(function($r) {
                        return $r['account']['name'];
                    });
                    foreach($updateSuccessfullyAccountList as $updateSuccessfullyAccount) {
                        /** @var VehicleMgmt $vehicleMgmt */
                        $vehicleMgmt = $vehicleMgmts->where('vehicle_id', $updateSuccessfullyAccount['account']['name'])->first();
                        if(!$vehicleMgmt) {
                            continue;
                        }
                        $vehicleStatus = $vehicleMgmt->vehicleStatus;
                        if(!$vehicleStatus) {
                            continue;
                        }

                        $vehicleStatus->deploy_status = 1;
                        $vehicleStatus->save();
                    }
                    $updateVehicleIds = $updateSuccessfullyVehicleIds->merge($updateFailVehicleIds);

                    $vehicleMgmts = $vehicleMgmts->whereNotIn('vehicle_id', $updateVehicleIds);
                    foreach($vehicleMgmts as $vehicleMgmt) {
                        $vehicleStatus = $vehicleMgmt->vehicleStatus;
                        if(!$vehicleStatus) {
                            continue;
                        }

                        $vehicleStatus->deploy_status = 2;
                        $vehicleStatus->save();
                    }

                    if($status == 0) {
                        $projectRepository = new ProjectRepository();
                        $projectRepository->deploy($projectName);
                    }
                    event(new ProjectDeployUpdated($projectDeploy));
                    break;
                default:
                    sleep(1);
                    event(new MqttCommandUpdated($mqttCommand, true));
            }
        } else {
            $vehicleMgmt = null;
            if(isset($res['account']['name'])) {
                $vehicleId = $res['account']['name'];
                $vehicleMgmt = VehicleMgmt::where('vehicle_id', $vehicleId)->with([
                    'objectMgmt',
                    'vehicleStatus'
                ])->first();
                /** @var ObjectMgmt $objectMgmt */
                if(!$vehicleMgmt || !$vehicleMgmt->objectMgmt) {
                    $this->destroyWork($job);
                    return;
                }
            }

            switch($typename) {
                case 'on_transfer_completed':
                case 'set_cylinder_completed':
                case 'set_door_completed':
                    $uuid = $res['m_command_id'];
                    $mqttCommand = MqttCommand::whereCommandId($uuid)->first();
                    $mqttCommand->receive_command = json_encode($res);
                    $mqttCommand->is_completed = 1;
                    $mqttCommand->save();
                    $mqttCommand = $mqttCommand->load([
                        'regionMgmt',
                        'vehicleMgmt'
                    ]);

                    if($typename == 'on_transfer_completed') {
                        $vehicleMgmt->theta = null;
                        $vehicleMgmt->save();

                        $regionMgmt = $mqttCommand->regionMgmt;
                        CleanArea::whereRegionMgmtId($regionMgmt->id)->where('vehicle_mgmt_id', $vehicleMgmt->vehicle_id)->update([
                            'enable' => 0
                        ]);
                    } else if($typename == 'set_cylinder_completed') {
                        $sendCommand = json_decode($mqttCommand->send_command, true);
                        $cleanStatus = CleanStatus::find($res['cleanstation_ID']);
                        $cleanStatus->cylinder_status = $sendCommand['data']['action'];
                        $cleanStatus->save();
                    } else if($typename == 'set_door_completed') {
                        $sendCommand = json_decode($mqttCommand->send_command, true);
                        $cleanStatus = CleanStatus::find($res['cleanstation_ID']);
                        $cleanStatus->door_status = $sendCommand['data']['action'];
                        $cleanStatus->save();
                    }
                    event(new MqttCommandUpdated($mqttCommand, true));
                    break;
                case 'on_vehicle_discharged':
                    $mqttCommand = MqttCommand::where('mqtt_type_id', 6)->where('vehicle_id', $vehicleMgmt->vehicle_id)->where('is_completed', 0)->with([
                        'regionMgmt',
                        'vehicleMgmt'
                    ])->first();
                    /** @var MqttCommand $mqttCommand */
                    $mqttCommand->is_completed = 1;
                    $mqttCommand->receive_command = json_encode($res);
                    $mqttCommand->save();

                    $vehicleMgmt->theta = null;
                    $vehicleMgmt->save();
                    event(new MqttCommandUpdated($mqttCommand, true));
                    break;
                case 'on_vehicle_update_vertex':
                    $vehicleMgmt->position_x = $res['position']['x'];
                    $vehicleMgmt->position_y = $res['position']['y'];
                    $vehicleMgmt->theta = $res['position']['w'];
                    $vehicleMgmt->save();
                    $vehicleMgmt = $vehicleMgmt->load([
                        'vehicleStatus.regionMgmt',
                        'cleanArea.turningPoints'
                    ]);
                    event(new VehicleMgmtUpdated($vehicleMgmt));
                    break;
                case 'on_vehicle_update_dust_value':
                    $laserDust = new LaserDust();
                    $laserDust->val_1 = $res['dust_value']['0.3um'];
                    $laserDust->val_2 = $res['dust_value']['0.5um'];
                    $laserDust->val_3 = $res['dust_value']['1.0um'];
                    $laserDust->val_4 = $res['dust_value']['2.5um'];
                    $laserDust->val_5 = $res['dust_value']['5.0um'];
                    $laserDust->val_6 = $res['dust_value']['10.0um'];
                    $laserDust->vehicle_id = $vehicleMgmt->vehicle_id;
                    $laserDust->save();

                    $vertex = $vehicleMgmt->vehicleStatus->vertex;

                    $location = Location::whereVertexId($vertex->id)->first();

                    $pollutionConditions = PollutionCondition::all();
                    $acceptanceGrades = AcceptanceGrade::whereIsDefault(0)->get();

                    /** @var AcceptanceGrade $acceptanceGrade */
                    $acceptanceGrade = $acceptanceGrades->where('organism_kind', 'microparticle_dot_5')->where('grade', $vertex->regionMgmt->cleanliness_grade)->first();
                    $microOrganism = new MicroOrganism();
                    $microOrganism->Time = Carbon::now();
                    $microOrganism->created_at = null;
                    $microOrganism->device_name = $location->device_name;
                    $microOrganism->location_id = $location->id;
                    $microOrganism->organism_kind = 'microparticle_dot_5';
                    $microOrganism->organism_value = $laserDust->val_2;
                    $microOrganism->room_name = $location->room;
                    if($acceptanceGrade) {
                        $microOrganism->color = getColor($pollutionConditions, $acceptanceGrade, $microOrganism->organism_value);
                        $microOrganism->score = ($microOrganism->organism_value / $acceptanceGrade->action) * 100;
                    }
                    $microOrganism->save();
                    event(new MicroOrganismCreated($microOrganism));

                    /** @var AcceptanceGrade $acceptanceGrade */
                    $acceptanceGrade = $acceptanceGrades->where('organism_kind', 'microparticle_5')->where('grade', $vertex->regionMgmt->cleanliness_grade)->first();
                    $microOrganism = new MicroOrganism();
                    $microOrganism->Time = Carbon::now();
                    $microOrganism->device_name = $location->device_name;
                    $microOrganism->location_id = $location->id;
                    $microOrganism->organism_kind = 'microparticle_5';
                    $microOrganism->organism_value = $laserDust->val_5;
                    $microOrganism->room_name = $location->room;
                    if($acceptanceGrade) {
                        $microOrganism->color = getColor($pollutionConditions, $acceptanceGrade, $microOrganism->organism_value);
                        $microOrganism->score = ($microOrganism->organism_value / $acceptanceGrade->action) * 100;
                    }
                    $microOrganism->save();
                    event(new MicroOrganismCreated($microOrganism));
                    event(new LaserDustCreated($laserDust));
                    break;
                case 'on_vehicle_update_afs_path':
                    $afsPath = $res['afs_path'];
                    /** @var MqttCommand $mqttCommand */
                    $mqttCommand = MqttCommand::whereIn('mqtt_command_type_id', [
                        2,
                        4
                    ])->where('is_completed', 0)->first();
                    $mqttCommand->is_completed = 1;
                    $mqttCommand->receive_command = json_encode($res);
                    $mqttCommand->save();
                    $mqttCommand = $mqttCommand->load('vehicleMgmt.cleanArea');
                    $vehicleMgmt = $mqttCommand->vehicleMgmt;
                    foreach($afsPath as $r) {
                        $turningPoint = new TurningPoint();
                        $turningPoint->clean_area_id = $vehicleMgmt->cleanArea->id;
                        $turningPoint->x = $r['x'];
                        $turningPoint->y = $r['y'];
                        $turningPoint->save();
                    }

                    $mqttCommand = $mqttCommand->load('vehicleMgmt.cleanArea.turningPoints');
                    event(new MqttCommandUpdated($mqttCommand, true));
                    break;
            }
        }
        $this->destroyWork($job);
    }

    private function destroyWork(RabbitMQJob $job): void {
        try {
            $job->delete();
        } catch(BindingResolutionException) {
        }
    }
}
