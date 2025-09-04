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
            $table->decimal('resolution', 15)->default(1);
            $table->decimal('origin_x', 15)->default(0);
            $table->decimal('origin_y', 15)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropColumn('resolution');
            $table->dropColumn('origin_x');
            $table->dropColumn('origin_y');
        });
    }
};
