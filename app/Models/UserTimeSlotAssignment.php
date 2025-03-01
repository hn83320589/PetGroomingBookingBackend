<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property int $daily_time_slot_id 時程id
 * @property int $user_id 店員id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DailyTimeSlot $dailyTimeSlot
 * @property-read \App\Models\TFactory|null $use_factory
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment whereDailyTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTimeSlotAssignment whereUserId($value)
 * @mixin \Eloquent
 */
class UserTimeSlotAssignment extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return mixed
     */
    public function dailyTimeSlot()
    {
        return $this->belongsTo(DailyTimeSlot::class, 'daily_time_slot_id', 'id');
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
