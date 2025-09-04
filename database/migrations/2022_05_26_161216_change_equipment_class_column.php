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
        Schema::table('equipment_class', function(Blueprint $table) {
            $table->string('name', 30)->nullable();
            $table->tinyInteger('enable')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('equipment_class', function(Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('enable');
        });
    }
};
