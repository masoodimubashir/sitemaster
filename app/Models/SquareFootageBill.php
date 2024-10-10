<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SquareFootageBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'image_path',
        'wager_name',
        'price',
        'type',
        'multiplier',
        'phase_id',
        'supplier_id'
    ];

    /**
     * Get the supplier that owns the SquareFootageBill
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the phase that owns the SquareFootageBill
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
