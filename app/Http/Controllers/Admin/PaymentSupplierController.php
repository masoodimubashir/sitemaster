<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        // Basic validation (keep lenient to avoid breaking other flows)
        $validated = Validator::make($request->all(), [
            'screenshot' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'transaction_type' => 'nullable|in:0,1',
            'site_id' => 'nullable|exists:sites,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_initiator' => 'nullable|in:0,1',
            'created_at' => 'required|date',
            'narration' => 'nullable|string|max:255',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'errors' => $validated->errors(),
            ], 422);
        }

        $image_path = null;
        if ($request->hasFile('screenshot')) {
            $image_path = $request->file('screenshot')->store('Payment', 'public');
        }

        $role = auth()->user()->role_name ?? 'user';
        $payToAdmin = (bool)$request->input('payment_initiator');
        $isAdmin = $role === 'admin';

        try {
            // Case 1: User role – always save as AdminPayment
            if (!$isAdmin) {
                AdminPayment::create([
                    'screenshot' => $image_path,
                    'amount' => $request->input('amount'),
                    'transaction_type' => 0,
                    'created_at' => $request->input('created_at'),
                ]);

                return response()->json(['message' => 'Payment To Admin']);
            }

            // Case 2: Admin chose Pay To Admin – store in AdminPayment
            if ($payToAdmin) {
                AdminPayment::create([
                    'screenshot' => $image_path,
                    'amount' => $request->input('amount'),
                    'transaction_type' => (int) $request->input('transaction_type', 0),
                    'created_at' => $request->input('created_at'),
                ]);

                return response()->json(['message' => 'Payment To Admin']);
            }

            // Case 3: Admin paying against a site/supplier – store in Payment
            $payment = new Payment();
            $payment->amount = $request->input('amount');
            $payment->site_id = $request->input('site_id');
            $payment->supplier_id = $request->input('supplier_id');
            $payment->verified_by_admin = 1;
            $payment->payment_initiator = $request->filled('supplier_id') && $request->filled('site_id') ? 1 : 0;
            $payment->transaction_type = $request->input('transaction_type');
            $payment->screenshot = $image_path;
            $payment->narration = $request->input('narration', null);
            $payment->created_at = $request->input('created_at');
            $payment->save();

            return response()->json(['message' => 'Payment Done...']);
        } catch (\Exception $e) {
            Log::error('Error creating supplier payment: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $supplier = Supplier::with('payments')->find($id);

        $payments = $supplier->payments()
            ->where('verified_by_admin', 1)
            ->with('site')
            ->paginate(10);

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
