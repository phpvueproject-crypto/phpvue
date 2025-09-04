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
            $table->float('position_x')->change();
            $table->float('position_y')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_mgmt', function(Blueprint $table) {
            $table->decimal('position_x', 15)->change();
            $table->decimal('position_y', 15)->change();
        });
    }
};
