<?php

namespace App\Console\Commands;

use App\Events\CleanAreaCreated;
use App\Events\CleanAreaUpdated;
use App\Events\CleanStatusUpdated;
use App\Events\ClearStatusCreated;
use App\Events\DoorStatusUpdated;
use App\Events\ElevatorStatusUpdated;
use App\Events\MqttCommandUpdated;
use App\Events\ParkingLotStatusUpdated;
use App\Events\StationStatusUpdated;
use App\Events\SweepCreated;
use App\Events\VehicleStatusUpdated;
use App\Models\CleanArea;
use App\Models\CleanStatus;
use App\Models\ClearStatus;
use App\Models\DoorStatus;
use App\Models\ElevatorStatus;
use App\Models\MqttCommand;
use App\Models\ParkingLotStatus;
use App\Models\StationStatus;
use App\Models\Sweep;
use App\Models\VehicleMgmt;
use App\Models\VehicleStatus;
use App\Models\Vertex;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;
use PDO;

/**
 * Class SubscribeToTriggers
 *
 * @package App\Console\Commands
 */
class SubscribeToTriggers extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'psql:subscribe-to-triggers {--timeout=} {--t|table=* : Tables to synchronize.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for changes on database and update the platform accordingly';

    /**
     *  Tables to synchronize.
     *
     * @var array
     */
    protected $tables;

    /**
     * @var
     */
    private $subscribers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->tables = [];
        $this->subscribe();
    }

    /**
     * Subscribe to modification events on these tables.
     */
    private function subscribe(): void {
        $this->listen('vehicle_status', 'onVehicleStatus');
        $this->listen('parking_lot_status', 'onParkingLotStatus');
        $this->listen('elevator_status', 'onElevatorStatus');
        $this->listen('door_status', 'onDoorStatus');
        $this->listen('station_status', 'onStationStatus');
        $this->listen('clear_status', 'onClearStatus');
        $this->listen('sweep', 'onSweep');
        $this->listen('mqtt_commands', 'onMqttCommand');
        $this->listen('clean_statuses', 'onCleanStatus');
        $this->listen('clean_areas', 'onCleanArea');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): mixed {
        $timeout = (int)$this->option('timeout');

        if($table = $this->option('table')) {
            if(is_array($table)) {
                $this->tables = $table;
            } else {
                $this->tables[] = $table;
            }
        }

        try {
            $dbo = DB::connection('pgsql')->getPdo();
            $dbo->exec('LISTEN "events"');
            while(true) {
                $event = $dbo->pgsqlGetNotify(PDO::FETCH_ASSOC, $timeout * 1000);
                if($this->output->isDebug()) {
                    $this->getOutput()->write($event);
                    $this->getOutput()->write(PHP_EOL);
                }

                $payload = json_decode($event['payload']);
                $table = $payload->table;
                $action = $payload->action;
                $data = $payload->data;

                $observer = null;
                $subject = implode('@', [$table, strtolower($action)]);
                if(array_key_exists($subject, $this->subscribers)) {
                    $observer = $this->subscribers[$subject];
                } else if(array_key_exists($table, $this->subscribers)) {
                    $observer = $this->subscribers[$table];
                }
                if(isset($observer) && method_exists($this, $observer->handler)) {
                    $handler = $observer->handler;
                    $this->$handler($data, $action);
                }
            }
        } catch(Exception) {
        }

        return null;
    }

    /**
     * Set up observers to handle events on a table.
     *
     * @param $entity
     * @param $handler
     */
    private function listen($entity, $handler): void {
        if(!isset($this->subscribers)) {
            $this->subscribers = [];
        }

        $info = explode('@', $entity);
        $table = $info[0];
        $action = count($info) > 1 ? $info[1] : null;

        $observer = new \stdClass();
        $observer->table = $table;
        $observer->action = $action;
        $observer->handler = $handler;
        $subject = !empty($action) ? implode('@', [$table, strtolower($action)]) : $table;
        $this->subscribers[$subject] = $observer;
    }

    protected function onVehicleStatus($vehicleStatus, $action = null): void {
        if($action != 'UPDATE') {
            return;
        }
        $vehicleStatus = VehicleStatus::whereVehicleId($vehicleStatus->vehicle_id)->first();
        $vertex = Vertex::whereRelation('regionMgmt.project', 'is_deploy', 1)->where('is_deploy', 1)->where('name', $vehicleStatus->vehicle_location)->first();
        $vehicleStatus->vertex_id = $vertex?->id;
        $vehicleStatus->save();
        $vehicleStatus = $vehicleStatus->load([
            'vertex',
            'regionMgmt',
            'vehicleMgmt.cleanArea.turningPoints'
        ]);

        event(new VehicleStatusUpdated($vehicleStatus));
    }

    protected function onParkingLotStatus($parkingLotStatus, $action = null): void {
        if($action != 'UPDATE') {
            return;
        }
        /** @var ParkingLotStatus $parkingLotStatus */
        $parkingLotStatus = ParkingLotStatus::with('parkingLotMgmt.vertex.regionMgmt')->find($parkingLotStatus->parking_lot_id);
        event(new ParkingLotStatusUpdated($parkingLotStatus));

    }

    protected function onElevatorStatus($elevatorStatus, $action = null): void {
        if($action != 'UPDATE') {
            return;
        }
        /** @var ElevatorStatus|array $elevatorStatus */
        $elevatorStatus = ElevatorStatus::with('elevatorMgmt.vertices')->find($elevatorStatus->elevator_id);
        event(new ElevatorStatusUpdated($elevatorStatus));
    }

    protected function onDoorStatus($doorStatus, $action = null): void {
        if($action != 'UPDATE') {
            return;
        }
        /** @var DoorStatus|array $doorStatus */
        $doorStatus = DoorStatus::with('doorMgmt.edge.regionMgmt')->find($doorStatus->door_id);
        event(new DoorStatusUpdated($doorStatus));
    }

    protected function onStationStatus($stationStatus, $action = null): void {
        if($action != 'UPDATE') {
            return;
        }

        $stationStatus = StationStatus::whereStationId($stationStatus->station_id)->with('stationMgmt.vertex')->first();
        $vehicleMgmts = VehicleMgmt::all();
        $stationStatus->setVehicleMgmts($vehicleMgmts);
        event(new StationStatusUpdated($stationStatus));
    }

    protected function onSweep($sweep, $action = null): void {
        if($action == 'INSERT') {
            $sweep = Sweep::find($sweep->id);
            event(new SweepCreated($sweep));
        }
    }

    protected function onMqttCommand($mqttCommand, $action = null): void {
        $mqttCommand = MqttCommand::find($mqttCommand->id);
        event(new MqttCommandUpdated($mqttCommand));
    }

    protected function onCleanStatus($cleanStatus, $action = null): void {
        $cleanStatus = CleanStatus::find($cleanStatus->cleanstation_ID);
        event(new CleanStatusUpdated($cleanStatus));
    }

    protected function onCleanArea($cleanArea, $action = null): void {
        $cleanArea = CleanArea::find($cleanArea->id);
        if($action == 'INSERT') {
            event(new CleanAreaCreated($cleanArea));
        } else if($action == 'UPDATE') {
            event(new CleanAreaUpdated($cleanArea));
        }
    }

    protected function onClearStatus($clearStatus, $action = null): void {
        if($action == 'INSERT') {
            $clearStatus = ClearStatus::find($clearStatus->id);
            event(new ClearStatusCreated($clearStatus));
        }
    }
}
