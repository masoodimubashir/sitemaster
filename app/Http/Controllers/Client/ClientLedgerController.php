<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
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
        $phase_id = $request->input('phase_id', 'all');
        $wager_id = $request->input('wager_id', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Call the service or method
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id,
        );

        // Create ledger data including wasta and labours
        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wastas,
            $labours
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        // Calculate balances including wasta and labours
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];
        $returns = $withoutServiceCharge['return'];

        $perPage = $request->get('per_page', 20);

        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage(
                $request->input('page', 1),
                $perPage
            ),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1), // Changed from 10 to 1 for default page
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $suppliers = Supplier::where([
            'deleted_at' => null,
        ])->orderBy('name', 'asc')->get();

        $sites = Site::where([
            'deleted_at' => null,
            'client_id' => Auth::user()->id
        ])->orderBy('site_name', 'asc')->get();


        // Get phases belonging to sites of current user
        $phases = Phase::with('site') // Eager load site relationship
            ->whereNull('deleted_at')
            ->whereHas('site', function ($query) {
                $query->whereNull('deleted_at')
                    ->where('client_id', Auth::id());
            })
            ->orderBy('phase_name', 'asc')
            ->get();

        // Return view with all necessary data
        return view('profile.partials.Admin.Ledgers.client-ledger', compact(
            'paginatedLedgers',
            'total_paid',
            'total_due',
            'total_balance',
            'suppliers',
            'sites',
            'effective_balance',
            'phases',
            'returns'
        ));


    }
}
