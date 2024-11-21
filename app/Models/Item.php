<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['item_name'];

    protected function itemName(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => ucwords($value),
        );
    }

    /**
     * Get the material that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function material(): HasMany
    {
        return $this->hasMany(ConstructionMaterialBilling::class);
    }

}
