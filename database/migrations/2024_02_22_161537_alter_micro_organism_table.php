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
        Schema::table('micro_organism', function(Blueprint $table) {
            $table->renameColumn('location_name', 'device_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('micro_organism', function(Blueprint $table) {
            $table->renameColumn('device_name', 'location_name');
        });
    }
};
