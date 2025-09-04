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
            $table->smallInteger('theta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_mgmt', function(Blueprint $table) {
            $table->dropColumn('theta');
        });
    }
};
