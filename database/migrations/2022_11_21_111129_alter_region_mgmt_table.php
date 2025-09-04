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
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->string('md5_file', 32)->default('00000000000000000000000000000000');
        });

        Schema::dropIfExists('region_images');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropColumn('md5_file');
        });

        Schema::create('region_images', function(Blueprint $table) {
            $table->id();
            $table->string('region', 256)->nullable();
            $table->string('name', 50);
            $table->string('md5_file', 32)->default('00000000000000000000000000000000');
            $table->timestamps();
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
