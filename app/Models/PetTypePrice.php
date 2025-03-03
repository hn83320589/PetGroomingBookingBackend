<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $pet_type_id
 * @property int $service_id 等級
 * @property int $extra_price 加購價
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TFactory|null $use_factory
 * @property-read \App\Models\PetType $petType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereExtraPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice wherePetTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PetTypePrice extends Model {
    use HasFactory;

    /**
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return mixed
     */
    public function petType() {
        return $this->belongsTo(PetType::class, 'pet_type_id', 'id');
    }
}
