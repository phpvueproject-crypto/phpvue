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
            $table->primary(['region_mgmt_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt_user', function(Blueprint $table) {
            $table->dropPrimary(['region_mgmt_id', 'user_id']);
        });
    }
};
