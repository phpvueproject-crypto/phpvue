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
        Schema::table('charger_mgmt', function(Blueprint $table) {
            $table->string('charging_station_location')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('charger_mgmt', function(Blueprint $table) {
            $table->string('charging_station_location')->nullable(false)->change();
        });
    }
};
