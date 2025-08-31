<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\Wager;
use App\Models\Wasta;
use App\Services\DataService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        [$payments, $raw_materials, $squareFootageBills, $expenses,] = $this->dataService->getData(
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
        $returns = $withoutServiceCharge['return'];


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
            'returns' => $returns
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
            'box9' => 'Returns'
        ];

        // ✅ Generate the PDF with data
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site PDF');
        $pdf->infoTable($headers, $data);
        $pdf->siteTableData($phaseData);
        $file_name = 'Site PDF_' . $data['site_name'] . '_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->Output($file_name, 'D');
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
        ];

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site Phase Report');
        $pdf->phaseTableData($headers, $phases, $phaseCosting);
        $file_name = 'Site_' . $phase->phase_name . "_" . "Phase" . '_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->Output($file_name, 'D');

    }


    public function showLedgerPdf(Request $request)
    {

        $date_filter = $request->input('date_filter');
        $site_id = $request->input('site_id');
        $supplier_id = $request->input('supplier_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $phase_id = $request->input('phase_id');

        // Check if attendance should be excluded
        $excludeAttendance = $request->input('exclude_attendance', false);

        if ($excludeAttendance) {
            // For supplier controller - exclude attendance data
            [$payments, $raw_materials, $squareFootageBills, $expenses] = $this->dataService->getData(
                $date_filter,
                $site_id,
                $supplier_id,
                $start_date,
                $end_date,
                $phase_id
            );

            $ledgers = $this->dataService->makeData(
                $payments,
                $raw_materials,
                $squareFootageBills,
                $expenses
            )->sortByDesc(function ($d) {
                return $d['created_at'];
            });
        } else {
            // For site and ledger controllers - include attendance data
            [$payments, $raw_materials, $squareFootageBills, $expenses, $attendances] = $this->dataService->getData(
                $date_filter,
                $site_id,
                $supplier_id,
                $start_date,
                $end_date,
                $phase_id
            );

            $ledgers = $this->dataService->makeData(
                $payments,
                $raw_materials,
                $squareFootageBills,
                $expenses,
                $attendances
            )->sortByDesc(function ($d) {
                return $d['created_at'];
            });
        }


        // Calculate balances
        $balances = $this->dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $effective_balance = $withoutServiceCharge['due'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];
        $service_charge_amount = $balances['service_charge_amount'];
        $returns = $withoutServiceCharge['return'];


        $ledgers = $ledgers->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 10);
        $pdf->SetTitle('Ledger');
        $pdf->ledgerTable($ledgers, $total_paid, $total_due, $total_balance, $effective_balance, $service_charge_amount, $returns);
        $file_name = 'Ledger_' . Carbon::now()->format('Y-m-d') . '.pdf';
        $pdf->Output();
        exit();

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
        $file_name = 'Payments_' . $supplier->name . '_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->Output($file_name, 'D');
    }

    public function showSitePaymentPdf(string $id)
    {

        $site_id = base64_decode($id);

        $site = Site::with([
            'payments' => function ($pay) {
                $pay->where('verified_by_admin', 1)
                    ->with(['supplier', 'site'])
                    ->where(function ($query) {
                        $query->whereNull('transaction_type')
                            ->orWhere('transaction_type', 0);
                    })
                    ->latest();
            }
        ])
            ->withSum([
                'payments' => function ($query) {
                    $query->where('verified_by_admin', 1)
                        ->where(function ($q) {
                            $q->whereNull('transaction_type')
                                ->orWhere('transaction_type', 0);
                        });
                }
            ], 'amount')
            ->find($site_id);


        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTitle('Site Payment');
        $pdf->sitePaymentTable($site);
        $file_name = 'Payments_' . $site->site_name . '_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->Output($file_name, 'D');
    }



    // public function generateAttendancePdf(Request $request)
    // {
    //     $site = Site::findOrFail($request->input('site_id'));

    //     // --- Date Filters ---
    //     $dateFilter = $request->input('date_filter', 'month');
    //     $monthYear = $request->input('monthYear', now()->format('Y-m'));
    //     $customStart = $request->input('custom_start', now()->startOfMonth()->format('Y-m-d'));
    //     $customEnd = $request->input('custom_end', now()->endOfMonth()->format('Y-m-d'));
    //     $specificDate = $request->input('specific_date', null);

    //     // Calculate date range based on filter type
    //     if ($dateFilter === 'month') {
    //         [$year, $month] = explode('-', $monthYear);
    //         $startDate = Carbon::create($year, $month, 1)->startOfDay();
    //         $endDate = $startDate->copy()->endOfMonth()->endOfDay();
    //         $dateRange = Carbon::parse($startDate)->format('F, Y');
    //     } elseif ($dateFilter === 'day' && $specificDate) {
    //         $startDate = Carbon::parse($specificDate)->startOfDay();
    //         $endDate = $startDate->copy()->endOfDay();
    //         $dateRange = Carbon::parse($specificDate)->format('M d, Y');
    //     } else {
    //         $startDate = Carbon::parse($customStart)->startOfDay();
    //         $endDate = Carbon::parse($customEnd)->endOfDay();
    //         $dateRange = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
    //     }

    //     // --- Additional Filters ---
    //     $typeFilter = $request->input('type');
    //     $wastaIdFilter = $request->input('wasta_id');
    //     $attendanceFilter = $request->input('attendance_filter');

    //     // --- Base setups query with attendance data ---
    //     $setupQuery = AttendanceSetup::with([
    //         'setupable',
    //         'attendances' => function ($q) use ($startDate, $endDate) {
    //             $q->whereBetween('attendance_date', [$startDate, $endDate])
    //                 ->orderBy('attendance_date', 'asc');
    //         }
    //     ])
    //         ->where('site_id', $site->id)
    //         ->when($typeFilter, function ($q) use ($typeFilter) {
    //             if ($typeFilter === 'wasta') {
    //                 $q->where('setupable_type', Wasta::class);
    //             } elseif ($typeFilter === 'wager') {
    //                 $q->where('setupable_type', Wager::class);
    //             }
    //         })
    //         ->when($wastaIdFilter, function ($q) use ($wastaIdFilter) {
    //             $q->where(function ($sub) use ($wastaIdFilter) {
    //                 $sub->where(function ($x) use ($wastaIdFilter) {
    //                     $x->where('setupable_type', Wasta::class)
    //                         ->where('setupable_id', $wastaIdFilter);
    //                 })->orWhere(function ($x) use ($wastaIdFilter) {
    //                     $x->where('setupable_type', Wager::class)
    //                         ->whereIn('setupable_id', Wager::where('wasta_id', $wastaIdFilter)->pluck('id'));
    //                 });
    //             });
    //         })
    //         ->orderBy('name');

    //     // --- Apply attendance filter ---
    //     if ($attendanceFilter && $attendanceFilter !== 'all') {
    //         $setupQuery->whereHas('attendances', function ($q) use ($startDate, $endDate, $attendanceFilter) {
    //             $q->whereBetween('attendance_date', [$startDate, $endDate]);
    //             if ($attendanceFilter === 'present') {
    //                 $q->where('is_present', 1);
    //             } elseif ($attendanceFilter === 'absent') {
    //                 $q->where('is_present', 0);
    //             }
    //         });
    //     }

    //     $setups = $setupQuery->get();

    //     // --- Workers transformation with detailed attendance ---
    //     $workers = $setups->map(function ($setup) use ($startDate, $endDate) {
    //         $attendances = $setup->attendances;
    //         $presentDays = $attendances->where('is_present', 1)->count();
    //         $absentDays = $attendances->where('is_present', 0)->count();
    //         $totalWorkingDays = $attendances->count();

    //         $presentAmount = $attendances->where('is_present', 1)->sum(function ($att) use ($setup) {
    //             return $att->price;
    //         });

    //         // Get attendance dates for detailed view
    //         $attendanceDates = $attendances->map(function ($att) {
    //             return [
    //                 'date' => Carbon::parse($att->attendance_date)->format('M d'),
    //                 'status' => $att->is_present ? 'P' : 'A',
    //                 'amount' => $att->is_present ? ($att->price) : 0
    //             ];
    //         });

    //         $type = class_basename($setup->setupable_type);

    //         return [
    //             'id' => $setup->id,
    //             'name' => $setup->name,
    //             'type' => $type,
    //             'count' => $setup->count,
    //             'price' => $setup->price,
    //             'present_days' => $presentDays,
    //             'absent_days' => $absentDays,
    //             'total_working_days' => $totalWorkingDays,
    //             'attendance_percentage' => $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100, 1) : 0,
    //             'total_amount' => $presentAmount,
    //             'wasta_id' => $type === 'Wasta' ? $setup->setupable_id : optional($setup->setupable)->wasta_id,
    //             'wasta_name' => $type === 'Wasta' ? optional($setup->setupable)->wasta_name : optional($setup->setupable->wasta)->wasta_name,
    //             'is_wasta' => $type === 'Wasta',
    //             'is_labor' => $type === 'Wager',
    //             'attendance_details' => $attendanceDates,
    //         ];
    //     });

    //     // --- Grouped workers for hierarchical display ---
    //     $wastasWithLabors = $workers->where('is_wasta', true)->map(function ($wasta) use ($workers) {
    //         $labors = $workers->where('is_labor', true)->where('wasta_id', $wasta['wasta_id'])->values();

    //         $totalContractorAmount = $wasta['total_amount'] + $labors->sum('total_amount');
    //         $totalContractorDays = $wasta['present_days'] + $labors->sum('present_days');

    //         return [
    //             'id' => $wasta['wasta_id'],
    //             'setup_id' => $wasta['id'],
    //             'name' => $wasta['wasta_name'],
    //             'is_wasta' => true,
    //             'price' => $wasta['price'],
    //             'present_days' => $wasta['present_days'],
    //             'absent_days' => $wasta['absent_days'],
    //             'attendance_percentage' => $wasta['attendance_percentage'],
    //             'total_amount' => $wasta['total_amount'],
    //             'labors' => $labors,
    //             'total_workers' => $labors->count() + 1,
    //             'total_contractor_amount' => $totalContractorAmount,
    //             'total_contractor_days' => $totalContractorDays,
    //             'attendance_details' => $wasta['attendance_details'],
    //         ];
    //     })->values();

    //     $directLabors = $workers->where('is_labor', true)->whereNull('wasta_id')->values();

    //     // Calculate totals
    //     $grandTotalAmount = $workers->sum('total_amount');
    //     $grandTotalDays = $workers->sum('present_days');
    //     $totalWorkers = $workers->count();

    //     // Create PDF with better formatting
    //     $pdf = new \FPDF('L', 'mm', 'A4'); // Landscape for more space
    //     $pdf->AddPage();
    //     $pdf->SetAutoPageBreak(true, 15);

    //     // === HEADER SECTION ===
    //     $pdf->SetFillColor(44, 62, 80); // Dark blue background
    //     $pdf->SetTextColor(255, 255, 255); // White text
    //     $pdf->SetFont('Arial', 'B', 16);
    //     $pdf->Cell(0, 15, 'ATTENDANCE REPORT' .  strtoupper($site->site_name), 0, 1, 'C', true);

    //     // === REPORT INFO SECTION ===
    //     $pdf->SetTextColor(0, 0, 0); // Black text
    //     $pdf->SetFillColor(236, 240, 241); // Light gray

    //     $pdf->SetFont('Arial', 'B', 10);
    //     $pdf->Cell(40, 8, 'Report Period:', 1, 0, 'L', true);
    //     $pdf->SetFont('Arial', '', 10);
    //     $pdf->Cell(60, 8, $dateRange, 1, 0, 'L', true);

    //     $pdf->SetFont('Arial', 'B', 10);
    //     $pdf->Cell(30, 8, 'Generated:', 1, 0, 'L', true);
    //     $pdf->SetFont('Arial', '', 10);
    //     $pdf->Cell(0, 8, date('M d, Y H:i'), 1, 1, 'L', true);

    //     $pdf->Ln(10);

    //     // === SUMMARY SECTION ===
    //     $pdf->SetFont('Arial', 'B', 12);
    //     $pdf->Cell(0, 8, 'SUMMARY STATISTICS', 0, 1, 'L');
    //     $pdf->Ln(2);

    //     // Summary boxes
    //     $pdf->SetFont('Arial', 'B', 10);
    //     $pdf->Cell(92, 10, 'TOTAL WORKERS', 1, 0, 'C', true);
    //     $pdf->Cell(92, 10, 'TOTAL PRESENT DAYS', 1, 0, 'C', true);
    //     $pdf->Cell(92, 10, 'TOTAL AMOUNT', 1, 1, 'C', true);

    //     $pdf->SetFont('Arial', 'B', 14);
    //     $pdf->SetFillColor(230, 230, 230);
    //     $pdf->SetTextColor(0, 0, 0);
    //     $pdf->Cell(92, 15, $totalWorkers, 1, 0, 'C', true);
    //     $pdf->Cell(92, 15, $grandTotalDays, 1, 0, 'C', true);
    //     $pdf->Cell(92, 15, number_format($grandTotalAmount, 0), 1, 1, 'C', true);

    //     $pdf->Ln(18);

    //     // === DETAILED ATTENDANCE SECTION ===
    //     $pdf->SetFont('Arial', 'B', 12);
    //     $pdf->Cell(0, 8, 'DETAILED ATTENDANCE', 0, 1, 'L');
    //     $pdf->Ln(2);

    //     // Table headers
    //     $pdf->SetFillColor(52, 73, 94); // Dark header
    //     $pdf->SetTextColor(255, 255, 255);
    //     $pdf->SetFont('Arial', 'B', 10);

    //     $pdf->Cell(15, 10, 'S.No', 1, 0, 'C', true);
    //     $pdf->Cell(50, 10, 'Worker Name', 1, 0, 'L', true);
    //     $pdf->Cell(20, 10, 'Type', 1, 0, 'C', true);
    //     $pdf->Cell(20, 10, 'Present', 1, 0, 'C', true);
    //     $pdf->Cell(20, 10, 'Absent', 1, 0, 'C', true);
    //     $pdf->Cell(25, 10, 'Attendance %', 1, 0, 'C', true);
    //     $pdf->Cell(25, 10, 'Rate/Day', 1, 0, 'C', true);
    //     $pdf->Cell(30, 10, 'Total Amount', 1, 0, 'C', true);
    //     $pdf->Cell(40, 10, 'Recent Attendance', 1, 1, 'C', true);

    //     $pdf->SetTextColor(0, 0, 0);
    //     $pdf->SetFont('Arial', '', 9);

    //     $serialNumber = 1;

    //     // === CONTRACTORS AND THEIR LABORERS ===
    //     foreach ($wastasWithLabors as $wasta) {
    //         // Contractor row
    //         $pdf->SetFillColor(220, 220, 220); // Light gray for contractor
    //         $pdf->SetFont('Arial', 'B', 10);

    //         $pdf->Cell(15, 8, $serialNumber++, 1, 0, 'C', true);
    //         $pdf->Cell(50, 8, $wasta['name'] . ' (Contractor)', 1, 0, 'L', true);
    //         $pdf->Cell(20, 8, 'Wasta', 1, 0, 'C', true);
    //         $pdf->Cell(20, 8, $wasta['present_days'], 1, 0, 'C', true);
    //         $pdf->Cell(20, 8, $wasta['absent_days'], 1, 0, 'C', true);
    //         $pdf->Cell(25, 8, $wasta['attendance_percentage'] . '%', 1, 0, 'C', true);
    //         $pdf->Cell(25, 8, number_format($wasta['price'], 0), 1, 0, 'C', true);
    //         $pdf->Cell(30, 8, number_format($wasta['total_amount'], 0), 1, 0, 'C', true);

    //         // Recent attendance (last 5 days)
    //         $recentAttendance = array_slice($wasta['attendance_details']->toArray(), -5);
    //         $attendanceStr = '';
    //         foreach ($recentAttendance as $att) {
    //             $attendanceStr .= $att['date'] . ':' . $att['status'] . ' ';
    //         }
    //         $pdf->Cell(40, 8, trim($attendanceStr), 1, 1, 'L', true);

    //         // Laborers under this contractor
    //         $pdf->SetFont('Arial', '', 9);
    //         foreach ($wasta['labors'] as $labor) {
    //             $pdf->SetFillColor(255, 255, 255); // White for laborers

    //             $pdf->Cell(15, 7, $serialNumber++, 1, 0, 'C');
    //             $pdf->Cell(50, 7, '  - ' . $labor['name'], 1, 0, 'L');
    //             $pdf->Cell(20, 7, 'Labor', 1, 0, 'C');
    //             $pdf->Cell(20, 7, $labor['present_days'], 1, 0, 'C');
    //             $pdf->Cell(20, 7, $labor['absent_days'], 1, 0, 'C');
    //             $pdf->Cell(25, 7, $labor['attendance_percentage'] . '%', 1, 0, 'C');
    //             $pdf->Cell(25, 7, number_format($labor['price'], 0), 1, 0, 'C');
    //             $pdf->Cell(30, 7, number_format($labor['total_amount'], 0), 1, 0, 'C');

    //             // Recent attendance for laborer
    //             $recentLaborAttendance = array_slice($labor['attendance_details']->toArray(), -5);
    //             $laborAttendanceStr = '';
    //             foreach ($recentLaborAttendance as $att) {
    //                 $laborAttendanceStr .= $att['date'] . ':' . $att['status'] . ' ';
    //             }
    //             $pdf->Cell(40, 7, trim($laborAttendanceStr), 1, 1, 'L');
    //         }

    //         // Contractor subtotal
    //         $pdf->SetFillColor(240, 240, 240);
    //         $pdf->SetFont('Arial', 'B', 9);
    //         $pdf->Cell(165, 8, 'Subtotal for ' . $wasta['name'] . ' (Contractor + ' . count($wasta['labors']) . ' labors)', 1, 0, 'R', true);
    //         $pdf->Cell(30, 8, number_format($wasta['total_contractor_amount'], 0), 1, 1, 'C', true);

    //         $pdf->Ln(3);
    //     }

    //     // === DIRECT LABORERS SECTION ===
    //     if ($directLabors->count() > 0) {
    //         $pdf->SetFillColor(231, 76, 60); // Red header
    //         $pdf->SetTextColor(255, 255, 255);
    //         $pdf->SetFont('Arial', 'B', 12);
    //         $pdf->Cell(0, 10, 'DIRECT LABORERS (No Contractor)', 0, 1, 'L', true);
    //         $pdf->Ln(2);

    //         $pdf->SetTextColor(0, 0, 0);
    //         $pdf->SetFont('Arial', '', 9);

    //         foreach ($directLabors as $labor) {
    //             $pdf->SetFillColor(255, 255, 255);

    //             $pdf->Cell(15, 7, $serialNumber++, 1, 0, 'C');
    //             $pdf->Cell(50, 7, $labor['name'], 1, 0, 'L');
    //             $pdf->Cell(20, 7, 'Labor', 1, 0, 'C');
    //             $pdf->Cell(20, 7, $labor['present_days'], 1, 0, 'C');
    //             $pdf->Cell(20, 7, $labor['absent_days'], 1, 0, 'C');
    //             $pdf->Cell(25, 7, $labor['attendance_percentage'] . '%', 1, 0, 'C');
    //             $pdf->Cell(25, 7, number_format($labor['price'], 0), 1, 0, 'C');
    //             $pdf->Cell(30, 7, number_format($labor['total_amount'], 0), 1, 0, 'C');

    //             // Recent attendance
    //             $recentAttendance = array_slice($labor['attendance_details']->toArray(), -5);
    //             $attendanceStr = '';
    //             foreach ($recentAttendance as $att) {
    //                 $attendanceStr .= $att['date'] . ':' . $att['status'] . ' ';
    //             }
    //             $pdf->Cell(40, 7, trim($attendanceStr), 1, 1, 'L');
    //         }
    //     }

    //     // === GRAND TOTAL ===
    //     $pdf->Ln(10);
    //     $pdf->SetFillColor(44, 62, 80); // Dark blue
    //     $pdf->SetTextColor(255, 255, 255);
    //     $pdf->SetFont('Arial', 'B', 12);
    //     $pdf->Cell(165, 10, 'GRAND TOTAL', 1, 0, 'R', true);
    //     $pdf->Cell(30, 10, number_format($grandTotalAmount, 0), 1, 1, 'C', true);

    //     // === FOOTER ===
    //     $pdf->Ln(8);
    //     $pdf->SetTextColor(100, 100, 100);
    //     $pdf->SetFont('Arial', 'I', 8);
    //     $pdf->Cell(0, 5, 'Report generated on ' . date('F d, Y \a\t H:i:s'), 0, 1, 'L');
    //     $pdf->Cell(0, 5, 'Legend: P = Present, A = Absent | Recent attendance shows last 5 working days', 0, 1, 'L');

    //     // Generate filename
    //     $filename = 'Attendance_' . str_replace(' ', '_', $site->site_name) . '_' .
    //         ($dateFilter === 'custom'
    //             ? $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd')
    //             : $startDate->format('F_Y')) . '.pdf';

    //     // Output the PDF
    //     $pdf->Output();
    //     exit();
    // }


    public function generateAttendancePdf(Request $request)
    {


        $siteId = $request->site_id;

        // Handle month filter - if month_filter is provided, override start_date and end_date
        if ($request->filled('month_filter')) {
            $monthYear = explode('-', $request->month_filter);
            $year = $monthYear[0];
            $month = $monthYear[1];

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        } else {
            // Use provided dates or default to current month
            $startDate = Carbon::parse($request->start_date ?? now()->startOfMonth());
            $endDate = Carbon::parse($request->end_date ?? now()->endOfMonth());
        }

        // Ensure end date is not before start date
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
        }

        $site = Site::findOrFail($siteId);

        // Base query for wastas with eager loading scoped to site
        $wastaQuery = Wasta::whereHas('attendanceSetups', function ($q) use ($site) {
            $q->where('site_id', $site->id);
        })
            ->with([
                'attendanceSetups' => fn($q) => $q->where('site_id', $site->id)
                    ->with(['attendances' => fn($a) => $a->whereBetween('attendance_date', [$startDate, $endDate])]),
                'wagers.attendanceSetups' => fn($q) => $q->where('site_id', $site->id)
                    ->with(['attendances' => fn($a) => $a->whereBetween('attendance_date', [$startDate, $endDate])]),
            ]);

        // Apply wasta filter if selected
        if ($request->filled('wasta_id')) {
            $wastaQuery->where('id', $request->wasta_id);
        }

        $wastas = $wastaQuery->get();

        // Base query for independent workers (no wasta) scoped to site
        $independentQuery = Wager::whereNull('wasta_id')
            ->whereHas('attendanceSetups', function ($q) use ($site) {
                $q->where('site_id', $site->id);
            })
            ->with([
                'attendanceSetups' => fn($q) => $q->where('site_id', $site->id)
                    ->with(['attendances' => fn($a) => $a->whereBetween('attendance_date', [$startDate, $endDate])]),
            ]);

        // Apply wager filter if selected (for independent workers)
        if ($request->filled('wager_id')) {
            $independentQuery->where('id', $request->wager_id);
        }

        $independents = $independentQuery->get();

        $dates = CarbonPeriod::create($startDate, $endDate);
        $dateArray = iterator_to_array($dates);
        $totalDays = count($dateArray);

        // Process data for display with filtering
        $attendanceData = [];
        $grandTotalDays = 0;
        $grandTotalAmount = 0;

        // Process Wastas and their workers
        foreach ($wastas as $wasta) {
            // Skip if we're filtering by worker type and it's not contractors
            if ($request->worker_type === 'workers' || $request->worker_type === 'independents') {
                continue;
            }

            $dailyAttendance = [];
            $presentCount = 0;

            foreach ($dateArray as $date) {
                $present = $wasta->attendanceSetups->flatMap->attendances
                    ->firstWhere('attendance_date', $date->format('Y-m-d'));
                $isPresent = $present && $present->is_present;

                // Skip if filtering by attendance status
                if ($request->attendance_status === 'present' && !$isPresent)
                    continue;
                if ($request->attendance_status === 'absent' && $isPresent)
                    continue;

                $dailyAttendance[] = $isPresent;
                if ($isPresent)
                    $presentCount++;
            }

            // Skip if no attendance matches the filter
            if ($request->attendance_status === 'present' && $presentCount === 0)
                continue;
            if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                continue;

            $amount = $wasta->price * $presentCount;
            $grandTotalDays += $presentCount;
            $grandTotalAmount += $amount;

            $attendanceData[] = [
                'id' => 'wasta_' . $wasta->id,
                'name' => $wasta->wasta_name,
                'type' => 'Contractor',
                'rate' => $wasta->price,
                'daily' => $dailyAttendance,
                'days' => $presentCount,
                'amount' => $amount,
                'is_contractor' => true,
                'parent_id' => null
            ];

            // Process wagers under this wasta
            foreach ($wasta->wagers as $wager) {
                // Skip if we're filtering by worker type and it's not workers
                if ($request->worker_type === 'contractors' || $request->worker_type === 'independents') {
                    continue;
                }

                // Apply wager filter if selected
                if ($request->filled('wager_id') && $wager->id != $request->wager_id) {
                    continue;
                }

                $dailyAttendance = [];
                $presentCount = 0;

                foreach ($dateArray as $date) {
                    $present = $wager->attendanceSetups->flatMap->attendances
                        ->firstWhere('attendance_date', $date->format('Y-m-d'));
                    $isPresent = $present && $present->is_present;

                    // Skip if filtering by attendance status
                    if ($request->attendance_status === 'present' && !$isPresent)
                        continue;
                    if ($request->attendance_status === 'absent' && $isPresent)
                        continue;

                    $dailyAttendance[] = $isPresent;
                    if ($isPresent)
                        $presentCount++;
                }

                // Skip if no attendance matches the filter
                if ($request->attendance_status === 'present' && $presentCount === 0)
                    continue;
                if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                    continue;

                $amount = $wager->price * $presentCount;
                $grandTotalDays += $presentCount;
                $grandTotalAmount += $amount;

                $attendanceData[] = [
                    'id' => 'wager_' . $wager->id,
                    'name' => $wager->wager_name,
                    'type' => 'Worker',
                    'rate' => $wager->price,
                    'daily' => $dailyAttendance,
                    'days' => $presentCount,
                    'amount' => $amount,
                    'is_contractor' => false,
                    'parent_id' => 'wasta_' . $wasta->id
                ];
            }
        }

        // Process independent workers
        foreach ($independents as $worker) {
            // Skip if we're filtering by worker type and it's not independents
            if ($request->worker_type === 'contractors' || $request->worker_type === 'workers') {
                continue;
            }

            $dailyAttendance = [];
            $presentCount = 0;

            foreach ($dateArray as $date) {
                $present = $worker->attendanceSetups->flatMap->attendances
                    ->firstWhere('attendance_date', $date->format('Y-m-d'));
                $isPresent = $present && $present->is_present;

                // Skip if filtering by attendance status
                if ($request->attendance_status === 'present' && !$isPresent)
                    continue;
                if ($request->attendance_status === 'absent' && $isPresent)
                    continue;

                $dailyAttendance[] = $isPresent;
                if ($isPresent)
                    $presentCount++;
            }

            // Skip if no attendance matches the filter
            if ($request->attendance_status === 'present' && $presentCount === 0)
                continue;
            if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                continue;

            $amount = $worker->price * $presentCount;
            $grandTotalDays += $presentCount;
            $grandTotalAmount += $amount;

            $attendanceData[] = [
                'id' => 'independent_' . $worker->id,
                'name' => $worker->wager_name,
                'type' => 'Independent',
                'rate' => $worker->price,
                'daily' => $dailyAttendance,
                'days' => $presentCount,
                'amount' => $amount,
                'is_contractor' => false,
                'parent_id' => null
            ];
        }

        // Calculate totals for statistics (after filtering)
        $totalWorkers = count(array_filter($attendanceData, fn($item) => !$item['is_contractor']));
        $totalContractors = count(array_filter($attendanceData, fn($item) => $item['is_contractor']));

        // === Calculate dynamic column widths based on number of days ===
        $pageWidth = 277; // A4 landscape usable width (297-20 margins)
        $nameWidth = 45;
        $rateWidth = 20;
        $totalDaysWidth = 18;
        $totalAmountWidth = 25;

        // Calculate remaining width for date columns
        $fixedWidth = $nameWidth + $rateWidth + $totalDaysWidth + $totalAmountWidth;
        $availableForDates = $pageWidth - $fixedWidth;
        $dayColumnWidth = min(8, $availableForDates / $totalDays); // Max 8mm per day

        // If days don't fit, we'll need to split into multiple tables or reduce day column width
        if ($dayColumnWidth < 4) {
            $dayColumnWidth = 4; // Minimum readable width
            // Consider splitting large date ranges into multiple pages
        }

        // === Initialize PDF ===
        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->SetCreator('Attendance Management System');
        $pdf->SetAuthor($site->site_name);
        $pdf->SetTitle("Attendance Report - {$site->site_name}");
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // === COMPACT HEADER ===
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 10, 'ATTENDANCE REPORT - ' . strtoupper($site->site_name), 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(127, 140, 141);
        $pdf->Cell(0, 6, "Period: {$startDate->format('M d, Y')} - {$endDate->format('M d, Y')}", 0, 1, 'C');

        // Add filter information to PDF header
        $filterInfo = [];
        if ($request->filled('worker_type') && $request->worker_type !== 'all') {
            $filterInfo[] = "Type: " . ucfirst($request->worker_type);
        }
        if ($request->filled('attendance_status') && $request->attendance_status !== 'all') {
            $filterInfo[] = "Status: " . ucfirst($request->attendance_status);
        }
        if ($request->filled('search')) {
            $filterInfo[] = "Search: " . $request->search;
        }

        if (!empty($filterInfo)) {
            $pdf->Cell(0, 4, "Filters Applied: " . implode(' | ', $filterInfo), 0, 1, 'C');
        }

        $pdf->Ln(3);

        // === COMPACT SUMMARY ===
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(52, 152, 219);
        $pdf->SetTextColor(255, 255, 255);

        $summaryWidth = $pageWidth / 4;
        $pdf->Cell($summaryWidth, 6, "Workers: " . ($totalWorkers + $totalContractors), 1, 0, 'C', true);
        $pdf->Cell($summaryWidth, 6, "Contractors: $totalContractors", 1, 0, 'C', true);
        $pdf->Cell($summaryWidth, 6, "Total Days: $grandTotalDays", 1, 0, 'C', true);
        $pdf->Cell($summaryWidth, 6, "Amount: " . number_format($grandTotalAmount, 2), 1, 1, 'C', true);
        $pdf->Ln(5);

        // === TABLE HEADER ===
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(44, 62, 80);

        // First row: Name, Rate, Date headers, Totals
        $pdf->Cell($nameWidth, 10, 'Name', 1, 0, 'C', true);
        $pdf->Cell($rateWidth, 10, 'Rate', 1, 0, 'C', true);

        // Date columns
        foreach ($dateArray as $date) {
            $pdf->Cell($dayColumnWidth, 10, $date->format('d'), 1, 0, 'C', true);
        }

        $pdf->Cell($totalDaysWidth, 10, 'Days', 1, 0, 'C', true);
        $pdf->Cell($totalAmountWidth, 10, 'Amount', 1, 1, 'C', true);

        // Second row: Month names for date columns (if space allows)
        if ($dayColumnWidth >= 6) {
            $pdf->SetFont('helvetica', '', 6);
            $pdf->Cell($nameWidth, 5, '', 1, 0, 'C', true);
            $pdf->Cell($rateWidth, 5, '', 1, 0, 'C', true);

            $currentMonth = '';
            foreach ($dateArray as $date) {
                $monthAbbr = $date->format('M');
                $display = ($monthAbbr != $currentMonth) ? $monthAbbr : '';
                $currentMonth = $monthAbbr;
                $pdf->Cell($dayColumnWidth, 5, $display, 1, 0, 'C', true);
            }

            $pdf->Cell($totalDaysWidth, 5, '', 1, 0, 'C', true);
            $pdf->Cell($totalAmountWidth, 5, '', 1, 1, 'C', true);
        }

        // === DATA ROWS ===
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetDrawColor(189, 195, 199);

        $rowCount = 0;
        foreach ($attendanceData as $row) {
            $rowCount++;

            // Check for page break
            if ($pdf->GetY() > 180) {
                $pdf->AddPage();

                // Repeat header
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetFillColor(52, 73, 94);
                $pdf->SetTextColor(255, 255, 255);

                $pdf->Cell($nameWidth, 10, 'Name', 1, 0, 'C', true);
                $pdf->Cell($rateWidth, 10, 'Rate', 1, 0, 'C', true);

                foreach ($dateArray as $date) {
                    $pdf->Cell($dayColumnWidth, 10, $date->format('d'), 1, 0, 'C', true);
                }

                $pdf->Cell($totalDaysWidth, 10, 'Days', 1, 0, 'C', true);
                $pdf->Cell($totalAmountWidth, 10, 'Amount', 1, 1, 'C', true);

                $pdf->SetTextColor(44, 62, 80);
            }

            // Row styling
            if ($rowCount % 2 == 0) {
                $pdf->SetFillColor(248, 249, 250);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }

            if ($row['is_contractor']) {
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetFillColor(231, 243, 255);
            } else {
                $pdf->SetFont('helvetica', '', 7);
            }

            // Name (truncated if necessary)
            $displayName = strlen($row['name']) > 25 ? substr($row['name'], 0, 22) . '...' : $row['name'];
            $pdf->Cell($nameWidth, 8, $displayName, 1, 0, 'L', true);

            // Rate
            $pdf->Cell($rateWidth, 8, number_format($row['rate'], 2), 1, 0, 'R', true);

            // Daily attendance
            foreach ($row['daily'] as $isPresent) {
                $symbol = $isPresent ? 'P' : 'A';
                if ($isPresent) {
                    $pdf->SetTextColor(34, 139, 34); // Green for Present
                } else {
                    $pdf->SetTextColor(220, 53, 69); // Red for Absent
                }
                $pdf->Cell($dayColumnWidth, 8, $symbol, 1, 0, 'C', true);
            }

            // Reset text color
            $pdf->SetTextColor(44, 62, 80);

            // Total days and amount
            $pdf->Cell($totalDaysWidth, 8, $row['days'], 1, 0, 'C', true);
            $pdf->Cell($totalAmountWidth, 8, number_format($row['amount'], 2), 1, 1, 'R', true);
        }

        // === TOTALS ROW ===
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(44, 62, 80);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell($nameWidth + $rateWidth, 8, 'GRAND TOTAL', 1, 0, 'C', true);

        // Empty cells for date columns
        foreach ($dateArray as $date) {
            $pdf->Cell($dayColumnWidth, 8, '', 1, 0, 'C', true);
        }

        $pdf->Cell($totalDaysWidth, 8, $grandTotalDays, 1, 0, 'C', true);
        $pdf->Cell($totalAmountWidth, 8, number_format($grandTotalAmount, 2), 1, 1, 'R', true);

        // === FOOTER ===
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(127, 140, 141);
        $pdf->Cell(0, 4, 'Generated: ' . now()->format('M d, Y g:i A') . ' | Records: ' . count($attendanceData) . ' | P=Present, A=Absent', 0, 1, 'C');

        // === OUTPUT ===
        $filename = "Attendance_{$site->site_name}_{$startDate->format('M-d')}_to_{$endDate->format('M-d')}.pdf";
        $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);

        $pdf->Output();
        exit();
    }


}
