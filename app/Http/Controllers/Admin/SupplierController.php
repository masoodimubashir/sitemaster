<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\PaymentSupplier;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
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

        return redirect()->to('admin/suppliers')->with('status', 'create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::with([
            'constructionMaterialBilling' => function ($query) {
                $query->where('verified_by_admin', 1)
                    ->with([
                        'phase' => function ($phase) {
                            $phase->whereNull('deleted_at')
                                ->with([
                                    'site' => function ($siteQuery) {
                                        $siteQuery->whereNull('deleted_at');
                                    }
                                ]);
                        }
                    ]);
            },
            'dailyWagers' => function ($daily_wager) {
                $daily_wager->with([
                    'phase' => function ($phase) {
                        $phase->whereNull('deleted_at');
                    },
                    'wagerAttendances'
                ]);
            },
            'squareFootages' => function ($sqft) {
                $sqft->where('verified_by_admin', 1)
                    ->with([
                        'phase' => function ($phase) {
                            $phase->whereNull('deleted_at')
                                ->with([
                                    'site' => function ($siteQuery) {
                                        $siteQuery->whereNull('deleted_at');
                                    }
                                ]);
                        }
                    ]);
            },
            'paymentSuppliers' => function ($payment) {
                $payment->where('verified_by_admin', 1);
            }
        ])
            ->find($id);

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
                'site' => $material->phase->site->site_name,
                'site_owner' => $material->phase->site->site_owner_name,
                'site_id' => $material->phase->site->id
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
                'site' => $wager->phase->site->site_name,
                'site_owner' => $wager->phase->site->site_owner_name,
                'site_id' => $wager->phase->site->id
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
                'site' => $sqft->phase->site->site_name,
                'site_owner' => $sqft->phase->site->site_owner_name,
                'site_id' => $sqft->phase->site->id
            ];
        }));

        // Merge Payments (Credit)
        $data = $data->merge($supplier->paymentSuppliers->map(function ($payment) {
            return [
                'created_at' => $payment->created_at,
                'type' => 'Payment',
                'image' => $payment->screenshot,
                'item' => 'Payment',
                'price_per_unit' => 0,
                'total_price' => $payment->amount,
                'transaction_type' => 'credit',
                'site' => $payment->site->site_name,
                'site_owner' => $payment->site->site_owner_name,
                'site_id' => $payment->site->id,
            ];
        }));

        // Calculate totals
        $totalDebit = $data->where('transaction_type', 'debit')->sum('total_price');
        $totalCredit = $data->where('transaction_type', 'credit')->sum('total_price');

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

        if ($supplier->is_raw_material_provider === 1) {
            return view('profile.partials.Admin.Supplier.show-supplier_raw_material', $data);
        }

        return view('profile.partials.Admin.Supplier.show_supplier_workforce', $data);

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

        $hasPaymentRecords = PaymentSupplier::where(function ($query) use ($supplier) {
            $query->orWhere('supplier_id', $supplier->id);
        })->exists();

        if ($hasPaymentRecords) {
            return redirect()->to('admin/suppliers')->with('status', 'error');
        }

        $supplier->delete();

        return redirect()->to('admin/suppliers')->with('status', 'delete');
    }
}
