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

    public function users()
    {
        return $this->belongsToMany(User::class, 'site_users');
    }

    /**
     * Get all the paymentSuppliers for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'site_id', 'id');
    }

    /**
     * Get all the phases for the Site
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
     * Get all the attendanceSetups for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceSetups(): HasMany
    {
        return $this->hasMany(AttendanceSetup::class);
    }

    /**
     * Get all the wastas for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wastas(): HasMany
    {
        return $this->hasMany(Wasta::class);
    }
}
