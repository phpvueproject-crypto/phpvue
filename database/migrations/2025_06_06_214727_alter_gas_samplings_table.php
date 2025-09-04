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
        Schema::table('gas_samplings', function(Blueprint $table) {
            $table->foreignId('remote_management_system_status_id')->nullable()->constrained('remote_management_system_statuses')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('gas_samplings', function(Blueprint $table) {
            $table->dropConstrainedForeignId('remote_management_system_status_id');
        });
    }
};
