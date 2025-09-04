<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ActionNodeCreated implements ShouldBroadcastNow {
    private array $actionNode;

    public function __construct(array $actionNode) {
        $this->actionNode = $actionNode;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('actionNodes')
        ];
    }

    public function broadcastWith(): array {
        return [
            'actionNode' => $this->actionNode
        ];
    }
}
