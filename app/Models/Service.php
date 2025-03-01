<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property-read \App\Models\TFactory|null $use_factory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetAppointment> $petAppointments
 * @property-read int|null $pet_appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetType> $petType
 * @property-read int|null $pet_type_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetTypePrice> $petTypePrices
 * @property-read int|null $pet_type_prices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pet> $pets
 * @property-read int|null $pets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceDescription> $serviceDescriptions
 * @property-read int|null $service_descriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTimeSlotAssignment> $userTimeSlotAssignments
 * @property-read int|null $user_time_slot_assignments_count
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
class Service extends Model {
    use HasFactory;

    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @return mixed
     */
    public function petAppointments() {
        return $this->hasMany(PetAppointment::class, 'service_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petTypePrices() {
        return $this->hasMany(PetTypePrice::class, 'service_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petType() {
        return $this->hasManyThrough(PetType::class, PetTypePrice::class, 'service_id', 'id', 'id', 'pet_type_id');
    }

    /**
     * @return mixed
     */
    public function userTimeSlotAssignments() {
        return $this->hasManyThrough(UserTimeSlotAssignment::class, PetAppointment::class, 'service_id', 'id', 'id', 'user_time_slot_assignments_id');
    }

    /**
     * @return mixed
     */
    public function pets() {
        return $this->hasManyThrough(Pet::class, PetAppointment::class, 'service_id', 'id', 'id', 'pet_id');
    }

    /**
     * @return mixed
     */
    public function serviceDescriptions() {
        return $this->hasMany(ServiceDescription::class, 'service_id', 'id');
    }
}
