<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Client;
use App\Models\Item;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\UserSiteNotification;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{

    public function __construct(public DataService $dataService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $sites = Site::latest()->paginate(10);

        $users = User::where('role_name', 'site_engineer')->get();

        $clients = Client::all();

        return view('profile.partials.Admin.Site.sites', compact('sites', 'users', 'clients'));
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

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'site_name' => 'required|string|min:5',
            'service_charge' => 'required|decimal:0,2',
            'location' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'contact_no' => 'required|digits:10',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Some Form Fields Are Missing...',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $validatedData = $validator->validated();

            $user = User::find($validatedData['user_id']);
            $client = Client::find($validatedData['client_id']);

            $site = Site::create([
                'site_name' => $validatedData['site_name'],
                'location' => $validatedData['location'],
                'contact_no' => $validatedData['contact_no'],
                'service_charge' => $validatedData['service_charge'],
                'site_owner_name' => $client->name,
                'is_on_going' => true,
                'user_id' => $validatedData['user_id'],
                'client_id' => $validatedData['client_id'],
            ]);

            $user->notify(new UserSiteNotification());

            return response()->json([
                'status' => true,
                'message' => 'Site created successfully!',
                'data' => $site
            ], 201);
        } catch (\Exception $e) {
            Log::error('Site creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function showSiteDetails(string $id)
    {

        $site_id = base64_decode($id);

        // Default filter options — or pull from request if needed
        $dateFilter = 'lifetime';
        $supplier_id = 'all';
        $wager_id = 'all';
        $startDate = 'start_date';
        $endDate = 'end_date';
        $phase_id = 'all';


        // Load site (for service charge, name, etc.)
        $site = Site::findOrFail($site_id);

        // Load processed financial data
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $this->dataService->getData(
            $dateFilter,
            $site_id,
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


        return view('profile.partials.Admin.Site.show-site-detail', compact(
            'site',
            'phaseData'
        ));
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

        return view("profile.partials.Admin.Site.show-site", compact(
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
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $site = Site::find($id);

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

        $site->update($request->validated());

        return redirect()->route('sites.index')->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $site_id = base64_decode($id);

        $site = Site::where('id', $site_id)->first();

        $hasPaymentRecords = $site::query()
            ->whereHas('payments')
            ->orWhereHas('adminPayments')
            ->exists();

        if ($hasPaymentRecords) {
            return redirect()->back()->with('status', 'hasPaymentRecords');
        }

        $site->delete();

        return redirect()->back()->with('status', 'delete');
    }
}
