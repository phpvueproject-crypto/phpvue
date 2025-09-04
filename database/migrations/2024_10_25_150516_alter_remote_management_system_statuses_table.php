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
            $table->dateTime('step_1_datetime')->nullable();
            $table->dateTime('step_2_datetime')->nullable(); // Equivalent to step_1_end
            $table->dateTime('step_3_datetime')->nullable();
            $table->dateTime('step_4_datetime')->nullable();
            $table->dateTime('step_5_datetime')->nullable();
            $table->dateTime('step_6_datetime')->nullable();
            $table->dateTime('step_7_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            // Use more descriptive names for datetime fields
            $table->dropColumn('step_1_datetime');
            $table->dropColumn('step_2_datetime'); // Equivalent to step_1_end
            $table->dropColumn('step_3_datetime');
            $table->dropColumn('step_4_datetime');
            $table->dropColumn('step_5_datetime');
            $table->dropColumn('step_6_datetime');
            $table->dropColumn('step_7_datetime');
        });
    }
};
