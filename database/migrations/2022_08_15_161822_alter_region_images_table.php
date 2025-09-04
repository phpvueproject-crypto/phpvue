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
        Schema::table('region_images', function(Blueprint $table) {
            $table->string('md5_file', 32)->default('00000000000000000000000000000000');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_images', function(Blueprint $table) {
            $table->dropColumn('md5_file');
        });
    }
};
