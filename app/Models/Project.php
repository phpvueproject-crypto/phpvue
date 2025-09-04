<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Project
 *
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $id
 * @property int $is_deploy
 * @property-read \App\Models\ProjectDeploy|null $projectDeploy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegionMgmt> $regionMgmts
 * @property-read int|null $region_mgmts_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereIsDeploy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Project extends Eloquent {
    public function regionMgmts(): HasMany {
        return $this->hasMany(RegionMgmt::class);
    }

    public function projectDeploy(): HasOne {
        return $this->hasOne(ProjectDeploy::class, 'project_name', 'name');
    }
}
