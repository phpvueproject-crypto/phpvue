<?php

namespace App\Http\Controllers;

use App\Events\MirStatusUpdated;
use App\Models\Device;
use App\Models\MirStatus;
use App\Repositories\MirRepository;
use GuzzleHttp\Exception\GuzzleException;

class MirStatusController extends Controller {
    private MirRepository $mir;

    public function __construct(MirRepository $mir) {
        $this->mir = $mir;
    }

    public function index(): array {
        $device = Device::whereIsConnected(1)->first();
        $mirStatuesPagination = MirStatus::whereDeviceId($device?->id)->orderByDesc('created_at')->paginate(10);

        return [
            'status' => 0,
            'data'   => [
                'mirStatuses' => $mirStatuesPagination->items(),
                'pagination'  => [
                    'last_page'    => $mirStatuesPagination->lastPage(),
                    'current_page' => $mirStatuesPagination->currentPage()
                ],
                'device'      => $device
            ]
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function clearVehicleErrorTypeId($id): array {
        $this->mir->resetError();

        $mirStatus = MirStatus::findOrFail($id);
        $mirStatus->vehicle_error_type_id = null;
        $mirStatus->vehicle_error_message = null;
        $mirStatus->save();
        event(new MirStatusUpdated($mirStatus));

        return [
            'status' => 0
        ];
    }
}
