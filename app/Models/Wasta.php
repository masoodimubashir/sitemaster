<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Wasta extends Model
{

    protected $fillable = [
        'wasta_id',
        'phase_id',
        'wasta_name',
        'price',
        'contact_no'
    ];


    /**
     * Get all of the attendances for the Wasta
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }


    public function labours()
    {
        return $this->hasMany(Labour::class);
    }


    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }
}
