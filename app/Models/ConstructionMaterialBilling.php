<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstructionMaterialBilling extends Model
{

    protected $fillable = [
        'item_image_path',
        'amount',
        'item_name',
        'verified_by_admin',
        'supplier_id',
        'phase_id',
        'unit_count'
    ];

    /**
     * Get the supplier that owns the ConstructionMaterialBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the phase that owns the ConstructionMaterialBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }


}
