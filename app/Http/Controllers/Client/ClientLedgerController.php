<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ClientLedgerController extends Controller
{
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

        $user_site_ids = Site::where('client_id', Auth::user()->id)->pluck('id')->toArray();

        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers
        )
            ->filter(function ($item) use ($user_site_ids) {
                return in_array($item['site_id'], $user_site_ids);
            })
            ->sortByDesc(function ($d) {
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



        return view('profile.partials.Admin.Ledgers.client-ledger', compact(
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
}
