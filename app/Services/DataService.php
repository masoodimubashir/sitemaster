<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\SquareFootageBill;

class DataService
{


    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function getData($dateFilter)
    {

        $dateRange = $this->filterByDate($dateFilter);

        $payments = PaymentSupplier::with([
            'site' => function ($site) {
                $site->with(['phases'])->withoutTrashed();
            },
            'supplier' => function ($supplier) {
                $supplier->withoutTrashed();
            }
        ])->whereHas('site', function ($site) {
            $site->whereNull('deleted_at');
        })->whereHas('supplier', function ($supplier) {
            $supplier->whereNull('deleted_at');
        })->whereHas('site.phases', function ($phase) {
            $phase->whereNull('deleted_at');
        })->where('verified_by_admin', 1)
            ->when($dateFilter, function ($query) use ($dateRange, $dateFilter) {

                if ($dateFilter !== 'lifetime' && $dateRange) {
                    return $query->whereBetween('created_at', $dateRange);
                }
            })
            ->latest()
            ->get();

        $raw_materials = ConstructionMaterialBilling::with(['phase.site', 'supplier'])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('supplier', function ($supplier) {
                $supplier->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })
            ->where('verified_by_admin', 1)
            ->when($dateFilter, function ($query) use ($dateRange, $dateFilter) {
                if ($dateFilter !== 'lifetime' && $dateRange) {
                    return $query->whereBetween('created_at', $dateRange);
                }
            })
            ->latest()
            ->get();

        $squareFootageBills = SquareFootageBill::with([
            'phase' => function ($phase) {
                $phase->with([
                    'site' => function ($site) {
                        $site->withoutTrashed();
                    }
                ])->withoutTrashed();
            },
            'supplier' => function ($supplier) {
                $supplier->withoutTrashed();
            },
        ])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('supplier', function ($supplier) {
                $supplier->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })
            ->where('verified_by_admin', 1)
            ->when($dateFilter, function ($query) use ($dateRange, $dateFilter) {

                if ($dateFilter !== 'lifetime' && $dateRange) {
                    return $query->whereBetween('created_at', $dateRange);
                }
            })
            ->latest()
            ->get();

        $expenses = DailyExpenses::with([
            'phase' => function ($phase) {
                $phase->with([
                    'site' => function ($site) {
                        $site->withoutTrashed();
                    }
                ])->withoutTrashed();
            },
        ])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })
            ->where('verified_by_admin', 1)
            ->when($dateFilter, function ($query) use ($dateRange, $dateFilter) {
                if ($dateFilter !== 'lifetime' && $dateRange) {
                    return $query->whereBetween('created_at', $dateRange);
                }
            })
            ->latest()
            ->get();

        $wagers = DailyWager::with([
            'phase' => function ($phase) {
                $phase->with([
                    'site' => function ($site) {
                        $site->withoutTrashed();
                    },
                    'wagerAttendances' => function ($q) {
                        $q->where('verified_by_admin', 1);
                    }
                ])->withoutTrashed();
            },
            'supplier' => function ($supplier) {
                $supplier->withoutTrashed();
            },
        ])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('supplier', function ($supplier) {
                $supplier->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })
            ->when($dateFilter, function ($query) use ($dateRange, $dateFilter) {

                if ($dateFilter !== 'lifetime' && $dateRange) {
                    return $query->whereBetween('created_at', $dateRange);
                }
            })
            ->latest()
            ->get();

        return [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers];
    }

    public  function makeData($payments = null, $raw_materials = null, $squareFootageBills = null, $expenses = null, $wagers = null)
    {

        $ledgers = collect();

        $ledgers = $ledgers->merge($payments->map(function ($pay) {
            return [
                'supplier' => $pay->supplier->name ?? '',
                'description' => $pay->item_name ?? 'NA',
                'category' => 'Payments',
                'debit' => 'NA',
                'credit' => $pay->amount,
                'phase' => $pay->phase->phase_name ?? 'NA',
                'site' => $pay->site->site_name ?? 0,
                'site_owner' => $pay->site->site_owner_name,
                'contact_no' => $pay->site->contact_no,
                'site_id' => $pay->site_id ?? null,
                'supplier_id' => $pay->supplier_id ?? null,
                'created_at' => $pay->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($raw_materials->map(function ($material) {

            $service_charge_amount = $this->getServiceChargeAmount($material->amount, $material->phase->site->service_charge);

            return [
                'supplier' => $material->supplier->name ?? 0,
                'description' => $material->item_name ?? 0,
                'category' => 'Raw Material',
                'debit' => $material->amount,
                'credit' => 0,
                'total_amount_with_service_charge' => $service_charge_amount + $material->amount,
                'phase' => $material->phase->phase_name ?? 0,
                'site' => $material->phase->site->site_name ?? 0,
                'site_id' => $material->phase->site_id ?? null,
                'supplier_id' => $material->supplier_id ?? null,
                'created_at' => $material->created_at,
            ];

        }));

        $ledgers = $ledgers->merge($squareFootageBills->map(function ($bill) {

            $bill_amount = $bill->price * $bill->multiplier;
            $service_charge_amount = $this->getServiceChargeAmount($bill_amount, $bill->phase->site->service_charge);

            return [
                'supplier' => $bill->supplier->name ?? 0,
                'description' => $bill->wager_name ?? 0,
                'category' => 'Square Footage Bill',
                'debit' => $bill_amount,
                'credit' => 0,
                'total_amount_with_service_charge' => $service_charge_amount + $bill_amount,
                'phase' => $bill->phase->phase_name ?? 0,
                'site' => $bill->phase->site->site_name ?? 0,
                'site_id' => $bill->phase->site_id ?? null,
                'supplier_id' => $bill->supplier_id ?? null,
                'created_at' => $bill->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($expenses->map(function ($expense) {

            $service_charge_amount = $this->getServiceChargeAmount($expense->price, $expense->phase->site->service_charge);

            return [
                'supplier' => $expense->supplier->name ?? null,
                'description' => $expense->item_name ?? null,
                'category' => 'Daily Expense',
                'debit' => $expense->price,
                'credit' => 0,
                'total_amount_with_service_charge' => $service_charge_amount + $expense->price,
                'phase' => $expense->phase->phase_name ?? 0,
                'site' => $expense->phase->site->site_name ?? 0,
                'site_id' => $expense->phase->site_id ?? null,
                'supplier_id' => $expense->supplier_id ?? null,
                'created_at' => $expense->created_at,
            ];
        }));

        $ledgers = $ledgers->merge($wagers->map(function ($wager) {

            $service_charge_amount = $this->getServiceChargeAmount($wager->wager_total, $wager->phase->site->service_charge);



            return [
                'supplier' => $wager->supplier->name ?? '',
                'description' => $wager->wager_name ?? 0,
                'category' => 'Daily Wager',
                'debit' => $wager->wager_total,
                'credit' => 0,
                'total_amount_with_service_charge' => $service_charge_amount + $wager->wager_total,
                'phase' => $wager->phase->phase_name ?? 0,
                'site' => $wager->phase->site->site_name ?? 0,
                'site_id' => $wager->phase->site_id ?? null,
                'supplier_id' => $wager->supplier_id ?? null,
                'created_at' => $wager->created_at,
            ];
        }));

        return $ledgers;
    }


    /**
     * Get date range based on filter or custom dates
     */
    private  function filterByDate($dateFilter)
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
                return null;

            default:
                return [
                    $now->copy()->startOfDay()->toDateTimeString(),
                    $now->copy()->endOfDay()->toDateTimeString()
                ];
        }
    }

    /**
     * Get Due, Credit and Balancene based on filter Of The Models
     */

    public function calculateBalances($ledgers)
    {

        $total_paid = 0;
        $total_due = 0;
        $total_balance = 0;


        foreach ($ledgers as $item) {
            switch ($item['category']) {
                case 'Payments':
                    $total_paid += is_string($item['credit']) ? floatval($item['credit']) : $item['credit'];
                    break;
                case 'Raw Material':
                case 'Square Footage Bill':
                case 'Daily Expense':
                case 'Daily Wager':
                    $total_due += is_string($item['debit']) ? floatval($item['debit']) : $item['debit'];
                    break;
            }
        }

        $total_balance = $total_due - $total_paid;

        return [$total_paid, $total_due, $total_balance];
    }

    public function calculateBalancesWithServiceCharge($ledgers)
    {

        $total_paid = 0;
        $total_due = 0;
        $total_balance = 0;


        foreach ($ledgers as $item) {
            switch ($item['category']) {
                case 'Payments':
                    $total_paid += is_string($item['credit']) ? floatval($item['credit']) : $item['credit'];
                    break;
                case 'Raw Material':
                case 'Square Footage Bill':
                case 'Daily Expense':
                case 'Daily Wager':
                    $total_due += $item['total_amount_with_service_charge'];
                    break;
            }
        }

        $total_balance = $total_due - $total_paid;

        return [$total_paid, $total_due, $total_balance];
    }

    public function getServiceChargeAmount($amount, $service_charge)
    {

        $service_charge_amount = ($amount * $service_charge) / 100;

        return $service_charge_amount;
    }
}
