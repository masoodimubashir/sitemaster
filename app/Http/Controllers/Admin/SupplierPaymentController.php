<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierPaymentController extends Controller
{
    public function __invoke(Request $request, string $id, DataService $dataService)
    {

        //  Get The Data From The Request 
        $dateFilter = $request->get('date_filter', 'today');
        $site_id = $request->input('site_id', 'all');
        $supplier_id = $request->input('supplier_id', $id);
        $wager_id = $request->input('wager_id', 'all');

        // Get ongoing sites is ongoing count  and is not ongoing count
        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        // Get All the data on the arguments given
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours] = $dataService->getData($dateFilter, $site_id, $supplier_id, $wager_id);

        // Prepare the data for the view
        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours);

        // Sort the data by created_at
        $ledgers = $ledgers->sortByDesc(function ($d) {
            return $d['created_at'];
        })->whereNotNull('supplier_id');


        //  Calculate the balances
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];

        // Paginate the data
        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), 10),
            $ledgers->count(),
            10,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $sites = $paginatedLedgers->filter(fn($site) => $site['site_id'] !== '--')->unique('site');

        return view(
            "profile.partials.Admin.Ledgers.supplier-ledger",
            compact(
                'payments',
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'id',
                'total_balance',
                'sites',
                'effective_balance'

            )

        );
    }
}
