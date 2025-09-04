<?php

namespace App\Jobs;

use Str;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;

class RabbitMQJob extends BaseJob {
    public function payload() {
        $data = json_decode($this->getRawBody(), true);
        return [
            'job'  => '\App\Jobs\MqttReceiver@handle',
            'data' => $data
        ];
    }

    public function getRawBody(): string {
        $data = json_decode($this->message->getBody(), true);
        if(!isset($data['uuid'])) {
            $data['uuid'] = Str::uuid()->toString();
        }
        return json_encode($data);
    }
}
