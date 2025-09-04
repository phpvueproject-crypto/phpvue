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
    public function up() {
        Schema::table('region_mgmt_user', function(Blueprint $table) {
            $table->unsignedTinyInteger('is_write')->default(0);
            $table->unsignedTinyInteger('is_read')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt_user', function(Blueprint $table) {
            $table->dropColumn('is_write');
            $table->dropColumn('is_read');
        });
    }
};
