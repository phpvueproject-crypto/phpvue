<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ProjectDeploy
 *
 * @property string $project_name
 * @property \Illuminate\Support\Carbon $deploy_ts
 * @property mixed $profile_log
 * @property int|null $deploy_status
 * @property string|null $deploy_fail_desc
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy whereDeployFailDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy whereDeployStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy whereDeployTs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy whereProfileLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectDeploy whereProjectName($value)
 * @mixin Eloquent
 */
class ProjectDeploy extends Eloquent {
    protected $table = 'project.project_deploy';
    protected $primaryKey = 'project_name';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $dates = ['deploy_ts'];
    protected $hidden = [
        'profile_log'
    ];

    public function project(): HasOne {
        return $this->hasOne(Project::class);
    }
}
