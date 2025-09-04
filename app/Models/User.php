<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Shanmuga\LaravelEntrust\Traits\LaravelEntrustUserTrait;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $account
 * @property int $enable
 * @property int $has_carrier
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegionMgmt> $readRegionMgmts
 * @property-read int|null $read_region_mgmts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegionMgmt> $regionMgmts
 * @property-read int|null $region_mgmts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StationMgmt> $stationMgmts
 * @property-read int|null $station_mgmts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleMgmt> $vehicleMgmts
 * @property-read int|null $vehicle_mgmts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegionMgmt> $writeRegionMgmts
 * @property-read int|null $write_region_mgmts_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereAccount($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereEnable($value)
 * @method static Builder|User whereHasCarrier($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withRole(string $role)
 * @mixin \Eloquent
 */
class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
    use LaravelEntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'account',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles(): BelongsToMany {
        return $this->belongsToMany(Role::class);
    }

    public function findForPassport($username): Builder|User|null {
        return $this->where('account', $username)->first();
    }

    public function regionMgmts(): BelongsToMany {
        return $this->belongsToMany(RegionMgmt::class);
    }

    public function readRegionMgmts(): BelongsToMany {
        return $this->belongsToMany(RegionMgmt::class)->where('is_read', 1);
    }

    public function writeRegionMgmts(): BelongsToMany {
        return $this->belongsToMany(RegionMgmt::class)->where('is_write', 1);
    }

    public function vehicleMgmts(): BelongsToMany {
        return $this->belongsToMany(VehicleMgmt::class, 'user_vehicle_mgmt', 'user_id', 'vehicle_id', 'id', 'vehicle_id');
    }

    public function stationMgmts(): BelongsToMany {
        return $this->belongsToMany(StationMgmt::class, 'station_mgmt_user', 'user_id', 'station_id', 'id', 'station_id');
    }
}
