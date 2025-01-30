<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_no',
        'address',
        'is_raw_material_provider',
        'is_workforce_provider'
    ];



    /**
     * Get the constructionMaterialBilling that owns the Supplier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function constructionMaterialBilling(): HasMany
    {
        return $this->hasMany(ConstructionMaterialBilling::class, 'supplier_id', 'id');
    }

    /**
     * Get all of the paymentSuppliers for the Supplier
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentSuppliers(): HasMany
    {
        return $this->hasMany(PaymentSupplier::class);
    }

    /**
     * Get all of the dailyWagers for the Supplier
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dailyWagers(): HasMany
    {
        return $this->hasMany(DailyWager::class);
    }

    /**
     * Get all of the squareFootages for the Supplier
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function squareFootages(): HasMany
    {
        return $this->hasMany(SquareFootageBill::class);
    }


    /**
     * Polymorphic relationship to AdminPayment.
     */
    public function adminPayments(): MorphMany
    {
        return $this->morphMany(AdminPayment::class, 'entity');
    }


}
