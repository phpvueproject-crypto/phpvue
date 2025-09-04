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
        Schema::table('edges', function(Blueprint $table) {
            $table->unsignedTinyInteger('is_deploy')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('edges', function(Blueprint $table) {
            $table->dropColumn('is_deploy');
        });
    }
};
