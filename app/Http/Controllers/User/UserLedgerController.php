<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\SquareFootageBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class UserLedgerController extends Controller
{
    public function __invoke(Request $request, $id)
    {


        $dateFilter = $request->date_filter;

        // Base query for ongoing sites
        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');
        $is_ongoing_count = $ongoingSites->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        $site_service_charge = Site::find($id)->service_charge;

        $dateRange = $this->getDateRange($dateFilter);


        $ledgers = $ledgers->merge($payments->map(function ($pay) {
            return [
                'supplier' => $pay->supplier->name ?? '',
                'description' => $pay->item_name ?? 'NA',
                'category' => 'Payments',
                'debit' => 'NA',
                'credit' => $pay->amount,
                'phase' => $pay->phase->phase_name ?? 'N/A',
                'site' => $pay->phase->site->site_name ?? 'N/A',
                'site_id' => $pay->site_id ?? null,
                'supplier_id' => $pay->supplier_id ?? null,
                'created_at' => $pay->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($raw_materials->map(function ($material) {
            return [
                'supplier' => $material->supplier->name ?? 'NA',
                'description' => $material->item_name ?? 'NA',
                'category' => 'Raw Material',
                'debit' => $material->amount,
                'credit' => 0,
                'phase' => $material->phase->phase_name ?? 'N/A',
                'site' => $material->phase->site->site_name ?? 'N/A',
                'site_id' => $material->phase->site_id ?? null,
                'supplier_id' => $material->supplier_id ?? null,
                'created_at' => $material->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($squareFootageBills->map(function ($bill) {
            return [
                'supplier' => $bill->supplier->name ?? 'NA',
                'description' => $bill->wager_name ?? 'NA',
                'category' => 'Square Footage Bill',
                'debit' => $bill->price * $bill->multiplier,
                'credit' => 0,
                'phase' => $bill->phase->phase_name ?? 'N/A',
                'site' => $bill->phase->site->site_name ?? 'N/A',
                'site_id' => $bill->phase->site_id ?? null,
                'supplier_id' => $bill->supplier_id ?? null,
                'created_at' => $bill->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($expenses->map(function ($expense) {
            return [
                'supplier' => $expense->supplier->name ?? '',
                'description' => $expense->item_name ?? 'NA',
                'category' => 'Daily Expense',
                'debit' => $expense->price,
                'credit' => 0,
                'phase' => $expense->phase->phase_name ?? 'N/A',
                'site' => $expense->phase->site->site_name ?? 'N/A',
                'site_id' => $expense->phase->site_id ?? null,
                'supplier_id' => $expense->supplier_id ?? null,
                'created_at' => $expense->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($wagers->map(function ($wager) {
            return [
                'supplier' => $wager->supplier->name ?? '',
                'description' => $wager->wager_name ?? 'NA',
                'category' => 'Daily Wager',
                'debit' => $wager->wager_total,
                'credit' => 0,
                'phase' => $wager->phase->phase_name ?? 'N/A',
                'site' => $wager->phase->site->site_name ?? 'N/A',
                'site_id' => $wager->phase->site_id ?? null,
                'supplier_id' => $wager->supplier_id ?? null,
                'created_at' => $wager->created_at,
            ];
        }));

        $ledgers = $ledgers->filter(fn($ledger) => $ledger['site_id'] == $id);

        $totals = $this->calculateBalances($ledgers);

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
                'payments',
                'paginatedLedgers',
                'total_paid',
                'total_due',
                'id',
                'total_balance',
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
