<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\VertexConfigurationType
 *
 * @property int $id
 * @property int $vertex_type_id
 * @property int $disabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_default
 * @property int|null $vertex_configuration_column_id
 * @property-read \App\Models\VertexConfigurationColumn|null $vertexConfigurationColumn
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType query()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereVertexConfigurationColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfigurationType whereVertexTypeId($value)
 * @mixin Eloquent
 */
class VertexConfigurationType extends Eloquent {
    public function vertexConfigurationColumn(): BelongsTo {
        return $this->belongsTo(VertexConfigurationColumn::class);
    }
}
