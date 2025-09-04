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
            $table->integer('mm')->default(0);
            $table->dropColumn('actual_width');
            $table->dropColumn('actual_height');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropColumn('mm');
            $table->unsignedSmallInteger('actual_width')->default(0);
            $table->unsignedSmallInteger('actual_height')->default(0);
        });
    }
};
