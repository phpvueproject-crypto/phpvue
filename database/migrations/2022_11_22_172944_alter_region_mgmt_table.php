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
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->unsignedBigInteger('edit_user_id')->nullable();
            $table->dateTime('edited_at')->nullable();
            $table->foreign('edit_user_id')->references('id')->on('users')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropColumn('edit_user_id');
            $table->dropColumn('edited_at');
        });
    }
};
