<?php

use App\Models\Vertex;
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
        $vertices = Vertex::with('location')->get();
        /** @var Vertex $vertex */
        foreach($vertices as $vertex) {
            $location = $vertex->location;
            if($location) {
                $location->x_px = $vertex->c_x_px;
                $location->y_px = $vertex->c_y_px;
                $location->save();
            }
        }

        Schema::table('vertices', function(Blueprint $table) {
            $table->dropColumn('c_x_px');
            $table->dropColumn('c_y_px');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('vertices', function(Blueprint $table) {
            $table->float('c_x_px')->nullable();
            $table->float('c_y_px')->nullable();
        });
    }
};
