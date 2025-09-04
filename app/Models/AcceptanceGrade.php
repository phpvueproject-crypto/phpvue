<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\AcceptanceGrade
 *
 * @property int $id
 * @property string|null $organism_kind
 * @property int|null $action
 * @property int|null $warn
 * @property int|null $general
 * @property int|null $normal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_default
 * @property string $grade
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereGeneral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereNormal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereOrganismKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptanceGrade whereWarn($value)
 * @mixin Eloquent
 */
class AcceptanceGrade extends Eloquent {
    protected $table = 'acceptance_grades';
}
