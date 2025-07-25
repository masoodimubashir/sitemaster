<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
                'screenshot' => 'nullable|mimes:png,jpg,webp, jpeg|max:1024',
                'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99',],
                'transaction_type' => 'sometimes|in:0,1',
                'site_id' => 'required|exists:sites,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'payment_initiator' => 'nullable|in:0,1',
            ]);

            if ($validatedData->fails()) {
                
                return response()->json([
                    'errors' => $validatedData->errors(),
                ], 422);
            }

            $site = Site::find($request->input('site_id'));

            if (auth()->user()->role_name === 'site_engineer') {

                $this->sendpaymentToAdmin($request);

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new PaymentNotification($request->input('amount'), $site->site_name)
                );

                return response()->json([
                    'message' => 'Payment To Admin'
                ]);
            }

            $image_path = null;

            if ($request->hasFile('screenshot')) {
                $image_path = $request->file('screenshot')->store('Payment', 'public');
            }

            try {

                $payment = new Payment();
                $payment->amount = $request->input('amount');
                $payment->site_id = $request->input('site_id');
                $payment->supplier_id = $request->input('supplier_id');
                $payment->verified_by_admin = auth()->user()->role_name === 'site_engineer' ? 0 : 1;
                $payment->payment_initiator = $request->filled('supplier_id') && $request->filled('site_id') ? 1 : 0;
                $payment->transaction_type = $request->input('transaction_type');
                $payment->screenshot = $image_path;
                $payment->save();

                return response()->json([
                    'message' => 'Payment Done...'
                ]);

            } catch (\Exception $e) {
                Log::error('Error creating supplier payment: ' . $e->getMessage());
                return response()->json(['error' => 'An unexpected error occurred: ']);

            }
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


    private function sendpaymentToAdmin($request)
    {

        AdminPayment::create([
            'amount' => $request->input('amount'),
            'transaction_type' => $request->input('transaction_type'),
            'site_id' => $request->input('site_id'),
            'supplier_id' => $request->input('supplier_id'),
            'entity_id' => $request->input('supplier_id'),
            'entity_type' => Supplier::class,
        ]);

    }
}
