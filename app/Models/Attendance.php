<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{



    public $timestamps = false;


    protected $fillable = [
        'is_present',
        'attendance_date',
        'attendance_setup_id',
        'created_at',
        'updated_at'
    ];



    /**
     * Get the attendanceSetup that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attendanceSetup(): BelongsTo
    {
        return $this->belongsTo(AttendanceSetup::class);
    }

    

}
