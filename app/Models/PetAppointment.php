<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 *
 * @property int $id
 * @property int $pet_id 寵物id
 * @property int $service_id 服務id
 * @property string $status 服務狀態
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $price 價格
 * @property int|null $bath_product_id 進階服務id
 * @property-read \App\Models\TFactory|null $use_factory
 * @property-read \App\Models\Pet $pet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetAppointmentDetail> $petAppointmentDetail
 * @property-read int|null $pet_appointment_detail_count
 * @property-read \App\Models\Service $service
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereBathProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment wherePetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PetAppointment extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return mixed
     */
    public function bathProduct()
    {
        return $this->belongsTo(BathProduct::class, 'bath_product_id', 'id');
    }

    /**
     * @return mixed
     */
    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petAppointmentDetail()
    {
        return $this->hasMany(PetAppointmentDetail::class, 'pet_appointment_id', 'id');
    }

    /**
     * @return mixed
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    // 刪除時刪除底下PetAppointmentDetail的資料
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($petAppointment) {
            $petAppointment->petAppointmentDetail()->delete();
        });
    }
}
