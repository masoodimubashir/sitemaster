<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {

            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'required|mimes:png,jpg,webp, jpeg|max:1024',
                'amount' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],
                'transaction_type' => 'required|in:0,1',
                'site_id' => 'nullable|exists:sites,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'errors' => 'Forms Fields Are Missing..'
                ], 422);
            }

            $image_path = null;
            if ($request->hasFile('screenshot')) {

                $image_path = $request->file('screenshot')->store('Payment', 'public');

            }

            $transaction_type = null;
            if ($request->filled('site_id')) {
                $transaction_type = (int)$request->transaction_type === 0 ? 'sent' : 'received';
            }


            try {

                $payment = new Payment();
                $payment->amount = $request->input('amount');
                $payment->site_id = $request->input('site_id');
                $payment->supplier_id = $request->input('supplier_id');
                $payment->transaction_type = $transaction_type;
                $payment->verified_by_admin = 1;
                $payment->payment_initiator = $request->filled('site_id') ? 1 : 0;
                $payment->screenshot = $image_path;
                $payment->save();

                return response()->json([
                    'message' => 'Supplier payment created successfully.'
                ]);

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

        $supplier = Supplier::find($id);

        $payments = $supplier->paymentSuppliers()->where('verified_by_admin', 1)->paginate(10);

        return view('profile.partials.Admin.PaymentSuppliers.supplier-payment', compact('payments', 'supplier'));
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


        return view('profile.partials.Admin.PaymentSuppliers.supplier-payment', compact('paymentSuppliers', 'supplier'));
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
