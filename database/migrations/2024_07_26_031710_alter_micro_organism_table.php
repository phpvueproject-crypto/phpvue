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
        Schema::table('micro_organism', function(Blueprint $table) {
            $table->dropColumn('position');
            $table->unsignedDecimal('x')->nullable();
            $table->unsignedDecimal('y')->nullable();
            $table->renameColumn('qr_code', 'bar_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('micro_organism', function(Blueprint $table) {
            $table->string('device_name')->nullable();
            $table->jsonb('position')->nullable();
            $table->dropColumn('x');
            $table->dropColumn('y');
            $table->renameColumn('barcode', 'qr_code');
        });
    }
};
