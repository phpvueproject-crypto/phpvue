<?php

use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
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
        if(!Type::hasType('jsonb')) {
            Type::addType('jsonb', JsonType::class);
        }

        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->jsonb('command')->nullable()->change();
            $table->renameColumn('command', 'send_command');
            $table->jsonb('receive_command')->nullable()->after('send_command');
            $table->string('typename')->nullable();
            $table->unsignedTinyInteger('is_mission')->default(0);
            $table->string('sender_type')->default('ui');
            $table->string('sender_name')->default('ui');
            $table->string('receiver_type')->default('acs');
            $table->string('receiver_name')->default('acs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if(!Type::hasType('jsonb')) {
            Type::addType('jsonb', JsonType::class);
        }

        Schema::table('mqtt_commands', function(Blueprint $table) {
            $table->jsonb('send_command')->nullable(false)->change();
            $table->renameColumn('send_command', 'command');
            $table->dropColumn('receive_command');
            $table->dropColumn('typename');
            $table->dropColumn('is_mission');
            $table->dropColumn('sender_type');
            $table->dropColumn('sender_name');
            $table->dropColumn('receiver_type');
            $table->dropColumn('receiver_name');
        });
    }
};
