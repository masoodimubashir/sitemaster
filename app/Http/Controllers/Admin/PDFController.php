<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Services\DataService;
use Illuminate\Http\Request;

class PDFController extends Controller
{



    public function __construct(private DataService $dataService) {}

    public function showSitePdf(string $id)
    {
        // Decode site ID from base64
        $site_id = base64_decode($id);

        $dateFilter = 'today';
        $supplier_id = 'all';
        $wager_id = 'all';
        $startDate = 'start_date';
        $endDate = 'end_date';

        // Get filtered collections from your data service
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours] = $this->dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $wager_id,
            $startDate,
            $endDate
        );

        // Merge and sort all financial data
        $ledgers = $this->dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers,
            $wastas,
            $labours
        )->sortByDesc(fn($entry) => $entry['created_at']);

        // Group ledgers by phase
        $ledgersGroupedByPhase = $ledgers->groupBy(function ($item) {
            return empty($item['category']);
        });

        // Fetch site info for the PDF header
        $site = Site::findOrFail($site_id);

        $phaseData = [];

        foreach ($ledgersGroupedByPhase as $phaseName => $records) {
            // Sum totals by category (using debit for costs)
            $construction_total = $records->where('category', 'Material')->sum('debit');
            $square_total = $records->where('category', 'SQFT')->sum('debit');
            $expenses_total = $records->where('category', 'Expense')->sum('debit');
            $wager_total = $records->where('category', 'Wager')->sum('debit');
            $wasta_total = $records->where('category', 'Wasta')->sum('debit');
            $labour_total = $records->where('category', 'Labour')->sum('debit');
            $payments_total = $records->where('category', 'Payment')->sum('credit');


            $subtotal = $construction_total + $square_total + $expenses_total + $wager_total + $wasta_total + $labour_total;
            $withService = ($subtotal * $site->service_charge / 100) + $subtotal;

            $phaseData[] = [
                'phase' => $phaseName,
                'site_service_charge' => $site->service_charge,
                'construction_total_amount' => $construction_total,
                'square_footage_total_amount' => $square_total,
                'daily_expenses_total_amount' => $expenses_total,
                'daily_wagers_total_amount' => $wager_total,
                'daily_wastas_total_amount' => $wasta_total,
                'daily_labours_total_amount' => $labour_total,
                'total_payment_amount' => $payments_total,
                'phase_total' => $subtotal,
                'phase_total_with_service_charge' => $withService,
                'construction_material_billings' => $records->where('category', 'Material'),
                'square_footage_bills' => $records->where('category', 'SQFT'),
                'daily_expenses' => $records->where('category', 'Expense'),
                'daily_wagers' => $records->where('category', 'Wager'),
                'daily_wastas' => $records->where('category', 'Wasta'),
                'daily_labours' => $records->where('category', 'Labour'),
            ];
        }

        // Calculate grand totals
        $grandTotal = collect($phaseData)->sum('phase_total_with_service_charge');
        $totalSupplierPaymentAmount = $ledgers->sum(fn($p) => floatval($p['credit'] ?? 0));

        $balances = $this->dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];

        // Prepare header data for PDF
        $data = [
            'site_name' => $site->site_name,
            'contact_no' => $site->contact_no,
            'service_charge' => $site->service_charge,
            'balance' => $grandTotal - $totalSupplierPaymentAmount,
            'site_owner_name' => $site->site_owner_name,
            'location' => $site->location,
            'total_balance' => $withServiceCharge['balance'],
            'total_due' => $withServiceCharge['due'],
            'effective_balance' => $withoutServiceCharge['due'],
            'total_paid' => $withServiceCharge['paid'],
        ];

        $headers = [
            'box1' => 'Site Name',
            'box2' => 'Contact No',
            'box3' => 'Service Charge',
            'box4' => 'Balance',
            'box5' => 'Site Owner',
            'box6' => 'Location',
            'box7' => 'Debit',
            'box8' => 'Credit',
        ];


        // Generate PDF
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site Info');
        $pdf->infoTable($headers, $data);
        $pdf->siteTableData($phaseData);
        $pdf->Output();
        exit;
    }


   

    public function showPhasePdf(string $id)
    {

        $phase_id = base64_decode($id);
        $phase = Phase::with([
            'constructionMaterialBillings' => function ($q) {
                $q->where('verified_by_admin', 1)->whereHas('supplier', function ($query) {
                    $query->whereNull('deleted_at');
                })
                    ->with('supplier');
            },
            'squareFootageBills' => function ($q) {
                $q->where('verified_by_admin', 1)
                    ->whereHas('supplier', function ($query) {
                        $query->whereNull('deleted_at');
                    })
                    ->with('supplier');
            },
            'dailyWagers' => function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->whereNull('deleted_at');
                })
                    ->whereHas('wagerAttendances', function ($query) {
                        $query->where('verified_by_admin', 1); // Only include records where 'verified_by_admin' is 1
                    })
                    ->with([
                        'wagerAttendances' => function ($query) {
                            $query->where('verified_by_admin', 1); // Make sure to load only the wagerAttendances that are verified by admin
                        },
                        'supplier' => function ($q) {
                            $q->withoutTrashed();
                        }
                    ])
                    ->latest();
            },
            'dailyExpenses' => function ($q) {
                $q->where('verified_by_admin', 1);
            },
            'wagerAttendances' => function ($q) {
                $q->whereHas('dailyWager.supplier', function ($query) {
                    $query->whereNull('deleted_at');
                })
                    ->with([
                        'dailyWager.supplier' => function ($q) {
                            $q->withoutTrashed();
                        }
                    ])->where('verified_by_admin', 1);
            }
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


        $supplier = Supplier::with(['payments' => function ($q) {
            $q->with(['site', 'supplier'])->where('verified_by_admin', 1);
        }])->latest()->find(base64_decode($id));

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

        $site_id = base64_decode($id);

        $site = Site::with([
            'payments' => function ($pay) {
                $pay->where('verified_by_admin', 1)
                    ->with(['supplier', 'site']);
            }
        ], 'phases')
            ->latest()
            ->find($site_id);


        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Supplier Payment History');
        $pdf->sitePaymentTable($site);
        $pdf->Output();
        exit;
    }

    public function showLedgerPdf(Request $request, DataService $dataService)
    {

        $dateFilter = $request->get('date_filter', 'lifetime');
        $site_id = $request->input('site_id', $request->input('site_id'));
        $supplier_id = $request->input('supplier_id', 'all');
        $wager_id = $request->input('wager_id', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Call the service to get all data including wasta and labours
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $wager_id,
            $startDate,
            $endDate
        );

        // Create ledger data including wasta and labours
        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wagers,
            $wastas,
            $labours
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        // Calculate balances
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];


        $ledgers = $ledgers->sortByDesc(function ($d) {
            return $d['created_at'];
        });


        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 10);
        $pdf->SetTitle('Supplier Payment History');
        $pdf->ledgerTable($ledgers, $total_paid, $total_due, $total_balance, $effective_balance);
        $pdf->Output();
        exit;
    }
}
