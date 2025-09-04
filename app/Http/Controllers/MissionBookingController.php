<?php

namespace App\Http\Controllers;

use App\Http\Requests\MissionBookingRequest;
use App\Models\MissionBooking;

class MissionBookingController extends Controller {
    // 顯示多筆
    public function index(): array {
        $pagination = MissionBooking::with('mission')->orderByDesc('id')->paginate(20);

        return [
            'status' => 0,
            'data'   => [
                'missionBookings' => $pagination->items(),
                'pagination'      => [
                    'last_page'    => $pagination->lastPage(),
                    'current_page' => $pagination->currentPage()
                ],
            ]
        ];
    }

    // 顯示一筆
    public function show($id): array {
        $missionBooking = MissionBooking::findOrFail($id);

        return [
            'status' => 0,
            'data'   => [
                'missionBooking' => $missionBooking
            ]
        ];
    }

    // 新增
    public function store(MissionBookingRequest $request): array {
        $missionBooking = MissionBooking::create($request->all());

        return [
            'status' => 0,
            'data'   => [
                'missionBooking' => $missionBooking
            ]
        ];
    }

    // 更新
    public function update(MissionBookingRequest $request, $id): array {
        $missionBooking = MissionBooking::findOrFail($id);
        $missionBooking->update($request->all());

        return [
            'status' => 0,
            'data'   => [
                'missionBooking' => $missionBooking
            ]
        ];
    }

    // 刪除
    public function destroy($id): array {
        $missionBooking = MissionBooking::find($id);

        if(!$missionBooking) {
            return ['status' => 1, 'message' => 'Mission Booking not found'];
        }

        $missionBooking->delete();

        return [
            'status' => 0
        ];
    }
}
