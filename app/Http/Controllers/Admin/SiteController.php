<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Client;
use App\Models\Item;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\UserSiteNotification;

class   SiteController extends Controller
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

        $site = Site::create([
            'site_name' => $request->site_name,
            'service_charge' => $request->service_charge,
            'location' => $request->location,
            'site_owner_name' => $client->name,
            'contact_no' => $client->number,
            'user_id' => $request->user_id,
            'client_id' => $request->client_id,
            'is_on_going' => true
        ]);


        $user->notify(new UserSiteNotification());

        return redirect()->route('sites.index')->with('status', 'create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $site_id = base64_decode($id);

        $site = Site::with([
            'phases.constructionMaterialBillings' => function($query) {
                $query->with('supplier')
                      ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                      ->latest();
            },
            'phases.squareFootageBills' => function($query) {
                $query->with('supplier')
                      ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                      ->latest();
            },
            'phases.dailyWagers' => function($query) {
                $query->with(['wagerAttendances', 'supplier'])
                      ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                      ->latest();
            },
            'phases.dailyExpenses' => function($query) {
                $query->latest();
            },
            'phases.wagerAttendances' => function($query) {
                $query->with('dailyWager.supplier')
                      ->whereHas('dailyWager.supplier', fn($q) => $q->whereNull('deleted_at'))
                      ->latest();
            },
            'payments' => function($query) {
                $query->where('verified_by_admin', 1);
            },

        ])->findOrFail($site_id);



        $totalPaymentSuppliersAmount = $site->payments()
            ->where('verified_by_admin', 1)
            ->sum('amount');
        $grand_total_amount = 0;

        foreach ($site->phases as $phase) {

            $phase->construction_total_amount = $phase->constructionMaterialBillings->filter(function ($material) {
                return $material->verified_by_admin === 1;
            })
                ->sum('amount');

            $phase->square_footage_total_amount = $phase->squareFootageBills
                ->filter(function ($sqft) {
                    return $sqft->verified_by_admin === 1;
                })
                ->reduce(function ($sum, $sqft) {
                    return $sum + ($sqft->price * $sqft->multiplier);
                }, 0);

            $phase->daily_expenses_total_amount = $phase->dailyExpenses->filter(function ($expense) {
                return $expense->verified_by_admin === 1;
            })->sum('price');


            foreach ($phase->dailyWagers as $wager) {
                $phase->daily_wagers_total_amount += $wager->wager_total;
            }

            $phase->construction_total_service_charge_amount = ($site->service_charge / 100) * $phase->construction_total_amount +  $phase->construction_total_amount;
            $phase->daily_expense_total_service_charge_amount = ($site->service_charge / 100) * $phase->daily_expenses_total_amount + $phase->daily_expenses_total_amount;
            $phase->daily_wagers_total_service_charge_amount = ($site->service_charge / 100) * $phase->daily_wagers_total_amount + $phase->daily_wagers_total_amount;


            // Phase Total Amount
            $phase->phase_total_amount = $phase->construction_total_amount + $phase->daily_expenses_total_amount + $phase->daily_wagers_total_amount + $phase->square_footage_total_amount;

            $phase->sqft_total_service_charge_amount = (($site->service_charge / 100) * $phase->square_footage_total_amount) + $phase->square_footage_total_amount;

            $phase->phase_total_service_charge_amount = ($site->service_charge / 100) * $phase->phase_total_amount;

            $phase->phase_total_with_service_charge_amount = $phase->phase_total_amount + $phase->phase_total_service_charge_amount;

            $grand_total_amount += $phase->phase_total_with_service_charge_amount;
        }


        $balance = $grand_total_amount - $totalPaymentSuppliersAmount;

        $suppliers = Supplier::orderBy('name')->get();

        $workforce_suppliers = Supplier::where('is_workforce_provider', 1)->orderBy('name')->get();

        $raw_material_providers = Supplier::where('is_raw_material_provider', 1)->orderBy('name')->get();

        $wagers = $site->phases->flatMap(function ($phase) {

            return $phase->dailyWagers->map(function ($wager) {
                return [
                    'id' => $wager->id,
                    'name' => $wager->wager_name,
                ];
            });

        })->values()->toArray();


        $items = Item::orderBy('item_name')->get();

        return view(
            'profile.partials.Admin.Site.show-site',
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

        return redirect()->route('sites.index')->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $site_id = base64_decode($id);

        $site = Site::where('id', $site_id)->first();

        $hasPaymentRecords = PaymentSupplier::where(function ($query) use ($site) {
            $query->where('site_id', $site->id)->first();
        })->exists();

        if ($hasPaymentRecords) {
            return redirect()->back()->with('status', 'error');
        }

        $site->phases()->delete();

        $site->delete();

        return redirect()->back()->with('status', 'delete');
    }
}
