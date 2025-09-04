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
        Schema::create('mqtt_commands', function(Blueprint $table) {
            $table->id();
            $table->string('vehicle_id')->nullable();
            $table->string('obj_port_id')->nullable();
            $table->jsonb('command');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicle_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('obj_port_id')->references('obj_port_id')->on('object_port_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('mqtt_commands');
    }
};
