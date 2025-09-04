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
            $table->unsignedSmallInteger('cad_width')->nullable();
            $table->unsignedSmallInteger('cad_height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropColumn('cad_width');
            $table->dropColumn('cad_height');
        });
    }
};
