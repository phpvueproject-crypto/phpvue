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
            $table->unsignedMediumInteger('attach_vertex_id')->nullable();
            $table->string('name')->nullable();
            $table->foreign('attach_vertex_id')->references('id')->on('vertices')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vertices', function(Blueprint $table) {
            $table->dropForeign('vertices_attach_vertex_id_foreign');
            $table->dropColumn('name');
            $table->dropColumn('attach_vertex_id');
        });
    }
};
