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
        Schema::create('remote_management_system_statuses', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('location_id')->constrained('location')->cascadeOnDelete();
            $table->unsignedSmallInteger('status_A_time')->nullable();
            $table->unsignedSmallInteger('status_B_time')->nullable();
            $table->unsignedSmallInteger('status_C_time')->nullable();
            $table->unsignedSmallInteger('status_D_time')->nullable();
            $table->unsignedSmallInteger('status_E_time')->nullable();
            $table->unsignedSmallInteger('status_F_time')->nullable();
            $table->unsignedTinyInteger('is_latest')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('remote_management_system_statuses');
    }
};
