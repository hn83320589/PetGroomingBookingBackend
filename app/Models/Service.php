<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $display_name 顯示名稱
 * @property int $time 執行時間(單位:分鐘)
 * @property int $price 價格
 * @property int $has_bath_products
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereHasBathProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Service extends Model
{
    protected $hidden = ['created_at', 'updated_at'];

    public function petAppointments()
    {
        return $this->hasMany(PetAppointment::class, 'service_id', 'id');
    }

    public function petTypePrices()
    {
        return $this->hasMany(PetTypePrice::class, 'service_id', 'id');
    }

    public function petType()
    {
        return $this->hasManyThrough(PetType::class, PetTypePrice::class, 'service_id', 'id', 'id', 'pet_type_id');
    }

    public function userTimeSlotAssignments()
    {
        return $this->hasManyThrough(UserTimeSlotAssignment::class, PetAppointment::class, 'service_id', 'id', 'id', 'user_time_slot_assignments_id');
    }

    public function pets()
    {
        return $this->hasManyThrough(Pet::class, PetAppointment::class, 'service_id', 'id', 'id', 'pet_id');
    }

    public function serviceDescriptions()
    {
        return $this->hasMany(ServiceDescription::class, 'service_id', 'id');
    }
}
