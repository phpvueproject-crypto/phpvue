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
        Schema::create('maps', function(Blueprint $table) {
            $table->unsignedMediumInteger('id', true);
            $table->string('name', 30);
            $table->string('region', 256);
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('maps');
    }
};
