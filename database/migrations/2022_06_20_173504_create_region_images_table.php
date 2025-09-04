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
        Schema::create('region_images', function(Blueprint $table) {
            $table->id();
            $table->string('region', 256)->nullable();
            $table->string('name', 50);
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
        Schema::dropIfExists('region_images');
    }
};
