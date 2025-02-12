<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $slot_date 日期
 * @property string $slot_time 時段
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot whereSlotDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot whereSlotTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyTimeSlot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DailyTimeSlot extends Model
{
    //
}
