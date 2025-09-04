<?php

use App\Models\MqttCommand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->string('command_id')->nullable();
        });

        $mqttCommands = MqttCommand::all();
        foreach($mqttCommands as $mqttCommand) {
            $command = json_decode($mqttCommand->command, true);
            $mqttCommand->command_id = $command['id']['uuid'];
            $mqttCommand->save();
        }

        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->string('command_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->dropColumn('command_id');
        });
    }
};
