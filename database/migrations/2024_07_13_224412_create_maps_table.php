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
        Schema::create('maps', function(Blueprint $table) {
            $table->string('guid')->primary();
            $table->string('name');
            $table->float('origin_x', 10, 6); // 10 是總長度，6 是小數位數
            $table->float('origin_y', 10, 6); // 你可以根據需要調整這些值
            $table->unsignedSmallInteger('resolution');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('maps');
    }
};
