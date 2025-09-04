<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function app\is_json;

/**
 * App\Models\EdgeConfiguration
 *
 * @property int $id
 * @property int $edge_id
 * @property string $type
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Edge $edge
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration whereEdgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EdgeConfiguration whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EdgeConfiguration extends Eloquent {
    public function edge(): BelongsTo {
        return $this->belongsTo(Edge::class);
    }

    public function getDataAttribute($value) {
        if(is_json($value)) {
            return json_decode($value, true);
        } else {
            return $value;
        }
    }
}
