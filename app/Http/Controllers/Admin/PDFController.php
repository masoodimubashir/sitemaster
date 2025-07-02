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


    // public function showSitePdf(string $id)
    // {

    //     $site_id = base64_decode($id);
    //     $dateFilter = 'lifetime';
    //     $supplier_id = 'all';
    //     $wager_id = 'all';
    //     $startDate = 'start_date';
    //     $endDate = 'end_date';

    //     // Get filtered collections from your data service
    //     [$payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours] = $this->dataService->getData(
    //         $dateFilter,
    //         $site_id,
    //         $supplier_id,
    //         $wager_id,
    //         $startDate,
    //         $endDate
    //     );

    //     dd($payments, $raw_materials, $squareFootageBills, $expenses, $wastas, $labours);

    //     // Merge and sort all financial data
    //     $ledgers = $this->dataService->makeData(
    //         $payments,
    //         $raw_materials,
    //         $squareFootageBills,
    //         $expenses,
    //         $wastas,
    //         $labours

    //     )->sortByDesc(fn($entry) => $entry['created_at']);

    //     dd($ledgers);

    //     // Group ledgers by phase
    //     $ledgersGroupedByPhase = $ledgers->groupBy('phase');

    //     // Fetch site info for the PDF header
    //     $site = Site::findOrFail($site_id);

    //     $phaseData = [];

    //     foreach ($ledgersGroupedByPhase as $phaseName => $records) {

    //         // Sum totals by category (using debit for costs)
    //         $construction_total = $records->where('category', 'Material')->sum('debit');
    //         $square_total = $records->where('category', 'SQFT')->sum('debit');
    //         $expenses_total = $records->where('category', 'Expense')->sum('debit');
    //         $wager_total = $records->where('category', 'Wager')->sum('debit');
    //         $wasta_total = $records->where('category', 'Wasta')->sum('debit');
    //         $labour_total = $records->where('category', 'Labour')->sum('debit');
    //         $payments_total = $records->where('category', 'Payment')->sum('credit');

    //         $subtotal = $construction_total + $square_total + $expenses_total + $wager_total + $wasta_total + $labour_total;
    //         $withService = ($subtotal * $site->service_charge / 100) + $subtotal;

    //         $phaseData[] = [
    //             'phase' => $phaseName,
    //             'site_service_charge' => $site->service_charge,
    //             'construction_total_amount' => $construction_total,
    //             'square_footage_total_amount' => $square_total,
    //             'daily_expenses_total_amount' => $expenses_total,
    //             'daily_wagers_total_amount' => $wager_total,
    //             'daily_wastas_total_amount' => $wasta_total,
    //             'daily_labours_total_amount' => $labour_total,
    //             'total_payment_amount' => $payments_total,
    //             'phase_total' => $subtotal,
    //             'phase_total_with_service_charge' => $withService,
    //             'construction_material_billings' => $records->where('category', 'Material'),
    //             'square_footage_bills' => $records->where('category', 'SQFT'),
    //             'daily_expenses' => $records->where('category', 'Expense'),
    //             'daily_wagers' => $records->where('category', 'Wager'),
    //             'daily_wastas' => $records->where('category', 'Wasta'),
    //             'daily_labours' => $records->where('category', 'Labour'),
    //         ];
    //     }

    //     // Calculate grand totals
    //     $grandTotal = collect($phaseData)->sum('phase_total_with_service_charge');
    //     $totalSupplierPaymentAmount = $ledgers->sum(fn($p) => floatval($p['credit'] ?? 0));

    //     $balances = $this->dataService->calculateAllBalances($ledgers);


    //     $withoutServiceCharge = $balances['without_service_charge'];
    //     $withServiceCharge = $balances['with_service_charge'];

    //     // Prepare header data for PDF
    //     $data = [
    //         'site_name' => $site->site_name,
    //         'contact_no' => $site->contact_no,
    //         'service_charge' => $site->service_charge,
    //         'balance' => $grandTotal - $totalSupplierPaymentAmount,
    //         'site_owner_name' => $site->site_owner_name,
    //         'location' => $site->location,
    //         'total_balance' => $withServiceCharge['balance'],
    //         'total_due' => $withServiceCharge['due'],
    //         'effective_balance' => $withoutServiceCharge['due'],
    //         'total_paid' => $withServiceCharge['paid'],
    //     ];

    //     $headers = [
    //         'box1' => 'Site Name',
    //         'box2' => 'Contact No',
    //         'box3' => 'Service Charge',
    //         'box4' => 'Balance',
    //         'box5' => 'Site Owner',
    //         'box6' => 'Location',
    //         'box7' => 'Debit',
    //         'box8' => 'Credit',
    //     ];

    //     // Generate PDF
    //     $pdf = new PDF();
    //     $pdf->AliasNbPages();
    //     $pdf->AddPage();
    //     $pdf->SetFont('Times', '', 12);
    //     $pdf->SetTitle('Site Info');
    //     $pdf->infoTable($headers, $data);
    //     $pdf->siteTableData($phaseData);
    //     $pdf->Output();
    //     exit;
    // }


    // public function showSitePdf(string $id)
    // {

    //     $site_id = base64_decode($id);

    //     $site = Site::with([
    //         'phases' => function ($query) {
    //             $query->whereNull('deleted_at');
    //         },
    //         'phases.constructionMaterialBillings' => function ($query) {
    //             $query->with('supplier')
    //                 ->where('verified_by_admin', 1)
    //                 ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
    //                 ->whereNull('deleted_at')
    //                 ->latest();
    //         },
    //         'phases.squareFootageBills' => function ($query) {
    //             $query->with('supplier')
    //                 ->where('verified_by_admin', 1)
    //                 ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
    //                 ->whereNull('deleted_at')
    //                 ->latest();
    //         },
    //         'phases.dailyWagers' => function ($query) {
    //             $query->with(['wagerAttendances', 'supplier'])
    //                 ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
    //                 ->whereNull('deleted_at')
    //                 ->latest();
    //         },
    //         'phases.dailyExpenses' => function ($query) {
    //             $query->whereNull('deleted_at');
    //         },
    //         'phases.wagerAttendances' => function ($query) {
    //             $query->with('dailyWager.supplier')
    //                 ->whereHas('dailyWager.supplier', fn($q) => $q->whereNull('deleted_at'))
    //                 ->whereNull('deleted_at')
    //                 ->latest();
    //         },
    //         'payments' => function ($query) {
    //             $query->where('verified_by_admin', 1);
    //         },
    //     ])
    //     ->find($site_id);

    //     $totalSupplierPaymentAmount = $site->payments()
    //     ->where('verified_by_admin', 1)
    //     ->sum('amount');

    //     $siteData = [
    //         'site' => $site,
    //         'phases' => []
    //     ];

    //     foreach ($site->phases as $phase) {

    //         // Calculate totals for each phase
    //         $construction_total = $phase->constructionMaterialBillings->sum('amount');
    //         $daily_expenses_total = $phase->dailyExpenses->sum('price');

    //         foreach ($phase->dailyWagers as $wager) {
    //             $phase->daily_wagers_total_amount += $wager->wager_total;
    //         }

    //         $square_footage_total = $phase->squareFootageBills->reduce(function ($carry, $bill) {
    //             return $carry + ($bill->price * $bill->multiplier);
    //         }, 0);

    //         $daily_wagers_total = $phase->daily_wagers_total_amount;

    //         $phase_total = $construction_total + $daily_expenses_total + $daily_wagers_total + $square_footage_total;
    //         $total_with_service_charge = ($phase_total * $site->service_charge / 100) + $phase_total;

    //         $siteData['phases'][] = [
    //             'phase' => $phase->phase_name,
    //             'site_service_charge' => $site->service_charge,
    //             'construction_total_amount' => $construction_total,
    //             'daily_expenses_total_amount' => $daily_expenses_total,
    //             'daily_wagers_total_amount' => $daily_wagers_total,
    //             'square_footage_total_amount' => $square_footage_total,
    //             'phase_total' => $phase_total,
    //             'phase_total_with_service_charge' => $total_with_service_charge,
    //             'construction_material_billings' => $phase->constructionMaterialBillings,
    //             'daily_expenses' => $phase->dailyExpenses,
    //             'daily_wagers' => $phase->dailyWagers,
    //             'square_footage_bills' => $phase->squareFootageBills,
    //             'wager_attendances' => $phase->wagerAttendances,
    //         ];
    //     }

    //     // Optionally calculate the grand total for the site
    //     $siteData['grand_total_amount'] = array_reduce($siteData['phases'], function ($carry, $phase): mixed {
    //         return $carry + $phase['phase_total_with_service_charge'];
    //     }, 0);


    //     $data = [
    //         'site_name' => $site->site_name,
    //         'contact_no' => $site->contact_no,
    //         'service_charge' => $site->service_charge,
    //         'balance' => $siteData['grand_total_amount'] - $totalSupplierPaymentAmount,
    //         'site_owner_name' => $site->site_owner_name,
    //         'location' => $site->location,
    //         'debit' =>  $siteData['grand_total_amount'],
    //         'credit' => $totalSupplierPaymentAmount,
    //     ];

    //     $headers = [
    //         'box1' => 'Site Name',
    //         'box2' => 'Conatct No',
    //         'box3' => 'Service Charge',
    //         'box4' => 'Balance',
    //         'box5' => 'Site Owner',
    //         'box6' => 'Location',
    //         'box7' => 'Debit',
    //         'box8' => 'Credit',
    //     ];

    //     $pdf = new PDF();
    //     $pdf->AliasNbPages();
    //     $pdf->AddPage();
    //     $pdf->SetFont('Times', '', 12);
    //     $pdf->SetTitle('Site Info');
    //     $pdf->infoTable($headers, $data);
    //     $pdf->siteTableData($siteData['phases']);
    //     $pdf->Output();
    //     exit;
    // }


    // public function showPhasePdf(string $id)
    // {
    //     // Decode the phase ID
    //     $phase_id = base64_decode($id);
    //     $phase = Phase::with('site')->findOrFail($phase_id);
    //     $site = $phase->site;

    //     // Define filter parameters (you can modify these to accept from request if needed)
    //     $dateFilter = 'today';
    //     $supplier_id = 'all';
    //     $wager_id = 'all';
    //     $startDate = 'start_date';
    //     $endDate = 'end_date';

    //     // Get filtered collections from data service
    //     [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers, $wastas, $labours] = $this->dataService->getData(
    //         $dateFilter,
    //         $site->id,
    //         $supplier_id,
    //         $wager_id,
    //         $startDate,
    //         $endDate
    //     );

    //     // Combine all entries into one collection
    //     $ledgers = $this->dataService->makeData(
    //         $payments,
    //         $raw_materials,
    //         $squareFootageBills,
    //         $expenses,
    //         $wagers,
    //         $wastas,
    //         $labours
    //     )->filter(fn($entry) => $entry['phase_id'] == $phase_id)
    //         ->sortByDesc(fn($entry) => $entry['created_at']);

    //     // Group all ledger entries under this single phase
    //     $records = $ledgers;

    //     $construction_total = $records->where('category', 'Material')->sum('debit');
    //     $square_total = $records->where('category', 'SQFT')->sum('debit');
    //     $expenses_total = $records->where('category', 'Expense')->sum('debit');
    //     $wager_total = $records->where('category', 'Wager')->sum('debit');
    //     $wasta_total = $records->where('category', 'Wasta')->sum('debit');
    //     $labour_total = $records->where('category', 'Labour')->sum('debit');
    //     $payments_total = $records->where('category', 'Payment')->sum('credit');

    //     $subtotal = $construction_total + $square_total + $expenses_total + $wager_total + $wasta_total + $labour_total;
    //     $withService = ($subtotal * $site->service_charge / 100) + $subtotal;

    //     $phaseData = [[
    //         'phase' => $phase->phase_name,
    //         'site_service_charge' => $site->service_charge,
    //         'construction_total_amount' => $construction_total,
    //         'square_footage_total_amount' => $square_total,
    //         'daily_expenses_total_amount' => $expenses_total,
    //         'daily_wagers_total_amount' => $wager_total,
    //         'daily_wastas_total_amount' => $wasta_total,
    //         'daily_labours_total_amount' => $labour_total,
    //         'total_payment_amount' => $payments_total,
    //         'phase_total' => $subtotal,
    //         'phase_total_with_service_charge' => $withService,
    //         'construction_material_billings' => $records->where('category', 'Material'),
    //         'square_footage_bills' => $records->where('category', 'SQFT'),
    //         'daily_expenses' => $records->where('category', 'Expense'),
    //         'daily_wagers' => $records->where('category', 'Wager'),
    //         'daily_wastas' => $records->where('category', 'Wasta'),
    //         'daily_labours' => $records->where('category', 'Labour'),
    //     ]];

    //     // Header info for this one phase
    //     $data = [
    //         'phase_name' => $phase->phase_name,
    //         'site_name' => $site->site_name,
    //         'contact_no' => $site->contact_no,
    //         'service_charge' => $site->service_charge,
    //         'balance' => $withService - $payments_total,
    //         'site_owner_name' => $site->site_owner_name,
    //         'location' => $site->location,
    //         'total_balance' => $withService,
    //         'total_due' => $withService - $payments_total,
    //         'effective_balance' => $subtotal - $payments_total,
    //         'total_paid' => $payments_total,
    //     ];

    //     $headers = [
    //         'box1' => 'Phase Name',
    //         'box2' => 'Site Name',
    //         'box3' => 'Contact No',
    //         'box4' => 'Service Charge',
    //         'box5' => 'Balance',
    //         'box6' => 'Site Owner',
    //         'box7' => 'Location',
    //         'box8' => '',
    //     ];

    //     // Generate PDF
    //     $pdf = new PDF();
    //     $pdf->AliasNbPages();
    //     $pdf->AddPage();
    //     $pdf->SetFont('Times', '', 12);
    //     $pdf->SetTitle('Phase Info');
    //     $pdf->infoTable($headers, $data);
    //     $pdf->siteTableData($phaseData); // Use same method for consistency
    //     $pdf->Output();
    //     exit;
    // }


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
}
