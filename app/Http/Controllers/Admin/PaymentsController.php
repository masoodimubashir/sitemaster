<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentsController extends Controller
{

    // public function __construct(DataService $dataService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataService $dataService)
    {

        $dateFilter = $request->get('date_filter', 'today');

        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

        $ledgers = $ledgers->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        [$total_paid, $total_due, $total_balance] = $dataService->calculateBalances($ledgers);

        $perPage = $request->get('per_page', 10);

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 10),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view("profile.partials.Admin.PaymentSuppliers.payments", compact(
            'paginatedLedgers',
            'total_paid',
            'total_due',
            'total_balance',
            'is_ongoing_count',
            'is_not_ongoing_count'
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
        //
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
