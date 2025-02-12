<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $notice
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice whereNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Notice extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
}
