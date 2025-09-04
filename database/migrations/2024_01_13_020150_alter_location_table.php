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
        Schema::table('location', function(Blueprint $table) {
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->decimal('x', 15)->nullable();
            $table->decimal('y', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('location', function(Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('name');
            $table->dropColumn('x', 15);
            $table->dropColumn('y', 15);
        });
    }
};
