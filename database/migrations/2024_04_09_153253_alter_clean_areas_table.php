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
        Schema::table('clean_areas', function(Blueprint $table) {
            $table->string('vehicle_mgmt_id')->nullable();
            $table->foreign('vehicle_mgmt_id')->references('vehicle_id')->on('vehicle_mgmt')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('clean_areas', function(Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_mgmt_id');
        });
    }
};
