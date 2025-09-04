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
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->string('mission_queue_id')->nullable();
            $table->string('map_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->dropColumn('mission_queue_id');
            $table->dropColumn('map_id');
        });
    }
};
