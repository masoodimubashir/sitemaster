<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WagerAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_present',
        'no_of_persons',
        'user_id',
        'daily_wager_id' ,
        'phase_id',
        'verified_by_admin'
    ];

    /**
     * Get the dailyWager that owns the WagerAttendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dailyWager(): BelongsTo
    {
        return $this->belongsTo(DailyWager::class);
    }

    /**
     * Get the phase that owns the WagerAttendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    

}
