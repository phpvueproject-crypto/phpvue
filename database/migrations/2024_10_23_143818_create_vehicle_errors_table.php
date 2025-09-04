<?php

use App\Models\VehicleErrorType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('vehicle_error_types', function(Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $vehicleErrorTypesData = [
            ['id' => 1, 'name' => 'MiR異常'],
            ['id' => 2, 'name' => '空氣採樣機異常'],
            ['id' => 3, 'name' => '微塵計數器異常'],
            ['id' => 4, 'name' => '讀碼器異常']
        ];

        foreach($vehicleErrorTypesData as $vehicleErrorTypeData) {
            $vehicleErrorType = new VehicleErrorType();
            $vehicleErrorType->id = $vehicleErrorTypeData['id'];
            $vehicleErrorType->name = $vehicleErrorTypeData['name'];
            $vehicleErrorType->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('vehicle_error_types');
    }
};
