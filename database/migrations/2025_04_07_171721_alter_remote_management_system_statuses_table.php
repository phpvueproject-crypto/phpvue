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
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            $table->unsignedSmallInteger('total_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            $table->dropColumn('total_time');
        });
    }
};
