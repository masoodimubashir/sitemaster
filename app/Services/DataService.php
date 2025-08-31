<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\Payment;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Models\Wager;
use App\Models\Wasta;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;

class DataService
{

    public function getData(string $dateFilter, $site_id, $supplier_id, ?string $startDate = null, ?string $endDate = null, $phase_id): array
    {

        $dateRange = $this->filterByDate($dateFilter, $startDate, $endDate);

        return [
            $this->getPayments($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getRawMaterials($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getSquareFootageBills($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getExpenses($dateFilter, $dateRange, $site_id, $supplier_id, $phase_id),
            $this->getAttendances($dateFilter, $dateRange, $site_id, $phase_id),
        ];
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

    // FIXME:  Check Why the filter is not working based on the site_id when i try to use $this->isActiveSite()..

    private function getPayments(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        // Payments are not tied to a phase in this context; honor current behavior
        if ($this->isValidPhase($phase_id)) {
            return collect();
        }

        $query = Payment::query()
            ->where('verified_by_admin', 1)
            ->with(['site', 'supplier']);

        // Direct relation: site_id is a column on payments
        $this->applyCommonFilters($query, $dateFilter, $dateRange, $site_id, $supplier_id, $phase_id, 'direct');

        return $query->get();
    }

    private function isValidPhase($phase_id): bool
    {
        return $phase_id && $phase_id !== 'all';
    }

    // Private helper methods

    /**
     * Apply common filters to queries across different models.
     * $type: 'direct' for models with site_id column; 'nested' for models linked via phase.site
     */
    private function applyCommonFilters($query, string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id, string $type)
    {
        // Supplier filter
        if ($this->isValidSupplier($supplier_id)) {
            $query->where('supplier_id', $supplier_id);
        }
        // Date filter
        if ($this->isFilteredDate($dateFilter, $dateRange)) {
            $query->whereBetween('created_at', $dateRange);
        }
        // Phase filter (only for nested relations that have phase)
        if ($this->isValidPhase($phase_id) && $type === 'nested') {
            $query->whereHas('phase', fn($q) => $q->where('id', $phase_id));
        }
        // Site filter
        if ($this->isValidSite($site_id)) {
            if ($type === 'nested') {
                $query->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id));
            } else { // direct
                $query->where('site_id', $site_id);
            }
        }

        return $query;
    }

    private function isValidSupplier($supplier_id): bool
    {
        return $supplier_id && $supplier_id !== 'all';
    }

    private function isFilteredDate(string $dateFilter, ?array $dateRange): bool
    {
        return $dateFilter !== 'lifetime' && $dateRange;
    }

    private function isValidSite($site_id): bool
    {
        return $site_id && $site_id !== 'all';
    }

    private function getRawMaterials(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        $query = ConstructionMaterialBilling::query()
            ->with(['phase.site', 'supplier'])
            ->where('verified_by_admin', 1)
            ->latest();

        // Nested relation via phase.site
        $this->applyCommonFilters($query, $dateFilter, $dateRange, $site_id, $supplier_id, $phase_id, 'nested');

        return $query->get();
    }

    private function getSquareFootageBills(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        $query = SquareFootageBill::query()
            ->with([
                'phase' => fn($phase) => $phase->with(['site' => fn($site) => $site->withoutTrashed()])->withoutTrashed(),
                'supplier' => fn($supplier) => $supplier->withoutTrashed(),
            ])
            ->where('verified_by_admin', 1)
            ->latest();

        // Nested relation via phase.site
        $this->applyCommonFilters($query, $dateFilter, $dateRange, $site_id, $supplier_id, $phase_id, 'nested');

        return $query->get();
    }

    private function getExpenses(string $dateFilter, ?array $dateRange, $site_id, $supplier_id, $phase_id): Collection
    {
        $query = DailyExpenses::query()
            ->with(['phase.site' => fn($site) => $site->withoutTrashed()])
            ->where('verified_by_admin', 1)
            ->latest();

        // Nested relation via phase.site
        $this->applyCommonFilters($query, $dateFilter, $dateRange, $site_id, $supplier_id, $phase_id, 'nested');

        return $query->get();
    }

    private function getAttendances(string $dateFilter, ?array $dateRange, $site_id, $phase_id): Collection
    {
        return Attendance::query()
            ->with([
                'attendanceSetup.setupable',
                'attendanceSetup.site'
            ])
            ->when($this->isFilteredDate($dateFilter, $dateRange), fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($this->isValidSite($site_id), function ($q) use ($site_id) {
                return $q->whereHas('attendanceSetup', fn($setupQuery) => $setupQuery->where('site_id', $site_id));
            })
            ->where('is_present', 1)
            ->latest('attendance_date')
            ->get();
    }

    public function makeData(?Collection $payments = null, ?Collection $rawMaterials = null, ?Collection $squareFootageBills = null, ?Collection $expenses = null, ?Collection $attendances = null): Collection
    {

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

        if ($attendances) {
            $ledgers = $ledgers->merge($this->transformAttendances($attendances));
        }

        return $ledgers;
    }

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
                'transaction_type' => $hasSupplier && $hasSite ? 'Sent By ' . ucwords($pay->site->site_name) : ($pay->transaction_type === 0 ? 'Return To Firm' : 'Received By Admin'),
                'payment_initiator' => $hasSite && !$hasSupplier ? 'Site' : ($hasSupplier ? 'Supplier' : 'Admin'),
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

    private function calculateServiceCharge(float $amount, float $serviceChargePercentage): float
    {
        return ($amount * $serviceChargePercentage) / 100;
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

    private function transformAttendances(Collection $attendances): Collection
    {
        return $attendances->map(function ($attendance) {
            $setup = $attendance->attendanceSetup;
            $setupable = $setup->setupable;

            // Get site information from attendance_setup
            $site = null;
            $serviceChargePercentage = 0;

            if ($setup->site_id) {
                // Assuming you have a Site model relationship or can fetch it
                $site = Site::find($setup->site_id);
                $serviceChargePercentage = $site->service_charge ?? 0;
            }

            // Calculate total amount (count × price from attendance_setup)
            $totalAmount = ($setup->count ?? 1) * ($setup->price ?? 0);
            $serviceCharge = $this->calculateServiceCharge($totalAmount, $serviceChargePercentage);

            // Determine description based on setupable type
            $description = 'Unknown';
            if ($setupable instanceof Wasta) {
                $description = $setupable->wasta_name ?? 'Wasta';
            } elseif ($setupable instanceof Wager) {
                $description = $setupable->wager_name ?? 'Wager';
            }

            $amount_status = $setup->count > 0 ? '' : 'No Count';

            return [
                'id' => $attendance->id,
                'verified_by_admin' => 1,
                'description' => $description,
                'category' => 'Attendance',
                'credit' => 0,
                'amount_status' => $amount_status,
                'debit' => $totalAmount,
                'return' => 0,
                'transaction_type' => null,
                'payment_initiator' => 'NA',
                'site' => $site->site_name ?? null,
                'supplier' => null,
                'supplier_id' => null,
                'site_id' => $setup->site_id ?? null,
                'phase' => null,
                'phase_id' => null,
                'created_at' => $attendance->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
                'image' => null,
            ];
        });
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
            $credit = (float)($item['credit'] ?? 0);
            $debit = (float)($item['debit'] ?? 0);
            $return = (float)($item['return'] ?? 0);

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
                // For other categories (Labour, Material, Attendance, etc.)
                $totals['without_service_charge']['due'] += $debit;
                $totals['with_service_charge']['due'] += $item['total_amount_with_service_charge'] ?? $debit;
                $totals['service_charge_amount'] += $item['service_charge_amount'] ?? 0;
            }
        }

        $totals['without_service_charge']['balance'] =
            ($totals['without_service_charge']['due'] - $totals['without_service_charge']['paid'])
            - $totals['without_service_charge']['return'];

        $totals['with_service_charge']['balance'] =
            ($totals['with_service_charge']['due'] - $totals['with_service_charge']['paid'])
            - $totals['with_service_charge']['return'];


        return $totals;
    }

    public function getSuppliersWithSites($site_id = null, $supplier_id = null, $phase_id = null): Collection
    {

        $suppliers = collect();

        $models = [
            Payment::class => ['relation' => 'site', 'type' => 'direct'],
            ConstructionMaterialBilling::class => ['relation' => 'phase.site', 'type' => 'nested'],
            SquareFootageBill::class => ['relation' => 'phase.site', 'type' => 'nested'],
            DailyExpenses::class => ['relation' => 'phase.site', 'type' => 'nested'],
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

        // Return unique supplier-site pairs, sorted by supplier then site
        return $suppliers
            ->unique(function ($item) {
                return ($item['supplier_id'] ?? 'null') . '|' . ($item['site_id'] ?? 'null');
            })
            ->sortBy([
                ['supplier_name', 'asc'],
                ['site_name', 'asc'],
            ])
            ->values();
    }

    private function isActiveSite(): Closure
    {
        return fn($site) => $site->where([
            'deleted_at' => null,
            'is_on_going' => 1
        ]);
    }

    /**
     * Get financial summary for a specific phase
     */
    public function getPhaseFinancialSummary($siteId, $phaseId): array
    {
        [$payments, $raw_materials, $squareFootageBills, $expenses] = $this->getData(
            'lifetime',
            $siteId,
            'all',
            null,
            null,
            $phaseId
        );

        $ledgers = $this->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses
        );

        $materials = $ledgers->where('category', 'Material');
        $sqft = $ledgers->where('category', 'SQFT');
        $expenses = $ledgers->where('category', 'Expense');
        $payments = $ledgers->where('category', 'Payment');

        $totalCosts = $materials->sum('debit') + $sqft->sum('debit') + $expenses->sum('debit');
        $totalPayments = $payments->sum('credit');
        $balance = $totalCosts - $totalPayments;

        return [
            'materials_cost' => $materials->sum('debit'),
            'sqft_cost' => $sqft->sum('debit'),
            'expenses_cost' => $expenses->sum('debit'),
            'total_costs' => $totalCosts,
            'total_payments' => $totalPayments,
            'balance' => $balance,
            'materials' => $materials->values(),
            'sqft_work' => $sqft->values(),
            'expenses' => $expenses->values(),
            'payments' => $payments->values()
        ];
    }

    /**
     * Get attendance data for a specific site
     */
    public function getSiteAttendanceData($siteId, $dateFilter = 'today'): array
    {
        $dateRange = $this->filterByDate($dateFilter, null, null);
        $attendances = $this->getAttendances($dateFilter, $dateRange, $siteId, 'all');

        $totalWorkers = $attendances->count();
        $dailyCost = $attendances->sum('debit');

        // Calculate monthly cost (approximate)
        $monthlyCost = $dailyCost * 30;

        return [
            'total_workers' => $totalWorkers,
            'daily_cost' => $dailyCost,
            'monthly_cost' => $monthlyCost,
            'attendances' => $attendances->values()
        ];
    }


}


