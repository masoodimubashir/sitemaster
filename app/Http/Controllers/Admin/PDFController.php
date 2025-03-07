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
    public function showSitePdf(string $id)
    {

        $site_id = base64_decode($id);

        $site = Site::with([
            'phases' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'phases.constructionMaterialBillings' => function ($query) {
                $query->with('supplier')
                    ->where('verified_by_admin', 1)
                    ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'phases.squareFootageBills' => function ($query) {
                $query->with('supplier')
                    ->where('verified_by_admin', 1)
                    ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'phases.dailyWagers' => function ($query) {
                $query->with(['wagerAttendances', 'supplier'])
                    ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'phases.dailyExpenses' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'phases.wagerAttendances' => function ($query) {
                $query->with('dailyWager.supplier')
                    ->whereHas('dailyWager.supplier', fn($q) => $q->whereNull('deleted_at'))
                    ->whereNull('deleted_at')
                    ->latest();
            },
            'payments' => function ($query) {
                $query->where('verified_by_admin', 1);
            },
        ])
        ->find($site_id);

        $totalSupplierPaymentAmount = $site->payments()
        ->where('verified_by_admin', 1)
        ->sum('amount');

        $siteData = [
            'site' => $site,
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

            $phase_total = $construction_total + $daily_expenses_total + $daily_wagers_total + $square_footage_total;
            $total_with_service_charge = ($phase_total * $site->service_charge / 100) + $phase_total;

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

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData($dateFilter, $site_id, $supplier_id, $wager_id);

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);

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
