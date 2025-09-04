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
        DB::statement('ALTER TABLE mir_statuses ALTER COLUMN mission_queue_id TYPE BIGINT USING mission_queue_id::BIGINT');
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->foreign('mission_queue_id')->references('id')->on('mission_queues')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        // 刪除外鍵約束
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->dropForeign(['mission_queue_id']);
        });
        // 回滾列類型為 INTEGER
        DB::statement('ALTER TABLE mir_statuses ALTER COLUMN mission_queue_id TYPE VARCHAR(255) USING mission_queue_id::VARCHAR');
    }
};
