<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Hash;

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

        $sites = Site::with(['users', 'client']) // eager load relationships
        ->latest()
            ->paginate(10);

        $users = User::where('role_name', 'site_engineer')->get();
        $clients = Client::all();

        return view('profile.partials.Admin.Site.sites', compact('sites', 'users', 'clients'));

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
            'engineer_ids' => 'sometimes|array|min:1',
            'engineer_ids.*' => 'exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'contact_no' => 'required|digits:10',
            // Inline engineer creation (optional)
            'new_engineer.name' => 'sometimes|required|string|min:3',
            'new_engineer.username' => 'sometimes|required|string|min:6|unique:users,username',
            'new_engineer.password' => 'sometimes|required|confirmed|min:6',
        ], [
            'engineer_ids.required' => 'The Site Engineer is required.',
            'engineer_ids.*.exists' => 'One or more selected engineers are invalid.',
            'new_engineer.name.required' => 'Engineer name is required when creating a new engineer.',
            'new_engineer.username.required' => 'Engineer username is required.',
            'new_engineer.username.unique' => 'This username is already taken.',
            'new_engineer.password.required' => 'Engineer password is required.',
            'new_engineer.password.confirmed' => 'Engineer password confirmation does not match.',
        ]);

        // Custom conditional validation: require either engineer_ids or new_engineer
        $validator->after(function ($v) use ($request) {
            $hasSelected = is_array($request->input('engineer_ids')) && count($request->input('engineer_ids')) > 0;
            $newName = data_get($request->all(), 'new_engineer.name');
            if (!$hasSelected && empty($newName)) {
                $v->errors()->add('engineer_ids', 'Please select at least one engineer or create a new one.');
            }
        });

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

            // Build engineer list (existing + optional newly created)
            $engineerIds = $validatedData['engineer_ids'] ?? [];

            // Create new engineer if provided
            if (isset($validatedData['new_engineer']) && !empty($validatedData['new_engineer']['name'] ?? null)) {
                $new = $validatedData['new_engineer'];
                $newUser = User::create([
                    'name' => $new['name'],
                    'username' => $new['username'],
                    'password' => Hash::make($new['password']),
                ]);
                $engineerIds[] = $newUser->id;
            }

            if (count($engineerIds) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please select at least one engineer or create a new one.',
                    'errors' => ['engineer_ids' => ['Engineer is required']]
                ], 422);
            }

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
            $site->users()->attach($engineerIds);

            // Notify all assigned users
            $users = User::whereIn('id', $engineerIds)->get();

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
     * Display the specified resource.
     */
    public function showSiteDetails(string $id)
    {

        $site = Site::findOrFail(base64_decode($id));

        // Default filter options — or pull from request if needed
        $dateFilter = 'lifetime';
        $supplier_id = 'all';
        $startDate = 'start_date';
        $endDate = 'end_date';
        $phase_id = 'all';
        $site_id = $site->id;

        // Call the service to get all data including attendances
        [$payments, $raw_materials, $squareFootageBills, $expenses] = $this->dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );

        $ledgers = $this->dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
        )->filter(function ($entry) {
            return !empty($entry['phase']);
        })->sortByDesc(fn($entry) => $entry['created_at'])
            ->groupBy('phase');

        // Per-phase breakdown
        $phaseData = [];

        foreach ($ledgers as $phaseName => $records) {

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

    public function show(Request $request, DataService $dataService, $id)
    {

        $site = Site::with('client')
            ->select('id', 'site_name', 'client_id')->where([
                'is_on_going' => 1,
                'deleted_at' => null
            ])
            ->find(base64_decode($id));

        $dateFilter = $request->input('date_filter', 'today');
        $site_id = $request->input('site_id', $site->id);
        $supplier_id = $request->input('supplier_id', 'all');
        $phase_id = $request->input('phase_id', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Call the service to get all data including attendances
        [$payments, $raw_materials, $squareFootageBills, $expenses, $attendances] = $dataService->getData(
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
            $attendances
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
        $supp = Supplier::whereNull('deleted_at')->orderBy('name')->get();

        $phases = Phase::where(['deleted_at' => null, 'site_id' => $site_id])->latest()->get();

        return view("profile.partials.Admin.Site.show-site", compact(
            'paginatedLedgers',
            'total_paid',
            'total_due',
            'total_balance',
            'suppliers',
            'effective_balance',
            'items',
            'phases',
            'site',
            'returns',
            'supp'
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


        $site = Site::where('id', $id)->first();

        $hasPaymentRecords = $site->payments()->exists();

        if ($hasPaymentRecords) {
            return redirect()->back()->with('status', 'hasPaymentRecords');
        }

        $site->delete();

        return redirect()->back()->with('status', 'delete');

    }
}
