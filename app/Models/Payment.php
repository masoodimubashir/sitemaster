<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{


    public $timestamps = false;

    protected $fillable = [
        'screenshot',
        'site_id',
        'supplier_id',
        'verified_by_admin',
        'amount',
        'transaction_type',
        'payment_initiator',
        'created_at',
        'updated_at',
        'narration'
    ];

    /**
     * Get the site that owns the PaymentSupplier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the supplier that owns the PaymentSupplier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
