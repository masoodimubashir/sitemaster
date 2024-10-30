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

class UserLedgerController extends Controller
{
    public function __invoke(Request $request, string $id)
    {



        $payments = PaymentSupplier::with(['site', 'supplier'])->latest()->paginate(10);

        $raw_materials = ConstructionMaterialBilling::with(['phase.site', 'supplier'])->get();
        $sgft = SquareFootageBill::with(['phase.site', 'supplier'])->get();
        $expenses = DailyExpenses::with(['phase.site'])->get();
        $wagers = DailyWager::with(['phase.site', 'supplier', 'phase.wagerAttendances'])->get();

        $ledgers = collect();

        $ledgers = $ledgers->merge($payments->getCollection()->map(function ($pay) {
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
                'credit' => 'NA',
                'phase' => $material->phase->phase_name ?? 'N/A',
                'site' => $material->phase->site->site_name ?? 'N/A',
                'site_id' => $material->phase->site_id ?? null,
                'supplier_id' => $material->supplier_id ?? null,
                'created_at' => $material->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($sgft->map(function ($bill) {
            return [
                'supplier' => $bill->supplier->name ?? 'NA',
                'description' => $bill->wager_name ?? 'NA',
                'category' => 'Square Footage Bill',
                'debit' => $bill->price,
                'credit' => 'NA',
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
                'credit' => 'NA',
                'phase' => $expense->phase->phase_name ?? 'N/A',
                'site' => $expense->phase->site->site_name ?? 'N/A',
                'site_id' => $expense->phase->site_id ?? null,
                'supplier_id' => $expense->supplier_id ?? null,
                'created_at' => $expense->created_at,
            ];
        }));

        // Merge daily wagers into ledgers
        $ledgers = $ledgers->merge($wagers->map(function ($wager) {
            return [
                'supplier' => $wager->supplier->name ?? '',
                'description' => $wager->wager_name ?? 'NA',
                'category' => 'Daily Wager',
                'debit' => $wager->phase->wagerAttendances->sum('no_of_persons') * $wager->price_per_day,
                'credit' => 'NA',
                'phase' => $wager->phase->phase_name ?? 'N/A',
                'site' => $wager->phase->site->site_name ?? 'N/A',
                'site_id' => $wager->phase->site_id ?? null,
                'supplier_id' => $wager->supplier_id ?? null,
                'created_at' => $wager->created_at,
            ];
        }));


        // Filter ledgers by site ID if provided
        $ledgers = $ledgers->filter(fn($ledger) => $ledger['site_id'] == $id);


        $dateFilter = $request->get('date_filter', 'today');

        $now = Carbon::now();

        // Apply date filter
        $ledgers = $this->filterLedgersByDate($ledgers, $dateFilter, $now);

        // Sort ledgers and format created_at
        $ledgers = $ledgers->sortBy('created_at')->map(function ($ledger) {
            $ledger['created_at'] = Carbon::parse($ledger['created_at'])->format('d-M-Y H:i A');
            return $ledger;
        });

        [$total_balance, $total_debit, $total_credit, $currentBalance, $ledgers] = $this->calculateBalances($ledgers);

        $final_total_balance = $total_balance - $total_credit;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        // Paginate the ledgers
        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($currentPage, $perPage),
            $ledgers->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // Count ongoing and not ongoing sites
        $is_ongoing_count = Site::where('is_on_going', 1)->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();


        return view("profile.partials.Admin.Ledgers.site-ledger", compact('payments', 'paginatedLedgers', 'final_total_balance', 'total_debit', 'total_credit', 'is_ongoing_count', 'is_not_ongoing_count', 'id'));
    }
    private function filterLedgersByDate($ledgers, $dateFilter, $now)
    {
        switch ($dateFilter) {
            case 'yesterday':
                return $ledgers->filter(fn($ledger) => Carbon::parse($ledger['created_at'])->isYesterday());
            case 'last_week':
                return $ledgers->filter(fn($ledger) => Carbon::parse($ledger['created_at'])->isLastWeek());
            case 'last_month':
                return $ledgers->filter(fn($ledger) => Carbon::parse($ledger['created_at'])->isLastMonth());
            case 'last_year':
                return $ledgers->filter(fn($ledger) => Carbon::parse($ledger['created_at'])->isLastYear());
            case 'lifetime':
                return $ledgers;
            case 'today':
            default:
                return $ledgers->filter(fn($ledger) => Carbon::parse($ledger['created_at'])->isToday());
        }
    }

    private function calculateBalances($ledgers)
    {
        $total_balance = 0;
        $total_debit = 0;
        $total_credit = 0;
        $currentBalance = 0;

        $ledgers = $ledgers->map(function ($ledger) use (&$currentBalance, &$total_balance, &$total_debit, &$total_credit) {
            $debitAmount = $ledger['debit'] === 'NA' ? 0 : $ledger['debit'];
            $creditAmount = $ledger['credit'] === 'NA' ? 0 : $ledger['credit'];

            if ($debitAmount > 0) {
                $currentBalance += $debitAmount;
                $total_debit += $debitAmount;
            }

            if ($creditAmount > 0) {
                $currentBalance -= $creditAmount;
                $total_credit += $creditAmount;
            }

            $total_balance += $currentBalance;
            $ledger['balance'] = $currentBalance;

            return $ledger;
        });

        return [$total_balance, $total_debit, $total_credit, $currentBalance, $ledgers];
    }
}
