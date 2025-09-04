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
        Schema::create('edge_configurations', function(Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('edge_id');
            $table->string('type', 30);
            $table->string('data', 256);
            $table->timestamps();
            $table->foreign('edge_id')->references('id')->on('edges')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('edge_configurations');
    }
};
