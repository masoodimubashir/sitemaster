<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierTotalAmount extends Model
{

    protected $fillable = [
        'supplier_id',
        'amount',
    ];

}
