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

        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

        $service_charge = Site::find($id)->service_charge;

        $ledgers = $ledgers->filter(fn($ledger) => $ledger['site_id'] == $id)
            ->sortByDesc(function ($d) {
                return $d['created_at'];
            });

        [$total_paid, $total_due, $total_balance] = $dataService->calculateBalances($ledgers);

        $perPage = 10;

        $service_charge_amount = $dataService->getServiceChargeAmount($total_due, $service_charge);

        $total_due = $total_due + $service_charge_amount;
        $total_balance = $total_due - $total_paid;


        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view("profile.partials.Admin.Ledgers.site-ledger",
            compact(
                'payments',
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'id',
                'total_balance',
                'service_charge'
            )
        );
    }



}
