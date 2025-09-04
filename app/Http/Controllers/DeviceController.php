<?php

namespace App\Http\Controllers;

use App\Events\DeviceUpdated;
use App\Http\Requests\DeviceRequest;
use App\Models\Device;
use App\Models\MirStatus;
use App\Repositories\MirRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Process\Process;
use Validator;

class DeviceController extends Controller {
    private MirRepository $mir;

    public function __construct(MirRepository $mir) {
        $this->mir = $mir;
    }

    public function index(): array {
        $devices = Device::with('mirStatus')->orderBy('name')->get();

        return [
            'status' => 0,
            'data'   => [
                'devices' => $devices
            ]
        ];
    }

    public function show($id): array {
        $device = Device::with('mirStatus')->find($id);

        return [
            'status' => 0,
            'data'   => [
                'device' => $device
            ]
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function update(DeviceRequest $request, $id): Response|array|Application|ResponseFactory {
        $validator = Validator::make($request->all(), [
            'ap'   => "nullable|url:http,https|max:255",
            'ip'   => "nullable|ip",
            'name' => 'required|max:255',
        ], [], [
            'ap'   => 'AP連線',
            'ip'   => 'IP連線',
            'name' => '名稱'
        ]);
        if($validator->fails()) {
            return [
                'status' => config('errors.data_repeat'),
                'data'   => [
                    'errors' => $validator->errors()
                ]
            ];
        }

        $device = Device::find($id);
        $device = $this->saveModel($request, $device);

        return [
            'status' => 0,
            'data'   => [
                'device' => $device
            ]
        ];
    }

    /**
     * @throws GuzzleException
     */
    private function saveModel(DeviceRequest $request, Device $device): Device {
        $device->update($request->validated());
        if(!$device->mirStatus) {
            $mirStatus = new MirStatus();
            $mirStatus->device_id = $device->id;
            $mirStatus->save();
        } else {
            $mirStatus = $device->mirStatus;
        }
        $this->mir->postRegister(112, $mirStatus->initial_petri_count);
        $mirStatus->update($request->validated('mir_status'));

        return $device;
    }

    public function updateIsConnected(Request $request, $id): array {
        $device = Device::findOrFail($id);
        $processNames = [
            'amdr-check-vehicle-connection:*'
        ];
        $command = [];
        try {
            $isConnected = $request->input('is_connected');
            if($isConnected) {
                $this->mir->getWifiConnections();
                $success = $this->mir->getWifiConnections();
                if($success) {
                    $device->is_connected = $isConnected;
                    $device->connected_at = Carbon::now();
                    $command = array_merge(['supervisorctl', 'start'], $processNames);
                }
            } else {
                $device->is_connected = 0;
                $command = array_merge(['supervisorctl', 'stop'], $processNames);
            }
            $device->save();
        } catch(GuzzleException) {
            $device->is_connected = 0;
            $device->save();
            $command = array_merge(['supervisorctl', 'stop'], $processNames);
        }
        if(count($command) > 0) {
            $process = new Process($command);
            $process->run();
        }
        event(new DeviceUpdated($device));

        return [
            'status' => 0,
            'data'   => [
                'device' => $device
            ]
        ];
    }
}
