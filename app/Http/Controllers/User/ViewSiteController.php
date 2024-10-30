<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\Item;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ViewSiteController extends Controller
{


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $site_id = base64_decode($id);

        $site = Site::with([
            'phases.constructionMaterialBillings' => function ($q) {
                $q->where('verified_by_admin', 1)->with(['supplier' => function ($q) {
                    $q->withTrashed();
                }])->latest();
            },
            'phases.squareFootageBills' => function ($q) {
                $q->where('verified_by_admin', 1)->with('supplier', function ($q) {
                    $q->withTrashed();
                })->latest();
            },
            'phases.dailyWagers' => function ($q) {
                $q->where('verified_by_admin', 1)->with('supplier', function ($q) {
                    $q->withTrashed();
                })->latest();
            },
            'phases.dailyExpenses' => function ($q) {
                $q->where('verified_by_admin', 1)->withTrashed()->latest();
            },
            'phases.wagerAttendances' => function ($q) {
                $q->with(['dailyWager' => function ($q) {
                    $q->where('verified_by_admin', 1)
                        ->with(['supplier' => function ($q) {
                            $q->withTrashed();
                        }]);
                }])->withTrashed()->latest();
            },
            'paymeentSuppliers'
        ])->findOrFail($site_id);

        $totalPaymentSuppliersAmount = $site->paymeentSuppliers()->sum('amount');

        $grand_total_construction_amount = 0;
        $grand_total_daily_expenses_amount = 0;
        $grand_total_daily_wagers_amount = 0;
        $grand_total_square_footage_amount = 0;

        foreach ($site->phases as $phase) {
            $phase->construction_total_amount = $phase->constructionMaterialBillings->sum('amount');
            $phase->daily_expenses_total_amount = $phase->dailyExpenses->sum('price');
            $phase->daily_wagers_total_amount = $phase->dailyWagers->sum('price_per_day');

            $phase->square_footage_total_amount = $phase->squareFootageBills->reduce(function ($carry, $bill) {
                return $carry + ($bill->price * $bill->multiplier);
            }, 0);

            $phase->total_amount = $phase->construction_total_amount +
                $phase->daily_expenses_total_amount +
                $phase->daily_wagers_total_amount +
                $phase->square_footage_total_amount;

            $grand_total_construction_amount += $phase->construction_total_amount;
            $grand_total_daily_expenses_amount += $phase->daily_expenses_total_amount;
            $grand_total_daily_wagers_amount += $phase->daily_wagers_total_amount;
            $grand_total_square_footage_amount += $phase->square_footage_total_amount;
        }

        $grand_total_amount = $grand_total_construction_amount +
            $grand_total_daily_expenses_amount +
            $grand_total_daily_wagers_amount +
            $grand_total_square_footage_amount;

        $suppliers = Supplier::orderBy('name')->get();

        $workforce_suppliers = Supplier::where('is_workforce_provider', 1)->orderBy('name')->get();

        $raw_material_providers = Supplier::where('is_raw_material_provider', 1)->orderBy('name')->get();

        $wagers = DailyWager::orderBy('wager_name')->get();

        $items = Item::orderBy('item_name')->get();

        return view('profile.User.Site.show-site', compact('site', 'grand_total_amount', 'suppliers', 'workforce_suppliers', 'raw_material_providers', 'wagers', 'items', 'totalPaymentSuppliersAmount'));
    }
}
