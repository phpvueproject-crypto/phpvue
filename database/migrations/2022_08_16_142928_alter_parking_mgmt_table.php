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
        Schema::table('parking_mgmt', function(Blueprint $table) {
            $table->string('parking_space_location')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('parking_mgmt', function(Blueprint $table) {
            $table->string('parking_space_location')->nullable(false)->change();
        });
    }
};
