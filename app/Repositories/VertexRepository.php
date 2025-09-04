<?php

namespace App\Repositories;

use App\Events\VertexUpdated;
use App\Models\MqttCommand;
use App\Models\Vertex;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Queue;

class VertexRepository {
    public function disableVertex($vertexNames, Vertex $vertex): void {
        $mqttCommand = new MqttCommand();
        $mqttCommand->mqtt_command_type_id = 15;
        $mqttCommand->region_mgmt_id = $vertex->region_mgmt_id;
        $mqttCommand->user_id = Auth::id();
        $mqttCommand->data = $vertexNames;
        $mqttCommand->send_command = json_encode($mqttCommand->preview_send_command);
        Queue::connection('rabbitmq')->pushRaw($mqttCommand->send_command);
        $mqttCommand->save();
    }

    public function getVertex($vertexName, $deviceName): Model|Builder|Vertex {
        return Vertex::whereIsDeploy(1)->whereHas('vertexConfigurations', function(Builder $query) use ($deviceName, $vertexName) {
            if($vertexName) {
                $query->where('type', 'vertex_name');
                $query->where('data', $vertexName);
            } else {
                $query->where('type', 'device_name');
                $query->where('data', $deviceName);
            }
        })->firstOrFail();
    }
}
