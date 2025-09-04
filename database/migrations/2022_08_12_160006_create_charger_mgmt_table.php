<?php

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
        Schema::create('configure.charger_mgmt', function(Blueprint $table) {
            $table->string('charging_station_id');
            $table->string('charging_station_location');
            $table->string('prefer_vehicle');
            $table->primary('charging_station_id', 'charger_mgmt_pk');
            $table->unique('charging_station_location', 'charger_mgmt_charging_station_location_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('configure.charger_mgmt');
    }
};
