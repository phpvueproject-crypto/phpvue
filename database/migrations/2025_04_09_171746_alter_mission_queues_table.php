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
        Schema::table('mission_queues', function(Blueprint $table) {
            $table->text('remark')->nullable();
            $table->foreignId('start_location_id')->nullable()->constrained('location')->restrictOnUpdate(
            )->restrictOnDelete();
            $table->foreignId('end_location_id')->nullable()->constrained('location')->restrictOnUpdate(
            )->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('mission_queues', function(Blueprint $table) {
            $table->dropColumn('remark');
            $table->dropConstrainedForeignId('start_location_id');
            $table->dropConstrainedForeignId('end_location_id');
        });
    }
};
