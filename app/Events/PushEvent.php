<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PushEvent implements ShouldBroadcastNow {
    private $channel;
    private $action;
    private $data;

    public function __construct($channel, $action, $data = []) {
        $this->channel = $channel;
        $this->action = $action;
        $this->data = $data;
    }

    public function broadcastOn() {
        return new PrivateChannel($this->channel);
    }

    public function broadcastWith() {
        return [
            'action' => $this->action,
            'data'   => (array)$this->data
        ];
    }
}
