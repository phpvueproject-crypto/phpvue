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
        Schema::table('edges', function(Blueprint $table) {
            $table->unsignedBigInteger('region_mgmt_id');
            $table->foreign('region_mgmt_id')->references('id')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('edges', function(Blueprint $table) {
            $table->dropSoftDeletes('region_mgmt_id');
        });
    }
};
