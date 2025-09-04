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
            $table->string('obj_uid')->nullable();
            $table->foreign('obj_uid')->references('obj_uid')->on('object_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vertices', function(Blueprint $table) {
            $table->dropColumn('obj_uid');
        });
    }
};
