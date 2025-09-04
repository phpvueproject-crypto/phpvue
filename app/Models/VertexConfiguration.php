<?php

namespace App\Models;

use Eloquent;
use function app\is_json;

/**
 * App\Models\VertexConfiguration
 *
 * @property int $id
 * @property int $vertex_id
 * @property string $type
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Vertex $vertex
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VertexConfiguration whereVertexId($value)
 * @mixin Eloquent
 */
class VertexConfiguration extends Eloquent {
    public function vertex() {
        return $this->belongsTo(Vertex::class);
    }

    public function getDataAttribute($value) {
        if(is_json($value)) {
            return json_decode($value, true);
        } else {
            return $value;
        }
    }
}
