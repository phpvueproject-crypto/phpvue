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
        Schema::table('room_environment.newtable', function(Blueprint $table) {
            $table->unsignedBigInteger('region_id');
            $table->foreign('region_id')->references('id')->on('region_mgmt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('room_environment.newtable', function(Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
