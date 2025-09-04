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
        Schema::create('clean_statuses', function(Blueprint $table) {
            $table->string('cleanstation_ID')->primary();
            $table->string('cleanstation_status')->nullable();
            $table->string('door_status')->nullable();
            $table->string('cylinder_status')->nullable();
            $table->unsignedInteger('temperature')->nullable();
            $table->unsignedInteger('humidity')->nullable();
            $table->unsignedInteger('pressure_difference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('clean_statuses');
    }
};
