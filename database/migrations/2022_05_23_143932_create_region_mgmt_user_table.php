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
        Schema::create('region_mgmt_user', function(Blueprint $table) {
            $table->string('region', 256);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['user_id', 'region']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('region_mgmt_user');
    }
};
