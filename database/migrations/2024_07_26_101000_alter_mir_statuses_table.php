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
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('location')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
        });
    }
};
