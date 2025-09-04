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
        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->unsignedBigInteger('vertex_id');
            $table->foreign('vertex_id')->references('id')->on('vertices')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->dropForeign('vehicle_status_vertex_id_foreign');
            $table->dropColumn('vertex_id');
        });
    }
};
