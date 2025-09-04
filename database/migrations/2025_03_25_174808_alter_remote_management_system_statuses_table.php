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
    public function up(): void {
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            $table->unsignedSmallInteger('r2_value')->nullable();
            $table->unsignedSmallInteger('r3_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            $table->dropColumn('r2_value');
            $table->dropColumn('r3_value');
        });
    }
};
