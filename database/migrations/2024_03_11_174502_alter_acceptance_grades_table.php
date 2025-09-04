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
        Schema::table('acceptance_grades', function(Blueprint $table) {
            $table->primary(['id']);
        });
        DB::select("SELECT setval('acceptance_grades_id_seq', (SELECT max(id) FROM configure.acceptance_grades));");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('acceptance_grades', function(Blueprint $table) {
            $table->dropPrimary(['acceptance_grades_pkey']);
        });
    }
};
