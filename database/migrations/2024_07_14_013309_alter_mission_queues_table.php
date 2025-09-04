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
            $table->string('mission_id')->nullable();
            $table->foreign('mission_id')->references('guid')->on('missions')->onUpdate('cascade')->onDelete('cascade');
            $table->dropColumn('mission_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('mission_queues', function(Blueprint $table) {
            $table->dropConstrainedForeignId('mission_id');
            $table->string('mission_name');
        });
    }
};
