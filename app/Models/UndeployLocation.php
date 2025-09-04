<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UndeployLocation
 *
 * @property int $id
 * @property int $vertex_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vertex $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UndeployLocation whereVertexId($value)
 * @mixin Eloquent
 */
class UndeployLocation extends Eloquent {
    public function vertex(): BelongsTo {
        return $this->belongsTo(Vertex::class);
    }
}
