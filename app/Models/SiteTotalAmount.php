<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTotalAmount extends Model
{

    protected $fillable = [
        'phase_id',
        'total_amount',
    ];

}
