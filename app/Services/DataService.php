<?php

namespace App\Services;

use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\Labour;
use App\Models\Payment;
use App\Models\SquareFootageBill;
use App\Models\Wasta;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DataService
{
    public function __construct() {}

    public function getData(
        string $dateFilter,
        $site_id,
        $supplier_id,
        $wager_id,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dateRange = $this->filterByDate($dateFilter, $startDate, $endDate);

        return [
            $this->getPayments($dateFilter, $dateRange, $site_id, $supplier_id),
            $this->getRawMaterials($dateFilter, $dateRange, $site_id, $supplier_id),
            $this->getSquareFootageBills($dateFilter, $dateRange, $site_id, $supplier_id),
            $this->getExpenses($dateFilter, $dateRange, $site_id, $supplier_id),
            $this->getWagers($dateFilter, $dateRange, $site_id, $supplier_id, $wager_id),
            $this->getWastas($dateFilter, $dateRange, $site_id, $supplier_id),
            $this->getLabours($dateFilter, $dateRange, $site_id, $supplier_id),
        ];
    }

    public function makeData(
        ?Collection $payments = null,
        ?Collection $rawMaterials = null,
        ?Collection $squareFootageBills = null,
        ?Collection $expenses = null,
        ?Collection $wagers = null,
        ?Collection $wastas = null,
        ?Collection $labours = null
    ): Collection {
        $ledgers = collect();

        if ($payments) {
            $ledgers = $ledgers->merge($this->transformPayments($payments));
        }

        if ($rawMaterials) {
            $ledgers = $ledgers->merge($this->transformRawMaterials($rawMaterials));
        }

        if ($squareFootageBills) {
            $ledgers = $ledgers->merge($this->transformSquareFootageBills($squareFootageBills));
        }

        if ($expenses) {
            $ledgers = $ledgers->merge($this->transformExpenses($expenses));
        }

        if ($wagers) {
            $ledgers = $ledgers->merge($this->transformWagers($wagers));
        }

        if ($wastas) {
            $ledgers = $ledgers->merge($this->transformWastas($wastas));
        }

        if ($labours) {
            $ledgers = $ledgers->merge($this->transformLabours($labours));
        }

        return $ledgers;
    }

    public function calculateAllBalances(Collection $ledgers): array
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
            'total_debits' => 0,
            'total_credits' => 0,
        ];

        foreach ($ledgers as $item) {
            $credit = (float)($item['credit']);
            $debit = (float)($item['debit']);

            $totals['total_debits'] += $debit;
            $totals['total_credits'] += $credit;

            if ($item['category'] === 'Payment') {
                $totals['without_service_charge']['paid'] += $credit;
                $totals['with_service_charge']['paid'] += $credit;
                $totals['without_service_charge']['due'] += $debit;
                $totals['with_service_charge']['due'] += $debit;
            } else {
                $totals['without_service_charge']['due'] += $debit;
                $totals['with_service_charge']['due'] += $item['total_amount_with_service_charge'] ?? $debit;
            }
        }

        $totals['without_service_charge']['balance'] =
            $totals['without_service_charge']['due'] - $totals['without_service_charge']['paid'];

        $totals['with_service_charge']['balance'] =
            $totals['with_service_charge']['due'] - $totals['with_service_charge']['paid'];

        return $totals;
    }

    // Private helper methods

    private function transformPayments(Collection $payments): Collection
    {
        return $payments->map(function ($pay) {
            return [
                'id' => $pay->id,
                'verified_by_admin' => $pay->verified_by_admin,
                'description' => 'Payment',
                'category' => 'Payment',
                'credit' => $pay->amount,
                'debit' => 0,
                'transaction_type' => $pay->supplier_id && $pay->site_id
                    ? 'Sent By ' . ucwords($pay->site->site_name)
                    : ($pay->transaction_type === 0 ? 'Sent By Firm' : 'Received By Firm'),
                'payment_initiator' => !empty($pay->site_id) && empty($pay->supplier_id)
                    ? 'Site'
                    : (!empty($pay->supplier_id) ? 'Supplier' : 'Admin'),
                'site' => $pay->site->site_name ?? '--',
                'supplier' => $pay->supplier->name ?? '--',
                'supplier_id' => $pay->supplier_id ?? '--',
                'site_id' => $pay->site_id ?? '--',
                'phase' => $pay->phase->phase_name ?? '--',
                'phase_id' => $pay->phase_id ?? '--',
                'created_at' => $pay->created_at,
            ];
        });
    }

    private function transformRawMaterials(Collection $materials): Collection
    {
        return $materials->map(function ($material) {

            $serviceCharge = $this->calculateServiceCharge($material->amount, $material->phase->site->service_charge);


            return [
                'id'=> $material->id,
                'verified_by_admin' => $material->verified_by_admin,
                'description' => $material->item_name ?? '--',
                'category' => 'Material',
                'credit' => 0,
                'debit' => $material->amount,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'site' => $material->phase->site->site_name ?? '--',
                'total_amount_with_service_charge' => $serviceCharge + $material->amount,
                'supplier' => $material->supplier->name ?? '--',
                'supplier_id' => $material->supplier_id ?? '--',
                'site_id' => $material->phase->site_id ?? '--',
                'phase' => $material->phase->phase_name ?? '--',
                'phase_id' => $material->phase_id ?? '--',
                'created_at' => $material->created_at,
            ];
        });
    }

    private function transformSquareFootageBills(Collection $bills): Collection
    {
        return $bills->map(function ($bill) {

            $amount = $bill->price * $bill->multiplier;
            $serviceCharge = $this->calculateServiceCharge($amount, $bill->phase->site->service_charge);

            return [

                'id' => $bill->id,
                'verified_by_admin' => $bill->verified_by_admin,
                'description' => $bill->wager_name ?? '--',
                'category' => 'SQFT',
                'debit' => $amount,
                'credit' => 0,
                'total_amount_with_service_charge' => $serviceCharge + $amount,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'site' => $bill->phase->site->site_name ?? '--',
                'supplier' => $bill->supplier->name ?? '--',
                'supplier_id' => $bill->supplier_id ?? '--',
                'site_id' => $bill->phase->site_id ?? '--',
                'phase' => $bill->phase->phase_name ?? '--',
                'phase_id' => $bill->phase_id ?? '--',
                'created_at' => $bill->created_at,
            ];
        });
    }

    private function transformExpenses(Collection $expenses): Collection
    {
        return $expenses->map(function ($expense) {

            $serviceCharge = $this->calculateServiceCharge($expense->price, $expense->phase->site->service_charge);

            return [

                'id' => $expense->id,
                'verified_by_admin'=> $expense->paid_by_admin,
                'description' => $expense->item_name ?? '--',
                'category' => 'Expense',
                'credit' => 0,
                'debit' => $expense->price,
                'total_amount_with_service_charge' => $serviceCharge + $expense->price,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $expense->phase->site->site_name ?? '--',
                'supplier' => '--',
                'supplier_id' => '--',
                'site_id' => $expense->phase->site_id ?? '--',
                'phase' => $expense->phase->phase_name ?? '--',
                'phase_id' => $expense->phase_id ?? '--',
                'created_at' => $expense->created_at,
            ];
        });
    }

    private function transformWagers(Collection $wagers): Collection
    {
        return $wagers->map(function ($wager) {

            $serviceCharge = $this->calculateServiceCharge($wager->wager_total, $wager->phase->site->service_charge);

            return [

                'id' => $wager->id,
                'verified_by_admin' => $wager->verified_by_admin,
                'description' => $wager->wager_name ?? '--',
                'category' => 'Wager',
                'credit' => 0,
                'debit' => $wager->wager_total,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'total_amount_with_service_charge' => $serviceCharge + $wager->wager_total,
                'site' => $wager->phase->site->site_name ?? '--',
                'supplier' => $wager->supplier->name ?? '--',
                'supplier_id' => $wager->supplier_id ?? '--',
                'site_id' => $wager->phase->site_id ?? '--',
                'phase' => $wager->phase->phase_name ?? '--',
                'phase_id' => $wager->phase_id ?? '--',
                'created_at' => $wager->created_at,
            ];
        });
    }

    private function transformWastas(Collection $wastas): Collection
    {
        return $wastas->map(function ($wasta) {

            $totalAmount = $this->calculateWastaTotal($wasta);
            $serviceCharge = $this->calculateServiceCharge($totalAmount, $wasta->phase->site->service_charge ?? 0);

            return [

                'id' => $wasta->id,
                'verified_by_admin' => $wasta->verified_by_admin,
                'description' => $wasta->wasta_name ?? '--',
                'category' => 'Wasta',
                'credit' => 0,
                'debit' => $totalAmount,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $wasta->phase->site->site_name ?? '--',
                'supplier' => '--',
                'supplier_id' => '--',
                'site_id' => $wasta->phase->site_id ?? '--',
                'phase' => $wasta->phase->phase_name ?? '--',
                'phase_id' => $wasta->phase_id ?? '--',
                'created_at' => $wasta->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
                'service_charge_percentage' => $wasta->site->service_charge ?? 0,
            ];
        });
    }

    private function transformLabours(Collection $labours): Collection
    {
        return $labours->map(function ($labour) {
     
            $totalAmount = $this->calculateLabourTotal($labour);
            $serviceCharge = $this->calculateServiceCharge($totalAmount, $labour->phase->site->service_charge ?? 0);

            return [

                'id' => $labour->id,
                'verified_by_admin' => $labour->verified_by_admin,
                'description' => $labour->labour_name ?? '--',
                'category' => 'Labour',
                'credit' => 0,
                'debit' => $totalAmount,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $labour->phase->site->site_name ?? '--',
                'supplier' => '--',
                'supplier_id' => '--',
                'site_id' => $labour->phase->site_id ?? '--',
                'phase' => $labour->phase->phase_name ?? '--',
                'phase_id' => $labour->phase_id ?? '--',
                'created_at' => $labour->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
            ];
        });
    }

    private function calculateWastaTotal(Wasta $wasta): float
    {
        $totalDays = $wasta->attendances->where('is_present', true)->count();
        return $wasta->price * $totalDays;
    }

    private function calculateLabourTotal(Labour $labour): float
    {
        $totalDays = $labour->attendances->where('is_present', true)->count();
        return $labour->price * $totalDays;
    }

    private function calculateServiceCharge(float $amount, float $serviceChargePercentage): float
    {
        return ($amount * $serviceChargePercentage) / 100;

    }

    private function filterByDate(string $dateFilter, ?string $startDate, ?string $endDate): ?array
    {
        return match ($dateFilter) {
            'yesterday' => [now()->yesterday()->startOfDay(), now()->yesterday()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfDay(),
                $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay(),
            ],
            'lifetime' => null,
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    private function getSupplierIdFromWager($wager_id, $supplier_id)
    {
        if ($this->isValidWager($wager_id)) {
            $wager = DailyWager::find($wager_id);
            return $wager?->supplier_id ?? $supplier_id;
        }
        return $supplier_id;
    }

    private function getPayments(string $dateFilter, ?array $dateRange, $site_id, $supplier_id): Collection
    {
        return Payment::query()
            ->with(['site', 'supplier'])
            ->where('verified_by_admin', 1)
            ->when($this->isValidSite($site_id), fn($q) => $q->where('site_id', $site_id))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->get();
    }

    private function getRawMaterials(string $dateFilter, ?array $dateRange, $site_id, $supplier_id): Collection
    {
        return ConstructionMaterialBilling::with(['phase.site', 'supplier'])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', $this->activeSiteQuery())
            ->where('verified_by_admin', 1)
            ->when($dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->latest()
            ->get();
    }

    private function getSquareFootageBills(string $dateFilter, ?array $dateRange, $site_id, $supplier_id): Collection
    {
        return SquareFootageBill::with([
            'phase' => fn($phase) => $phase->with(['site' => fn($site) => $site->withoutTrashed()])->withoutTrashed(),
            'supplier' => fn($supplier) => $supplier->withoutTrashed(),
        ])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', $this->activeSiteQuery())
            ->where('verified_by_admin', 1)
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->latest()
            ->get();
    }

    private function getExpenses(string $dateFilter, ?array $dateRange, $site_id, $supplier_id): Collection
    {



        return DailyExpenses::with(['phase.site' => fn($site) => $site->withoutTrashed()])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', $this->activeSiteQuery())
            ->when($supplier_id !== 'all', fn($q) => $q->whereNotNull('supplier_id'))
            ->when($supplier_id === 'all', fn($q) => $q->whereNull('supplier_id'))
            ->where('verified_by_admin', 1)
            ->when(
                $this->isFilteredDate($dateFilter, $dateRange),
                fn($q) =>
                $q->whereBetween('created_at', $dateRange)
            )

            ->when(
                $this->isValidSite($site_id),
                fn($q) =>
                $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id))
            )

            ->latest()
            ->get();
    }


    private function getWagers(
        string $dateFilter,
        ?array $dateRange,
        $site_id,
        $supplier_id,
        $wager_id
    ): Collection {
        return DailyWager::with(['phase.site', 'supplier', 'wagerAttendances' => fn($q) => $q->where('verified_by_admin', 1)])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', $this->activeSiteQuery())
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isValidWager($wager_id), fn($q) => $q->where('id', $wager_id))
            ->latest()
            ->get();
    }

    private function getWastas(string $dateFilter, ?array $dateRange, $site_id, $supplier_id): Collection
    {
        return Wasta::with([
            'phase.site',
            'attendances' => fn($q) => $q->where('is_present', 1),
        ])
            ->whereHas('phase.site', $this->activeSiteQuery())
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase', fn($q) => $q->where('site_id', $site_id)))
            
            ->when($supplier_id !== 'all', fn($q) => $q->whereNotNull('supplier_id'))
            ->when($supplier_id === 'all', fn($q) => $q->whereNull('supplier_id'))
            ->when(
                $this->isFilteredDate($dateFilter, $dateRange),
                fn($q) =>
                $q->where(function ($query) use ($dateRange) {
                    $query->whereBetween('created_at', $dateRange)
                        ->orWhereHas('attendances', fn($q) => $q->where('is_present', 1)->whereBetween('attendance_date', $dateRange));
                })
            )
            ->latest()
            ->get();
    }


    private function getLabours(string $dateFilter, ?array $dateRange, $site_id, $supplier_id): Collection
    {


        return Labour::with([
            'wasta',
            'phase.site',
            'attendances' => fn($q) => $q->where('is_present', 1),
        ])
            ->whereHas('phase.site', $this->activeSiteQuery())
            ->when(
                $this->isValidSite($site_id),
                fn($q) =>
                $q->whereHas(
                    'phase',
                    fn($q) =>
                    $q->where('site_id', $site_id)
                )
            )
            ->when($supplier_id !== 'all', fn($q) => $q->whereNotNull('supplier_id'))
            ->when($supplier_id === 'all', fn($q) => $q->whereNull('supplier_id'))
            ->when(
                $this->isFilteredDate($dateFilter, $dateRange),
                fn($q) =>
                $q->where(function ($query) use ($dateRange) {
                    $query->whereBetween('created_at', $dateRange)
                        ->orWhereHas('attendances', fn($q) => $q->where('is_present', 1)->whereBetween('attendance_date', $dateRange));
                })
            )
            ->latest()
            ->get();
    }


    private function activeSiteQuery(): \Closure
    {
        return fn($site) => $site->where([
            'deleted_at' => null,
            'is_on_going' => 1
        ]);
    }

    private function isValidWager($wager_id): bool
    {
        return $wager_id && $wager_id !== 'all';
    }

    private function isValidSite($site_id): bool
    {
        return $site_id && $site_id !== 'all';
    }

    private function isValidSupplier($supplier_id): bool
    {
        return $supplier_id && $supplier_id !== 'all';
    }

    private function isFilteredDate(string $dateFilter, ?array $dateRange): bool
    {
        return $dateFilter !== 'lifetime' && $dateRange;
    }
}
