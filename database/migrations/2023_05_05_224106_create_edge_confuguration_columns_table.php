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
        Schema::create('edge_configuration_types', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('validate')->nullable();
            $table->string('view_type');
            $table->unsignedTinyInteger('is_unique')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('edge_configuration_types');
    }
};
