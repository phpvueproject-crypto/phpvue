<?php

use App\Models\DispatchRuleMgmt;
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
        Schema::table('dispatch_rule_mgmt', function(Blueprint $table) {
            $table->dropForeign('dispatch_rule_mgmt_vehicle_mgmt_vehicle_id_fk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DispatchRuleMgmt::where('prefer_vehicle', 'like', "%,%")->delete();
        Schema::table('dispatch_rule_mgmt', function(Blueprint $table) {
            $table->foreign('prefer_vehicle', 'dispatch_rule_mgmt_vehicle_mgmt_vehicle_id_fk')->references('vehicle_id')->on('vehicle_mgmt')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
