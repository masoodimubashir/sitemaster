<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PaymentSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $request->validate([
            'screenshot' => 'required|mimes:png,jpg,webp|max:1024',
            'supplier_id' => 'required|exists:suppliers,id',
            'site_id' => 'required|exists:sites,id',
            'amount' => 'required|integer',
        ]);


        $image_path = null;

        if ($request->hasFile('screenshot')) {
            $image_path = $request->file('screenshot')->store('Supplier_Payemnt');
        }

        PaymentSupplier::create([
            'screenshot' => $image_path,
            'supplier_id' => $request->supplier_id,
            'site_id' => $request->site_id,
            'amount' => $request->amount,
            'is_verified' => $request->has('is_verified') ? true : false
        ]);

        return redirect()->back()->with('message', 'supplier payment created..');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $site = Site::findOrFail($id);

        $site->paymeentSuppliers()->latest()->paginate(1);

        return view('profile.partials.Admin.PaymentSuppliers.site-payment-supplier', compact('site'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);

        $paymentSuppliers = $supplier->paymentSuppliers()
            ->with('site')
            ->latest()
            ->paginate(10);


        return view('profile.partials.Admin.PaymentSuppliers.supplier-payment', compact('paymentSuppliers'));
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
