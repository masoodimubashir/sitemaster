<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role_name'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all of the sites for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * Get all of the construction_material_billings for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function construction_material_billings(): HasMany
    {
        return $this->hasMany(ConstructionMaterialBilling::class, 'user_id', 'id');
    }

    /**
     * Get all of the wagerAttendances for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wagerAttendances(): HasMany
    {
        return $this->hasMany(WagerAttendance::class);
    }

    
}
