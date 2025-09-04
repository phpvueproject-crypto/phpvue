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
        Schema::create('mir_statuses', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->jsonb('position');
            $table->string('robot_model');
            $table->string('mission_text');
            $table->jsonb('velocity');
            $table->unsignedDouble('battery_percentage');
            $table->timestamps();
            $table->foreign('device_id')->references('id')->on('devices')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('mir_statuses');
    }
};
