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
        Schema::create('clean_areas', function(Blueprint $table) {
            $table->id();
            $table->string('region', 256);
            $table->float('start_goal_x');
            $table->float('start_goal_y');
            $table->float('end_goal_x');
            $table->float('end_goal_y');
            $table->unsignedTinyInteger('enable')->default(1);
            $table->timestamps();
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('clean_areas');
    }
};
