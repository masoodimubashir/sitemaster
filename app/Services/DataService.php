<?php

namespace App\Services;

use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\Labour;
use App\Models\Payment;
use App\Models\SquareFootageBill;
use App\Models\Wasta;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DataService
{
   

    public function getData(
        string $dateFilter,
        $site_id,
        $supplier_id,
        ?string $startDate = null,
        ?string $endDate = null,
        $phase_id
    ): array {
        $dateRange = $this->filterByDate($dateFilter, $startDate, $endDate);

        return [
            $this->getPayments($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getRawMaterials($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getSquareFootageBills($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getExpenses($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getWastas($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getLabours($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
        ];
    }

    public function makeData(
        ?Collection $payments = null,
        ?Collection $rawMaterials = null,
        ?Collection $squareFootageBills = null,
        ?Collection $expenses = null,
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
                'balance' => 0,
                'return' => 0,
            ],
            'with_service_charge' => [
                'paid' => 0,
                'due' => 0,
                'balance' => 0,
                'return' => 0
            ],
            'total_debits' => 0,
            'total_credits' => 0,
            'total_return' => 0,
            'service_charge_amount' => 0,
        ];

        foreach ($ledgers as $item) {
            $credit = (float) ($item['credit'] ?? 0);
            $debit = (float) ($item['debit'] ?? 0);
            $return = (float) ($item['return'] ?? 0);

            // Always sum these totals
            $totals['total_debits'] += $debit;
            $totals['total_credits'] += $credit;
            $totals['total_return'] += $return;

            if ($item['category'] === 'Payment') {
                $totals['without_service_charge']['paid'] += $credit;
                $totals['with_service_charge']['paid'] += $credit;

                $totals['without_service_charge']['return'] += $return;
                $totals['with_service_charge']['return'] += $return;

                $totals['without_service_charge']['due'] += $debit;
                $totals['with_service_charge']['due'] += $debit;
            } else {
                // For other categories (Labour, Material, etc.)
                $totals['without_service_charge']['due'] += $debit;
                $totals['with_service_charge']['due'] += $item['total_amount_with_service_charge'] ?? $debit;
                $totals['service_charge_amount'] += $item['service_charge_amount'] ?? 0;
            }
        }

        // dd($totals);

        $totals['without_service_charge']['balance'] =
            ($totals['without_service_charge']['due'] - $totals['without_service_charge']['paid'])
            - $totals['without_service_charge']['return'];

        $totals['with_service_charge']['balance'] =
            ($totals['with_service_charge']['due'] - $totals['with_service_charge']['paid'])
            - $totals['with_service_charge']['return'];


        return $totals;
    }

    // Private helper methods

    private function transformPayments(Collection $payments): Collection
    {

        return $payments->map(function ($pay) {
            $hasSupplier = !empty($pay->supplier_id);
            $hasSite = !empty($pay->site_id);

            $amount_status = $pay->amount > 0 ? '' : 'Pending Payment';

            $credit = 0;
            $return = 0;

            if ($hasSupplier || $hasSite) {
                if ($pay->transaction_type === null || $pay->transaction_type === 0) {
                    $credit = $pay->amount;
                } elseif ($pay->transaction_type === 1) {
                    $return = $pay->amount;
                }
            } else {
                // Admin-initiated transactions
                if ($pay->payment_initiator === 0) {
                    if ($pay->transaction_type === 1) {
                        $return = $pay->amount;
                    } else {
                        $credit = $pay->amount;
                    }
                }
            }

            return [
                'id' => $pay->id,
                'verified_by_admin' => $pay->verified_by_admin,
                'description' => 'Payment',
                'category' => 'Payment',
                'credit' => $credit,
                'amount_status' => $amount_status,
                'debit' => 0,
                'return' => $return,
                'transaction_type' => $hasSupplier && $hasSite
                    ? 'Sent By ' . ucwords($pay->site->site_name)
                    : ($pay->transaction_type === 0 ? 'Return To Firm' : 'Received By Admin'),
                'payment_initiator' => $hasSite && !$hasSupplier
                    ? 'Site'
                    : ($hasSupplier ? 'Supplier' : 'Admin'),
                'site' => $pay->site->site_name ?? null,
                'supplier' => $pay->supplier->name ?? null,
                'supplier_id' => $pay->supplier_id ?? null,
                'site_id' => $pay->site_id ?? null,
                'phase' => $pay->phase->phase_name ?? null,
                'phase_id' => $pay->phase_id ?? null,
                'created_at' => $pay->created_at,
                'image' => $pay->screenshot ?? null,
            ];
        });
    }

    private function transformRawMaterials(Collection $materials): Collection
    {
        return $materials->map(function ($material) {

            $serviceCharge = $this->calculateServiceCharge($material->amount * $material->unit_count, $material->phase->site->service_charge);

            $amount_status = $material->amount > 0 ? '' : 'Pending Price';
            
            $total_amount = $material->amount * $material->unit_count;

            return [
                'id' => $material->id,
                'verified_by_admin' => $material->verified_by_admin,
                'description' => $material->item_name ?? null,
                'category' => 'Material',
                'credit' => 0,
                'amount_status' => $amount_status,
                'debit' => $total_amount,
                'return' => 0,
                'transaction_type' => null,
                'payment_initiator' => 'NA',
                'site' => $material->phase->site->site_name ?? null,
                'total_amount_with_service_charge' => $serviceCharge + ($material->amount * $material->unit_count),
                'supplier' => $material->supplier->name ?? null,
                'supplier_id' => $material->supplier_id ?? null,
                'site_id' => $material->phase->site_id ?? null,
                'phase' => $material->phase->phase_name ?? null,
                'phase_id' => $material->phase_id ?? null,
                'created_at' => $material->created_at,
                'service_charge_amount' => $serviceCharge,
                'image' => $material->item_image_path ?? null,


            ];
        });
    }

    private function transformSquareFootageBills(Collection $bills): Collection
    {
        return $bills->map(function ($bill) {

            $amount = $bill->price * $bill->multiplier;

            $amount_status = $bill->price > 0 ? '' : 'Pending Price';

            $serviceCharge = $this->calculateServiceCharge($amount, $bill->phase->site->service_charge);

            return [

                'id' => $bill->id,
                'verified_by_admin' => $bill->verified_by_admin,
                'description' => $bill->wager_name ?? null,
                'category' => 'SQFT',
                'amount_status' => $amount_status,
                'debit' => $amount,
                'credit' => 0,
                'return' => 0,
                'total_amount_with_service_charge' => $serviceCharge + $amount,
                'transaction_type' => null,
                'payment_initiator' => 'NA',
                'site' => $bill->phase->site->site_name ?? null,
                'supplier' => $bill->supplier->name ?? null,
                'supplier_id' => $bill->supplier_id ?? null,
                'site_id' => $bill->phase->site_id ?? null,
                'phase' => $bill->phase->phase_name ?? null,
                'phase_id' => $bill->phase_id ?? null,
                'created_at' => $bill->created_at,
                'service_charge_amount' => $serviceCharge,
                'image' => $bill->image_path ?? null,

            ];
        });
    }

    private function transformExpenses(Collection $expenses): Collection
    {
        return $expenses->map(function ($expense) {

            $serviceCharge = $this->calculateServiceCharge($expense->price, $expense->phase->site->service_charge);

            $amount_status = $expense->price > 0 ? '' : 'Pending Price';


            return [

                'id' => $expense->id,
                'verified_by_admin' => $expense->paid_by_admin,
                'description' => $expense->item_name ?? null,
                'category' => 'Expense',
                'credit' => 0,
                'amount_status' => $amount_status,
                'debit' => $expense->price,
                'return' => 0,
                'total_amount_with_service_charge' => $serviceCharge + $expense->price,
                'transaction_type' => null,
                'payment_initiator' => 'NA',
                'site' => $expense->phase->site->site_name ?? null,
                'supplier' => null,
                'supplier_id' => null,
                'site_id' => $expense->phase->site_id ?? null,
                'phase' => $expense->phase->phase_name ?? null,
                'phase_id' => $expense->phase_id ?? null,
                'created_at' => $expense->created_at,
                'service_charge_amount' => $serviceCharge,
                'image' => $expense->bill_photo ?? null,

            ];
        });
    }


    private function transformWastas(Collection $wastas): Collection
    {
        return $wastas->map(function ($wasta) {

            $totalAmount = $this->calculateWastaTotal($wasta);

            $serviceCharge = $this->calculateServiceCharge($totalAmount, $wasta->phase->site->service_charge ?? 0);

            $amount_status = $wasta->price > 0 ? '' : 'Pending Price';


            return [

                'id' => $wasta->id,
                'verified_by_admin' => $wasta->verified_by_admin,
                'description' => $wasta->wasta_name . ' /' . $wasta->price . ':Day' ?? null,
                'category' => 'Wasta',
                'credit' => 0,
                'amount_status' => $amount_status,
                'debit' => $totalAmount,
                'return' => 0,
                'transaction_type' => null,
                'payment_initiator' => 'NA',
                'site' => $wasta->phase->site->site_name ?? null,
                'supplier' => null,
                'supplier_id' => null,
                'site_id' => $wasta->phase->site_id ?? null,
                'phase' => $wasta->phase->phase_name ?? null,
                'phase_id' => $wasta->phase_id ?? null,
                'created_at' => $wasta->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
                'service_charge_percentage' => $wasta->site->service_charge ?? 0,
                'image' => null,
            ];
        });
    }

    private function transformLabours(Collection $labours): Collection
    {
        return $labours->map(function ($labour) {

            $totalAmount = $this->calculateLabourTotal($labour);

            $serviceCharge = $this->calculateServiceCharge($totalAmount, $labour->phase->site->service_charge ?? 0);

            $amount_status = $labour->price > 0 ? '' : 'Pending Price';


            return [

                'id' => $labour->id,
                'verified_by_admin' => $labour->verified_by_admin,
                'description' => $labour->labour_name . ' /' . $labour->price . ':Day' ?? null,
                'category' => 'Labour',
                'credit' => 0,
                'amount_status' => $amount_status,
                'debit' => $totalAmount,
                'return' => 0,
                'transaction_type' => null,
                'payment_initiator' => 'NA',
                'site' => $labour->phase->site->site_name ?? null,
                'supplier' => null,
                'supplier_id' => null,
                'site_id' => $labour->phase->site_id ?? null,
                'phase' => $labour->phase->phase_name ?? null,
                'phase_id' => $labour->phase_id ?? null,
                'created_at' => $labour->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
                'image' => null,

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



    private function getPayments(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        // Return empty collection if phase is selected
        if ($this->isValidPhase($phase_id)) {
            return collect();
        }

        return Payment::query()
            ->where('verified_by_admin', 1)
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('site', fn($sq) => $sq->where('id', $site_id)))
            ->with(['site', 'supplier'])
            ->get();
    }

    private function getRawMaterials(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {


        return ConstructionMaterialBilling::query()
            ->with(['phase.site', 'supplier'])
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidPhase($phase_id), fn($q) => $q->whereHas('phase', fn($sq) => $sq->where('id', $phase_id)))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->where('verified_by_admin', 1)
            ->latest()
            ->get();
    }

    private function getSquareFootageBills(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        return SquareFootageBill::query()
            ->with([
                'phase' => fn($phase) => $phase->with(['site' => fn($site) => $site->withoutTrashed()])->withoutTrashed(),
                'supplier' => fn($supplier) => $supplier->withoutTrashed(),
            ])
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidPhase($phase_id), fn($q) => $q->whereHas('phase', fn($sq) => $sq->where('id', $phase_id)))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->where('verified_by_admin', 1)
            ->latest()
            ->get();
    }

    private function getExpenses(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        return DailyExpenses::query()
            ->with(['phase.site' => fn($site) => $site->withoutTrashed()])
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidPhase($phase_id), fn($q) => $q->whereHas('phase', fn($sq) => $sq->where('id', $phase_id)))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->where('verified_by_admin', 1)

            ->latest()
            ->get();
    }


    private function getWastas(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        return Wasta::query()
            ->with([
                'phase.site',
                'attendances' => fn($q) => $q->where('is_present', 1),
            ])
            ->whereHas('attendances', fn($q) => $q->where('is_present', 1)) // <-- Enforce at least one presence
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isValidPhase($phase_id), fn($q) => $q->whereHas('phase', fn($sq) => $sq->where('id', $phase_id)))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->latest()
            ->get();
    }
    private function getLabours(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        return Labour::query()
            ->with([
                'wasta',
                'phase.site',
                'attendances' => fn($q) => $q->where('is_present', 1),
            ])
            ->whereHas('attendances', fn($q) => $q->where('is_present', 1)) // <-- Enforce at least one presence
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSupplier($supplier_id), fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($this->isValidPhase($phase_id), fn($q) => $q->whereHas('phase', fn($sq) => $sq->where('id', $phase_id)))
            ->when($this->isValidSite($site_id), fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->latest()
            ->get();
    }


    /**
     * Get all suppliers with their site info (without date filters)
     * 
     * @param int|null $site_id
     * @param int|null $supplier_id
     * @param int|null $phase_id
     * @return Collection
     */
    public function getSuppliersWithSites(
        $site_id = null,
        $supplier_id = null,
        $phase_id = null
    ): Collection {
        $suppliers = collect();

        // Define models and their relationships
        $models = [
            Payment::class => ['relation' => 'site', 'type' => 'direct'],
            ConstructionMaterialBilling::class => ['relation' => 'phase.site', 'type' => 'nested'],
            SquareFootageBill::class => ['relation' => 'phase.site', 'type' => 'nested'],
            DailyExpenses::class => ['relation' => 'phase.site', 'type' => 'nested'],
            Wasta::class => ['relation' => 'phase.site', 'type' => 'nested'],
            Labour::class => ['relation' => 'phase.site', 'type' => 'nested']
        ];

        foreach ($models as $model => $config) {
            $query = $model::query()
                ->whereNotNull('supplier_id')
                ->with(['supplier', $config['relation']]);

            // Apply site filter if provided
            if ($this->isValidSite($site_id)) {
                if ($config['type'] === 'nested') {
                    $query->whereHas('phase', fn($q) => $q->whereHas('site', fn($sq) => $sq->where('id', $site_id)));
                } else {
                    $query->where('site_id', $site_id);
                }
            }

            // Apply supplier filter if provided
            if ($this->isValidSupplier($supplier_id)) {
                $query->where('supplier_id', $supplier_id);
            }

            // Apply phase filter if provided
            if ($this->isValidPhase($phase_id)) {
                if ($config['type'] === 'nested') {
                    $query->whereHas('phase', fn($q) => $q->where('id', $phase_id));
                }
            }

            $suppliers = $suppliers->merge(
                $query->get()
                    ->map(function ($record) use ($config) {
                        $site = $config['type'] === 'nested'
                            ? ($record->phase->site ?? null)
                            : ($record->site ?? null);

                        return [
                            'supplier_id' => $record->supplier_id,
                            'supplier_name' => $record->supplier->name ?? null,
                            'site_id' => $site->id ?? null,
                            'site_name' => $site->site_name ?? null
                        ];
                    })
            );
        }

        // Return unique suppliers sorted by name
        return $suppliers->unique('supplier_id')
            ->sortBy('supplier_name')
            ->values();
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

    private function isValidPhase($phase_id)
    {
        return $phase_id && $phase_id !== 'all';
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
