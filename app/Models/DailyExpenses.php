<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyExpenses extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_name',
        'price',
        'phase_id',
        'user_id',
    ];

    /**
     * Get the phase that owns the DailyExpenses
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
