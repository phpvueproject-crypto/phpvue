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
        Schema::create('turning_points', function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clean_area_id');
            $table->float('x');
            $table->float('y');
            $table->timestamps();
            $table->foreign('clean_area_id')->references('id')->on('clean_areas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('turning_points');
    }
};
