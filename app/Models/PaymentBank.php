<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentBank extends Model
{
    protected $fillable = [
        'from',
        'from_type',
        'to',
        'to_type',
        'amount',
        'is_on_going'
    ];

    
}
