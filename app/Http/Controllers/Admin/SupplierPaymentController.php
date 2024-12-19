<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierPaymentController extends Controller
{
    public function __invoke(Request $request, string $id, DataService $dataService)
    {


        $dateFilter = $request->get('date_filter', 'lifetime');
        $site_id = $request->input('site_id', 'all');
        $supplier_id = $request->input('supplier_id', $id);

        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter, $site_id, $supplier_id);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

        $ledgers = $ledgers->sortByDesc(function ($d) {
                return $d['created_at'];
            });

        [$total_paid, $total_due, $total_balance] = $dataService->calculateBalances($ledgers);

        $perPage = 10;

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $sites = $paginatedLedgers->unique('site_id');

        return view("profile.partials.Admin.Ledgers.supplier-ledger",
            compact(
                'payments',
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'id',
                'total_balance',
                'sites',
            )
        );
    }



}
