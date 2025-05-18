<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'site_id', 'id');
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


    /**
     * Polymorphic relationship to AdminPayment.
     */
    public function adminPayments(): MorphMany
    {
        return $this->morphMany(AdminPayment::class, 'entity');
    }


    /**
     * Get all of the labours for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function labours(): HasMany
    {
        return $this->hasMany(Labour::class);
    }

    /**
     * Get all of the wastas for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wastas(): HasMany
    {
        return $this->hasMany(Wasta::class);
    }
}
