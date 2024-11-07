<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\Item;
use App\Models\PaymentSupplier;
use App\Models\Phase;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PDFController extends Controller
{
    public function showSitePdf(string $id)
    {
        $site_id = base64_decode($id);

        $site = Site::with([
            'phases.constructionMaterialBillings.supplier' => function ($q) {
                $q->withTrashed();
            },
            'phases.squareFootageBills.supplier' => function ($q) {
                $q->withTrashed();
            },
            'phases.dailyWagers' => function ($q) {
                $q->with(['wagerAttendances', 'supplier'])->withTrashed()->latest();
            },
            'phases.dailyExpenses' => function ($q) {
                $q->withTrashed();
            },
            'phases.wagerAttendances.dailyWager.supplier' => function ($q) {
                $q->withTrashed();
            },
            'paymeentSuppliers'
        ])->findOrFail($site_id);

        $totalSupplierPaymentAmount = $site->paymeentSuppliers->sum('amount');

        // Initialize the site array
        $siteData = [
            'site' => $site, // Keep the site as an object
            'phases' => []
        ];

        foreach ($site->phases as $phase) {

            // Calculate totals for each phase
            $construction_total = $phase->constructionMaterialBillings->sum('amount');
            $daily_expenses_total = $phase->dailyExpenses->sum('price');

            foreach ($phase->dailyWagers as $wager) {
                $phase->daily_wagers_total_amount += $wager->wager_total;
            }

            $square_footage_total = $phase->squareFootageBills->reduce(function ($carry, $bill) {
                return $carry + ($bill->price * $bill->multiplier);
            }, 0);

            $daily_wagers_total = $phase->daily_wagers_total_amount;

            // Calculate total for the phase with service charge
            $phase_total = $construction_total + $daily_expenses_total + $daily_wagers_total + $square_footage_total;
            $total_with_service_charge = ($phase_total * $site->service_charge / 100) + $phase_total;

            // Add phase data to the site array
            $siteData['phases'][] = [
                'phase' => $phase->phase_name,
                'site_service_charge' => $site->service_charge,
                'construction_total_amount' => $construction_total,
                'daily_expenses_total_amount' => $daily_expenses_total,
                'daily_wagers_total_amount' => $daily_wagers_total,
                'square_footage_total_amount' => $square_footage_total,
                'phase_total' => $phase_total,
                'phase_total_with_service_charge' => $total_with_service_charge,
                'construction_material_billings' => $phase->constructionMaterialBillings,
                'daily_expenses' => $phase->dailyExpenses,
                'daily_wagers' => $phase->dailyWagers,
                'square_footage_bills' => $phase->squareFootageBills,
                'wager_attendances' => $phase->wagerAttendances,
            ];
        }

        // Optionally calculate the grand total for the site
        $siteData['grand_total_amount'] = array_reduce($siteData['phases'], function ($carry, $phase) {
            return $carry + $phase['phase_total_with_service_charge'];
        }, 0);

        $data = [
            'site_name' => $site->site_name,
            'contact_no' => $site->contact_no,
            'service_charge' => $site->service_charge,
            'balance' => $siteData['grand_total_amount'] - $totalSupplierPaymentAmount,
            'site_owner_name' => $site->site_owner_name,
            'location' => $site->location,
            'debit' =>  $siteData['grand_total_amount'],
            'credit' => $totalSupplierPaymentAmount,
        ];

        $headers = [
            'box1' => 'Site Name',
            'box2' => 'Conatct No',
            'box3' => 'Service Charge',
            'box4' => 'Balance',
            'box5' => 'Site Owner',
            'box6' => 'Location',
            'box7' => 'Debit',
            'box8' => 'Credit',
        ];

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site Info');
        $pdf->infoTable($headers, $data);
        $pdf->siteTableData($siteData['phases']);
        $pdf->Output();
        exit;
    }

    public function showPhasePdf(string $id)
    {

        $phase_id = base64_decode($id);

        $phase = Phase::with([
            'constructionMaterialBillings.supplier' => function ($q) {
                $q->withTrashed();
            },
            'squareFootageBills.supplier' => function ($q) {
                $q->withTrashed();
            },
            'dailyWagers' => function ($q) {
                $q->with(['wagerAttendances', 'supplier'])->withTrashed();
            },

            'dailyExpenses' => function ($q) {
                $q->withTrashed();
            },
            'wagerAttendances.dailyWager.supplier' => function ($q) {
                $q->withTrashed();
            },
        ])->findOrFail($phase_id);

        $phases = [
            'phase_name' => $phase->phase_name,
            'site_name' => $phase->site->site_name,
            'service_charge' => $phase->site->service_charge,
            'contact_no' => $phase->site->contact_no,
            'site_owner_name' => $phase->site->site_owner_name,
            'location' =>  $phase->site->location,
            'construction_material_billings' => $phase->constructionMaterialBillings,
            'daily_expenses' => $phase->dailyExpenses,
            'daily_wagers' => $phase->dailyWagers,
            'square_footage_bills' => $phase->squareFootageBills,
            'wager_attendances' => $phase->wagerAttendances,
        ];


        foreach ($phase->dailyWagers as $wager) {
            $phase->daily_wagers_total_amount += $wager->wager_total;
        }

        $phaseCosting = [
            'construction_total_amount' => $phase->construction_total_amount = $phase->constructionMaterialBillings->sum('amount'),
            'daily_expenses_total_amount' => $phase->daily_expenses_total_amount = $phase->dailyExpenses->sum('price'),
            'daily_wagers_total_amount' => $phase->daily_wagers_total_amount,
            'daily_wager_attendance_amount' => $phase->daily_wager_attendance_amount = $phase->wagerAttendances->sum('no_of_persons'),
            'square_footage_total_amount' => $phase->square_footage_total_amount = $phase->squareFootageBills->reduce(function ($carry, $bill) {
                return $carry + ($bill->price * $bill->multiplier);
            }, 0),
            'total_amount' => $phase->total_amount = $phase->construction_total_amount +
                $phase->daily_expenses_total_amount +
                $phase->daily_wagers_total_amount +
                $phase->square_footage_total_amount,
        ];

        $headers = [
            'box1' => 'Phase Name',
            'box2' => 'Site Name',
            'box3' => 'Conatct No',
            'box4' => 'Service Charge',
            'box5' => 'Balance',
            'box6' => 'Site Owner',
            'box7' => 'Location',
            'box8' => ''
        ];

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site Phase Info');
        $pdf->phaseTableData($headers, $phases, $phaseCosting);
        $pdf->Output();
        exit;
    }

    public function showSupplierPaymentPdf(string $id)
    {


        $supplier = Supplier::with('paymentSuppliers')->latest()->find(base64_decode($id));

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Supplier Payment History');
        $pdf->supplierPaymentTable($supplier);
        $pdf->Output();
        exit;
    }

    public function showSitePaymentPdf(string $id)
    {

        $site = Site::with('paymeentSuppliers')->latest()->find(base64_decode($id));

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Supplier Payment History');
        $pdf->sitePaymentTable($site);
        $pdf->Output();
        exit;
    }

    public function showLedgerPdf(Request $request)
    {

        // Get date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $dateFilter = $request->get('date_filter', 'today');

        // Get the date range based on filter or custom dates
        $dateRange = $this->getDateRange($dateFilter, $startDate, $endDate);

        // Base query for ongoing sites
        $ongoingSites = Site::where('is_on_going', 1)->pluck('id');

        // Build queries with date filtering
        $payments = PaymentSupplier::with(['site', 'supplier'])
            ->whereIn('site_id', $ongoingSites)
            ->when($dateRange, function ($query, $dateRange) {
                return $query->whereBetween('created_at', $dateRange);
            })
            ->latest()
            ->get();

        $raw_materials = ConstructionMaterialBilling::with(['phase.site', 'supplier'])
            ->when($dateRange, function ($query, $dateRange) {
                return $query->whereBetween('created_at', $dateRange);
            })
            ->where('verified_by_admin', 1)
            ->latest()
            ->get();

        $squareFootageBills = SquareFootageBill::with(['phase.site', 'supplier'])
            ->when($dateRange, function ($query, $dateRange) {
                return $query->whereBetween('created_at', $dateRange);
            })
            ->where('verified_by_admin', 1)
            ->latest()
            ->get();

        $expenses = DailyExpenses::with(['phase.site'])
            ->when($dateRange, function ($query, $dateRange) {
                return $query->whereBetween('created_at', $dateRange);
            })
            ->where('verified_by_admin', 1)
            ->latest()
            ->get();

        $wagers = DailyWager::with(['phase.site', 'supplier', 'phase.wagerAttendances'])
            ->when($dateRange, function ($query, $dateRange) {
                return $query->whereBetween('created_at', $dateRange);
            })
            ->where('verified_by_admin', 1)
            ->latest()
            ->get();


        // Execute queries and merge results
        $ledgers = collect()->sortBy('created_at');

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

        // Merge raw materials into ledgers
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

        // Merge square footage bills into ledgers
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

        // Merge expenses into ledgers
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

        // Merge daily wagers into ledgers
        $ledgers = $ledgers->merge($wagers->map(function ($wager) {
            return [
                'supplier' => $wager->supplier->name ?? '',
                'description' => $wager->wager_name ?? 'NA',
                'category' => 'Daily Wager',
                'debit' => $wager->phase->wagerAttendances->sum('no_of_persons') * $wager->price_per_day,
                'credit' => 0,
                'phase' => $wager->phase->phase_name ?? 'N/A',
                'site' => $wager->phase->site->site_name ?? 'N/A',
                'site_id' => $wager->phase->site_id ?? null,
                'supplier_id' => $wager->supplier_id ?? null,
                'created_at' => $wager->created_at,
            ];
        }));

        $totals = $this->calculateBalances($ledgers);
        $total_paid = $totals['total_paid'];
        $total_due = $totals['total_due'];
        $total_balance = $totals['total_balance'];

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Supplier Payment History');
        $pdf->ledgerTable($ledgers, $total_paid, $total_due, $total_balance);
        $pdf->Output();
        exit;
    }

    private function getDateRange($dateFilter, $startDate = null, $endDate = null)
    {
        // If custom dates are provided, use them
        if ($startDate && $endDate) {
            return [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ];
        }

        $now = Carbon::now();

        return match ($dateFilter) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay()
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay()
            ],
            'this_week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek()
            ],
            'last_week' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek()
            ],
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth()
            ],
            'this_quarter' => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter()
            ],
            'last_quarter' => [
                $now->copy()->subQuarter()->startOfQuarter(),
                $now->copy()->subQuarter()->endOfQuarter()
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear()
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear()
            ],
            'custom' => $startDate && $endDate ? [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ] : null,
            default => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay()
            ]
        };
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
