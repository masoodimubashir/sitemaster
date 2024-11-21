<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SitePaymentController extends Controller
{


    public function __invoke(Request $request, string $id, DataService $dataService)
    {

        $dateFilter = $request->get('date_filter', 'today');

        $site = Site::find($id);

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

        $ledgers = $ledgers->filter(fn($ledger) => $ledger['site_id'] == $site->id)
            ->sortByDesc(function ($d) {
                return $d['created_at'];
            });

        [$total_paid, $total_due, $total_balance] = $dataService->calculateBalancesWithServiceCharge($ledgers);

        $perPage = 10;

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view("profile.partials.Admin.Ledgers.site-ledger",
            compact(
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'total_balance',
                'site'
            )
        );
    }



}
