<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Services\DataService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PaymentsController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataService $dataService)
    {


        $dateFilter = $request->input('date_filter', 'today');
        $site_id = $request->input('site_id', 'all');
        $supplier_id = $request->input('supplier_id', 'all');
        $phase_id = $request->input('phase_id', 'all');
        $wager_id = $request->input('wager_id', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Call the service or method
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id,
        );

        // Create ledger data including wasta and labours
        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wastas,
            $labours
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        // Calculate balances including wasta and labours
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];
        $returns = $withoutServiceCharge['return'];


        $perPage = $request->get('per_page', 20);

        $site = Site::query();
        $is_ongoing_count = $site->where('is_on_going', 1)->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage(
                $request->input('page', 1),
                $perPage
            ),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1), // Changed from 10 to 1 for default page
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $suppliers = Supplier::where([
            'deleted_at' => null,
        ])->orderBy('name', 'asc')->get();

        $sites = Site::where([
            'deleted_at' => null,
        ])->orderBy('site_name', 'asc')->get();

        $phases = Phase::with('site')->where([
            'deleted_at' => null,
        ])->get();


        return view("profile.partials.Admin.PaymentSuppliers.payments", compact(
            'paginatedLedgers',
            'total_paid',
            'total_due',
            'total_balance',
            'is_ongoing_count',
            'is_not_ongoing_count',
            'suppliers',
            'sites',
            'effective_balance',
            'phases',
            'returns'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {

    //     try {

    //         DB::beginTransaction();

    //         $validatedData = Validator::make($request->all(), [
    //             'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //             'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
    //             'transaction_type' => 'nullable|in:0,1',
    //             'entity_type' => 'required|in:site,supplier',
    //             'entity_id' => 'required|integer',
    //             'payment_id' => 'required|integer',
    //         ]);

    //         if ($validatedData->fails()) {
    //             return response()->json([
    //                 'errors' => $validatedData->errors()
    //             ], 422);
    //         }

    //         $adminPayment = AdminPayment::find($request->input('payment_id'));

    //         if (!$adminPayment) {
    //             return response()->json([
    //                 'error' => 'Admin payment entry not found.',
    //             ], 404);
    //         }

    //         if ($adminPayment->amount < $request->input('amount')) {
    //             return response()->json([
    //                 'error' => 'Insufficient balance in admin payment.',
    //             ], 422);
    //         }

    //         $transactionType = $request->input('transaction_type');
    //         $entityType = $request->input('entity_type');
    //         $entityId = $request->input('entity_id');

    //         // Determine site_id and supplier_id based on logic
    //         $siteId = null;
    //         $supplierId = null;

    //         if (is_null($transactionType)) {

    //             if ($entityType === 'site') {
    //                 $siteId = $entityId;
    //                 $supplierId = $adminPayment->supplier_id;
    //             } elseif ($entityType === 'supplier') {
    //                 $supplierId = $entityId;
    //                 $siteId = $adminPayment->site_id;
    //             }

    //             if (!$siteId || !$supplierId) {
    //                 return response()->json([
    //                     'error' => 'Both site and supplier must be specified for internal transfers.',
    //                 ], 422);
    //             }

    //         } else {
    //             // For normal sent/received transactions
    //             if ($entityType === 'site') {
    //                 $siteId = $entityId;
    //             } elseif ($entityType === 'supplier') {
    //                 $supplierId = $entityId;
    //             }
    //         }

    //         $payment = Payment::create([
    //             'amount' => $request->input('amount'),
    //             'transaction_type' => $transactionType,
    //             'site_id' => $siteId,
    //             'supplier_id' => $supplierId,
    //             'verified_by_admin' => 1,
    //             'payment_initiator' => 1, // Always admin for now
    //         ]);

    //         if (!$payment) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'error' => 'Payment could not be created. Please try again.',
    //             ], 500);
    //         }

    //         if ($adminPayment->amount == $request->input('amount')) {
    //             $adminPayment->delete();
    //         } else {
    //             $adminPayment->update([
    //                 'amount' => $adminPayment->amount - $request->input('amount'),
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Payment created successfully.',
    //         ], 201);

    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         Log::error("Payment creation failed: " . $e->getMessage());

    //         return response()->json([
    //             'error' => 'Payment cannot be made. Please try again later.',
    //         ], 500);
    //     }

    // }




    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'nullable|image|mimes:jpeg,jpg|max:2048',
                'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'transaction_type' => 'nullable|in:0,1',
                'entity_type' => 'required|in:site,supplier',
                'entity_id' => 'required|integer',
                'payment_id' => 'required|integer',
            ]);

            if ($validatedData->fails()) {
                return response()->json([
                    'errors' => $validatedData->errors()
                ], 422);
            }

            $adminPayment = AdminPayment::find($request->input('payment_id'));

            if (!$adminPayment) {
                return response()->json([
                    'error' => 'Admin payment entry not found.',
                ], 404);
            }

            if ($adminPayment->amount < $request->input('amount')) {
                return response()->json([
                    'error' => 'Insufficient balance in admin payment.',
                ], 422);
            }

            $transactionType = $request->input('transaction_type');
            $entityType = $request->input('entity_type');
            $entityId = $request->input('entity_id');

            // Determine site_id and supplier_id based on logic
            $siteId = null;
            $supplierId = null;

            if (is_null($transactionType)) {
                if ($entityType === 'site') {
                    $siteId = $entityId;
                    $supplierId = $adminPayment->supplier_id;
                } elseif ($entityType === 'supplier') {
                    $supplierId = $entityId;
                    $siteId = $adminPayment->site_id;
                }

                if (!$siteId || !$supplierId) {
                    return response()->json([
                        'error' => 'Both site and supplier must be specified for internal transfers.',
                    ], 422);
                }
            } else {
                // For normal sent/received transactions
                if ($entityType === 'site') {
                    $siteId = $entityId;
                } elseif ($entityType === 'supplier') {
                    $supplierId = $entityId;
                }
            }

            // Handle screenshot logic
            $screenshotPath = null;
            $oldScreenshotPath = $adminPayment->screenshot;

            if ($request->hasFile('screenshot')) {
                // New screenshot uploaded
                $screenshotPath = $request->file('screenshot')->store('payment_screenshots', 'public');

                // Delete old screenshot if exists
                if ($oldScreenshotPath && Storage::disk('public')->exists($oldScreenshotPath)) {
                    Storage::disk('public')->delete($oldScreenshotPath);
                }
            } elseif ($oldScreenshotPath) {
                // No new screenshot but existing one in admin payment
                $screenshotPath = $oldScreenshotPath;
            }
            // Else both are null, so $screenshotPath remains null

            $payment = Payment::create([
                'amount' => $request->input('amount'),
                'transaction_type' => $transactionType,
                'site_id' => $siteId,
                'supplier_id' => $supplierId,
                'verified_by_admin' => 1,
                'payment_initiator' => 0, // Always admin for now
                'screenshot' => $screenshotPath, // Set the screenshot path
            ]);

            if (!$payment) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Payment could not be created. Please try again.',
                ], 500);
            }

            // Update admin payment with new screenshot if it was uploaded
            if ($request->hasFile('screenshot')) {
                $adminPayment->update(['screenshot' => $screenshotPath]);
            }

            if ($adminPayment->amount == $request->input('amount')) {
                $adminPayment->delete();
            } else {
                $adminPayment->update([
                    'amount' => $adminPayment->amount - $request->input('amount'),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment created successfully.',
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Payment creation failed: " . $e->getMessage());

            // Clean up if screenshot was uploaded but transaction failed
            if (isset($screenshotPath) && $screenshotPath && Storage::disk('public')->exists($screenshotPath)) {
                Storage::disk('public')->delete($screenshotPath);
            }

            return response()->json([
                'error' => 'Payment cannot be made. Please try again later.',
            ], 500);
        }
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $sites = Site::latest()->get();

        // $suppliers = Supplier::latest()->get();

        // return view('profile.partials.Admin.PaymentSuppliers.create-payment', compact('sites', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), ['entity_id' => 'required', 'entity_type' => 'required', 'transaction_type' => 'required|in:0,1', 'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99',]]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => 'Form Fields Are Missing.', 'errors' => $validator->errors(),], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated_data = $validator->validated();

            $admin_payment = AdminPayment::find($id);
            if (!$admin_payment) {
                return response()->json(['status' => false, 'message' => 'Payment not found.',], Response::HTTP_NOT_FOUND);
            }

            // Map Payment Data
            $entity_id = $validated_data['entity_id'];
            $entity_type = $validated_data['entity_type'];

            $payment_data = ['screenshot' => null, 'site_id' => $entity_type === Site::class ? $entity_id : null, 'supplier_id' => $entity_type === Supplier::class ? $entity_id : null, 'verified_by_admin' => 1, 'amount' => $admin_payment->amount, 'transaction_type' => $validated_data['transaction_type'], 'payment_initiator' => $entity_type === Site::class || Supplier::class ? 1 : 0,];

            $admin_payment->update(['amount' => $payment_data['amount']]);

            $payment = Payment::create($payment_data);
            if (!$payment) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Payment could not be created.',], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Payment created successfully!',], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage(),], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


}
