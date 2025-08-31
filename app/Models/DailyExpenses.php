<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyExpenses extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_name',
        'price',
        'phase_id',
        'user_id',
        'site_id',
        'bill_photo',
        'verified_by_admin',
        'created_at',
        'updated_at'
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

    /**
     * Get the supplier that owns the DailyExpenses
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
