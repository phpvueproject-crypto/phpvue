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
        Schema::create('laser_dusts', function(Blueprint $table) {
            $table->id();
            $table->float('val_1');
            $table->float('val_2');
            $table->float('val_3');
            $table->float('val_4');
            $table->float('val_5');
            $table->float('val_6');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('laser_dusts');
    }
};
