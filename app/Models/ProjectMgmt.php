<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ProjectMgmt
 *
 * @property string $project_name
 * @property mixed $profile
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegionMgmt> $regionMgmts
 * @property-read int|null $region_mgmts_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMgmt whereProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectMgmt whereProjectName($value)
 * @mixin Eloquent
 */
class ProjectMgmt extends Eloquent {
    protected $table = 'project.project_mgmt';
    protected $primaryKey = 'project_name';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $dates = ['deploy_ts'];

    public function regionMgmts(): HasMany {
        return $this->hasMany(RegionMgmt::class, 'project_name');
    }

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class, 'name', 'project_name');
    }
}
