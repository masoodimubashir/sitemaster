<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminPayment extends Model
{


    public $timestamps = false;

    protected $fillable = [
        'amount',
        'transaction_type',
        'screenshot',
        'created_at',
        'updated_at'
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
