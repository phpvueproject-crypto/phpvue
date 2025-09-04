<?php

namespace App\Models;

use DB;
use Eloquent;
use File;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use function app\quadrantCoordToimgCoordX;
use function app\quadrantCoordToimgCoordY;

/**
 * App\Models\RegionMgmt
 *
 * @property string $region
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $img_height
 * @property int|null $img_width
 * @property int|float $mm
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $project_name
 * @property string|null $undeploy_background_file_md5
 * @property int|null $edit_user_id
 * @property string|null $edited_at
 * @property float $resolution
 * @property float $origin_x
 * @property float $origin_y
 * @property int|null $yaw
 * @property int|null $route_id
 * @property int $origin_start_direction
 * @property string|null $undeploy_route_file_md5
 * @property string|null $deploy_background_file_md5
 * @property string|null $deploy_route_file_md5
 * @property int $id
 * @property int|null $x_px
 * @property int|null $y_px
 * @property int|null $project_id
 * @property int|null $cad_width
 * @property int|null $cad_height
 * @property string|null $cleanliness_grade
 * @property int|null $floors
 * @property int|null $floor_region_mgmt_id
 * @property-read \App\Models\AcceptableNode|null $acceptableNode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActionNode> $actionNodes
 * @property-read int|null $action_nodes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Edge> $edges
 * @property-read int|null $edges_count
 * @property-read \App\Models\User|null $editUser
 * @property-read RegionMgmt|null $floorRegionMgmt
 * @property-read bool $is_exist_background
 * @property-read bool $is_exist_cad
 * @property-read bool $is_exist_preview_background
 * @property-read int|float $origin_x_px
 * @property-read int|float $origin_y_px
 * @property-read \App\Models\Map|null $map
 * @property-read \App\Models\MqttCommand|null $mqttCommand
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\ProjectMgmt|null $projectMgmt
 * @property-read \App\Models\RoomEnvironment|null $roomEnvironment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RegionMgmt> $roomRegionMgmts
 * @property-read int|null $room_region_mgmts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vertex> $vertices
 * @property-read int|null $vertices_count
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereCadHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereCadWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereCleanlinessGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereDeployBackgroundFileMd5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereDeployRouteFileMd5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereEditUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereEditedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereFloorRegionMgmtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereFloors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereImgHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereImgWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereMm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereOriginStartDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereOriginX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereOriginY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereRouteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereUndeployBackgroundFileMd5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereUndeployRouteFileMd5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereXPx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereYPx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RegionMgmt whereYaw($value)
 * @mixin Eloquent
 */
class RegionMgmt extends Eloquent {
    use HasRelationships;

    protected $table = 'region_mgmt';
    protected $primaryKey = 'id';
    protected $appends = [
        'origin_x_px',
        'origin_y_px',
        'mm',
        'is_exist_preview_background',
        'is_exist_background',
        'is_exist_cad'
    ];

    public function getMmAttribute(): float|int {
        return $this->resolution * 1000;
    }

    public function getOriginXPxAttribute(): float|int {
        return quadrantCoordToimgCoordX($this->resolution, 0, $this->origin_x);
    }

    public function getOriginYPxAttribute(): float|int {
        return quadrantCoordToimgCoordY($this->resolution, 0, $this->img_height, $this->origin_y);
    }

    public function getOriginXAttribute($value): float {
        return (double)$value;
    }

    public function getOriginYAttribute($value): float {
        return (double)$value;
    }

    public function vertices(): HasMany {
        return $this->hasMany(Vertex::class);
    }

    public function edges(): HasMany {
        return $this->hasMany(Edge::class);
    }

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class);
    }

    public function projectMgmt(): BelongsTo {
        return $this->belongsTo(ProjectMgmt::class, 'project_name');
    }

    public function editUser(): BelongsTo {
        return $this->belongsTo(User::class, 'edit_user_id');
    }

    public function mqttCommand(): HasOne {
        return $this->hasOne(MqttCommand::class)->where('is_completed', 0);
    }

    public function roomEnvironment(): HasOne {
        return $this->hasOne(RoomEnvironment::class);
    }

    public function getIsExistPreviewBackgroundAttribute(): bool {
        $projectName = $this->project->name;
        $path = storage_path("app/projects/$projectName/$this->region/background_{$this->region}_preview.png");
        return File::exists($path);
    }

    public function getIsExistBackgroundAttribute(): bool {
        $projectName = $this->project->name;
        $path = storage_path("app/projects/$projectName/$this->region/background_{$this->region}.png");
        return File::exists($path);
    }

    public function getIsExistCadAttribute(): bool {
        $projectName = $this->project->name;
        $path = storage_path("app/projects/$projectName/$this->region/cad_{$this->region}.png");
        return File::exists($path);
    }

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function actionNodes(): HasMany {
        return $this->hasMany(ActionNode::class);
    }

    public function acceptableNode(): HasOne {
        return $this->hasOne(AcceptableNode::class)->orderByDesc('percent');
    }

    public function microOrganism(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment)->locations(), (new Location)->microOrganisms())->where('is_serious', 1);
    }

    public function microOrganisms(): HasManyDeep {
        return $this->hasManyDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment)->locations(), (new Location)->microOrganisms());
    }

    public function allRoomLocations(): HasManyDeep {
        return $this->hasManyDeepFromRelations($this->roomRegionMgmts(), (new RegionMgmt())->roomEnvironment(), (new RoomEnvironment())->locations());
    }

    public function locations(): HasManyDeep {
        return $this->hasManyDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment())->locations());
    }

    public function latestMicroOrganism(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment)->locations(), (new Location)->microOrganisms())->latest('Time');
    }

    public function seriousMicroOrganism(): HasOneDeep {
        return $this->hasOneDeepFromRelations($this->roomEnvironment(), (new RoomEnvironment)->locations(), (new Location)->microOrganisms())->orderByDesc(DB::raw("CAST(\"micro_organism\".\"Time\" AS DATE)"))->orderByDesc('score');
    }

    public function roomRegionMgmts(): HasMany {
        return $this->hasMany(RegionMgmt::class, 'floor_region_mgmt_id');
    }

    public function floorRegionMgmt(): BelongsTo {
        return $this->belongsTo(RegionMgmt::class, 'floor_region_mgmt_id');
    }

    public function map(): HasOne {
        return $this->hasOne(Map::class);
    }
}
