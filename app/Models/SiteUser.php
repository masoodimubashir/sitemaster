<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteUser extends Model
{
    protected $fillable = [
        'site_id',
        'user_id',
    ];
}
