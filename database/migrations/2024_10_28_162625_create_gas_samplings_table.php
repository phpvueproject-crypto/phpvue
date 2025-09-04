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
        Schema::create('gas_samplings', function(Blueprint $table) {
            $table->id(); // 自動遞增主鍵
            $table->foreignId('location_id')->constrained('location')->cascadeOnDelete();
            $table->decimal('average_volume', 10); // 抽氣量平均值
            $table->decimal('cumulative_volume', 10); // 抽氣量累積值
            $table->unsignedSmallInteger('second_mark'); // 秒數標記，例如 60, 120, 180
            $table->unsignedTinyInteger('is_latest')->default(1);
            $table->timestamps(); // Laravel 自動管理的 created_at 和 updated_at 欄位
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('gas_samplings');
    }
};
