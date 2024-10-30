<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        // Validate the incoming request


        if ($request->ajax()) {



            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'required|mimes:png,jpg,webp, jpeg|max:1024',
                'supplier_id' => 'required|exists:suppliers,id',
                'site_id' => 'required|exists:sites,id',
                'amount' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                    'regex:/^\d+(\.\d{0,2})?$/'
                ]
            ]);

            if ($validatedData->fails()) {
                return response()->json(['errors' =>  'Validation Error Try Agian...'], 422);
            }

            $image_path = null;

            // Handle file upload
            if ($request->hasFile('screenshot')) {
                $image_path = $request->file('screenshot')->store('SupplierPayment', 'public');
            }

            try {

                // Create the payment supplier entry
                PaymentSupplier::create([
                    'screenshot' => $image_path,
                    'supplier_id' => $request->supplier_id,
                    'site_id' => $request->site_id,
                    'amount' => $request->amount,
                    'verified_by_admin' => true
                ]);

                return response()->json(['message' => 'Supplier payment created successfully.']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'An unexpected error occurred: ']);
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
