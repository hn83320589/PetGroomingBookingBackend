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
 * @property-read \App\Models\Pet $pet
 * @property-read \App\Models\Service $service
 * @property-read \App\Models\UserTimeSlotAssignment|null $userTimeSlotAssignment
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment wherePetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetAppointment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PetAppointment extends Model {
    use HasFactory;

    /**
     * @return mixed
     */
    public function pet() {
        return $this->belongsTo(Pet::class, 'pet_id', 'id');
    }

    /**
     * @return mixed
     */
    public function service() {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    /**
     * @return mixed
     */
    public function userTimeSlotAssignment() {
        return $this->belongsTo(UserTimeSlotAssignment::class, 'user_time_slot_assignments_id', 'id');
    }
}
