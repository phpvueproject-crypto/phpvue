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
            $table->integer('initial_petri_count')->nullable()->comment('培養皿總數量');
            $table->integer('remaining_petri_count')->nullable()->comment('培養皿剩餘數量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->dropColumn(['initial_petri_count', 'remaining_petri_count']);
        });
    }
};
