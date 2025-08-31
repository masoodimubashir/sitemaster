<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSetup extends Model
{


    public $timestamps = false;


    protected $fillable = ['name', 'count', 'price', 'site_id', 'setupable_id', 'setupable_type', 'created_at', 'updated_at'];


    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get all of the attendances for the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }


    public function setupable()
    {
        return $this->morphTo();
    }

}
