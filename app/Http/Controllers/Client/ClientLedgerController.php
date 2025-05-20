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
        // Filter parameters
        $dateFilter = $request->input('date_filter', 'today');
        $site_id = $request->input('site_id', 'all');
        $supplier_id = $request->input('supplier_id', 'all');
        $wager_id = $request->input('wager_id', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');


        // Site statistics
        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        // Fetch data including wasta and labours
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $wager_id,
            $startDate,
            $endDate
        );

        // Filter by sites assigned to authenticated user
        $user_site_ids = Site::where('client_id', Auth::user()->id)->pluck('id')->toArray();

        // Create ledger data including wasta and labours
        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers,
            $wastas,
            $labours
        )
            ->filter(function ($item) use ($user_site_ids) {
                return in_array($item['site_id'], $user_site_ids);
            })
            ->sortByDesc(function ($d) {
                return $d['created_at'];
            });

        // Calculate balances including wasta and labours
        $balances = $dataService->calculateAllBalances($ledgers);

        // Access the values
        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];

        // Get specific totals
        $effective_balance = $withoutServiceCharge['due']; // Changed from 'balance' to 'due' as per first snippet
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];

        // Pagination settings
        $perPage = $request->get('per_page', 20);

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1), // Changed from 10 to 1 for default page
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Extract unique entities
        $suppliers = $paginatedLedgers->filter(fn($supplier) => $supplier['supplier_id'] !== '--')->unique('supplier_id');
        $sites = $paginatedLedgers->filter(fn($site) => $site['site_id'] !== '--')->unique('site_id');

        // Process wagers data
        $wagers = $paginatedLedgers->map(function ($ledger) {
            $ledger['wager_id'] = isset($ledger['wager_id']) ? $ledger['wager_id'] : null;
            return $ledger;
        })
            ->filter(function ($ledger) {
                return !is_null($ledger['wager_id']);
            })
            ->unique('wager_id');

        // Return view with all necessary data
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
