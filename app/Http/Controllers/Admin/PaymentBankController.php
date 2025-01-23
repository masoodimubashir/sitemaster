<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentBank;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Http\Request;


class PaymentBankController extends Controller
{
    public function index()
    {

        $sites = Site::latest()->get();
        $suppliers = Supplier::latest()->get();

        $collection = collect([
            ...$sites->map(function ($site) {
                return [
                    'id' => $site->id,
                    'name' => $site->site_name,
                    'category' => 'site',
                ];
            }),
            ...$suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'category' => 'Supplier',
                ];
            })
        ]);

        $payment_banks = PaymentBank::latest()->paginate(10);
        $total_amount = PaymentBank::sum('amount');


        return view('profile.partials.Admin.PaymentSuppliers.manage-payments', compact('collection', 'payment_banks', 'total_amount'));
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {

            try {

                $validated = $request->validate([
                    'from' => 'required|string',
                    'to' => 'required|string',
                    'amount' => 'required|numeric|min:0',
                    'is_on_going' => 'boolean'
                ]);

                $fromParts = explode('_', $validated['from']);
                $toParts = explode('_', $validated['to']);

                if (count($fromParts) !== 2 || count($toParts) !== 2) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid from/to format'
                    ], 422);
                }

                [$fromType, $fromId] = $fromParts;
                [$toType, $toId] = $toParts;


                $payment = PaymentBank::create([
                    'from' => $fromId,
                    'from_type' => $fromType,
                    'to' => $toId,
                    'to_type' => $toType,
                    'amount' => $validated['amount'],
                    'is_on_going' => $validated['is_on_going'] ?? false
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment recorded successfully',
                    'data' => $payment
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to process payment'
                ], 422);
            }
        }
    }

    public function edit($id)
    {
        $payment = PaymentBank::findOrFail($id);

        return response()->json($payment);
    }


    public function update(Request $request)
    {

        dd($request->all());    
        $validated = $request->validate([
            'payment_id' => 'required|exists:payment_banks,id',
            'from' => 'required|string',
            'to' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'is_on_going' => 'boolean',
        ]);

        $payment = PaymentBank::findOrFail($validated['payment_id']);
        $fromParts = explode('_', $validated['from']);
        $toParts = explode('_', $validated['to']);

        $payment->update([
            'from' => $fromParts[1],
            'from_type' => $fromParts[0],
            'to' => $toParts[1],
            'to_type' => $toParts[0],
            'amount' => $validated['amount'],
            'is_on_going' => $validated['is_on_going'] ?? false,
        ]);

        return response()->json(['status' => true, 'message' => 'Payment updated successfully']);
    }
}
