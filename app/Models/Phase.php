<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['phase_name', 'site_id'];

    /**
     * Get the phase that owns the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get all of the constructionMaterialBillings for the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function constructionMaterialBillings(): HasMany
    {
        return $this->hasMany(ConstructionMaterialBilling::class);
    }

    /**
     * Get all of the squareFootageBills for the Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function squareFootageBills(): HasMany
    {
        return $this->hasMany(SquareFootageBill::class);
    }

    /**
     * Get all of the daily_wagers for the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dailyWagers(): HasMany
    {
        return $this->hasMany(DailyWager::class, 'phase_id', 'id');
    }

    /**
     * Get all of the dailyExpenses for the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dailyExpenses(): HasMany
    {
        return $this->hasMany(DailyExpenses::class);
    }

    /**
     * Get all of the wagerAttendances for the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wagerAttendances(): HasMany
    {
        return $this->hasMany(WagerAttendance::class, 'phase_id', 'id');
    }



    /**
     * Get all of the wastas for the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wastas(): HasMany
    {
        return $this->hasMany(Wasta::class);
    }

    /**
     * Get all of the labours for the Phase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function labours(): HasMany
    {
        return $this->hasMany(Labour::class);
    }

    public function getExistsRecordsAttribute()
    {

        if (
            $this->constructionMaterialBillings()->where('verified_by_admin', 1)->exists() ||
            $this->squareFootageBills()->where('verified_by_admin', 1)->exists() ||
            $this->dailyWagers()->exists() ||  $this->dailyExpenses()->where('verified_by_admin', 1)->exists() ||
            $this->wagerAttendances()->where('verified_by_admin')->exists()
        ) {
            return true;
        } else {
            return false;
        }
    }
}
