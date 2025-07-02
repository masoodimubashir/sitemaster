<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteRequest;
use App\Models\DailyWager;
use App\Models\Item;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Client;
use App\Models\PaymentSupplier;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ViewSiteController extends Controller
{

    /**
     * Display the specified resource.
     */

    public function __construct(public DataService $dataService)
    {
    }




    public function showDetails(Request $request, string $id)
    {


        // Default filter options — or pull from request if needed
        $dateFilter = 'lifetime';
        $supplier_id = 'all';
        $wager_id = 'all';
        $startDate = 'start_date';
        $endDate = 'end_date';
        $phase_id = 'all';


        // Load site (for service charge, name, etc.)
        $site = Site::findOrFail($id);

        // Load processed financial data
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $this->dataService->getData(
            $dateFilter,
            $id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );

        // Combine and group all entries by phase
        $ledgers = $this->dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wastas,
            $labours
        )->filter(function ($entry) {
            return !empty($entry['phase']); // Only include entries with a phase
        })->sortByDesc(fn($entry) => $entry['created_at']);


        $ledgersGroupedByPhase = $ledgers->groupBy('phase');

        $balances = $this->dataService->calculateAllBalances($ledgers);
        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];

        // Per-phase breakdown
        $phaseData = [];

        foreach ($ledgersGroupedByPhase as $phaseName => $records) {

            $construction_total = $records->where('category', 'Material')->sum('debit');
            $square_total = $records->where('category', 'SQFT')->sum('debit');
            $expenses_total = $records->where('category', 'Expense')->sum('debit');
            $wasta_total = $records->where('category', 'Wasta')->sum('debit');
            $labour_total = $records->where('category', 'Labour')->sum('debit');
            $payments_total = $records->where('category', 'Payment')->sum('credit');

            $subtotal = $construction_total + $square_total + $expenses_total + $wasta_total + $labour_total;
            $withService = ($subtotal * $site->service_charge / 100) + $subtotal;


            $phaseData[] = [
                'phase' => $phaseName,
                'phase_id' => $records->first()['phase_id'],
                'construction_total_amount' => $construction_total,
                'square_footage_total_amount' => $square_total,
                'daily_expenses_total_amount' => $expenses_total,
                'daily_wastas_total_amount' => $wasta_total,
                'daily_labours_total_amount' => $labour_total,
                'total_payment_amount' => $payments_total,
                'phase_total' => $subtotal,
                'phase_total_with_service_charge' => $withService,
                'total_balance' => $withServiceCharge['balance'],
                'total_due' => $withServiceCharge['due'],
                'effective_balance' => $withoutServiceCharge['due'],
                'total_paid' => $withServiceCharge['paid'],
                'construction_material_billings' => $records->where('category', 'Material'),
                'square_footage_bills' => $records->where('category', 'SQFT'),
                'daily_expenses' => $records->where('category', 'Expense'),
                'daily_wastas' => $records->where('category', 'Wasta'),
                'daily_labours' => $records->where('category', 'Labour'),
            ];
        }

        return view(
            'profile.User.Site.show-site-detail',
            compact(
                'site',
                'phaseData'
            )
        );
    }


    public function show($id, Request $request, DataService $dataService)
    {


        $id = base64_decode($id);

        $dateFilter = $request->input('date_filter', 'today');
        $site_id = $request->input('site_id', $id);
        $supplier_id = $request->input('supplier_id', 'all');
        $phase_id = $request->input('phase_id', 'all');
        $startDate = $request->input('start_date'); // for 'custom'
        $endDate = $request->input('end_date');

        // Call the service to get all data including wasta and labours
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $labours] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );


        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers,
            $labours
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        // Calculate balances
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];

        // Paginate the ledgers
        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), 20),
            $ledgers->count(),
            20,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get unique suppliers
        $suppliers = $dataService->getSuppliersWithSites($site_id);


        // Get additional data for the view
        $items = Item::orderBy('item_name')->get();

        $workforce_suppliers = Supplier::where([
            'is_workforce_provider' => 1,
            'deleted_at' => null
        ])->orderBy('name')->get();

        $raw_material_providers = Supplier::where([
            'is_raw_material_provider' => 1,
            'deleted_at' => null
        ])->orderBy('name')->get();

        $phases = Phase::where([
            'deleted_at' => null,
            'site_id' => $site_id
        ])->latest()->get();

        $site = Site::select('id', 'site_name')->where([
            'is_on_going' => 1,
            'deleted_at' => null
        ])->find($site_id);

        return view(
            'profile.User.Site.show-site',
            compact(
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'total_balance',
                'suppliers',
                'wagers',
                'effective_balance',
                'id',
                'items',
                'workforce_suppliers',
                'raw_material_providers',
                'phases',
                'site',
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $clients = Client::orderBy('name')->get();


        return view('profile.partials.Admin.Site.create-site', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteRequest $request)
    {

        $request->validated();

        $client = Client::find($request->client_id);

        Site::create([
            'site_name' => $request->site_name,
            'service_charge' => $request->service_charge,
            'location' => $request->location,
            'site_owner_name' => $client->name,
            'contact_no' => $client->number,
            'user_id' => $request->user_id,
            'client_id' => $request->client_id,
            'is_on_going' => true
        ]);


        // $user->notify(new UserSiteNotification());

        return redirect('/user/dashboard')->with('status', 'create');

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
