<?php

namespace App\Services;

use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\Payment;
use App\Models\SquareFootageBill;


class DataService
{


    public function __construct() {}

    public function getData($request)
    {

        $dateFilter = $request->input('date_filter', 'today');
        $site_id = $request->input('site_id', 'all');
        $supplier_id = $request->input('supplier_id', 'all');
        $wager_id = $request->input('wager_id', 'all');

        $dateRange = $this->filterByDate($dateFilter);

        $supplier_id = $this->getSupplierIdFromWager($wager_id, $supplier_id);

        $payments = $this->getPayments(
            $dateFilter,
            $dateRange,
            $site_id,
            $supplier_id
        );

        $raw_materials = $this->getRawMaterials(
            $dateFilter,
            $dateRange,
            $site_id,
            $supplier_id
        );

        $squareFootageBills = $this->getSquareFootageBills(
            $dateFilter,
            $dateRange,
            $site_id,
            $supplier_id
        );

        $expenses = $this->getExpenses(
            $dateFilter,
            $dateRange,
            $site_id
        );

        $wagers = $this->getWagers(
            $dateFilter,
            $dateRange,
            $site_id,
            $supplier_id,
            $wager_id
        );

        return [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers];
    }

    public function makeData($payments = null, $raw_materials = null, $squareFootageBills = null, $expenses = null, $wagers = null)
    {

        $ledgers = collect();

        $ledgers = $ledgers->merge($payments->map(function ($pay) {

            return [
                'description' => "Payment",
                'category' => 'Payment',
                'credit' => $pay->transaction_type === 0 ? $pay->amount : 0,
                'debit' => $pay->supplier && $pay->site_id ? $pay->amount : (($pay->transaction_type == 1) ? $pay->amount : 0),
                'transaction_type' => $pay->supplier_id && $pay->site_id ? '--' : (($pay->transaction_type === 0) ? 'Sent By Firm' : 'Received By Firm'),
                'payment_initiator' => !empty($pay->site_id) && empty($pay->supplier_id) ? 'Site' : (!empty($pay->supplier_id) ? 'Supplier' : 'Admin'),
                'site' => $pay->site->site_name ?? '--',
                'supplier' => $pay->supplier->name ?? '--',
                'supplier_id' => $pay->supplier_id ?? '--',
                'site_id' => $pay->site_id ?? '--',
                'phase' => $pay->phase->phase_name ?? '--',
                'created_at' => $pay->created_at,
            ];
        }));


        $ledgers = $ledgers->merge($raw_materials->map(function ($material) {

            $service_charge = $this->getServiceChargeAmount($material->amount, $material->phase->site->service_charge);

            return [
                'description' => $material->item_name ?? 'Raw Material',
                'category' => 'Raw Material',
                'credit' => 0,
                'debit' => $material->amount,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'site' => $material->phase->site->site_name ?? '--',
                'total_amount_with_service_charge' => $service_charge + $material->amount,
                'supplier' => $material->supplier->name ?? '--',
                'supplier_id' => $pay->supplier_id ?? '--',
                'site_id' => $pay->site_id ?? '--',
                'phase' => $material->phase->phase_name ?? '--',
                'created_at' => $material->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($squareFootageBills->map(function ($bill) {

            $amount = $bill->price * $bill->multiplier;

            $service_charge = $this->getServiceChargeAmount($amount, $bill->phase->site->service_charge);

            return [
                'description' => 'Square Footage Work',
                'category' => 'Square Footage Bill',
                'debit' => $amount,
                'credit' => 0,
                'total_amount_with_service_charge' => $service_charge + $amount,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'site' => $bill->phase->site->site_name ?? '--',
                'supplier' => $bill->supplier->name ?? '--',
                'supplier_id' => $pay->supplier_id ?? '--',
                'site_id' => $pay->site_id ?? '--',
                'phase' => $bill->phase->phase_name ?? '--',
                'created_at' => $bill->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($expenses->map(function ($expense) {

            $service_charge = $this->getServiceChargeAmount($expense->price, $expense->phase->site->service_charge);

            return [
                'description' => $expense->item_name ?? 'Daily Expense',
                'category' => 'Daily Expense',
                'credit' => 0,
                'debit' => $expense->price,
                'total_amount_with_service_charge' => $service_charge + $expense->price,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $expense->phase->site->site_name ?? '--',
                'supplier' => $expense->supplier->name ?? '--',
                'supplier_id' => $pay->supplier_id ?? '--',
                'site_id' => $pay->site_id ?? '--',
                'phase' => $expense->phase->phase_name ?? '--',
                'created_at' => $expense->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($wagers->map(function ($wager) {

            $service_charge = $this->getServiceChargeAmount($wager->wager_total, $wager->phase->site->service_charge);

            return [
                'description' => $wager->wager_name ?? 'Daily Wager',
                'category' => 'Daily Wager',
                'credit' => 0,
                'debit' => $wager->wager_total,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'total_amount_with_service_charge' => $service_charge + $wager->wager_total,
                'site' => $wager->phase->site->site_name ?? '--',
                'supplier' => $wager->supplier->name ?? '--',
                'supplier_id' => $wager->supplier_id ?? '--',
                'site_id' => $wager->phase->site_id ?? '--',
                'phase' => $wager->phase->phase_name ?? '--',
                'created_at' => $wager->created_at,
            ];
        }));

        return $ledgers;
    }



    public function filterByDate($dateFilter)
    {

        return match ($dateFilter) {
            'yesterday' => [now()->yesterday()->startOfDay(), now()->yesterday()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'lifetime' => null,
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function getSupplierIdFromWager($wager_id, $supplier_id)
    {
        if ($wager_id && $wager_id !== 'all') {
            $wager = DailyWager::find($wager_id);
            return $wager?->supplier_id ?? $supplier_id;
        }
        return $supplier_id;
    }

    private function getPayments($dateFilter, $dateRange, $site_id, $supplier_id)
    {

        return Payment::query()
            ->with(['site', 'supplier'])
            ->where('verified_by_admin', 1)
            ->when($this->isValidSite($site_id), fn($q) => $q->where('site_id', $site_id))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->get();
    }



    private function getRawMaterials($dateFilter, $dateRange, $site_id, $supplier_id)
    {
        return ConstructionMaterialBilling::with(['phase.site', 'supplier'])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', function ($site) {
                $site->where([
                    'deleted_at' => null,
                    'is_on_going' => 1
                ]);
            })
            ->where('verified_by_admin', 1)
            ->when($dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($site_id !== 'all', fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($supplier_id && $supplier_id != 'all', fn($q) => $q->where('supplier_id', $supplier_id))
            ->latest()
            ->get();
    }

    private function getSquareFootageBills($dateFilter, $dateRange, $site_id, $supplier_id)
    {
        return SquareFootageBill::with([
            'phase' => fn($phase) => $phase->with(['site' => fn($site) => $site->withoutTrashed()])->withoutTrashed(),
            'supplier' => fn($supplier) => $supplier->withoutTrashed(),
        ])->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', function ($site) {
                $site->where([
                    'deleted_at' => null,
                    'is_on_going' => 1
                ]);
            })
            ->where('verified_by_admin', 1)
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->latest()
            ->get();
    }

    private function getExpenses($dateFilter, $dateRange, $site_id)
    {
        return DailyExpenses::with(['phase.site' => fn($site) => $site->withoutTrashed()])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', function ($site) {
                $site->where([
                    'deleted_at' => null,
                    'is_on_going' => 1
                ]);
            })
            ->where('verified_by_admin', 1)
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->latest()
            ->get();
    }

    private function getWagers($dateFilter, $dateRange, $site_id, $supplier_id, $wager_id)
    {
        return DailyWager::with(['phase.site', 'supplier', 'wagerAttendances' => fn($q) => $q->where('verified_by_admin', 1)])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', function ($site) {
                $site->where([
                    'deleted_at' => null,
                    'is_on_going' => 1
                ]);
            })
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isValidWager($wager_id), fn($q) => $q->where('id', $wager_id))
            ->latest()
            ->get();
    }

    private function isValidWager($wager_id)
    {
        return $wager_id && $wager_id !== 'all';
    }

    private function getServiceChargeAmount($amount, $service_charge)
    {

        return ($amount * $service_charge) / 100;
    }

    private function isValidSite($site_id)
    {
        return $site_id && $site_id !== 'all';
    }

    private function isValidSupplier($supplier_id)
    {
        return $supplier_id && $supplier_id !== 'all';
    }

    private function isFilteredDate($dateFilter, $dateRange)
    {

        return $dateFilter !== 'lifetime' && $dateRange;
    }

    public function calculateAllBalances($ledgers)
    {


        $totals = [
            'without_service_charge' => [
                'paid' => 0,
                'due' => 0,
                'balance' => 0
            ],
            'with_service_charge' => [
                'paid' => 0,
                'due' => 0,
                'balance' => 0
            ],
        ];

        $total_debits = 0;
        $total_credits = 0;

        foreach ($ledgers as $item) {
            $credit = (float)($item['credit']);
            $debit = (float)($item['debit']);

            $total_debits += $debit;
            $total_credits += $credit;

            switch ($item['category']) {
                case 'Payment':
                    $totals['without_service_charge']['paid'] += $credit;
                    $totals['with_service_charge']['paid'] += $credit;
                    $totals['without_service_charge']['due'] += $debit;
                    $totals['with_service_charge']['due'] += $debit;
                    break;
                default:
                    $totals['without_service_charge']['due'] += $debit;
                    $totals['with_service_charge']['due'] += $item['total_amount_with_service_charge'];
                    break;
            }
        }

        $totals['without_service_charge']['balance'] = $totals['without_service_charge']['due'] - $totals['without_service_charge']['paid'];
        $totals['with_service_charge']['balance'] = $totals['with_service_charge']['due'] - $totals['with_service_charge']['paid'];

        $totals['total_debits'] = $total_debits;
        $totals['total_credits'] = $total_credits;

        return $totals;
    }
}
