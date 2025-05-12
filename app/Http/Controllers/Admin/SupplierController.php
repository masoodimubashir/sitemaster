<?php



namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Services\DataService;

class SupplierController extends Controller
{

    public function __construct(public DataService $dataService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);

        return view('profile.partials.Admin.Supplier.suppliers', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('profile.partials.Admin.Supplier.create-supplier');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $request->validated();

        Supplier::create([
            'name' => $request->name,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'is_raw_material_provider' => $request->provider === 'is_raw_material_provider' ? 1 : 0,
            'is_workforce_provider' => $request->provider === 'is_workforce_provider' ? 1 : 0,
        ]);

        return redirect()->back()->with('status', 'create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $supplier_id = $id;

        // Optional: use site/wager filters if needed later
        $site_id = 'all';
        $wager_id = 'all';
        $date_filter = 'lifetime'; // Show all data
        $start_date = null;
        $end_date = null;

        // Load the supplier
        $supplier = Supplier::findOrFail($supplier_id);

        // Use existing ledger logic
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $this->dataService->getData(
            $date_filter,
            $site_id,
            $supplier_id,
            $wager_id,
            $start_date,
            $end_date
        );

        $ledgers = $this->dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

        // Compute balances
        $totals = $this->dataService->calculateAllBalances($ledgers);

        // Group unique sites
        $sites = collect($ledgers)
            ->unique('site_id')
            ->map(function ($item) {
                return [
                    'site_id' => $item['site_id'],
                    'site_name' => $item['site'],
                ];
            })->values();

        // Prepare data
        $data = [
            'ledgers' => $ledgers,
            'supplier' => $supplier,
            'totalDebit' => $totals['without_service_charge']['due'],
            'totalCredit' => $totals['without_service_charge']['paid'],
            'balance' => $totals['without_service_charge']['balance'],
            'sites' => $sites,
        ];


        if ($supplier->is_raw_material_provider == 1) {
            return view('profile.partials.Admin.Supplier.show-supplier_raw_material', compact('data', 'sites'));
        }

        return view('profile.partials.Admin.Supplier.show_supplier_workforce', compact('data', 'sites'));
    }

    public function showSupplierDetail(string $id)
    {

        $supplier = Supplier::with([
            'constructionMaterialBilling' => function ($material) {
                $material->where([
                    'verified_by_admin' => 1,
                    'deleted_at' => null,
                ])->with(['phase' => function ($phase) {
                    $phase->with(['site' => fn($site) => ($site->whereNull('deleted_at'))])->whereNull('deleted_at');
                }])->latest();
            },
            'dailyWagers' => fn($daily_wager) => ($daily_wager->whereNull('deleted_at'))->latest(),
            'squareFootages' => function ($square_footage) {
                $square_footage->where([
                    'verified_by_admin' => 1,
                    'deleted_at' => null,
                ])->with(['phase' => function ($phase) {
                    $phase->with(['site' => fn($site) => ($site->whereNull('deleted_at'))])->whereNull('deleted_at');
                }])->latest();
            },
            'payments' => function ($payment) {
                $payment->where('verified_by_admin', 1);
            },
        ])->find($id);

        $data = collect();

        // Merge Construction Materials (Debit)
        $data = $data->merge($supplier->constructionMaterialBilling->map(function ($material) {
            return [
                'created_at' => $material->created_at,
                'type' => 'Material',
                'image' => $material->item_image_path,
                'item' => $material->item_name,
                'price_per_unit' => 0,
                'total_price' => $material->amount,
                'transaction_type' => 'debit',
                'site' => $material->phase->site->site_name ?? 'NA',
                'site_name' => $material->phase->site->site_name ?? 'NA',
                'site_id' => $material->phase->site->id ?? 'NA'
            ];
        }));

        // Merge Daily Wagers (Debit)
        $data = $data->merge($supplier->dailyWagers->map(function ($wager) {
            return [
                'created_at' => $wager->created_at,
                'type' => 'Daily Wager',
                'image' => null,
                'item' => $wager->wager_name,
                'price_per_unit' => $wager->price_per_day,
                'total_price' => $wager->wager_total,
                'transaction_type' => 'debit',
                'site' => $wager->phase->site->site_name ?? 'NA',
                'site_name' => $wager->phase->site->site_name ?? 'NA',
                'site_id' => $wager->phase->site->id ?? 'NA'
            ];
        }));

        // Merge Square Footages (Debit)
        $data = $data->merge($supplier->squareFootages->map(function ($sqft) {
            return [
                'created_at' => $sqft->created_at,
                'type' => 'SQFT',
                'image' => $sqft->image_path,
                'item' => $sqft->wager_name,
                'price_per_unit' => $sqft->price,
                'total_price' => $sqft->price * $sqft->multiplier,
                'transaction_type' => 'debit',
                'site' => $sqft->phase->site->site_name ?? 'NA',
                'site_name' => $sqft->phase->site->site_name ?? 'NA',
                'site_id' => $sqft->phase->site->id ?? 'NA'
            ];
        }));

        // Calculate totals
        $totalDebit = $data->where('transaction_type', 'debit')->sum('total_price');
        $totalCredit = $supplier->payments()->sum('amount');

        $balance = $totalDebit - $totalCredit;

        $sites = collect($data)
            ->unique('site')
            ->whereNotNull('site')
            ->values()
            ->all();

        $data = [
            'data' => $data,
            'supplier' => $supplier,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balance' => $balance,
            'sites' => $sites
        ];

            return view('profile.partials.Admin.Supplier.show-supplier-detail', compact('data', 'sites'));



    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        if (!$supplier) {
            return redirect()->back()->with('status', 'error');
        }

        return view('profile.partials.Admin.Supplier.edit-supplier', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {

        $request->validated();

        $supplier->update([
            'name' => $request->name,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'is_raw_material_provider' => $request->provider === 'is_raw_material_provider' ? 1 : 0,
            'is_workforce_provider' => $request->provider === 'is_workforce_provider' ? 1 : 0,
        ]);

        return redirect()->to('admin/suppliers')->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $supplier = Supplier::find($id);


        if (!$supplier) {
            return redirect()->back()->with('status', 'error');
        }

        $hasPaymnent = $supplier->payments()->exists();


        if ($hasPaymnent) {
            return redirect()->to('admin/suppliers')->with('status', 'hasPaymnent');
        }

        $supplier->delete();

        return redirect()->to('admin/suppliers')->with('status', 'delete');
    }
}
