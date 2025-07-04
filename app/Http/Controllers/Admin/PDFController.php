<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\Wasta;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PDFController extends Controller
{



    public function __construct(private DataService $dataService)
    {
    }

    public function showSitePdf(string $id)
    {

        $site_id = base64_decode($id);

        // Default filter options — or pull from request if needed
        $dateFilter = 'lifetime';
        $supplier_id = 'all';
        $startDate = 'start_date';
        $endDate = 'end_date';
        $phase_id = 'all';

        // Load site (for service charge, name, etc.)
        $site = Site::findOrFail($site_id);

        // Load processed financial data
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $this->dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );

        // Combine and group all entries by phase
        $ledgers = $this->dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $wastas,
            $labours
        )->sortByDesc(fn($entry) => $entry['created_at']);

        $ledgersGroupedByPhase = $ledgers->filter(function ($entry) {
            return !empty($entry['phase']);
        })->groupBy('phase');

        $balances = $this->dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];

        // dd($balances);

        // Per-phase breakdown
        $phaseData = [];

        foreach ($ledgersGroupedByPhase as $phaseName => $records) {

            $construction_total = $records->where('category', 'Material')->sum('debit');
            $square_total = $records->where('category', 'SQFT')->sum('debit');
            $expenses_total = $records->where('category', 'Expense')->sum('debit');
            $wasta_total = $records->where('category', 'Wasta')->sum('debit');
            $labour_total = $records->where('category', 'Labour')->sum('debit');
            $payments_total = $records->where('category', 'Payment')->sum('credit');

            $subtotal = $construction_total + $square_total + $expenses_total + $wasta_total + $labour_total;
            $withService = ($subtotal * $site->service_charge / 100) + $subtotal;


            $phaseData[] = [
                'phase' => $phaseName,
                'phase_id' => $records->first()['phase_id'],
                'construction_total_amount' => $construction_total,
                'square_footage_total_amount' => $square_total,
                'daily_expenses_total_amount' => $expenses_total,
                'daily_wastas_total_amount' => $wasta_total,
                'daily_labours_total_amount' => $labour_total,
                'total_payment_amount' => $payments_total,
                'phase_total' => $subtotal,
                'phase_total_with_service_charge' => $withService,
                'total_balance' => $withServiceCharge['balance'],
                'total_due' => $withServiceCharge['due'],
                'effective_balance' => $withoutServiceCharge['due'],
                'total_paid' => $withServiceCharge['paid'],
                'construction_material_billings' => $records->where('category', 'Material'),
                'square_footage_bills' => $records->where('category', 'SQFT'),
                'daily_expenses' => $records->where('category', 'Expense'),
                'daily_wastas' => $records->where('category', 'Wasta'),
                'daily_labours' => $records->where('category', 'Labour'),
            ];
        }

        $data = [
            'site_name' => $site->site_name,
            'contact_no' => $site->contact_no,
            'service_charge' => $site->service_charge,
            'site_owner_name' => $site->site_owner_name,
            'location' => $site->location,
            'total_balance' => $total_balance,
            'total_due' => $total_due,
            'effective_balance' => $effective_balance,
            'total_paid' => $total_paid,
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

        // ✅ Generate the PDF with data
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site Info');
        $pdf->infoTable($headers, $data);
        $pdf->siteTableData($phaseData); // Render each phase block
        $pdf->Output();
        exit;
    }

    public function showPhasePdf(string $id)
    {
        $phase_id = base64_decode($id);

        $phase = Phase::with([
            'constructionMaterialBillings' => function ($q) {
                $q->where('verified_by_admin', 1)
                    ->whereHas('supplier', fn($query) => $query->whereNull('deleted_at'))
                    ->with('supplier');
            },
            'squareFootageBills' => function ($q) {
                $q->where('verified_by_admin', 1)
                    ->whereHas('supplier', fn($query) => $query->whereNull('deleted_at'))
                    ->with('supplier');
            },

            'dailyExpenses' => fn($q) => $q->where('verified_by_admin', 1),

            'wastas' => fn($q) => $q->with(['attendances' => fn($att) => $att->where('is_present', 1)]),

            'labours' => fn($q) => $q->with(['attendances' => fn($att) => $att->where('is_present', 1)]),

        ])->findOrFail($phase_id);

        // Calculate totals
        $daily_wastas_total_amount = $phase->wastas->sum('price');
        $daily_labours_total_amount = $phase->labours->sum('price');

        // Count attendances present
        $wasta_attendance_count = $phase->wastas->flatMap->attendances->count();
        $labour_attendance_count = $phase->labours->flatMap->attendances->count();

        $phases = [
            'phase_name' => $phase->phase_name,
            'site_name' => $phase->site->site_name,
            'service_charge' => $phase->site->service_charge,
            'contact_no' => $phase->site->contact_no,
            'site_owner_name' => $phase->site->site_owner_name,
            'location' => $phase->site->location,
            'construction_material_billings' => $phase->constructionMaterialBillings,
            'daily_expenses' => $phase->dailyExpenses,
            'square_footage_bills' => $phase->squareFootageBills,
            'wager_attendances' => $phase->wagerAttendances ?? collect(),
            'daily_wastas' => $phase->wastas,
            'daily_labours' => $phase->labours,
        ];

        $phaseCosting = [
            'construction_total_amount' => $phase->constructionMaterialBillings->sum('amount'),
            'daily_expenses_total_amount' => $phase->dailyExpenses->sum('price'),
            'daily_wager_attendance_amount' => $phase->wagerAttendances->sum('no_of_persons'),
            'square_footage_total_amount' => $phase->squareFootageBills->reduce(fn($carry, $bill) => $carry + ($bill->price * $bill->multiplier), 0),
            'daily_wastas_total_amount' => $daily_wastas_total_amount,
            'daily_labours_total_amount' => $daily_labours_total_amount,
            'wasta_attendance_count' => $wasta_attendance_count,
            'labour_attendance_count' => $labour_attendance_count,
            'total_amount' =>
                $phase->constructionMaterialBillings->sum('amount') +
                $phase->dailyExpenses->sum('price') +
                $daily_wastas_total_amount +
                $daily_labours_total_amount +
                $phase->squareFootageBills->reduce(fn($carry, $bill) => $carry + ($bill->price * $bill->multiplier), 0),
        ];

        $headers = [
            'box1' => 'Phase Name',
            'box2' => 'Site Name',
            'box3' => 'Contact No',
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


        $supplier = Supplier::with([
            'payments' => function ($q) {
                $q->with(['site', 'supplier'])->where('verified_by_admin', 1);
            }
        ])->latest()->find(base64_decode($id));

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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $phase_id = $request->input('phase_id', 'all');

        // Call the service to get all data including wasta and labours
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );

        // Create ledger data including wasta and labours
        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
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
        $service_charge_amount = $balances['service_charge_amount'];

        $ledgers = $ledgers->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 10);
        $pdf->SetTitle('Supplier Payment History');
        $pdf->ledgerTable($ledgers, $total_paid, $total_due, $total_balance, $effective_balance, $service_charge_amount);
        $pdf->Output();
        exit;
    }

    public function generateAttendancePdf(Request $request)
    {
        // Parse month and year
        if ($request->filled('monthYear')) {
            [$year, $month] = explode('-', $request->input('monthYear'));
        } else {
            $month = now()->month;
            $year = now()->year;
        }

        // Load Wastas with their labours, phase, and site data
        $wastasQuery = Wasta::with([
            'phase.site',
            'labours.attendances' => function ($query) use ($month, $year) {
                $query->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year);
            },
            'attendances' => function ($query) use ($month, $year) {
                $query->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year);
            }
        ]);

        // Filter by site if selected
        $site = null;
        if ($request->filled('site_id')) {
            $wastasQuery->whereHas('phase.site', function ($query) use ($request) {
                $query->where('id', $request->input('site_id'));
            });
            $site = Site::find($request->input('site_id'));
        }

        $wastas = $wastasQuery->get();

        // Group wastas by phase
        $phases = $wastas->groupBy('phase_id');

        // Get date range for the month
        $startDate = Carbon::create($year, $month, 1);
        $endDate = Carbon::create($year, $month, $startDate->daysInMonth);
        $dates = $this->getDatesInRange($startDate, $endDate);

        // Generate PDF
        $pdf = new PDF();
        $pdf->SetTitle('Phase-wise Labour Attendance Report');

        foreach ($phases as $phaseId => $phaseWastas) {
            $phase = $phaseWastas->first()->phase;

            // Prepare title with all relevant information
            $title = ($site ? strtoupper($site->site_name) : 'ALL SITES') . ' LABOUR ATTENDANCE SHEET';
            $subtitle = 'Phase: ' . ($phase->phase_name ?? 'All Phases') .
                ' | Month: ' . $startDate->format('F Y');

            // Get all workers (wastas + their labours) for this phase
            $workers = [];
            foreach ($phaseWastas as $wasta) {
                $workers[] = [
                    'name' => $wasta->wasta_name,
                    'type' => 'wasta',
                    'price' => $wasta->price,
                    'contact' => $wasta->contact_no
                ];
                foreach ($wasta->labours as $labour) {
                    $workers[] = [
                        'name' => $labour->labour_name,
                        'type' => 'labour',
                        'price' => $labour->price,
                        'contact' => $labour->contact_no
                    ];
                }
            }

            // Prepare attendance data for each date
            $attendanceData = $this->prepareAttendanceData($phaseWastas, $startDate, $endDate);

            // Calculate totals for this phase
            $totals = $this->calculateTotals($phaseWastas);

            // Prepare phase info
            $info = [
                'site_name' => $site->site_name ?? 'All Sites',
                'phase_name' => $phase->phase_name ?? 'All Phases',
                'month_year' => $startDate->format('F Y')
            ];

            // Add a new page for each phase
            $pdf->phaseWiseAttendanceReport(
                $title,
                $subtitle,
                $dates,
                $workers,
                $attendanceData,
                $totals,
                $info
            );
        }

        $filename = 'Phasewise_Attendance_' .
            ($site ? $site->site_name . '_' : '') .
            $month . '_' . $year . '.pdf';
        $pdf->Output();
        exit;
    }

    protected function prepareAttendanceData($wastas, $startDate, $endDate)
    {
        $attendanceData = [];
        $current = clone $startDate;

        // Initialize attendance data structure with all workers
        foreach ($wastas as $wasta) {
            $attendanceData[$wasta->wasta_name] = [];
            foreach ($wasta->labours as $labour) {
                $attendanceData[$labour->labour_name] = [];
            }
        }

        // Populate attendance data for each date
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');

            foreach ($wastas as $wasta) {
                // Wasta attendance
                $attendanceData[$wasta->wasta_name][$dateStr] = $wasta->attendances
                    ->where('attendance_date', $dateStr)
                    ->where('is_present', true)
                    ->count() > 0 ? 1 : 0;

                // Labour attendance
                foreach ($wasta->labours as $labour) {
                    $attendanceData[$labour->labour_name][$dateStr] = $labour->attendances
                        ->where('attendance_date', $dateStr)
                        ->where('is_present', true)
                        ->count() > 0 ? 1 : 0;
                }
            }

            $current->addDay();
        }

        return $attendanceData;
    }

    protected function calculateTotals($wastas)
    {
        $totals = [
            'wastas' => [],
            'labours' => [],
            'grand_total' => 0
        ];

        foreach ($wastas as $wasta) {
            // Wasta totals
            $presentDays = $wasta->attendances->where('is_present', true)->count();
            $totals['wastas'][$wasta->wasta_name] = [
                'present_days' => $presentDays,
                'total_amount' => $presentDays * $wasta->price
            ];
            $totals['grand_total'] += $presentDays * $wasta->price;

            // Labour totals
            foreach ($wasta->labours as $labour) {
                $presentDays = $labour->attendances->where('is_present', true)->count();
                $totals['labours'][$labour->labour_name] = [
                    'present_days' => $presentDays,
                    'total_amount' => $presentDays * $labour->price
                ];
                $totals['grand_total'] += $presentDays * $labour->price;
            }
        }

        return $totals;
    }

    protected function getDatesInRange($startDate, $endDate)
    {
        $dates = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }
}
