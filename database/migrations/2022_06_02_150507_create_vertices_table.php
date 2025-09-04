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
        Schema::create('vertices', function(Blueprint $table) {
            $table->unsignedMediumInteger('id', true);
            $table->unsignedMediumInteger('map_id');
            $table->mediumInteger('x');
            $table->mediumInteger('y');
            $table->mediumInteger('z');
            $table->timestamps();
            $table->foreign('map_id')->references('id')->on('maps')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('vertices');
    }
};
