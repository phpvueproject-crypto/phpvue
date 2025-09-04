<?php

use App\Models\AcceptanceGrade;
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
        Schema::table('acceptance_grades', function(Blueprint $table) {
            $table->unsignedTinyInteger('is_default')->default(0)->after('normal');
            $table->char('grade1', 1)->nullable(false)->default('A');
        });

        $acceptanceGrades = AcceptanceGrade::all();
        foreach($acceptanceGrades as $acceptanceGrade) {
            $acceptanceGrade->grade1 = $acceptanceGrade->grade;
            $acceptanceGrade->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $acceptanceGrades = AcceptanceGrade::all();
        foreach($acceptanceGrades as $acceptanceGrade) {
            $acceptanceGrade->grade = $acceptanceGrade->grade1;
            $acceptanceGrade->save();
        }

        Schema::table('acceptance_grades', function(Blueprint $table) {
            $table->dropColumn('is_default');
            $table->dropColumn('grade1');
        });
    }
};
