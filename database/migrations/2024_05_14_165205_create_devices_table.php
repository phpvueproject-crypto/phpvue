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
        Schema::create('devices', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ap')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->unsignedTinyInteger('is_connected')->default(0);
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('devices');
    }
};
