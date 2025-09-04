<?php

namespace App\Http\Controllers;

use App\Events\RegionMgmtUpdated;
use App\Models\RegionMgmt;
use Illuminate\Http\Request;

class ChannelController extends Controller {
    public function trigger(Request $request): void {
        $channel = $request->input('channel');
        if(str_starts_with($channel, 'presence-regionMgmts.presence.')) {
            $regionMgmtId = str_replace('presence-regionMgmts.presence.', '', $channel);
            $regionMgmt = RegionMgmt::find($regionMgmtId);
            if($regionMgmt) {
                $event = $request->input('event');
                if($event == 'leave') {
                    $regionMgmt->edit_user_id = null;
                    $regionMgmt->edited_at = null;
                    $regionMgmt->save();
                    event(new RegionMgmtUpdated($regionMgmt));
                } else if($event == 'join') {
                    event(new RegionMgmtUpdated($regionMgmt));
                }
            }
        }
    }
}
