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
        Schema::table('mqtt_command_types', function(Blueprint $table) {
            $table->string('typename')->nullable();
            $table->unsignedTinyInteger('is_mission')->default(0);
            $table->string('sender_type')->default('ui');
            $table->string('sender_name')->default('ui');
            $table->string('receiver_type')->default('acs');
            $table->string('receiver_name')->default('acs');
            $table->string('mission_type')->nullable();
            $table->unsignedTinyInteger('is_schedule')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mqtt_command_types', function(Blueprint $table) {
            $table->dropColumn('typename');
            $table->dropColumn('is_mission');
            $table->dropColumn('sender_type');
            $table->dropColumn('sender_name');
            $table->dropColumn('receiver_type');
            $table->dropColumn('receiver_name');
            $table->dropColumn('mission_type');
            $table->dropColumn('is_schedule');
        });
    }
};
