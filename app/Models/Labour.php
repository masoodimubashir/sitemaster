<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Labour extends Model
{


    protected $fillable = [
        'wasta_id',
        'phase_id',
        'labour_name',
        'price',
        'contact_no'
    ];


    /**
     * Get all of the attendances for the Labour
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function wasta()
    {
        return $this->belongsTo(Wasta::class);
    }

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }
}
