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
        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->unsignedSmallInteger('deploy_status')->nullable();
            $table->text('deploy_fail_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vehicle_status', function(Blueprint $table) {
            $table->dropColumn('deploy_status');
            $table->dropColumn('deploy_fail_reason');
        });
    }
};
