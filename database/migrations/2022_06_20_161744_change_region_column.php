<?php

use App\Models\Map;
use App\Models\RegionMgmt;
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
            $table->string('name', 30)->nullable();
            $table->unsignedSmallInteger('img_width')->nullable();
            $table->unsignedSmallInteger('img_height')->nullable();
            $table->unsignedSmallInteger('actual_width')->nullable();
            $table->unsignedSmallInteger('actual_height')->nullable();
            $table->timestamps();
        });

        Schema::table('vertices', function(Blueprint $table) {
            $table->dropForeignIdFor(Map::class);
            $table->dropColumn('map_id');
            $table->string('region', 256)->nullable();
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('edges', function(Blueprint $table) {
            $table->dropForeignIdFor(Map::class);
            $table->dropColumn('map_id');
            $table->string('region', 256)->nullable();
            $table->foreign('region')->references('region')->on('region_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::dropIfExists('maps');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('region_mgmt', function(Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('img_width');
            $table->dropColumn('img_height');
            $table->dropColumn('actual_width');
            $table->dropColumn('actual_height');
            $table->dropTimestamps();
        });

        Schema::create('maps', function(Blueprint $table) {
            $table->unsignedMediumInteger('id', true);
            $table->string('name', 30);
            $table->string('region', 256);
            $table->unsignedSmallInteger('actual_width');
            $table->unsignedSmallInteger('actual_height');
            $table->unsignedSmallInteger('img_width');
            $table->unsignedSmallInteger('img_height');
            $table->timestamps();
        });

        Schema::table('vertices', function(Blueprint $table) {
            $table->dropForeign('vertices_region_foreign');
            $table->dropColumn('region');
            $table->unsignedMediumInteger('map_id')->nullable();
            $table->foreign('map_id')->references('id')->on('maps')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('edges', function(Blueprint $table) {
            $table->dropForeign('edges_region_foreign');
            $table->dropColumn('region');
            $table->unsignedMediumInteger('map_id')->nullable();
            $table->foreign('map_id')->references('id')->on('maps')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
