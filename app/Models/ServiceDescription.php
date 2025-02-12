<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $service_id
 * @property string $content
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDescription whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceDescription extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
