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
        Schema::create('user_vehicle_mgmt', function(Blueprint $table) {
            $table->string('vehicle_id', 256);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicle_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['user_id', 'vehicle_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('user_vehicle_mgmt');
    }
};
