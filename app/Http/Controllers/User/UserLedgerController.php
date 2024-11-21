<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class UserLedgerController extends Controller
{
    public function __invoke(Request $request, $id, DataService $dataService)
    {

        $dateFilter = $request->get('date_filter', 'today');

        $site = Site::find($id);

        Log::info($site);

        $ongoingSites = $site->where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = $site->where('is_on_going', 0)->count();

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

        $ledgers = $ledgers->filter(fn($ledger) => $ledger['site_id'] == $site->id)
            ->sortByDesc(function ($d) {
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

        return view("profile.partials.Admin.Ledgers.site-ledger",
            compact(
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'total_balance',
                'is_ongoing_count',
                'is_not_ongoing_count',
                'site',
            )
        );
    }
    private function getDateRange($dateFilter)
    {
        $now = Carbon::now();

        switch ($dateFilter) {

            case 'today':
                return [
                    $now->copy()->startOfDay()->toDateTimeString(),  // Ensure proper format for SQL query
                    $now->copy()->endOfDay()->toDateTimeString()
                ];

            case 'yesterday':
                return [
                    $now->copy()->subDay()->startOfDay()->toDateTimeString(),
                    $now->copy()->subDay()->endOfDay()->toDateTimeString()
                ];

            case 'this_week':
                return [
                    $now->copy()->startOfWeek()->toDateTimeString(),
                    $now->copy()->endOfWeek()->toDateTimeString()
                ];

            case 'this_month':
                return [
                    $now->copy()->startOfMonth()->toDateTimeString(),
                    $now->copy()->endOfMonth()->toDateTimeString()
                ];

            case 'this_year':
                return [
                    $now->copy()->startOfYear()->toDateTimeString(),
                    $now->copy()->endOfYear()->toDateTimeString()
                ];

            case 'lifetime':
                // return null;

            default:
                return [
                    $now->copy()->startOfDay()->toDateTimeString(),
                    $now->copy()->endOfDay()->toDateTimeString()
                ];
        }
    }

    private function calculateBalances($ledgers, $site_service_charge = 0)
    {

        $total_amount_payments = 0;
        $total_amount_non_payments = 0;
        $total_balance = 0;

        foreach ($ledgers as $item) {
            switch ($item['category']) {
                case 'Payments':
                    $total_amount_payments += is_string($item['credit']) ? floatval($item['credit']) : $item['credit'];
                    break;
                case 'Raw Material':
                case 'Square Footage Bill':
                case 'Daily Expense':
                case 'Daily Wager':
                    $total_amount_non_payments += is_string($item['debit']) ? floatval($item['debit']) : $item['debit'];
                    break;
            }
        }

        $total_amount_with_service_charge = ($site_service_charge / 100) * $total_amount_non_payments  + $total_amount_non_payments;

        $total_balance = $total_amount_with_service_charge - $total_amount_payments;

        return [
            'total_paid' => $total_amount_payments,
            'total_due' => $total_amount_non_payments,
            'total_balance' => $total_balance
        ];
    }
}
