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
        Schema::create('undeploy_locations', function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('floor')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('vertex_id');
            $table->timestamps();
            $table->foreign('vertex_id')->references('id')->on('vertices')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('undeploy_locations');
    }
};
