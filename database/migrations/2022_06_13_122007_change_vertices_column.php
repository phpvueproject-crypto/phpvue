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
            $table->string('tag')->nullable();
            $table->unsignedSmallInteger('vertex_type_id')->default(1);
            $table->foreign('vertex_type_id')->references('id')->on('vertex_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vertices', function(Blueprint $table) {
            $table->dropForeign('vertices_vertex_type_id_foreign');
            $table->dropColumn('vertex_type_id');
            $table->dropColumn('tag');
        });
    }
};
