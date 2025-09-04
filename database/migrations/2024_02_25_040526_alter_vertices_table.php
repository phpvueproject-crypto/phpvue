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
        Schema::table('vertices', function(Blueprint $table) {
            $table->float('c_x_px')->nullable();
            $table->float('c_y_px')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vertices', function(Blueprint $table) {
            $table->dropColumn('c_x_px');
            $table->dropColumn('c_y_px');
        });
    }
};
