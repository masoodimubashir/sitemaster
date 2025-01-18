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


        try {


            $request->validate([
                'amount' => 'required|numeric',
                'site_id' => 'required',
                'supplier_id' => 'required',
                'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('screenshot')) {

                $image = $request->file('screenshot');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = $request->file('screenshot')->storeAs('Payments', $imageName, 'public');
            }

            $payment = new PaymentSupplier();
            $payment->amount = $request->input('amount');
            $payment->site_id = $request->input('site_id');
            $payment->supplier_id = $request->input('supplier_id');
            $payment->verified_by_admin = 1;
            $payment->screenshot = $path;
            $payment->save();

            return response()->json(['message' => 'Payment created successfully']);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'An error occurred while creating the payment.'
            ]);
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
