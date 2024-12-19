<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Supplier;
use App\Services\DataService;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log as FacadesLog;

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

        FacadesLog::info($supplier_id);

        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter, $site_id, $supplier_id);

        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        [$total_paid, $total_due, $total_balance] = $dataService->calculateBalances($ledgers);

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



        return view("profile.partials.Admin.PaymentSuppliers.payments", compact(
            'paginatedLedgers',
            'total_paid',
            'total_due',
            'total_balance',
            'is_ongoing_count',
            'is_not_ongoing_count',
            'suppliers',
            'sites'
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
