<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentBill;
use App\Models\Site;
use Illuminate\Http\Request;

class PaymentBillsController extends Controller
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


        $request->validate([
            'amount' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'site_id' => 'required|exists:sites,id'
        ]);

        $amount = $request->total - $request->amount;

        $payment_bill = new PaymentBill();

        $payment_bill->amount = $request->amount;
        $payment_bill->supplier_id = $request->supplier_id;
        $payment_bill->site_id = $request->site_id;
        $payment_bill->save();


        return redirect()->back()->with('success', 'bill generated..');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
