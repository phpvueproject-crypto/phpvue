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
        Schema::create('production.charger_status', function(Blueprint $table) {
            $table->timestamp('update_ts');
            $table->string('charging_station_id');
            $table->string('booking');
            $table->string('booking_owner');
            $table->string('charging_vehicle_id');
            $table->string('charging_station_status');
            $table->primary('charging_station_id', 'parking_status_pk');
            $table->foreign('charging_station_id', 'charger_status_charger_mgmt_charging_station_id_fk')->references('charging_station_id')->on('charger_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('charging_vehicle_id', 'charger_status_charger_mgmt_vehicle_id_fk')->references('vehicle_id')->on('vehicle_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->unique('charging_vehicle_id', 'charger_status_charging_vehicle_id_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('production.charger_status');
    }
};
