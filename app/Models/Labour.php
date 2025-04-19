<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Labour extends Model
{


    protected $fillable = [
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
        return $this->morphMany(Attendance::class, 'attendances');
    }
}
