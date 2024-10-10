<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Client;
use App\Models\DailyWager;
use App\Models\Item;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\UserSiteNotification;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::latest()->paginate(10);

        return view('profile.partials.Admin.Site.sites', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where([
            'role_name' => 'site_engineer'
        ])->orderBy('id', 'desc')->get();

        $clients = Client::orderBy('name')->get();

        return view('profile.partials.Admin.Site.create-site', compact('clients', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteRequest $request)
    {

        $request->validated();

        $user = User::find($request->user_id);

        $client = Client::find($request->client_id);

        $user->notify(new UserSiteNotification());

        Site::create([
            'site_name' => $request->site_name,
            'service_charge' => $request->service_charge,
            'location' => $request->location,
            'site_owner_name' => $client->name,
            'contact_no' => $client->number,
            'user_id' => $request->user_id,
            'client_id' => $request->client_id
        ]);

        return redirect()->route('sites.index')->with('message', 'site created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $site_id = base64_decode($id);

        $site = Site::with([
            'phases.constructionMaterialBillings' => function ($q) {
                $q->with(['supplier' => function ($q) {
                    $q->withTrashed();
                }])->latest();
            },
            'phases.squareFootageBills' => function ($q) {
                $q->with('supplier', function ($q) {
                    $q->withTrashed();
                })->latest();
            },
            'phases.dailyWagers' => function ($q) {
                $q->with('supplier', function ($q) {
                    $q->withTrashed();
                })->latest();
            },
            'phases.dailyExpenses' => function ($q) {
                $q->withTrashed()->latest();
            },
            'phases.wagerAttendances' => function ($q) {
                $q->with(['dailyWager.supplier' => function ($q) {
                    $q->withTrashed();
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

        return view('profile.partials.Admin.Site.show-site', compact('site', 'grand_total_amount', 'suppliers', 'workforce_suppliers', 'raw_material_providers', 'wagers', 'items', 'totalPaymentSuppliersAmount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $site_id = base64_decode($id);

        $site = Site::find($site_id);

        $users = User::where([
            'role_name' => 'site_engineer',
        ])->orderBy('id', 'desc')->get();

        if (!$site) {
            return redirect()->back()->with('message', 'site not found');
        }

        return view('profile.partials.Admin.Site.edit-site', compact('site', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteRequest $request, string $id)
    {
        $site_id = base64_decode($id);

        $request->validated();

        $site = Site::find($site_id);

        $site->update($request->all());

        return redirect()->route('sites.index')->with('message', 'site updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $site_id = base64_decode($id);

        $site = Site::findOrFail($site_id);

        $site->delete();

        return redirect()->route('sites.index')->with('message', 'site deleted');
    }
}
