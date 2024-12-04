<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PendingPaymentsVerifications extends Controller
{


    public function index()
    {

        $payments = PaymentSupplier::where('verified_by_admin', 0)->with('site', 'supplier')->latest()->paginate(20);

        return view('profile.partials.Admin.PaymentBills.show-unverified_payments', compact('payments'));
    }
    public function verifyPayment(Request $request)
    {

        $validated = Validator::make($request->all(),[
            'id' => 'required|exists:payments,id',
        ]);

        try {
            $record = PaymentSupplier::findOrFail($request->id);

            $record->update([
                'verified_by_admin' => $request->verified,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $request->verified
                    ? 'Record verified successfully'
                    : 'Record unverified successfully',
                'new_status' => $request->verified
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to update verification status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
