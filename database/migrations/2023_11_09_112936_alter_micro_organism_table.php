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
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('location')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('micro_organism', function(Blueprint $table) {
            $table->dropForeign('micro_organism_location_id_foreign');
            $table->dropColumn('location_id');
        });
    }
};
