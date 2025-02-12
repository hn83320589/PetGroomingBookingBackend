<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $pet_type_id 類型id
 * @property string $name
 * @property string $gender 性別
 * @property string|null $birth_date 生日
 * @property string|null $weight 體重
 * @property int $user_id 飼主id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet wherePetTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereWeight($value)
 * @mixin \Eloquent
 */
class Pet extends Model
{
    public function petType()
    {
        return $this->belongsTo(PetType::class, 'pet_type_id', 'id');
    }

    public function petParent()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function petAppointments()
    {
        return $this->hasMany(PetAppointment::class, 'pet_id', 'id');
    }

    public function service()
    {
        return $this->hasManyThrough(Service::class, PetAppointment::class, 'pet_id', 'id', 'id', 'service_id');
    }

    public function userTimeSlotAssignments()
    {
        return $this->hasManyThrough(UserTimeSlotAssignment::class, PetAppointment::class, 'pet_id', 'id', 'id', 'user_time_slot_assignments_id');
    }
}
