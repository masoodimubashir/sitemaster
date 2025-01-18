<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Supplier;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class PaymentsController extends Controller
{

    // public function __construct(DataService $dataService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataService $dataService)
    {


        $dateFilter = $request->input('date_filter', 'today');
        $site_id = $request->input('site_id', 'all');
        $supplier_id = $request->input('supplier_id', 'all');
        $wager_id = $request->input('wager_id', 'all');


        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();


        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter, $site_id, $supplier_id, $wager_id);




        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });



        // [$total_paid, $total_due, $total_balance] = $dataService->calculateBalances($ledgers);

        $balances = $dataService->calculateAllBalances($ledgers);

        // Access the values
        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];

        // Get specific totals
        $effective_balance = $withoutServiceCharge['balance'];

        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];


        $perPage = $request->get('per_page', 20);


        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 10),
            ['path' => $request->url(), 'query' => $request->query()]
        );



        $suppliers = $paginatedLedgers->unique('supplier_id');
        $sites = $paginatedLedgers->unique('site_id');


        $wagers = $paginatedLedgers->map(function ($ledger) {
            $ledger['wager_id'] = isset($ledger['wager_id']) ? $ledger['wager_id'] : null;
            return $ledger;
        })
            ->filter(function ($ledger) {
                return !is_null($ledger['wager_id']);
            })
            ->unique('wager_id');



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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $sites = Site::latest()->get();

        // $suppliers = Supplier::latest()->get();

        // return view('profile.partials.Admin.PaymentSuppliers.create-payment', compact('sites', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        try {


            $validatedData = Validator::make($request->all(), [
                'screenshot' => 'required|mimes:png,jpg,webp, jpeg|max:1024',
                'supplier_id' => 'required|exists:suppliers,id',
                'site_id' => 'required|exists:sites,id',
                'amount' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ]
            ]);

            if ($validatedData->fails()) {
                return response()->json(['errors' =>  'Forms Fields Are Missing..'], 422);
            }

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
