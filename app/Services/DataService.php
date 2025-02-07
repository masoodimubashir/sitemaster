<?php

namespace App\Services;

use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\Payment;
use App\Models\PaymentSupplier;
use App\Models\SquareFootageBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DataService
{

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }


    public function getData($dateFilter, $site_id, $supplier_id, $wager_id)
    {

        $dateRange = $this->filterByDate($dateFilter);

        $wager = null;

        if ($wager_id && $wager_id !== 'all') {
            $wager = DailyWager::find($wager_id);
            $supplier_id = $wager?->supplier_id;
        }

//        $payments = PaymentSupplier::with([
//            'site.phases',
//            'supplier',
//            'site.adminPayments',
//            'supplier.adminPayments',
//        ])
//            ->whereHas('site', fn($q) => $q->whereNull('deleted_at'))
//            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
//            ->whereHas('site.phases', fn($q) => $q->whereNull('deleted_at'))
//            ->where('verified_by_admin', 1)
//            ->when($dateFilter !== 'lifetime' && $dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
//            ->when($site_id !== 'all', fn($q) => $q->where('site_id', $site_id))
//            ->when($supplier_id && $supplier_id != 'all', fn($q) => $q->where('supplier_id', $supplier_id))
//            ->latest()
//            ->get();


        $payments = Payment::query()
            ->with([
                'supplier' => function ($query) {
                    $query->whereNull('deleted_at')
                        ->withSum('adminPayments', 'amount');
                },
                'site' => function ($query) {
                    $query->whereNull('deleted_at')
                        ->where('is_on_going', 1)
                        ->withSum('adminPayments', 'amount');
                },
            ])
            ->where('verified_by_admin', 1)
            ->when($dateFilter !== 'lifetime' && $dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($site_id !== 'all', fn($q) => $q->where('site_id', $site_id))
            ->when($supplier_id && $supplier_id != 'all', fn($q) => $q->where('supplier_id', $supplier_id))
            ->latest()
            ->get();


        $raw_materials = ConstructionMaterialBilling::with(['phase.site', 'supplier'])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'))
            ->where('verified_by_admin', 1)
            ->when($dateFilter !== 'lifetime' && $dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($site_id !== 'all', fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($supplier_id && $supplier_id != 'all', fn($q) => $q->where('supplier_id', $supplier_id))
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
            })->when($site_id !== 'all', function ($q) use ($site_id) {
                return $q->whereHas('phase', function ($phaseQuery) use ($site_id) {
                    $phaseQuery->whereHas('site', function ($siteQuery) use ($site_id) {
                        $siteQuery->where('id', $site_id);
                    });
                });
            })->when($supplier_id && $supplier_id != 'all', function ($query) use ($supplier_id) {
                return $query->where('supplier_id', $supplier_id);
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
            })->when($site_id !== 'all', function ($q) use ($site_id) {
                return $q->whereHas('phase', function ($phaseQuery) use ($site_id) {
                    $phaseQuery->whereHas('site', function ($siteQuery) use ($site_id) {
                        $siteQuery->where('id', $site_id);
                    });
                });
            })
            ->latest()
            ->get();


        $wagers = DailyWager::with([
            'phase.site',
            'supplier',
            'wagerAttendances' => fn($q) => $q->where('verified_by_admin', 1)
        ])
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'))
            ->when($dateFilter !== 'lifetime' && $dateRange, fn($q) => $q->whereBetween('created_at', $dateRange))
            ->when($site_id !== 'all', fn($q) => $q->whereHas('phase.site', fn($sq) => $sq->where('id', $site_id)))
            ->when($supplier_id && $supplier_id != 'all', fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($wager_id && $wager_id !== 'all', fn($q) => $q->where('id', $wager_id))
            ->latest()
            ->get();

        return [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers];

    }

    /**
     * Get date range based on filter or custom dates
     */
    public function filterByDate($dateFilter)
    {
        $now = Carbon::now();

        switch ($dateFilter) {

//            case 'today':
//                return [
//                    $now->copy()->startOfDay()->toDateTimeString(),
//                    $now->copy()->endOfDay()->toDateTimeString()
//                ];

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

    public function makeData($payments = null, $raw_materials = null, $squareFootageBills = null, $expenses = null, $wagers = null)
    {

        $ledgers = collect();

//        $total_amount = 0;
//
//        $ledgers = $payments->map(function ($pay) use ($total_amount) {
//
//
////            dd($pay->site->admin_payments_sum_amount );
//
////            dd($pay->supplier);
//
//
//            $admin_site_total_amount = $pay->site->admin_payments_sum_amount ?? 0;
//
//            $admin_supplier_total_amount = $pay->supplier->admin_payments_sum_amount ?? 0;
//
////            dd();
//
//            $sitePaymentsTotal = $admin_site_total_amount + ($pay->amount ?? 0);
//
////            dd($sitePaymentsTotal);
//
//            $supplierPaymentsTotal = $admin_supplier_total_amount + ($pay->amount ?? 0);
//
////            dd($supplierPaymentsTotal);
//
//            $total_amount += $supplierPaymentsTotal;
//
//
//            return [
//                'date' => $pay->created_at,
//                'supplier' => $pay->supplier->name ?? 'NA',
//                'supplier_id' => $pay->supplier_id,
//                'site' => $pay->site->site_name ?? 'NA',
//                'site_id' => $pay->site_id,
//                'phase' => $pay->phase->phase_name ?? 'NA',
//                'description' => $pay->description ?? 'NA',
//                'category' => 'Payment',
//                'payment_mode' => $pay->payment_mode ?? 'NA',
//                'credit' => $admin_site_total_amount + $admin_supplier_total_amount ?? 0,
//                'debit' => 'NA',
//                'site_payments_total' => $sitePaymentsTotal,
//                'supplier_payments_total' => $supplierPaymentsTotal,
//                'total_amount' => $total_amount,
//                'site_owner' => $pay->site->site_owner_name ?? 'NA',
//                'contact_no' => $pay->site->contact_no ?? 'NA',
//                'created_at' => $pay->created_at,
//            ];
//        });


        $credit = 0;
        $ledgers = $ledgers->merge($payments->map(function ($pay) use (&$credit) {

            $admin_payment_supplier_amount = $pay->supplier->admin_payments_sum_amount ?? null;
            $admin_payment_site_amount = $pay->site->admin_payments_sum_amount ?? null;

            $site_total = $admin_payment_site_amount + $pay->amount;
            $supplier_total = $admin_payment_supplier_amount + $pay->amount;

            if ($admin_payment_supplier_amount !== null) {
                $credit += $admin_payment_supplier_amount;
            } elseif ($admin_payment_site_amount !== null) {
                $credit += $admin_payment_site_amount;
            } else {
                $credit += $site_total + $supplier_total;
            }


            return [

                'category' => 'Payment',
                'phase' => $pay->phase->phase_name ?? 'NA',
                'description' => $pay->description ?? 'NA',
                'admin_payment_supplier_amount' => $admin_payment_supplier_amount,
                'admin_payment_site_amount' => $admin_payment_site_amount,
                'site_payments_total' => $site_total,
                'supplier_payments_total' => $supplier_total,
                'site_total' => $site_total,
                'supplier_total' => $supplier_total,
                'transaction_type' => $pay->transaction_type === 1 ? 'Sent' : 'Received',
                'site_id' => $pay->site->id ?? 'NA',
                'site' => $pay->site->site_name ?? 'NA',
                'supplier_id' => $pay->supplier->id ?? 'NA',
                'supplier' => $pay->supplier->name ?? 'NA',
                'credit' => $credit,
                'debit' => 'NA',
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
                'supplier' => $expense->supplier->name ?? 'NA',
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

        $ledgers = $ledgers->merge(
            $wagers
                ->filter(function ($wager) {

                    return $wager->wagerAttendances->where('verified_by_admin', 1)->isNotEmpty();
                })
                ->map(function ($wager) {

                    $service_charge_amount = $this->getServiceChargeAmount($wager->wager_total, $wager->phase->site->service_charge);

                    return [
                        'wager_id' => $wager->id,
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
                })
        );


        return $ledgers;
    }

    public function getServiceChargeAmount($amount, $service_charge)
    {

        $service_charge_amount = ($amount * $service_charge) / 100;

        return $service_charge_amount;
    }

    /**
     * Get Due, Credit and Balancene based on filter Of The Models
     */

    // public function calculateBalances($ledgers)
    // {

    //     $total_paid = 0;
    //     $total_due = 0;
    //     $total_balance = 0;


    //     foreach ($ledgers as $item) {
    //         switch ($item['category']) {
    //             case 'Payments':
    //                 $total_paid += is_string($item['credit']) ? floatval($item['credit']) : $item['credit'];
    //                 break;
    //             case 'Raw Material':
    //             case 'Square Footage Bill':
    //             case 'Daily Expense':
    //             case 'Daily Wager':
    //                 $total_due += is_string($item['debit']) ? floatval($item['debit']) : $item['debit'];
    //                 break;
    //         }
    //     }

    //     $total_balance = $total_due - $total_paid;

    //     return [$total_paid, $total_due, $total_balance];
    // }
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
            ]
        ];

        foreach ($ledgers as $item) {
            $credit = is_string($item['credit']) ? floatval($item['credit']) : $item['credit'];

            switch ($item['category']) {
                case 'Payments':
                    $totals['without_service_charge']['paid'] += $credit;
                    $totals['with_service_charge']['paid'] += $credit;
                    break;

                case 'Raw Material':
                case 'Square Footage Bill':
                case 'Daily Expense':
                case 'Daily Wager':
                    $debit = is_string($item['debit']) ? floatval($item['debit']) : $item['debit'];
                    $totals['without_service_charge']['due'] += $debit;
                    $totals['with_service_charge']['due'] += $item['total_amount_with_service_charge'];
                    break;
            }
        }

        // Calculate final balances
        $totals['without_service_charge']['balance'] =
            $totals['without_service_charge']['due'] - $totals['without_service_charge']['paid'];

        $totals['with_service_charge']['balance'] =
            $totals['with_service_charge']['due'] - $totals['with_service_charge']['paid'];

        return $totals;
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
}
