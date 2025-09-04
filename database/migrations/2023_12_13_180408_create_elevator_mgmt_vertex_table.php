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
        Schema::table('configure.elevator_mgmt', function(Blueprint $table) {
            $table->primary(['elevator_id']);
        });

        Schema::create('ui.elevator_mgmt_vertex', function(Blueprint $table) {
            $table->unsignedBigInteger('vertex_id');
            $table->string('elevator_id');
            $table->foreign('vertex_id')->references('id')->on('vertices')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('elevator_id')->references('elevator_id')->on('elevator_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['vertex_id', 'elevator_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('configure.elevator_mgmt', function(Blueprint $table) {
            $table->dropPrimary(['elevator_mgmt_pkey']);
        });
        Schema::dropIfExists('ui.elevator_mgmt_vertex');
    }
};
