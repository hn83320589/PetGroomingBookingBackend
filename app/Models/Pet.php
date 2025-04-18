<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 *
 * @property int $id
 * @property int $pet_type_id 類型id
 * @property string $name
 * @property string $gender 性別
 * @property string|null $birth_date 生日
 * @property string|null $weight 體重
 * @property int $is_default 是否為預設 0：否；1：是
 * @property int $user_id 飼主id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Factories\Factory|null $factory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetAppointment> $petAppointments
 * @property-read int|null $pet_appointments_count
 * @property-read \App\Models\User $petParent
 * @property-read \App\Models\PetType $petType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetTypePrice> $petTypePrices
 * @property-read int|null $pet_type_prices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $service
 * @property-read int|null $service_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTimeSlotAssignment> $userTimeSlotAssignments
 * @property-read int|null $user_time_slot_assignments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet wherePetTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pet whereWeight($value)
 * @mixin \Eloquent
 */
class Pet extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petAppointments()
    {
        return $this->hasMany(PetAppointment::class, 'pet_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petParent()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petType()
    {
        return $this->belongsTo(PetType::class, 'pet_type_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petTypePrices()
    {
        return $this->hasMany(PetTypePrice::class, 'pet_type_id', 'pet_type_id')->orderBy('service_id');
    }

    /**
     * @return mixed
     */
    public function service()
    {
        return $this->hasManyThrough(Service::class, PetAppointment::class, 'pet_id', 'id', 'id', 'service_id');
    }

    /**
     * @return mixed
     */
    public function userTimeSlotAssignments()
    {
        return $this->hasManyThrough(UserTimeSlotAssignment::class, PetAppointment::class, 'pet_id', 'id', 'id', 'user_time_slot_assignments_id');
    }

    // 刪除時，連帶刪除預約紀錄
    protected static function boot()
    {
        parent::boot();

        // 刪除寵物時，連帶刪除預約紀錄
        static::deleting(function ($pet) {
            // 刪除預約紀錄時，連帶刪除預約紀錄明細
            // 先刪除預約紀錄明細
            $pet->petAppointments()->each(function ($petAppointment) {
                $petAppointment->petAppointmentDetail()->delete();
            });

            // 再刪除預約紀錄
            $pet->petAppointments()->delete();
        });
    }
}
