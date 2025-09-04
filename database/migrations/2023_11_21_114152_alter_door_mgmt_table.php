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
        Schema::table('door_mgmt', function(Blueprint $table) {
            $table->unsignedBigInteger('edge_id')->nullable();
            $table->foreign('edge_id')->references('id')->on('edges')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('door_mgmt', function(Blueprint $table) {
            $table->dropColumn('edge_id');
        });
    }
};
