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
        Schema::create('edges', function(Blueprint $table) {
            $table->unsignedMediumInteger('id', true);
            $table->unsignedMediumInteger('map_id');
            $table->unsignedSmallInteger('direction')->default(0);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->unsignedMediumInteger('start_vertex_id');
            $table->unsignedMediumInteger('end_vertex_id');
            $table->unsignedSmallInteger('enable')->default(1);
            $table->timestamps();
            $table->foreign('map_id')->references('id')->on('maps')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('start_vertex_id')->references('id')->on('vertices')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('end_vertex_id')->references('id')->on('vertices')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('edges');
    }
};
