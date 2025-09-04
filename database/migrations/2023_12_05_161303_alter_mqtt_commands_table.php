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
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->dropColumn('region');
            $table->unsignedBigInteger('region_mgmt_id')->nullable();
            $table->foreign('region_mgmt_id')->references('id')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->string('region');
            $table->dropConstrainedForeignId('region_mgmt_id');
        });
    }
};
