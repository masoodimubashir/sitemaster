<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workforce extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['workforce_name'];

    /**
     * Get all of the dailyWagers for the Workforce
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dailyWagers(): HasMany
    {
        return $this->hasMany(DailyWager::class);
    }

    /**
     * Get all of the squareFootagesBills for the Workforce
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function squareFootagesBills(): HasMany
    {
        return $this->hasMany(SquareFootageBill::class);
    }
}
