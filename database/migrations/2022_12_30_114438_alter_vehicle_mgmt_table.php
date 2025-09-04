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
        Schema::table('vehicle_mgmt', function(Blueprint $table) {
            $table->double('position_x')->nullable()->comment('清消機器人的當前位置X軸');
            $table->double('position_y')->nullable()->comment('清消機器人的當前位置Y軸');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_mgmt', function(Blueprint $table) {
            $table->dropColumn('position_x');
            $table->dropColumn('position_y');
        });
    }
};
