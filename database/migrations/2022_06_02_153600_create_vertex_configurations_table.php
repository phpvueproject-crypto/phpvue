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
        Schema::create('vertex_configurations', function(Blueprint $table) {
            $table->unsignedMediumInteger('id', true);
            $table->unsignedMediumInteger('vertex_id');
            $table->string('type', 30);
            $table->string('data', 256);
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
        Schema::dropIfExists('vertex_configurations');
    }
};
