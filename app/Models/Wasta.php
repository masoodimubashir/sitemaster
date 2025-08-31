<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Wasta extends Model
{

    protected $fillable = [
        'wasta_name',
        'contact_no',
        'price',
    ];



    public function attendanceSetups()
    {
        return $this->morphMany(AttendanceSetup::class, 'setupable');
    }



    public function labours()
    {
        return $this->hasMany(Labour::class);
    }


    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    /**
     * Get all of the wagers for the Wasta
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wagers(): HasMany
    {
        return $this->hasMany(Wager::class);
    }
}
