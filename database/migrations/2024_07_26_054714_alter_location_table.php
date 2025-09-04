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
            $table->renameColumn('qr_code', 'bar_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('location', function(Blueprint $table) {
            $table->renameColumn('bar_code', 'qr_code');
        });
    }
};
