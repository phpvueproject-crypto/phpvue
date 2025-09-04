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
        Schema::table('project_deploy', function(Blueprint $table) {
            $table->string('deploy_state', 9)->default('WAIT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('project_deploy', function(Blueprint $table) {
            $table->dropColumn('deploy_state');
        });
    }
};
