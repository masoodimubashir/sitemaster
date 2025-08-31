<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wager extends Model
{

    protected $fillable = [
        'wager_name',
        'wasta_id',
        'price'
    ];


    public function attendanceSetups()
    {
        return $this->morphMany(AttendanceSetup::class, 'setupable');
    }

    /**
     * Get the wasta that owns the Wager
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wasta(): BelongsTo
    {
        return $this->belongsTo(Wasta::class);
    }

   


}
