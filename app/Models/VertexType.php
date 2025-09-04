<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\VertexType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VertexConfigurationType> $VertexConfigurationTypes
 * @property-read int|null $vertex_configuration_types_count
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType query()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexType whereUpdatedAt($value)
 * @mixin Eloquent
 */
class VertexType extends Eloquent {
    public function VertexConfigurationTypes(): HasMany {
        return $this->hasMany(VertexConfigurationType::class);
    }
}
