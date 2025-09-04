<?php

namespace App\Http\Controllers;

use App\Repositories\MirRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;

class HookStatusController extends Controller {
    private MirRepository $mir;

    public function __construct(MirRepository $mir) {
        $this->mir = $mir;
    }

    /**
     * @throws GuzzleException
     */
    public function hookStatus(): array {
        $hookStatus = null;
        $status = 0;
        try {
            $hookStatus = $this->mir->getHookStatus();
        } catch(GuzzleException) {
            $status = config('errors.mir_vehicle_disconnected');
        }

        return [
            'status' => $status,
            'data'   => [
                'hookStatus' => $hookStatus
            ]
        ];
    }
}
