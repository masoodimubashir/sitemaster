<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PendingPaymentsVerifications extends Controller
{



    public function index(Request $request)
    {
        $payments = Payment::with(['site', 'supplier', 'site.phases'])
            ->when($request->filled('site_id'), function ($query) use ($request) {
                return $query->where('site_id', $request->site_id);
            })
            ->when($request->filled('phase_id'), function ($query) use ($request) {
                return $query->whereHas('site.phases', function ($q) use ($request) {
                    $q->where('phases.id', $request->phase_id);
                });
            })
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('date_from') && $request->filled('date_to'), function ($query) use ($request) {
                return $query->whereBetween('created_at', [
                    Carbon::parse($request->date_from)->startOfDay(),
                    Carbon::parse($request->date_to)->endOfDay()
                ]);
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        $sites = Site::with('phases')->get();
        $suppliers = Supplier::all();

        return view('profile.partials.Admin.PaymentBills.show-unverified_payments', [
            'payments' => $payments,
            'sites' => $sites,
            'suppliers' => $suppliers,
            'filters' => $request->only(['site_id', 'phase_id', 'supplier_id', 'date_from', 'date_to'])
        ]);
    }

    public function verifyPayment(Request $request)
    {

        try {
            $record = Payment::findOrFail($request->id);

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

    // In your PendingPaymentsVerifications controller

    public function edit($id)
    {
        $payment = Payment::findOrFail($id);
        $suppliers = Supplier::all();
        $sites = Site::all();

        return view('profile.partials.admin.paymentBills.edit', compact('payment', 'suppliers', 'sites'));
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric',
            'supplier_id' => 'required|exists:suppliers,id',
            'site_id' => 'required|exists:sites,id',
            'screenshot' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'notes' => 'nullable|string',
        ]);


        // Handle file upload
        if ($request->hasFile('screenshot')) {
            // Delete old file if exists
            if ($payment->screenshot) {
                Storage::disk('public')->delete($payment->screenshot);
            }
            $validated['screenshot'] = $request->file('screenshot')->store('Payment', 'public');
        } elseif ($request->remove_screenshot) {
            // Remove existing screenshot if checkbox is checked
            if ($payment->screenshot) {
                Storage::disk('public')->delete($payment->screenshot);
            }
            $validated['screenshot'] = null;
        } else {
            // Keep existing screenshot
            unset($validated['screenshot']);
        }

        $payment->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        // Delete associated screenshot
        if ($payment->screenshot) {
            Storage::disk('public')->delete($payment->screenshot);
        }

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }


    public function uploadScreenshot(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $payment = Payment::findOrFail($request->payment_id);


        // Delete old screenshot if exists
        if ($payment->screenshot) {
            Storage::disk('public')->delete($payment->screenshot);
        }

        // Store new screenshot
        $path = $request->file('screenshot')->store('Payment', 'public');
        $payment->update(['screenshot' => $path]);

        return response()->json([
            'status' => 'success',
            'message' => 'Screenshot uploaded successfully'
        ]);
    }


}
