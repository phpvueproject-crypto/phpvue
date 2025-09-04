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
        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->renameColumn('`mopping motor_status`', 'mopping_motor_status');
            $table->renameColumn('`air laser sensor_status`', 'air_laser_sensor_status');
            $table->renameColumn('`Depth camera_status`', 'depth_camera_status');
            $table->renameColumn('`pipe import_status`', 'pipe_import_status');
            $table->renameColumn('`sweep mode_status`', 'sweep_mode_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->renameColumn('mopping_motor_status', '`mopping motor_status`');
            $table->renameColumn('air_laser_sensor_status', '`air laser sensor_status`');
            $table->renameColumn('depth_camera_status', '`Depth camera_status`');
            $table->renameColumn('pipe_import_status', '`pipe import_status`');
            $table->renameColumn('sweep_mode_status', '`sweep mode_status`');
        });
    }
};
