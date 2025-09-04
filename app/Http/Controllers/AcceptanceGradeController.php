<?php

namespace App\Http\Controllers;

use App\Events\PollutionConditionUpdated;
use App\Models\AcceptanceGrade;
use App\Models\PollutionCondition;
use Auth;
use Illuminate\Http\Request;
use Str;

class AcceptanceGradeController extends Controller {
    public function index(Request $request): array {
        $acceptanceGrades = AcceptanceGrade::whereIsDefault(0);
        $grade = $request->input('grade');
        if($grade) {
            $acceptanceGrades = $acceptanceGrades->where('grade', $grade);
        }
        $acceptanceGrades = $acceptanceGrades->get();

        return [
            'status' => 0,
            'data'   => [
                'acceptanceGrades' => $acceptanceGrades
            ]
        ];
    }

    public function update(Request $request): array {
        $acceptanceGrades = $request->input('acceptance_grades');
        $existAcceptanceGradeIds = collect($acceptanceGrades)->filter(function($acceptanceGrade) {
            return $acceptanceGrade['id'] > 0;
        })->values()->pluck('id');
        $existAcceptanceGrades = AcceptanceGrade::whereIn('id', $existAcceptanceGradeIds)->where('is_default', 0)->get(
        );
        $user = Auth::user();
        foreach($acceptanceGrades as $inputAcceptanceGrade) {
            $id = $inputAcceptanceGrade['id'];
            $acceptanceGrade = $existAcceptanceGrades->where('id', $id)->first();
            if(!$acceptanceGrade) {
                $acceptanceGrade = new AcceptanceGrade();
            }
            $organismKind = $inputAcceptanceGrade['organism_kind'];
            $acceptanceGrade->organism_kind = $organismKind;
            $acceptanceGrade->grade = $inputAcceptanceGrade['grade'];
            $acceptanceGrade->is_default = 0;
            $permission = "$organismKind-exceeds-write";
            if(Str::startsWith($permission, 'microparticle_')) {
                $permission = "microparticle-exceeds-write";
            }
            if($user->hasPermission($permission)) {
                $acceptanceGrade->action = $inputAcceptanceGrade['action'];
                $acceptanceGrade->warn = $inputAcceptanceGrade['warn'];
                $acceptanceGrade->general = $inputAcceptanceGrade['general'];
                $acceptanceGrade->normal = $inputAcceptanceGrade['normal'];
            }
            $acceptanceGrade->save();

        }

        $pollutionConditions = $request->input('pollution_conditions', []);
        foreach($pollutionConditions as $inputPollutionCondition) {
            $pollutionCondition = PollutionCondition::findOrFail($inputPollutionCondition['id']);
            $oldColor = $pollutionCondition->color;
            $pollutionCondition->color = $inputPollutionCondition['color'];
            $pollutionCondition->save();
            if($oldColor != $pollutionCondition->color) {
                event(new PollutionConditionUpdated($pollutionCondition));
            }
        }

        return [
            'status' => 0,
            'data'   => [
                'acceptanceGrades' => $acceptanceGrades
            ]
        ];
    }

    public function reset(): array {
        AcceptanceGrade::where('is_default', 0)->delete();

        $acceptanceGrades = AcceptanceGrade::whereIsDefault(1)->orderBy('id')->get();
        foreach($acceptanceGrades as $defaultAcceptanceGrade) {
            $acceptanceGrade = new AcceptanceGrade();
            $acceptanceGrade->grade = $defaultAcceptanceGrade->grade;
            $acceptanceGrade->organism_kind = $defaultAcceptanceGrade->organism_kind;
            $acceptanceGrade->action = $defaultAcceptanceGrade->action;
            $acceptanceGrade->warn = $defaultAcceptanceGrade->warn;
            $acceptanceGrade->general = $defaultAcceptanceGrade->general;
            $acceptanceGrade->normal = $defaultAcceptanceGrade->normal;
            $acceptanceGrade->is_default = 0;
            $acceptanceGrade->save();
        }

        return [
            'status' => 0
        ];
    }
}
