<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $category 種類
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PetType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PetType extends Model {
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
    public function pets() {
        return $this->hasMany(Pet::class, 'pet_type_id', 'id');
    }

    /**
     * @return mixed
     */
    public function petTypePrices() {
        return $this->hasMany(PetTypePrice::class, 'pet_type_id', 'id')->orderBy('tier_level');
    }
}
