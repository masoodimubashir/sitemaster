<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;

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
            'name' =>  $request->name,
            'contact_no' => $request->contact_no,
            'address' => $request->address,
            'is_raw_material_provider' =>  $request->provider_type === 'is_raw_material_provider'  ? 1 : 0,
            'is_workforce_provider' => $request->provider_type === 'is_workforce_provider' ? 1 : 0
        ]);

        return redirect()->route('suppliers.index')->with('message', 'supplier created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $supplier_id = $id;

        $supplier = Supplier::find($supplier_id);

        if ($supplier->is_raw_material_provider === 1) {

            $supplier = $supplier->load([
                'constructionMaterialBilling' => function ($q) {
                    $q->with(['phase' => function ($q) {
                        $q->with(['site' => function ($q) {
                            $q->orderBy('created_at');
                        }]);
                    }]);
                },
                'paymentSuppliers'
            ])
            ->loadSum('constructionMaterialBilling', 'amount')
            ->loadSum('paymentSuppliers', 'amount');

            $totalBaseAmount = 0;

            foreach ($supplier->constructionMaterialBilling as $billing) {
                $totalBaseAmount += $billing->amount;
            }

            $grandTotal = $totalBaseAmount;

            $totalPaymentSuppliers = $supplier->paymentSuppliers_sum_amount;
            $supplier->total = $grandTotal + $totalPaymentSuppliers;

            $sites = $supplier->constructionMaterialBilling->pluck('phase.site')->unique('id');

            return view('profile.partials.Admin.Supplier.show-supplier_raw_material', compact('supplier', 'sites', 'grandTotal'));
        } else {

            $supplier->load([
                'dailyWagers.phase.site',
                'squareFootages.phase.site'
            ])
            ->loadSum('dailyWagers', 'price_per_day')
            ->loadSum('paymentSuppliers', 'amount');

            $totalDailyWagersPrice = 0;
            $totalSquareFootagesPrice = 0;

            foreach ($supplier->dailyWagers as $dailyWager) {
                $totalDailyWagersPrice += $dailyWager->price_per_day;
            }

            foreach ($supplier->squareFootages as $squareFootage) {
                $totalSquareFootagesPrice += $squareFootage->price * $squareFootage->multiplier;
            }

            $totalPrice = $totalDailyWagersPrice + $totalSquareFootagesPrice;

            $totalPaymentSuppliers = $supplier->paymentSuppliers_sum_amount;

            $supplier->total = $totalPrice + $totalPaymentSuppliers;

            return view('profile.partials.Admin.Supplier.show_supplier_workforce', compact('supplier'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        if (!$supplier) {
            return redirect()->back()->with('error', 'supplier not found');
        }

        return view('profile.partials.Admin.Supplier.edit-supplier', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $request->validated();

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')->with('message', 'supplier updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->back()->with('message', 'supplier deleted');
    }
}
