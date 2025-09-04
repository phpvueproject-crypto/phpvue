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
            $table->float('resolution')->change();
            $table->float('origin_x')->change();
            $table->float('origin_y')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->decimal('resolution', 15)->change();
            $table->decimal('origin_x', 15)->change();
            $table->decimal('origin_y', 15)->change();
        });
    }
};
