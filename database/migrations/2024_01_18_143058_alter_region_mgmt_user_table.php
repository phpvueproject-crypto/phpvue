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
        Schema::table('region_mgmt_user', function(Blueprint $table) {
            $table->dropColumn('region');
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
        Schema::table('region_mgmt_user', function(Blueprint $table) {
            $table->dropConstrainedForeignId('region_mgmt_id');
            $table->unsignedBigInteger('region');
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
