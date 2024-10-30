<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSitePayments extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request


        if ($request->ajax()) {
            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'required|mimes:png,jpg,webp|max:1024',
                'supplier_id' => 'required|exists:suppliers,id',
                'site_id' => 'required|exists:sites,id',
                'amount' => 'required|integer|min:1',
            ]);

            if ($validatedData->fails()) {
                return response()->json(['errors' => 'Validation Error... Try Again!'], 422);
            }

            $image_path = null;

            if ($request->hasFile('screenshot')) {
                $image_path = $request->file('screenshot')->store('SupplierPayment', 'public');
            }

            try {
                PaymentSupplier::create([
                    'screenshot' => $image_path,
                    'supplier_id' => $request->supplier_id,
                    'site_id' => $request->site_id,
                    'amount' => $request->amount,
                ]);

                return redirect()->back()->with('message', 'Supplier payment created successfully.');
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return redirect()->back()->withErrors(['error' => 'An unexpected error occurred: ']);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $site = Site::findOrFail($id);

        $site->paymeentSuppliers()->latest()->paginate(10);

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
