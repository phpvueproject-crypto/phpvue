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
        Schema::table('location', function(Blueprint $table) {
            $table->renameColumn('code', 'device_name');
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('location', function(Blueprint $table) {
            $table->renameColumn('device_name', 'code');
            $table->string('name');
        });
    }
};
