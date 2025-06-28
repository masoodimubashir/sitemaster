<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\Site;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentSiteController extends Controller
{
    public function showPayment(string $id)
    {

        $site = Site::find($id);

        $payments = $payments = $site->payments()
            ->with(['site', 'supplier'])
            ->where('verified_by_admin', 1)
            ->paginate(10);

        return view('profile.partials.Admin.PaymentSuppliers.site-payment-supplier', compact(
            'payments',
            'site'
        ));
    }

    public function makePayment(Request $request)
    {
        try {


            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'sometimes|mimes:png,jpg,webp, jpeg|max:1024',
                'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99',],
                'transaction_type' => 'nullable|in:0,1',
                'site_id' => 'nullable|exists:sites,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'payment_initiator' => 'required|in:0,1',
            ]);

            if ($validatedData->fails()) {
                return response()->json(
                    [
                        'errors' => $validatedData->errors(),
                    ],
                    422
                );
            }

            if ($request->filled('payment_initiator') && !$request->filled('supplier_id')) {


                AdminPayment::create([
                    'amount' => $request->input('amount'),
                    'transaction_type' => $request->input('transaction_type'),
                    'entity_id' => $request->input('site_id'),
                    'entity_type' => Site::class,
                ]);

                return response()->json(['message' => 'Payment To Admin']);
            }


            $path = null;

            if ($request->hasFile('screenshot')) {

                $image = $request->file('screenshot');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = $request->file('screenshot')->storeAs('Payment', $imageName, 'public');
            }

            $payment = new Payment();
            $payment->amount = $request->input('amount');
            $payment->site_id = $request->input('site_id');
            $payment->supplier_id = $request->input('supplier_id');
            $payment->transaction_type = $request->input('transaction_type');
            $payment->verified_by_admin = 1;
            $payment->payment_initiator = $request->filled('supplier_id') ? 1 : 0;
            $payment->screenshot = $path;
            $payment->save();

            return response()->json(['message' => 'Payment created successfully']);
        } catch (Exception $th) {

            return response()->json(['error' => 'Payment Cannot Be Made.. Try Again']);
        }
    }
}
