<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $price 價格
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TFactory|null $use_factory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PetAppointment> $petAppointments
 * @property-read int|null $pet_appointments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BathProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BathProduct extends Model {
    use HasFactory;

    /**
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    public function petAppointments() {
        return $this->hasMany(PetAppointment::class);
    }
}
