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
            $table->dropColumn('grade');
            $table->renameColumn('grade1', 'grade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('acceptance_grades', function(Blueprint $table) {
            $table->renameColumn('grade', 'grade1');
            $table->string('grade')->nullable();
        });
    }
};
