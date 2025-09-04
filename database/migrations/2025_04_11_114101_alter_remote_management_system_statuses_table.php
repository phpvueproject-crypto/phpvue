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
            $table->unsignedSmallInteger('sampling_point')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            $table->dropColumn('sampling_point');
        });
    }
};
