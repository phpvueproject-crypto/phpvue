<?php

use App\Models\MqttCommand;
use App\Models\ObjectMgmt;
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
            $table->string('region')->nullable();
            $table->unsignedTinyInteger('is_completed')->default(0);
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });

        $mqttCommands = MqttCommand::all();
        /** @var MqttCommand $mqttCommand */
        foreach($mqttCommands as $mqttCommand) {
            $objectMgmt = ObjectMgmt::whereObjId($mqttCommand->vehicle_id)->first();
            if(!$objectMgmt)
                continue;

            $mqttCommand->region = $objectMgmt->region;
            $mqttCommand->save();
        }

        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->string('region')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('is_completed');
        });
    }
};
