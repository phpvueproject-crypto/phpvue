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
        Schema::create('location_mission_queue', function(Blueprint $table) {
            $table->foreignId('location_id')->constrained('location')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('mission_queue_id')->constrained('mission_queues')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('location_mission_queue');
    }
};
