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
        Schema::table('elevator_mgmt', function(Blueprint $table) {
            $table->dropConstrainedForeignId('vertex_id');
            $table->foreign('elevator_id')->references('obj_id')->on('object_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('elevator_mgmt', function(Blueprint $table) {
            $table->unsignedBigInteger('vertex_id')->nullable();
            $table->foreign('vertex_id')->references('id')->on('vertices')->onUpdate('set null')->onDelete('set null');
            $table->dropForeign('elevator_mgmt_elevator_id_foreign');
        });
    }
};
