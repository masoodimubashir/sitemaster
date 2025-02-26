<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\Site;
use App\Models\Supplier;
use App\Services\DataService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PaymentsController extends Controller
{

    // public function __construct(DataService $dataService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataService $dataService)
    {


        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($request);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers)
            ->sortByDesc(function ($d) {
                return $d['created_at'];
            });

        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];

        $perPage = $request->get('per_page', 20);

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage(
                $request->input('page', 1),
                $perPage
            ),
            $ledgers->count(),
            $perPage,
            $request->input('page', 10),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $suppliers = $paginatedLedgers->unique('supplier_id');
        $sites = $paginatedLedgers->unique('site');

        $wagers = $paginatedLedgers->map(function ($ledger) {
            $ledger['wager_id'] = isset($ledger['wager_id']) ? $ledger['wager_id'] : null;
            return $ledger;
        })->filter(function ($ledger) {
            return !is_null($ledger['wager_id']);
        })->unique('wager_id');

        return view("profile.partials.Admin.PaymentSuppliers.payments", compact(
            'paginatedLedgers',
            'total_paid',
            'total_due',
            'total_balance',
            'is_ongoing_count',
            'is_not_ongoing_count',
            'suppliers',
            'sites',
            'wagers',
            'effective_balance'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            DB::beginTransaction();

            $validatedData = Validator::make($request->all(), [
                'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'transaction_type' => 'required|in:0,1',
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


            $payment = Payment::create([
                'amount' => $request->input('amount'),
                'transaction_type' => $request->input('transaction_type'),
                'site_id' => $request->input('entity_type') === 'site' ? $request->input('entity_id') : null,
                'supplier_id' => $request->input('entity_type') === 'supplier' ? $request->input('entity_id') : null,
                'verified_by_admin' => 1,
                'payment_initiator' => $request->filled('supplier_id') || $request->filled('site_id') ? 1 : 0,
            ]);


            if (!$payment) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Payment could not be created. Please try again.',
                ], 500);
            }

            if ($adminPayment->amount === $request->input('amount')) {
                $adminPayment->delete();
            } else {
                $adminPayment->update([
                    'amount' => $adminPayment->amount - $request->input('amount'),
                ]);
            }


            DB::commit();

            return response()->json([
                'message' => 'Payment created successfully.',
            ], 201); // Return 201 Created

        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Payment creation failed: " . $e->getMessage());

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
