<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\Item;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\get;

class ClientDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = auth()->user();

        $sites = $user->sites()->paginate(10);

        return view('profile.partials.Client.Dashboard.dashboard', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */


    public function show(string $id)
    {


        $site_id = base64_decode($id);

        $site = Site::with([
            'phases' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'phases.constructionMaterialBillings' => function ($query) {
                $query->with('supplier')
                    ->where('verified_by_admin', 1)
                    ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'phases.squareFootageBills' => function ($query) {
                $query->with('supplier')
                    ->where('verified_by_admin', 1)
                    ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'phases.dailyWagers' => function ($query) {
                $query->with(['wagerAttendances', 'supplier'])
                    ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'phases.dailyExpenses' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'phases.wagerAttendances' => function ($query) {
                $query->with('dailyWager.supplier')
                    ->whereHas('dailyWager.supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'payments' => function ($query) {
                $query->where('verified_by_admin', 1);
            },
        ])
        ->find($site_id);

        $totalPaymentSuppliersAmount = $site->payments()
            ->where('verified_by_admin', 1)
            ->sum('amount');

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

        return view('profile.partials.Client.Dashboard.show-site',
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
