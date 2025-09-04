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
        Schema::table('vehicle_mgmt', function(Blueprint $table) {
            $table->unsignedSmallInteger('stoppable_second')->default(5);
            $table->unsignedTinyInteger('chargeable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_mgmt', function(Blueprint $table) {
            $table->dropColumn('stoppable_second');
            $table->dropColumn('chargeable');
        });
    }
};
