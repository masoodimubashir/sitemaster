<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    protected $fillable = [
        'attendable_id',
        'attendable_type',
        'is_present',
        'attendance_date',
        'price'
    ];

    public function attendable()
    {
        return $this->morphTo();
    }
}
