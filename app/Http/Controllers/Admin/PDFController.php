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
            'phases.dailyWagers.supplier' => function ($q) {
                $q->withTrashed();
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
            $daily_wagers_total = $phase->dailyWagers->sum('price_per_day') * $phase->wagerAttendances->sum('no_of_persons');
            $square_footage_total = $phase->squareFootageBills->reduce(function ($carry, $bill) {
                return $carry + ($bill->price * $bill->multiplier);
            }, 0);

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

        // Table for construction material billings
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
            'dailyWagers.supplier' => function ($q) {
                $q->withTrashed();
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



        // $grand_total_construction_amount = 0;
        // $grand_total_daily_expenses_amount = 0;
        // $grand_total_daily_wagers_amount = 0;
        // $grand_total_square_footage_amount = 0;

        $phaseCosting = [
            'construction_total_amount' => $phase->construction_total_amount = $phase->constructionMaterialBillings->sum('amount'),
            'daily_expenses_total_amount' => $phase->daily_expenses_total_amount = $phase->dailyExpenses->sum('price'),
            'daily_wagers_total_amount' => $phase->daily_wagers_total_amount = $phase->dailyWagers->sum('price_per_day') * $phase->wagerAttendances->sum('no_of_persons'),
            'daily_wager_attendance_amount' => $phase->daily_wager_attendance_amount = $phase->wagerAttendances->sum('no_of_persons'),
            'square_footage_total_amount' => $phase->square_footage_total_amount = $phase->squareFootageBills->reduce(function ($carry, $bill) {
                return $carry + ($bill->price * $bill->multiplier);
            }, 0),
            'total_amount' => $phase->total_amount = $phase->construction_total_amount +
                $phase->daily_expenses_total_amount +
                $phase->daily_wagers_total_amount +
                $phase->square_footage_total_amount,
        ];

        // $grand_total_construction_amount += $phase->construction_total_amount;
        // $grand_total_daily_expenses_amount += $phase->daily_expenses_total_amount;
        // $grand_total_daily_wagers_amount += $phase->daily_wagers_total_amount;
        // $grand_total_square_footage_amount += $phase->square_footage_total_amount;

        // $grand_total_amount = $grand_total_construction_amount +
        //     $grand_total_daily_expenses_amount +
        //     $grand_total_daily_wagers_amount +
        //     $grand_total_square_footage_amount;

        // dd($phaseCosting);

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

        $payments = PaymentSupplier::with(['site', 'supplier'])->latest()->paginate(10);
        $raw_materials = ConstructionMaterialBilling::with(['phase.site', 'supplier'])->get();
        $sgft = SquareFootageBill::with(['phase.site', 'supplier'])->get();
        $expenses = DailyExpenses::with(['phase.site',])->get();
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
                'credit' => 0,
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
                'debit' => $bill->price * $bill->multiplier,
                'credit' => 0,
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

        $dateFilter = $request->get('date_filter', 'today');

        $now = Carbon::now();

        $ledgers = $this->filterLedgersByDate($ledgers, $dateFilter, $now);

        $ledgers = $ledgers->sortBy('created_at')->map(function ($ledger) {
            $ledger['created_at'] = Carbon::parse($ledger['created_at'])->format('d-M-Y H:i A');
            return $ledger;
        });

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
