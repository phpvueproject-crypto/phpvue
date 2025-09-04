<?php

namespace App\Jobs;

use App\Models\RegionMgmt;
use App\Models\VehicleMgmt;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Queue;
use function app\imgCoordToQuadrantCoordX;
use function app\imgCoordToQuadrantCoordY;
use function app\quadrantCoordToImgCoordX;
use function app\quadrantCoordToImgCoordY;

class MqttTestCmdJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $uuid;
    private $laserDection;
    private $startGoalX;
    private $startGoalY;
    private $endGoalX;
    private $endGoalY;
    private $startGoalXPx;
    private $startGoalYPx;
    private $endGoalXPx;
    private $endGoalYPx;
    private $showAfs;
    private $region;
    private $vehicleId;

    public function __construct($data, $region, $vehicleId, $showAfs = true) {
        $this->uuid = $data['id']['uuid'];
        $this->laserDection = $data['data'][0]['data']['laser_detection'] == 'on';
        $this->startGoalX = $data['data'][0]['data']['start_goal']['x'];
        $this->startGoalY = $data['data'][0]['data']['start_goal']['y'];
        $this->endGoalX = $data['data'][0]['data']['end_goal']['x'];
        $this->endGoalY = $data['data'][0]['data']['end_goal']['y'];
        $this->showAfs = $showAfs;
        $this->region = $region;
        $this->vehicleId = $vehicleId;
    }

    public function handle() {
        sleep(2);
        $conn = Queue::connection('rabbitmq-receiver');
        $regionMgmt = RegionMgmt::whereRegion($this->region)->first();
        $this->startGoalXPx = quadrantCoordToImgCoordX($regionMgmt->resolution, $this->startGoalX, $regionMgmt->origin_x);
        $this->startGoalYPx = quadrantCoordToImgCoordY($regionMgmt->resolution, $this->startGoalY, $regionMgmt->img_height, $regionMgmt->origin_y);
        $this->endGoalXPx = quadrantCoordToImgCoordX($regionMgmt->resolution, $this->endGoalX, $regionMgmt->origin_x);
        $this->endGoalYPx = quadrantCoordToImgCoordY($regionMgmt->resolution, $this->endGoalY, $regionMgmt->img_height, $regionMgmt->origin_y);
        $afsPath = [];
        $vehicleMgmt = VehicleMgmt::whereVehicleId($this->vehicleId)->first();
        if($this->showAfs) {
            $sectionHeight = 20;
            $y = $this->startGoalYPx;
            $isLeft = true;
            $i = 1;
            $c = 0;
            while($y <= ($this->endGoalYPx - 20)) {
                if($isLeft) {
                    $x = $this->endGoalXPx;
                    if($i == 1) {
                        $isLeft = false;
                    } else {
                        $c++;
                        if($c == 2) {
                            $isLeft = false;
                            $c = 0;
                        }
                    }
                } else {
                    $x = $this->startGoalXPx - 20;
                    $c++;
                    if($c == 2) {
                        $isLeft = true;
                        $c = 0;
                    }
                }
                if($i > 2 && $i % 2 == 1) {
                    if(($y + $sectionHeight) <= ($this->endGoalYPx - 20)) {
                        $y += $sectionHeight;
                    } else {
                        $y = $this->endGoalYPx - 20;
                    }
                }
                $afsPath[] = [
                    'x' => imgCoordToQuadrantCoordX($regionMgmt->resolution, $x, $regionMgmt->origin_x),
                    'y' => imgCoordToQuadrantCoordY($regionMgmt->resolution, $y, $regionMgmt->img_height, $regionMgmt->origin_y),
                    'w' => '0'
                ];
                if($y == $this->endGoalYPx - 20) {
                    $afsPath[] = [
                        'x' => imgCoordToQuadrantCoordX($regionMgmt->resolution, $this->startGoalXPx - (20), $regionMgmt->origin_x),
                        'y' => imgCoordToQuadrantCoordY($regionMgmt->resolution, $y, $regionMgmt->img_height, $regionMgmt->origin_y),
                        'w' => '0'
                    ];
                    break;
                }
                $i++;
            }
            $conn->pushRaw(json_encode([
                'typename' => 'on_vehicle_update_afs_path',
                'account' => [
                    'type' => $vehicleMgmt->vehicle_type,
                    'name' => $vehicleMgmt->vehicle_id
                ],
                'afs_path' => $afsPath
            ]));
            sleep(2);
        }

        for($i = 0; $i < count($afsPath); $i++) {
            $conn->pushRaw(json_encode([
                'typename'    => 'on_vehicle_update_vertex',
                'account' => [
                    'type' => $vehicleMgmt->vehicle_type,
                    'name' => $vehicleMgmt->vehicle_id
                ],
                'vertex_name' => 'v1',
                'position'    => [
                    'x' => $afsPath[$i]['x'],
                    'y' => $afsPath[$i]['y'],
                    'w' => '90'
                ]
            ]));
            if($this->laserDection) {
                $now = Carbon::now();
                $conn->pushRaw(json_encode([
                    'typename'   => 'on_vehicle_update_dust_value',
                    'account' => [
                        'type' => $vehicleMgmt->vehicle_type,
                        'name' => $vehicleMgmt->vehicle_id
                    ],
                    'date'       => $now->format('Y-m-d H:i:s.v'),
                    'position'   => [
                        'x' => $afsPath[$i]['x'],
                        'y' => $afsPath[$i]['y'],
                        'w' => '90'
                    ],
                    'dust_value' => [
                        '0.3um'  => str(1254),
                        '0.5um'  => str(363),
                        '1.0um'  => str(47),
                        '2.5um'  => str(1),
                        '5.0um'  => str(0),
                        '10.0um' => str(0)
                    ]
                ]));
            }

            sleep(5);
        }

        sleep(5);
        $conn->pushRaw(json_encode([
            'typename'     => 'on_transfer_completed',
            'account'      => [
                'type' => $vehicleMgmt->vehicle_type,
                'name' => $vehicleMgmt->vehicle_id
            ],
            'm_command_id' => $this->uuid
        ]));
    }
}
