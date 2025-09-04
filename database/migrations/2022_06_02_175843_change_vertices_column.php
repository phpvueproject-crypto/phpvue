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
        Schema::table('vertices', function(Blueprint $table) {
            $table->decimal('x', 15)->change();
            $table->decimal('y', 15)->change();
            $table->decimal('z', 15)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vertices', function(Blueprint $table) {
            $table->dropColumn('x');
            $table->dropColumn('y');
            $table->dropColumn('z');
        });

        Schema::table('vertices', function(Blueprint $table) {
            $table->mediumInteger('x')->nullable();
            $table->mediumInteger('y')->nullable();
            $table->mediumInteger('z')->nullable();
        });
    }
};
