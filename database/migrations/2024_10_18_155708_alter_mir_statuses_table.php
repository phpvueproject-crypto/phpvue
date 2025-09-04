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
            $table->string('state_text')->nullable();
            $table->dropColumn('state_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('mir_statuses', function(Blueprint $table) {
            $table->dropColumn('state_text');
            $table->unsignedSmallInteger('state_id')->default(11);
        });
    }
};
