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
            'site_name' => 'required|string|min:1',
            'service_charge' => 'required|decimal:0,2',
            'location' => 'required|string',
            'engineer_ids' => 'required|array|min:1',
            'engineer_ids.*' => 'exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'contact_no' => 'required|digits:10',
        ], [
            'engineer_ids.required' => 'The Site Engineer is required.',
            'engineer_ids.*.exists' => 'One or more selected engineers are invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Some form fields are missing or invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();

            $client = Client::findOrFail($validatedData['client_id']);

            $site = Site::create([
                'site_name' => $validatedData['site_name'],
                'location' => $validatedData['location'],
                'contact_no' => $validatedData['contact_no'],
                'service_charge' => $validatedData['service_charge'],
                'site_owner_name' => $client->name,
                'is_on_going' => true,
                'client_id' => $validatedData['client_id'],
            ]);

            // Attach engineers
            $site->users()->attach($validatedData['engineer_ids']);

            // Notify all assigned users
            $users = User::whereIn('id', $validatedData['engineer_ids'])->get();

            foreach ($users as $user) {
                $user->notify(new UserSiteNotification());
            }

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
        $startDate = $request->input('start_date');
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
        $returns = $withoutServiceCharge['return'];


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

        // Single query to get both workforce and raw material providers
        $supp = Supplier::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Separate them into different collections
        $workforce_suppliers = $supp->where('is_workforce_provider', 1);
        $raw_material_providers = $supp->where('is_raw_material_provider', 1);

        $phases = Phase::where([
            'deleted_at' => null,
            'site_id' => $site_id
        ])->latest()->get();

        $site = Site::with('client')
            ->select('id', 'site_name', 'client_id')
            ->where([
                'is_on_going' => 1,
                'deleted_at' => null
            ])
            ->find($site_id);

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
            'returns'
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
    public function update(Request $request, string $id)
    {
        try {

            $site_id = base64_decode($id);

            $validator = Validator::make($request->all(), [
                'site_name' => 'required|string|min:1',
                'service_charge' => 'required|decimal:0,2',
                'location' => 'required|string',
                'engineer_ids' => 'required|array|min:1',
                'engineer_ids.*' => 'exists:users,id',
                'contact_no' => 'required|digits:10',
            ], [
                'engineer_ids.required' => 'The Site Engineer is required.',
                'engineer_ids.*.exists' => 'One or more selected engineers are invalid.',
            ]);


            $validatedData = $validator->validated();

            $site = Site::findOrFail($site_id);

            // Update site details
            $site->update([
                'site_name' => $validatedData['site_name'],
                'location' => $validatedData['location'],
                'contact_no' => $validatedData['contact_no'],
                'service_charge' => $validatedData['service_charge'],
            ]);

            // Sync engineers - this will detach any not in the array and attach new ones
            $currentEngineers = $site->users()->pluck('users.id')->toArray();
            $newEngineers = $validatedData['engineer_ids'];

            $site->users()->sync($newEngineers);

            // Notify newly assigned users
            $addedEngineers = array_diff($newEngineers, $currentEngineers);
            if (!empty($addedEngineers)) {
                $users = User::whereIn('id', $addedEngineers)->get();
                foreach ($users as $user) {
                    $user->notify(new UserSiteNotification());
                }
            }

            return redirect()->route('sites.index')->with('status', 'update');

        } catch (\Exception $e) {
            Log::error('Site update failed: ' . $e->getMessage());
            return redirect()->back()->with('status', 'error');
        }
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
