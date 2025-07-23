<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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

        try {


            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'nullable|mimes:png,jpg,webp, jpeg|max:1024',
                'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99',],
                'transaction_type' => 'nullable|in:0,1',
                'site_id' => 'nullable|exists:sites,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
            ]);

            if ($validatedData->fails()) {
                return response()->json(
                    [
                        'errors' => $validatedData->errors(),
                    ],
                    422
                );
            }

            $image_path = null;

            if ($request->hasFile('screenshot')) {
                $image_path = $request->file('screenshot')->store('Payment', 'public');
            }

            $site = Site::find($request->input('site_id'));

            Notification::send(
                User::where('role_name', 'admin')->get(),
                new PaymentNotification($request->input('amount'), $site->site_name)
            );

            AdminPayment::create([
                'screenshot' => $image_path,
                'amount' => $request->input('amount'),
                'transaction_type' => $request->input('transaction_type'),
                'site_id' => $request->input('site_id'),
                'supplier_id' => $request->input('supplier_id'),
                'entity_id' => $request->input('site_id'),
                'entity_type' => Site::class,
            ]);

            return response()->json(['message' => 'Payment done...']);



        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json(['error' => 'Payment Cannot Be Made.. Try Again']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $site = Site::findOrFail($id);

        $site->payments()->where('verified_by_admin', 1)->latest()->paginate(10);

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
