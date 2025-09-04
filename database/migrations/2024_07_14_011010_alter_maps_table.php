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
        Schema::table('maps', function(Blueprint $table) {
            $table->unsignedBigInteger('region_mgmt_id')->nullable();
            $table->foreign('region_mgmt_id')->references('id')->on('region_mgmt')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('maps', function(Blueprint $table) {
            $table->dropColumn('region_mgmt_id');
        });
    }
};
