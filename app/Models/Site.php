<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_name',
        'location',
        'site_owner_name',
        'contact_no',
        'user_id',
        'service_charge',
        'client_id',
        'is_on_going',
    ];

    /**
     * Get the user that owns the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the paymeentSuppliers for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymeentSuppliers(): HasMany
    {
        return $this->hasMany(PaymentSupplier::class);
    }

    /**
     * Get all of the phases for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }


    /**
     * Get the client that owns the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

}
