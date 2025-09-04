<?php

namespace App\Events;

use App\Models\PollutionCondition;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PollutionConditionUpdated implements ShouldBroadcastNow {
    private PollutionCondition $pollutionCondition;

    public function __construct(PollutionCondition $pollutionCondition) {
        $this->pollutionCondition = $pollutionCondition;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('pollutionConditions'),
        ];
    }

    public function broadcastWith(): array {
        return [
            'pollutionCondition' => $this->pollutionCondition->toArray()
        ];
    }
}
