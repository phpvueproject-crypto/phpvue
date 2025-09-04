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
            $table->timestamp('start_time')->nullable();
            $table->dropColumn('step_1_datetime');
            $table->dropColumn('step_2_datetime');
            $table->dropColumn('step_3_datetime');
            $table->dropColumn('step_4_datetime');
            $table->dropColumn('step_5_datetime');
            $table->dropColumn('step_6_datetime');
            $table->dropColumn('step_7_datetime');
            $table->dropColumn('step_8_datetime');
            $table->dropColumn('step_9_datetime');
            $table->dropColumn('step_10_datetime');
            $table->dropColumn('step_11_datetime');
            $table->dropColumn('step_12_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('remote_management_system_statuses', function(Blueprint $table) {
            $table->dropColumn('start_time');
            $table->dateTime('step_1_datetime')->nullable();
            $table->dateTime('step_2_datetime')->nullable();
            $table->dateTime('step_3_datetime')->nullable();
            $table->dateTime('step_4_datetime')->nullable();
            $table->dateTime('step_5_datetime')->nullable();
            $table->dateTime('step_6_datetime')->nullable();
            $table->dateTime('step_7_datetime')->nullable();
            $table->dateTime('step_8_datetime')->nullable();
            $table->dateTime('step_9_datetime')->nullable();
            $table->dateTime('step_10_datetime')->nullable();
            $table->dateTime('step_11_datetime')->nullable();
            $table->dateTime('step_12_datetime')->nullable();
        });
    }
};
