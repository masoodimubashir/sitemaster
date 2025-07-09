<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminPayment extends Model
{

    protected $fillable = [
        'amount',
        'entity_type',
        'entity_id',
        'transaction_type',
        'site_id',
        'supplier_id',
        'screenshot'
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

}
