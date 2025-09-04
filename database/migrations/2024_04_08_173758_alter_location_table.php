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
        Schema::table('location', function(Blueprint $table) {
            $table->unsignedDouble('x_px')->nullable();
            $table->unsignedDouble('y_px')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('location', function(Blueprint $table) {
            $table->dropColumn('x_px');
            $table->dropColumn('y_px');
        });
    }
};
