<?php

namespace App\Http\Controllers;

use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MirController extends Controller {
    /**
     * @throws FileNotFoundException
     */
    public function getRegisters($id) {
        return json_decode(File::get(storage_path("app/mir/GetRegister{$id}.json")), true);;
    }

    /**
     * @throws FileNotFoundException
     */
    public function postRegisters(Request $request, $id) {
        $path = storage_path('app/mir/GetRegister.json');
        $register = json_decode(File::get($path), true);
        $register['value'] = $request->input('value');
        File::put($path, json_encode($register));
        return json_decode(File::get($path), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getStatus() {
        return json_decode(File::get(storage_path('app/mir/GetStatus.json')), true);
    }

    public function putStatus(): Response|Application|ResponseFactory {
        return response(null, 200);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMissions() {
        return json_decode(File::get(storage_path('app/mir/GetMissions.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMissionQueues(): mixed {
        return json_decode(File::get(storage_path('app/mir/GetMissionQueues.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMissionQueue() {
        return json_decode(File::get(storage_path('app/mir/GetMissionQueue.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function postMissionQueue(): Response|Application|ResponseFactory {
        return response(null, 200);
    }

    /**
     * @throws FileNotFoundException
     */
    public function deleteMissionQueue(): Response|Application|ResponseFactory {
        return response(null, 200);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMaps() {
        return json_decode(File::get(storage_path('app/mir/GetMaps.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMap($id) {
        return json_decode(File::get(storage_path('app/mir/GetMap.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getHookStatus() {
        return json_decode(File::get(storage_path('app/mir/GetHookStatus.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMission($id) {
        return json_decode(File::get(storage_path('app/mir/GetMission.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getPositions($mapId) {
        return json_decode(File::get(storage_path('app/mir/GetPositions.json')), true);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getPosition($id) {
        return json_decode(File::get(storage_path('app/mir/GetPosition.json')), true);
    }

    public function getWifiConnections(): array {
        return ['status' => 0];
    }

    /**
     * @throws FileNotFoundException
     */
    public function getErrorReports($id) {
        return json_decode(File::get(storage_path('app/mir/GetErrorReports.json')), true);
    }
}
