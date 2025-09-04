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
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->unsignedBigInteger('mqtt_command_type_id')->nullable();
            $table->unsignedTinyInteger('laser_detection')->default(0);
            $table->foreign('mqtt_command_type_id')->references('id')->on('mqtt_command_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->dropColumn('mqtt_command_type_id');
        });
    }
};
