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
        Schema::create('vehicle_colors', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('vehicle_id', 256);
            $table->string('color', 7);
            $table->timestamps();
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicle_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('vehicle_colors');
    }
};
