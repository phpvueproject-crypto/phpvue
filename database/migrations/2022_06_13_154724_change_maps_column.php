<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('maps', function($table) {
            $table->unsignedSmallInteger('img_width')->nullable();
            $table->unsignedSmallInteger('img_height')->nullable();
            $table->renameColumn('width', 'actual_width');
            $table->renameColumn('height', 'actual_height');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('maps', function($table) {
            $table->dropColumn('img_width');
            $table->dropColumn('img_height');
            $table->renameColumn('actual_width', 'width');
            $table->renameColumn('actual_height', 'height');
        });
    }
};
