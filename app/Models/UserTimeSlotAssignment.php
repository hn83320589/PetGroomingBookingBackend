<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $daily_time_slot_id 時程id
 * @property int $user_id 店員id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
    //
}
