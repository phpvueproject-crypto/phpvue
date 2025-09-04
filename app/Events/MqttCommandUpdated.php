<?php

namespace App\Events;

use App\Models\MqttCommand;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MqttCommandUpdated implements ShouldBroadcastNow {
    private MqttCommand $mqttCommand;
    private bool $isRabbitMq;

    public function __construct(MqttCommand $mqttCommand, bool $isRabbitMq = false) {
        $this->mqttCommand = $mqttCommand;
        $this->isRabbitMq = $isRabbitMq;
    }

    public function broadcastOn(): array {
        if(!$this->isRabbitMq) {
            return [
                new PrivateChannel('transferProcessings'),
                new PrivateChannel('mqttCommands'),
                new PrivateChannel("mqttCommands.{$this->mqttCommand->id}")
            ];
        } else {
            return [
                new PrivateChannel('rabbitmq'),
            ];
        }
    }

    public function broadcastWith(): array {
        $this->mqttCommand->send_command = null;
        return [
            'mqttCommand' => $this->mqttCommand->toArray()
        ];
    }
}
