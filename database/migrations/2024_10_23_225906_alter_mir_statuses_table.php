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
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->string('current_status')->nullable();
            $table->foreignId('vehicle_error_type_id')->nullable()->constrained('vehicle_error_types')->nullOnDelete();
            $table->text('vehicle_error_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->dropColumn('current_status');
            $table->dropConstrainedForeignId('vehicle_error_type_id');
            $table->dropColumn('vehicle_error_message');
        });
    }
};
