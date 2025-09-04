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
        Schema::table('vertices', function(Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('parking_mgmt', function(Blueprint $table) {
            $table->foreign('parking_space_location')->references('name')->on('vertices')->onUpdate('set null')->onDelete('set null');
        });

        Schema::table('charger_mgmt', function(Blueprint $table) {
            $table->foreign('charging_station_location')->references('name')->on('vertices')->onUpdate('set null')->onDelete('set null');
        });

        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->foreign('vehicle_location')->references('name')->on('vertices')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('parking_mgmt', function(Blueprint $table) {
            $table->dropForeign('parking_mgmt_parking_space_location_foreign');
        });

        Schema::table('charger_mgmt', function(Blueprint $table) {
            $table->dropForeign('charger_mgmt_charging_station_location_foreign');
        });

        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->dropForeign('vehicle_status_vehicle_location_foreign');
        });

        Schema::table('vertices', function(Blueprint $table) {
            $table->dropUnique('vertices_name_unique');
        });
    }
};
