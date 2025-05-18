<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SitePaymentController extends Controller
{


    public function __invoke(Request $request, string $id, DataService $dataService)
    {

        $dateFilter = $request->get('date_filter', 'lifetime');
        $site_id = $request->input('site_id', $id);
        $supplier_id = $request->input('supplier_id', 'all');
        $wager_id = $request->input('wager_id', 'all');

        $site = Site::find($id);

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours] = $dataService->getData($dateFilter, $site_id, $supplier_id, $wager_id);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours);

        $financialSummary  = $dataService->calculateAllBalances($ledgers);

        $effective_balance = $financialSummary['without_service_charge']['due'];
        $total_paid = $financialSummary['with_service_charge']['paid'];
        $total_due = $financialSummary['with_service_charge']['due'];
        $total_balance = $financialSummary['with_service_charge']['balance'];

        $perPage = 10;

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $suppliers = $paginatedLedgers->unique('supplier_id');

        return view(
            "profile.partials.Admin.Ledgers.site-ledger",
            compact(
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'total_balance',
                'site',
                'suppliers',
                'effective_balance'
            )
        );
    }
}
