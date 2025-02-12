<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $pet_type_id
 * @property int $tier_level 等級
 * @property int $extra_price 加購價
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereExtraPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice wherePetTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereTierLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetTypePrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PetTypePrice extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function petType()
    {
        return $this->belongsTo(PetType::class, 'pet_type_id', 'id');
    }
}
