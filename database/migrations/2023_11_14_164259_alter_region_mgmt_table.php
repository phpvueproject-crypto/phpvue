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
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('x_px')->nullable();
            $table->unsignedInteger('y_px')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropPrimary('region_mgmt_pkey');
            $table->dropColumn('x_px');
            $table->dropColumn('y_px');
        });
    }
};
