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
            $table->string('device_name')->nullable();
            $table->float('sweep_start_goal_x')->nullable();
            $table->float('sweep_start_goal_y')->nullable();
            $table->float('sweep_end_goal_x')->nullable();
            $table->float('sweep_end_goal_y')->nullable();
            $table->float('goal_x')->nullable();
            $table->float('goal_y')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->dropColumn('device_name');
            $table->dropColumn('sweep_start_goal_x');
            $table->dropColumn('sweep_start_goal_y');
            $table->dropColumn('sweep_end_goal_x');
            $table->dropColumn('sweep_end_goal_y');
            $table->dropColumn('goal_x');
            $table->dropColumn('goal_y');
        });
    }
};
