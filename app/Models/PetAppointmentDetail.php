<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 *
 * @property int $id
 * @property int $pet_appointment_id
 * @property int $user_time_slot_assignment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DailyTimeSlot> $dailyTimeSlots
 * @property-read int|null $daily_time_slots_count
 * @property-read \App\Models\TFactory|null $use_factory
 * @property-read \App\Models\PetAppointment $petAppointment
 * @property-read \App\Models\UserTimeSlotAssignment $userTimeSlotier_leveltAssignment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail wherePetAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointmentDetail whereUserTimeSlotAssignmentId($value)
 * @mixin \Eloquent
 */
class PetAppointmentDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function petAppointment()
    {
        return $this->belongsTo(PetAppointment::class, 'pet_appointment_id', 'id');
    }

    public function userTimeSlotAssignment()
    {
        return $this->belongsTo(UserTimeSlotAssignment::class, 'user_time_slot_assignment_id', 'id');
    }

    public function dailyTimeSlot()
    {
        return $this->hasOneThrough(DailyTimeSlot::class, UserTimeSlotAssignment::class, 'id', 'id', 'user_time_slot_assignment_id', 'daily_time_slot_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, UserTimeSlotAssignment::class, 'id', 'id', 'user_time_slot_assignment_id', 'user_id');
    }
}
