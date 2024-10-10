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
        $client = Auth::user();

        $sites = $client->sites()->latest()->paginate(10);

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
            'phases.constructionMaterialBillings' => function ($q) {
                $q->with([
                    'supplier' => function ($q) {
                        $q->withTrashed();
                    }
                ])->latest();
            },
            'phases.squareFootageBills' => function ($q) {
                $q->with('supplier')->latest();
            },
            'phases.dailyWagers' => function ($q) {
                $q->with('supplier')->latest();
            },
            'phases.dailyExpenses' => function ($q) {
                $q->latest();
            },
            'phases.wagerAttendances' => function ($q) {
                $q->with('dailyWager.supplier')->latest();
            }
        ])->findOrFail($site_id);

        // Initialize grand total variables
        $grand_total_construction_amount = 0;
        $grand_total_daily_expenses_amount = 0;
        $grand_total_daily_wagers_amount = 0;
        $grand_total_square_footage_amount = 0;

        // Iterate through phases and calculate totals
        foreach ($site->phases as $phase) {
            // Calculate totals for the current phase
            $phase->construction_total_amount = $phase->constructionMaterialBillings->sum('amount');
            $phase->daily_expenses_total_amount = $phase->dailyExpenses->sum('price');
            $phase->daily_wagers_total_amount = $phase->dailyWagers->sum('price_per_day');

            // Multiply price by multiplier for square footage bills
            $phase->square_footage_total_amount = $phase->squareFootageBills->reduce(function ($carry, $bill) {
                return $carry + ($bill->price * $bill->multiplier);
            }, 0);

            // Calculate the total amount for the current phase
            $phase->total_amount = $phase->construction_total_amount +
                $phase->daily_expenses_total_amount +
                $phase->daily_wagers_total_amount +
                $phase->square_footage_total_amount;

            // Accumulate grand totals
            $grand_total_construction_amount += $phase->construction_total_amount;
            $grand_total_daily_expenses_amount += $phase->daily_expenses_total_amount;
            $grand_total_daily_wagers_amount += $phase->daily_wagers_total_amount;
            $grand_total_square_footage_amount += $phase->square_footage_total_amount;
        }

        // Calculate the grand total of all phases
        $grand_total_amount = $grand_total_construction_amount +
            $grand_total_daily_expenses_amount +
            $grand_total_daily_wagers_amount +
            $grand_total_square_footage_amount;

        // $suppliers = Supplier::orderBy('name')->get();

        // $workforce_suppliers = Supplier::where('is_workforce_provider', 1)->orderBy('name')->get();
        // $raw_material_providers = Supplier::where('is_raw_material_provider', 1)->orderBy('name')->get();

        // $wagers = DailyWager::orderBy('wager_name')->get();


        // $items = Item::orderBy('item_name')->get();

        return view('profile.partials.Client.Dashboard.show-client-site', compact('site',  'grand_total_amount',));
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
