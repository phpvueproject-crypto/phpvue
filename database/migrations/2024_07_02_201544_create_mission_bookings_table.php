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
        Schema::create('mission_bookings', function(Blueprint $table) {
            $table->id();
            $table->string('mission_id');
            $table->string('mission_name');
            $table->string('schedule');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('mission_bookings');
    }
};
