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
        Schema::table('elevator_mgmt_vertex', function(Blueprint $table) {
            $table->renameColumn('elevator_id', 'elevator_mgmt_elevator_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('elevator_mgmt_vertex', function(Blueprint $table) {
            $table->renameColumn('elevator_mgmt_elevator_id', 'elevator_id');
        });
    }
};
