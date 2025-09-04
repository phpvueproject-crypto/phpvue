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
        Schema::table('event_class', function(Blueprint $table) {
            $table->unsignedSmallInteger('event_level')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('event_class', function(Blueprint $table) {
            $table->dropColumn('event_level');
        });
    }
};
