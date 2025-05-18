<?php

namespace App\Services;

use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\Labour;
use App\Models\Payment;
use App\Models\SquareFootageBill;
use App\Models\Wasta;


class DataService
{


    public function __construct() {}

    public function getData($dateFilter, $site_id, $supplier_id, $wager_id, $startDate = null, $endDate = null)
    {
        $dateRange = $this->filterByDate($dateFilter, $startDate, $endDate);

        $supplier_id = $this->getSupplierIdFromWager($wager_id, $supplier_id);

        $payments = $this->getPayments($dateFilter, $dateRange, $site_id, $supplier_id);
        $raw_materials = $this->getRawMaterials($dateFilter, $dateRange, $site_id, $supplier_id);
        $squareFootageBills = $this->getSquareFootageBills($dateFilter, $dateRange, $site_id, $supplier_id);
        $expenses = $this->getExpenses($dateFilter, $dateRange, $site_id);
        $wagers = $this->getWagers($dateFilter, $dateRange, $site_id, $supplier_id, $wager_id);
        $wastas = $this->getWastas($dateFilter, $dateRange, $site_id);
        $labours = $this->getLabours($dateFilter, $dateRange, $site_id);

        return [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours];
    }


    public function makeData($payments = null, $raw_materials = null, $squareFootageBills = null,  $expenses = null, $wagers = null, $wastas = null, $labours = null)
    {

        $ledgers = collect();

        $ledgers = $ledgers->merge($payments->map(function ($pay) {

            return [
                'description' => 'Payment',
                'category' => 'Payment',
                'credit' =>  $pay->amount,
                'debit' => 0,
                'transaction_type' => $pay->supplier_id && $pay->site_id ? 'Sent By ' . ucwords($pay->site->site_name) : (($pay->transaction_type === 0) ? 'Sent By Firm' : 'Received By Firm'),
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
                'description' => $material->item_name ?? '--',
                'category' => 'Material',
                'credit' => 0,
                'debit' => $material->amount,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'site' => $material->phase->site->site_name ?? '--',
                'total_amount_with_service_charge' => $service_charge + $material->amount,
                'supplier' => $material->supplier->name ?? '--',
                'supplier_id' => $material->supplier_id ?? '--',
                'site_id' => $material->phase->site_id ?? '--',
                'phase' => $material->phase->phase_name ?? '--',
                'created_at' => $material->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($squareFootageBills->map(function ($bill) {

            $amount = $bill->price * $bill->multiplier;

            $service_charge = $this->getServiceChargeAmount($amount, $bill->phase->site->service_charge);

            return [
                'description' => $bill->wager_name ?? '--',
                'category' => 'SQFT',
                'debit' => $amount,
                'credit' => 0,
                'total_amount_with_service_charge' => $service_charge + $amount,
                'transaction_type' => '--',
                'payment_initiator' => 'Supplier',
                'site' => $bill->phase->site->site_name ?? '--',
                'supplier' => $bill->supplier->name ?? '--',
                'supplier_id' => $bill->supplier_id ?? '--',
                'site_id' => $bill->phase->site_id ?? '--',
                'phase' => $bill->phase->phase_name ?? '--',
                'created_at' => $bill->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($expenses->map(function ($expense) {

            $service_charge = $this->getServiceChargeAmount($expense->price, $expense->phase->site->service_charge);

            return [
                'description' => $expense->item_name ?? '--',
                'category' => 'Expense',
                'credit' => 0,
                'debit' => $expense->price,
                'total_amount_with_service_charge' => $service_charge + $expense->price,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $expense->phase->site->site_name ?? '--',
                'supplier' =>  '--',
                'supplier_id' =>  '--',
                'site_id' => $expense->phase->site_id ?? '--',
                'phase' => $expense->phase->phase_name ?? '--',
                'created_at' => $expense->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($wagers->map(function ($wager) {

            $service_charge = $this->getServiceChargeAmount($wager->wager_total, $wager->phase->site->service_charge);

            return [
                'description' => $wager->wager_name ?? '--',
                'category' => 'Wager',
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


        $ledgers = $ledgers->merge($wastas->map(function ($wasta) {

            $totalAmount = $this->calculateWastaTotal($wasta);
            $serviceCharge = $this->getServiceChargeAmount($totalAmount, $wasta->site->service_charge ?? 0);

            return [
                'description' => $wasta->wasta_name ?? '--',
                'category' => 'Wasta',
                'credit' => 0,
                'debit' => $totalAmount,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $wasta->site->site_name ?? '--',
                'supplier' => '--',
                'supplier_id' => '--',
                'site_id' => $wasta->site_id ?? '--',
                'phase' => '--',
                'created_at' => $wasta->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
                'service_charge_percentage' => $wasta->site->service_charge ?? 0,
            ];
        }));

        // Add Labour data to ledgers
        $ledgers = $ledgers->merge($labours->map(function ($labour) {

            $totalAmount = $this->calculateLabourTotal($labour);
            $serviceCharge = $this->getServiceChargeAmount($totalAmount, $labour->site->service_charge ?? 0);

            return [
                'description' => $labour->labour_name ?? '--',
                'category' => 'Labour',
                'credit' => 0,
                'debit' => $totalAmount,
                'transaction_type' => '--',
                'payment_initiator' => 'Site',
                'site' => $labour->site->site_name ?? '--',
                'supplier' => '--',
                'supplier_id' => '--',
                'site_id' => $labour->site_id ?? '--',
                'phase' => '--',
                'created_at' => $labour->created_at,
                'total_amount_with_service_charge' => $totalAmount + $serviceCharge,
                'service_charge_amount' => $serviceCharge,
            ];
        }));


        return $ledgers;
    }





    private function calculateWastaTotal($wasta): float|int
    {
        // Calculate based on wasta price and attendances
        $totalDays = $wasta->attendances->where('is_present', true)->count();
        return $wasta->price * $totalDays;
    }

    private function calculateLabourTotal($labour)
    {
        // Calculate based on labour price and attendances
        $totalDays = $labour->attendances->where('is_present', true)->count();
        return $labour->price * $totalDays;
    }

    public function filterByDate($dateFilter, $startDate = null, $endDate = null)
    {
        return match ($dateFilter) {
            'yesterday' => [now()->yesterday()->startOfDay(), now()->yesterday()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : now()->startOfDay(),
                $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : now()->endOfDay(),
            ],
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

    private function getExpenses($dateFilter, $dateRange, $site_id, $supplier_id = null)
    {
        return DailyExpenses::with(['phase.site' => fn($site) => $site->withoutTrashed()])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', fn($site) => $site->where([
                'deleted_at' => null,
                'is_on_going' => 1,
            ]))
            ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id)) // add supplier filter if given
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


    private function getWastas($dateFilter, $dateRange, $site_id)
    {
        return Wasta::with([
            'site',
            'attendances' => function ($q) {
                $q->where('is_present', 1); // Always eager-load only present attendances
            }
        ])
            ->whereHas('site', function ($q) {
                $q->where([
                    'deleted_at' => null,
                    'is_on_going' => 1
                ]);
            })
            ->when($this->isValidSite($site_id), function ($q) use ($site_id) {
                $q->where('site_id', $site_id);
            })
            ->whereHas('attendances', function ($q) use ($site_id) {
                $q->where('is_present', 1) // Always check this
                    ->when($this->isValidSite($site_id), function ($q) use ($site_id) {
                        $q->whereHas('attendable', function ($q) use ($site_id) {
                            $q->where('site_id', $site_id);
                        });
                    });
            })
            ->when($this->isFilteredDate($dateFilter, $dateRange), function ($q) use ($dateRange) {
                $q->where(function ($query) use ($dateRange) {
                    $query->whereBetween('created_at', $dateRange)
                        ->orWhereHas('attendances', function ($q) use ($dateRange) {
                            $q->where('is_present', 1)
                                ->whereBetween('attendance_date', $dateRange);
                        });
                });
            })
            ->latest()
            ->get();
    }



    private function getLabours($dateFilter, $dateRange, $site_id)
    {
        return Labour::with([
            'wasta',
            'site',
            'attendances' => function ($q) {
                $q->where('is_present', 1); // Always eager-load only present attendances
            }
        ])
            ->whereHas('site', function ($q) {
                $q->where([
                    'deleted_at' => null,
                    'is_on_going' => 1
                ]);
            })
            ->when($this->isValidSite($site_id), function ($q) use ($site_id) {
                $q->where('site_id', $site_id);
            })
            ->whereHas('attendances', function ($q) use ($site_id) {
                $q->where('is_present', 1) // Always check this
                    ->when($this->isValidSite($site_id), function ($q) use ($site_id) {
                        $q->whereHas('attendable', function ($q) use ($site_id) {
                            $q->where('site_id', $site_id);
                        });
                    });
            })
            ->when($this->isFilteredDate($dateFilter, $dateRange), function ($q) use ($dateRange) {
                $q->where(function ($query) use ($dateRange) {
                    $query->whereBetween('created_at', $dateRange)
                        ->orWhereHas('attendances', function ($q) use ($dateRange) {
                            $q->where('is_present', 1)
                                ->whereBetween('attendance_date', $dateRange);
                        });
                });
            })
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

    private function isFilteredDate($dateFilter, $dateRange): bool
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
                    // Use total_amount_with_service_charge if it exists, fallback to debit
                    $totals['with_service_charge']['due'] += isset($item['total_amount_with_service_charge'])
                        ? (float)$item['total_amount_with_service_charge']
                        : $debit;
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
