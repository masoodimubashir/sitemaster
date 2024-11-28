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
            'phases' => function ($phase) {
                $phase->with([
                    'constructionMaterialBillings' => function ($q) {
                        $q->where('verified_by_admin', 1)
                            ->with('supplier')
                            ->whereHas('supplier', function ($q) {
                                $q->whereNull('deleted_at');
                            })->latest();
                    },
                    'squareFootageBills' => function ($q) {
                        $q->where('verified_by_admin', 1)
                            ->with('supplier')->whereHas('supplier', function ($q) {
                                $q->whereNull('deleted_at');
                            })->latest();
                    },
                    'dailyWagers' => function ($q) {
                        $q->with([
                            'wagerAttendances',
                            'supplier'
                        ])->whereHas('supplier', function ($q) {
                            $q->whereNull('deleted_at');
                        })->whereHas('wagerAttendances', function ($attendance) {
                            $attendance->where('verified_by_admin', 1);
                        })->latest();
                    },
                    'dailyExpenses' => function ($q) {
                        $q->where('verified_by_admin', 1)
                            ->latest();
                    },
                    'wagerAttendances' => function ($q) {
                        $q->where('verified_by_admin')
                            ->with(['dailyWager.supplier'])
                            ->whereHas('dailyWager.supplier', function ($q) {
                                $q->whereNull('deleted_at');
                            })->latest();
                    },
                ],);
            },
            'paymeentSuppliers' => function ($pay) {
                $pay->where('verified_by_admin', 1);
            }
        ])->findOrFail($site_id);

        $totalPaymentSuppliersAmount = $site->paymeentSuppliers()->sum('amount');

        $grand_total_amount = 0;

        foreach ($site->phases as $phase) {

            $phase->construction_total_amount = $phase->constructionMaterialBillings->sum('amount');
            $phase->daily_expenses_total_amount = $phase->dailyExpenses->sum('price');
            $phase->square_footage_total_amount = $phase->squareFootageBills->reduce(function ($sum, $sqft) {
                return $sum + ($sqft->price * $sqft->multiplier);
            }, 0);

            foreach ($phase->dailyWagers as $wager) {
                $phase->daily_wagers_total_amount += $wager->wager_total;
            }

            $phase->construction_total_service_charge_amount = ($site->service_charge / 100) * $phase->construction_total_amount +  $phase->construction_total_amount;

            $phase->daily_expense_total_service_charge_amount = ($site->service_charge / 100) * $phase->daily_expenses_total_amount + $phase->daily_expenses_total_amount;

            $phase->daily_wagers_total_service_charge_amount = ($site->service_charge / 100) * $phase->daily_wagers_total_amount + $phase->daily_wagers_total_amount;

            $phase->sqft_total_service_charge_amount = (($site->service_charge / 100) * $phase->square_footage_total_amount) + $phase->square_footage_total_amount;


            $phase->phase_total_amount = $phase->construction_total_amount + $phase->daily_expenses_total_amount + $phase->daily_wagers_total_amount + $phase->square_footage_total_amount;

            $phase->phase_total_service_charge_amount = ($site->service_charge / 100) * $phase->phase_total_amount;
            $phase->phase_total_with_service_charge_amount = $phase->phase_total_amount + $phase->phase_total_service_charge_amount;

            $grand_total_amount += $phase->phase_total_with_service_charge_amount;

        }


        $balance = $grand_total_amount - $totalPaymentSuppliersAmount;

        $suppliers = Supplier::orderBy('name')->get();

        $workforce_suppliers = Supplier::where('is_workforce_provider', 1)->orderBy('name')->get();

        $raw_material_providers = Supplier::where('is_raw_material_provider', 1)->orderBy('name')->get();

        $wagers = DailyWager::orderBy('wager_name')->get();

        $items = Item::orderBy('item_name')->get();

        return view('profile.User.Site.show-site',
            compact(
                'site',
                'grand_total_amount',
                'suppliers',
                'workforce_suppliers',
                'raw_material_providers',
                'wagers',
                'items',
                'totalPaymentSuppliersAmount',
                'balance'
            )
        );
    }
}
