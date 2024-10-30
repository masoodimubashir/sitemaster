<?php

namespace App\Http\Controllers\Admin;

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

class SupplierPaymentController extends Controller
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

        $ledgers = $ledgers->filter(fn($ledger) => $ledger['supplier_id'] == $id);

        $dateFilter = $request->get('date_filter', 'today');

        $now = Carbon::now();

        $ledgers = $this->filterLedgersByDate($ledgers, $dateFilter, $now);

        $ledgers = $ledgers->sortBy('created_at')->map(function ($ledger) {
            $ledger['created_at'] = Carbon::parse($ledger['created_at'])->format('d-M-Y H:i A');
            return $ledger;
        });

        $totals = $this->calculateBalances($ledgers);


        $totals = $this->calculateBalances($ledgers);
        $total_paid = $totals['total_paid'];
        $total_due = $totals['total_due'];
        $total_balance = $totals['total_balance'];

        $perPage = 10;
        $paginatedLedgers = new LengthAwarePaginator(
            $ledgers->forPage($request->input('page', 1), $perPage),
            $ledgers->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $is_ongoing_count = Site::where('is_on_going', 1)->count();
        $is_not_ongoing_count = Site::where('is_on_going', 0)->count();

        return view("profile.partials.Admin.Ledgers.supplier-ledger", compact('payments', 'paginatedLedgers', 'total_paid', 'total_due', 'id', 'total_balance', 'is_ongoing_count', 'is_not_ongoing_count'));

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

        // dd($ledgers);

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


        $total_balance = $total_amount_non_payments - $total_amount_payments;


        return [
            'total_paid' => $total_amount_payments,
            'total_due' => $total_amount_non_payments,
            'total_balance' => $total_balance
        ];
    }
}
