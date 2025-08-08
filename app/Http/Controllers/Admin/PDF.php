<?php


namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Fpdf\Fpdf;
use Illuminate\Support\Str;


class PDF extends Fpdf
{

    private int $height = 6;

    private int $width = 47;

    private int $font_size = 7;

    // Set Margins
    private int $m_left = 2;

    private int $m_top = 0;

    private int $m_right = 2;


    private int $r = 51;

    private int $g = 51;

    private int $b = 51;


    // Table Content Style
    private int $fill_r = 245;

    private int $fill_g = 245;

    private int $fill_b = 245;

    private int $c_f_r = 0;

    private int $c_f_g = 170;

    private int $c_f_b = 183;





    function Header()
    {
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(81, 177, 225);

        $pageWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Left cell
        $this->Cell($pageWidth / 2, 10, 'Address: DownTown Sopore, Baramulla', 0, 0, 'L');

        // Right cell
        $this->Cell($pageWidth / 2, 10, 'Contact No: +91-7204091802', 0, 0, 'R');

        $this->Ln(20);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(81, 177, 225);

        $pageWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        // Left cell
        $this->Cell($pageWidth / 2, 10, 'Structor Designs', 0, 0, 'L');

        // Right cell
        $this->Cell($pageWidth / 2, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }


    public function infoTable(array $headers, array $data)
    {


        if (empty($data)) {
            $this->Cell(0, 10, 'No site data available', 1);
            $this->Ln();
            return;
        }

        // Set title styling
        $this->SetTextColor(0, 170, 183);

        // Set general styling
        $this->SetFontSize($this->font_size);
        $this->SetFillColor($this->fill_r, $this->fill_g, $this->fill_b);
        $this->SetTextColor($this->r, $this->g, $this->b);

        // Site details section
        $this->Cell($this->width * 2, $this->height, $headers['box1'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, ucwords($data['site_name']), 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box5'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, ucwords($data['site_owner_name']), 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box6'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, ucwords($data['location']), 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box2'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['contact_no'], 1, 0, 'L', true);
        $this->Ln();
        $this->Ln();

        // Financial information section
        $this->Cell($this->width * 2, $this->height, $headers['box3'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['service_charge'], 1, 0, 'L', true);
        $this->Ln();

        // Add the four financial values
        $this->Cell($this->width * 2, $this->height, 'Total Balance', 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['total_balance'], 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Total Due', 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['total_due'], 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Effective Balance', 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['effective_balance'], 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Total Paid', 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['total_paid'], 1, 0, 'L', true);
        $this->Ln();


        $this->Cell($this->width * 2, $this->height, 'Total Returns', 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['returns'], 1, 0, 'L', true);
        $this->Ln();


        $this->AddPage();
    }

    public function siteTableData($ledgersGroupedByPhase)
    {

        // Check if data is empty
        if (empty($ledgersGroupedByPhase)) {
            $this->Cell(0, 10, 'No data available', 1, 0, 'C');
            $this->Ln();
            return;
        }

        // Process each phase
        foreach ($ledgersGroupedByPhase as $phaseData) {
            // Phase Header
            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('', 'B', 12);
            $this->Cell($this->width * 4, 10, strtoupper($phaseData['phase']), 1, 1, 'C', true);
            $this->SetFont('', '', 10);

            // Summary Table
            $this->renderSummaryTable($phaseData);

            // Detailed Tables
            $this->renderMaterialTable($phaseData['construction_material_billings'] ?? []);
            $this->renderSqftTable($phaseData['square_footage_bills'] ?? []);
            $this->renderExpenseTable($phaseData['daily_expenses'] ?? [], 'Daily Expenses', ['Date', 'Item Name', 'Price']);
            $this->renderWastaLabourTable($phaseData['daily_wastas'] ?? [], 'Daily Wastas');
            $this->renderWastaLabourTable($phaseData['daily_labours'] ?? [], 'Daily Labours');

            // Add page break if not last phase
            if ($phaseData !== end($ledgersGroupedByPhase)) {
                $this->AddPage();
            }
        }
    }


    function phaseTableData($headers, $phases, $phaseCosting)
    {

        $this->SetFont('', '', 8); // Set uniform font (no bold)
        $this->SetTextColor(51, 51, 51); // Default text color

        if (empty($phases)) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No data available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        // Phase Details Section
        $this->SetFillColor(245, 245, 245);

        $phase_details = [
            $headers['box1'] => $phases['phase_name'],
            $headers['box2'] => $phases['site_name'],
            $headers['box3'] => $phases['contact_no'],
            $headers['box4'] => $phases['service_charge'] . '%',
        ];
        foreach ($phase_details as $label => $value) {
            $this->Cell($this->width * 2, $this->height, $label, 1, 0, 'L', true);
            $this->Cell($this->width * 2, $this->height, ucwords($value), 1, 1, 'L', true);
        }

        $owner_details = [
            $headers['box6'] => $phases['site_owner_name'],
            $headers['box7'] => $phases['location'],
        ];
        foreach ($owner_details as $label => $value) {
            $this->Cell($this->width * 2, $this->height, $label, 1, 0, 'L', true);
            $this->Cell($this->width * 2, $this->height, ucwords($value), 1, 1, 'L', true);
        }

        $this->Ln(10);

        // Costing Table Headers
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);
        $cost_headers = ['Description', 'Amount', 'Service Charge', 'Total'];
        foreach ($cost_headers as $header) {
            $this->Cell($this->width * 1, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Calculate service charge amounts
        $getCharge = fn($amount) => $this->getServiceChargeAmount($amount, $phases['service_charge']);
        $rows = [
            'Raw Material' => $phaseCosting['construction_total_amount'],
            'Square Footage' => $phaseCosting['square_footage_total_amount'],
            'Daily Expenses' => $phaseCosting['daily_expenses_total_amount'],
            'Wasta' => $phaseCosting['daily_wastas_total_amount'] ?? 0,
            'Labour' => $phaseCosting['daily_labours_total_amount'] ?? 0,
        ];

        $totalAmount = 0;
        $totalServiceCharge = 0;

        foreach ($rows as $label => $amount) {
            $serviceCharge = $getCharge($amount);
            $total = $amount + $serviceCharge;

            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(51, 51, 51);

            $this->Cell($this->width * 1, $this->height, $label, 1);
            $this->Cell($this->width * 1, $this->height, number_format($amount, 2), 1, 0, 'L');
            $this->Cell($this->width * 1, $this->height, number_format($serviceCharge, 2), 1, 0, 'L');
            $this->Cell($this->width * 1, $this->height, number_format($total, 2), 1, 1, 'L');

            $totalAmount += $amount;
            $totalServiceCharge += $serviceCharge;
        }

        // Subtotal Row
        $this->SetFillColor(245, 245, 245);
        $this->Cell($this->width * 1, $this->height, 'Sub Total', 1);
        $this->Cell($this->width * 1, $this->height, number_format($totalAmount, 2), 1, 0, 'L');
        $this->Cell($this->width * 1, $this->height, number_format($totalServiceCharge, 2), 1, 0, 'L');
        $this->Cell($this->width * 1, $this->height, number_format($totalAmount + $totalServiceCharge, 2), 1, 1, 'L');

        $this->AddPage();

        // Construction Materials Table
        if ($phases['construction_material_billings']->isNotEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1, 10, 'Construction Materials', 0, 1, 'L');

            // Header
            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Item Name', 'Supplier', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.5, $this->height, $header, 1, 0, 'L', true);
            }
            $this->Ln();

            // Rows
            $this->SetTextColor(51, 51, 51);
            foreach ($phases['construction_material_billings'] as $material) {
                $amount = $material->amount;
                $charge = $this->getServiceChargeAmount($amount, $phases['service_charge']);
                $this->Cell($this->width / 1.5, $this->height, date('Y-m-d', strtotime($material->created_at)), 1);
                $this->Cell($this->width / 1.5, $this->height, $material->item_name, 1);
                $this->Cell($this->width / 1.5, $this->height, $material->supplier->name ?? '-', 1);
                $this->Cell($this->width / 1.5, $this->height, number_format($amount, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.5, $this->height, number_format($charge, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.5, $this->height, number_format($amount + $charge, 2), 1, 1, 'L');
            }
        }

        // Square Footage Bills Table
        if ($phases['square_footage_bills']->isNotEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1, 10, 'Square Footage Bills', 0, 1, 'L');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Work Type', 'Supplier', 'Price', 'Multiplier', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.75, $this->height, $header, 1, 0, 'L', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['square_footage_bills'] as $sqft) {
                $total = $sqft->price * $sqft->multiplier;
                $charge = $this->getServiceChargeAmount($total, $phases['service_charge']);
                $this->Cell($this->width / 1.75, $this->height, date('Y-m-d', strtotime($sqft->created_at)), 1);
                $this->Cell($this->width / 1.75, $this->height, $sqft->wager_name, 1);
                $this->Cell($this->width / 1.75, $this->height, Str::words($sqft->supplier->name ?? '-', 2, ''), 1);
                $this->Cell($this->width / 1.75, $this->height, number_format($sqft->price, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.75, $this->height, $sqft->multiplier, 1, 0, 'L');
                $this->Cell($this->width / 1.75, $this->height, number_format($charge, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.75, $this->height, number_format($total + $charge, 2), 1, 1, 'L');
            }
        }

        // Daily Expenses Table
        if ($phases['daily_expenses']->isNotEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1.25, 10, 'Daily Expenses', 0, 1, 'L');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Item Name', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.25, $this->height, $header, 1, 0, 'L', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['daily_expenses'] as $expense) {
                $charge = $this->getServiceChargeAmount($expense->price, $phases['service_charge']);
                $this->Cell($this->width / 1.25, $this->height, date('Y-m-d', strtotime($expense->created_at)), 1);
                $this->Cell($this->width / 1.25, $this->height, $expense->item_name, 1);
                $this->Cell($this->width / 1.25, $this->height, number_format($expense->price, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.25, $this->height, number_format($charge, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.25, $this->height, number_format($expense->price + $charge, 2), 1, 1, 'L');
            }
        }

        // Daily Wasta Table
        if ($phases['daily_wastas']->isNotEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1.25, 10, 'Daily Wastas', 0, 1, 'L');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Wasta Name', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.25, $this->height, $header, 1, 0, 'L', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['daily_wastas'] as $wasta) {
                $charge = $this->getServiceChargeAmount($wasta->price, $phases['service_charge']);
                $this->Cell($this->width / 1.25, $this->height, date('Y-m-d', strtotime($wasta->created_at)), 1);
                $this->Cell($this->width / 1.25, $this->height, $wasta->wasta_name, 1);
                $this->Cell($this->width / 1.25, $this->height, number_format($wasta->price, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.25, $this->height, number_format($charge, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.25, $this->height, number_format($wasta->price + $charge, 2), 1, 1, 'L');
            }
        }



        // Daily Labour Table
        if ($phases['daily_labours']->isNotEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1.25, 10, 'Daily Labours', 0, 1, 'L');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Labour Name', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.25, $this->height, $header, 1, 0, 'L', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['daily_labours'] as $labour) {
                $charge = $this->getServiceChargeAmount($labour->price, $phases['service_charge']);
                $this->Cell($this->width / 1.25, $this->height, date('Y-m-d', strtotime($labour->created_at)), 1);
                $this->Cell($this->width / 1.25, $this->height, $labour->labour_name, 1);
                $this->Cell($this->width / 1.25, $this->height, number_format($labour->price, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.25, $this->height, number_format($charge, 2), 1, 0, 'L');
                $this->Cell($this->width / 1.25, $this->height, number_format($labour->price + $charge, 2), 1, 1, 'L');
            }
        }
    }


    function supplierPaymentTable($supplier)
    {
        $this->setMargins($this->m_left, $this->m_top, $this->m_right);

        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 170, 183);
        $this->Cell(0, 10, '', 0, 1, 'L');
        $this->Cell(0, 10, 'Supplier Details', 0, 1, 'L');


        // Table style setup
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(51, 51, 51);
        $this->SetFillColor(255, 255, 255);

        // Define full width and column widths
        $pageWidth = $this->GetPageWidth() - $this->m_left - $this->m_right;
        $labelWidth = $pageWidth * 0.39;
        $valueWidth = $pageWidth * 0.6;

        // Helper function to add a row
        $addSupplierRow = function ($label, $value) use ($labelWidth, $valueWidth) {
            $this->SetFont('Arial', 'B', 10);
            $this->Cell($labelWidth, 8, $label, 1, 0, 'L', true);
            $this->SetFont('Arial', '', 10);
            $this->Cell($valueWidth, 8, $value, 1, 1, 'L', true);
        };

        // Add rows
        $addSupplierRow('Name:', $supplier->name ?? '--');
        $addSupplierRow('Contact No:', $supplier->contact_no ?? '--');
        $addSupplierRow('Address:', $supplier->address ?? '--');

        $this->Ln(10); // space before next section

        // === No Payments ===
        if ($supplier->payments->isEmpty()) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No Payment History Available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        // === Payment History Table ===
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 170, 183);
        $this->Cell(0, 10, 'Payment History', 0, 1, 'L');

        // Headers
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);

        $headers = ['Date', 'Site Name', 'Site Owner', 'Amount'];
        foreach ($headers as $header) {
            $this->Cell($this->width / 0.92, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Rows
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);
        $this->SetFont('Arial', '', 10);

        foreach ($supplier->payments as $payment) {
            $this->Cell($this->width / 0.92, $this->height, $payment->created_at->format('D-M-Y'), 1, 0, 'C', true);
            $this->Cell($this->width / 0.92, $this->height, $payment->site->site_name ?? '--', 1, 0, 'L', true);
            $this->Cell($this->width / 0.92, $this->height, ucwords($payment->site->site_owner_name ?? '--'), 1, 0, 'L', true);
            $this->Cell($this->width / 0.92, $this->height, number_format($payment->amount, 2), 1, 1, 'L', true);
        }
    }


    function sitePaymentTable($site)
    {

        // Handle empty data cases
        if (!$site || count($site->payments) <= 0) {
            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(200, 0, 0);
            $this->Cell(0, 10, 'No Data Available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        $this->SetTextColor(0, 170, 183);
        $this->SetFont('Helvetica', 'B', 16);
        $this->Cell(0, 10, 'SITE PAYMENT REPORT', 0, 1, 'C');
        $this->SetFont('Helvetica', '', 10);
        $this->Ln(5);

        $this->SetFillColor(240, 240, 240);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(0, 8, 'Site Information', 0, 1, 'L', true);
        $this->SetFont('Helvetica', '', 10);
        $this->Ln(2);

        $site_details = [
            'Site Name' => $site->site_name,
            'Location' => $site->location,
            'Contact No' => $site->contact_no,
            'Service Charge' => number_format($site->service_charge, 2),
            'Site Owner' => $site->site_owner_name,
            'Total Payments' => number_format($site->payments_sum_amount, 2),
        ];

        foreach ($site_details as $label => $value) {
            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(51, 51, 51);
            $this->Cell(60, 8, $label, 1, 0, 'L', true);

            $this->SetFillColor(255, 255, 255);
            $this->Cell(0, 8, $value, 1, 1, 'L', true);
        }

        $this->Ln(10);
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 11);

        // Table headers
        $headers = ['Date', 'Supplier', 'Amount'];
        $headerWidths = [50, 90, 50];

        foreach ($headers as $key => $header) {
            $this->Cell($headerWidths[$key], 8, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Table content
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);
        $this->SetFont('Helvetica', '', 9);

        $total = 0;
        foreach ($site->payments as $payment) {
            $this->Cell($headerWidths[0], 8, $payment->created_at->format('d-M-Y'), 1, 0, 'C');
            $this->Cell($headerWidths[1], 8, ucwords($payment->supplier->name ?? '-'), 1, 0, 'C');
            $this->Cell($headerWidths[2], 8, number_format($payment->amount, 2), 1, 1, 'C');
            $total += $payment->amount;
        }

        // ======================
        // SUMMARY SECTION
        // ======================
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetFillColor(220, 220, 220);
        $this->Cell(array_sum($headerWidths) - $headerWidths[2], 8, 'Total Payments', 1, 0, 'R', true);
        $this->Cell($headerWidths[2], 8, number_format($total, 2), 1, 1, 'R', true);

    }

    public function ledgerTable($ledgers, $total_paid, $total_due, $total_balance, $effective_balance, $service_charge_amount, $returns)
    {
        // Set top margin before rendering
        $this->SetMargins($this->m_left, $this->m_top, $this->m_right);
        $this->SetY($this->m_top);

        // Title
        $this->SetTextColor(0, 170, 183);
        $this->SetFontSize(16);
        $this->SetXY(10, 20);
        $this->Ln(10);

        $this->SetFontSize($this->font_size);

        // Column widths (transaction_type removed)
        $columns = [
            'date' => $this->width / 3.9,
            'supplier' => $this->width / 2,
            'site' => $this->width / 1.4,
            'phase' => $this->width / 1.9,
            'category' => $this->width / 2,
            'description' => $this->width / 1.4,
            'debit' => $this->width / 2.5,
            'credit' => $this->width / 2.5,
            'return' => $this->width / 2.9,
        ];

        // Table header (transaction_type removed)
        $this->SetFillColor(81, 177, 225);
        $this->SetTextColor(255, 255, 255);

        foreach (['Date', 'Supplier Name', 'Site Name', 'Phase', 'Type', 'Narration', 'Purchases', 'Payments', 'Return'] as $i => $label) {
            $this->Cell(array_values($columns)[$i], $this->height, $label, 1, 0, 'L', true);
        }
        $this->Ln();

        // Table content
        $this->SetTextColor($this->r, $this->g, $this->b);

        foreach ($ledgers as $ledger) {
            $x = $this->GetX();
            $y = $this->GetY();
            $cellHeight = 6;

            $supplierText = ucwords($ledger['supplier'] ?? 'NA');
            $descriptionText = ucwords($ledger['description']);

            $supplierHeight = ceil($this->GetStringWidth($supplierText) / $columns['supplier']) * $cellHeight;
            $descriptionHeight = ceil($this->GetStringWidth($descriptionText) / $columns['description']) * $cellHeight;
            $maxHeight = max($cellHeight, $supplierHeight, $descriptionHeight);

            // Date
            $this->SetXY($x, $y);
            $this->Cell($columns['date'], $maxHeight, $ledger['created_at']->format('d-M-y'), 1);
            $x += $columns['date'];

            // Supplier (MultiCell)
            $this->SetXY($x, $y);
            $this->MultiCell($columns['supplier'], $cellHeight, $supplierText, 1);
            $x += $columns['supplier'];
            $this->SetXY($x, $y); // Realign to same row

            // Site
            $this->Cell($columns['site'], $maxHeight, ucwords($ledger['site']), 1);
            $x += $columns['site'];

            // Phase
            $this->SetXY($x, $y);
            $this->Cell($columns['phase'], $maxHeight, ucwords($ledger['phase'] ?? 'NA'), 1);
            $x += $columns['phase'];

            // Category
            $this->SetXY($x, $y);
            $this->Cell($columns['category'], $maxHeight, $ledger['category'], 1);
            $x += $columns['category'];

            // Description (MultiCell)
            $this->SetXY($x, $y);
            $this->MultiCell($columns['description'], $cellHeight, $descriptionText, 1);
            $x += $columns['description'];
            $this->SetXY($x, $y);

            // Debit
            $this->Cell($columns['debit'], $maxHeight, $ledger['debit'], 1);
            $x += $columns['debit'];

            // Credit
            $this->SetXY($x, $y);
            $this->Cell($columns['credit'], $maxHeight, $ledger['credit'], 1);
            $x += $columns['credit'];

            // Return
            $this->SetXY($x, $y);
            $this->Cell($columns['return'], $maxHeight, $ledger['return'], 1);

            // Move to next row
            $this->Ln($maxHeight);
        }

        $this->Ln(5);

        // Summary Section
        $this->SetFillColor($this->fill_r, $this->fill_g, $this->fill_b);
        $this->SetTextColor($this->r, $this->g, $this->b);

        $summaryWidth = $this->width * 2.18;
        $summaryRows = [
            'Grand Effective Amount' => $total_balance,
            'Service Charges' => $service_charge_amount,
            'Payment Made' => $total_paid,
            'Total Amount' => $effective_balance,
            'Balance' => $total_due,
            'Total Returns' => $returns
        ];

        foreach ($summaryRows as $label => $value) {
            $this->Cell($summaryWidth, $this->height, $label, 1, 0, 'L', true);
            $this->Cell($summaryWidth, $this->height, $value, 1, 1, 'L', true);
        }

        $this->Ln();
    }


    private function getServiceChargeAmount($amount, $serviceCharge)
    {

        $serviceChargeAmount = 0;

        $serviceChargeAmount = ($serviceCharge / 100) * $amount;

        return $serviceChargeAmount;
    }


    private function renderSummaryTable($phaseData)
    {
        // Calculate totals
        $totals = [
            'Raw Material' => $phaseData['construction_total_amount'] ?? 0,
            'Square Footage' => $phaseData['square_footage_total_amount'] ?? 0,
            'Daily Expenses' => $phaseData['daily_expenses_total_amount'] ?? 0,
            'Daily Wastas' => $phaseData['daily_wastas_total_amount'] ?? 0,
            'Daily Labours' => $phaseData['daily_labours_total_amount'] ?? 0,
        ];

        // Table header
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);
        $this->Cell($this->width, $this->height, 'Description', 1, 0, 'C', true);
        $this->Cell($this->width, $this->height, 'Amount', 1, 0, 'C', true);
        $this->Cell($this->width, $this->height, 'Service Charge', 1, 0, 'C', true);
        $this->Cell($this->width, $this->height, 'Total', 1, 0, 'C', true);
        $this->Ln();

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        $phaseSubtotal = 0;

        foreach ($totals as $label => $amount) {
            $serviceCharge = $this->getServiceChargeAmount($amount, 10); // Assuming 10% service charge
            $total = $amount + $serviceCharge;
            $phaseSubtotal += $total;

            $this->Cell($this->width, $this->height, $label, 1);
            $this->Cell($this->width, $this->height, number_format($amount, 2), 1, 0, 'L');
            $this->Cell($this->width, $this->height, number_format($serviceCharge, 2), 1, 0, 'L');
            $this->Cell($this->width, $this->height, number_format($total, 2), 1, 0, 'L');
            $this->Ln();
        }

        // Phase Total
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($this->width, $this->height, 'Phase Total', 1);
        $this->Cell($this->width * 3, $this->height, number_format($phaseSubtotal, 2), 1, 0, 'L');
        $this->Ln();
    }

    private function renderMaterialTable($items)
    {
        if (empty($items))
            return;

        $this->Ln(5);
        $this->SetTextColor(0, 170, 183);
        $this->Cell(0, 10, 'Construction Materials', 0, 1, 'C');

        $headers = ['Date', 'Item Name', 'Supplier', 'Price'];
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);

        foreach ($headers as $header) {
            $this->Cell($this->width, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        foreach ($items as $item) {
            $this->Cell($this->width, $this->height, $item['created_at']->format('D-M-y'), 1);
            $this->Cell($this->width, $this->height, $item['description'], 1);
            $this->Cell($this->width, $this->height, $item['supplier'] ?? 'N/A', 1);
            $this->Cell($this->width, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'L');
            $this->Ln();
        }
    }

    private function renderSqftTable($items)
    {
        if (empty($items))
            return;

        $this->Ln(5);
        $this->SetTextColor(0, 170, 183);
        $this->Cell(0, 10, 'Square Footage Bills', 0, 1, 'C');

        $headers = ['Date', 'Work Type', 'Supplier', 'Price', 'Multiplier', 'Total Price'];
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);

        foreach ($headers as $header) {
            $this->Cell(31.3, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        foreach ($items as $item) {
            $this->Cell(31.3, $this->height, $item['created_at']->format('D-M-y'), 1);
            $this->Cell(31.3, $this->height, $item['description'], 1);
            $this->Cell(31.3, $this->height, $item['supplier'] ?? 'N/A', 1);
            $this->Cell(31.3, $this->height, number_format($item['debit'], 2), 1, 0, 'L');
            $this->Cell(31.3, $this->height, '1', 1, 0, 'L'); // Assuming multiplier is always 1
            $this->Cell(31.3, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'L');
            $this->Ln();
        }
    }

    private function renderExpenseTable($items, $title, $columns)
    {
        if (empty($items))
            return;

        $this->Ln(5);
        $this->SetTextColor(0, 170, 183);
        $this->Cell(0, 10, $title, 0, 1, 'C');

        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);

        foreach ($columns as $header) {
            $this->Cell($this->width * 1.33, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        foreach ($items as $item) {
            $this->Cell($this->width * 1.33, $this->height, $item['created_at']->format('D-M-y'), 1);
            $this->Cell($this->width * 1.33, $this->height, $item['description'], 1);
            $this->Cell($this->width * 1.33, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'L');
            $this->Ln();
        }
    }

    private function renderWastaLabourTable($items, $title)
    {
        if (empty($items))
            return;

        $this->Ln(5);
        $this->SetTextColor(0, 170, 183);
        $this->Cell(0, 10, $title, 0, 1, 'C');

        $headers = ['Date', 'Name', 'Amount', 'With Service Charge'];
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);

        foreach ($headers as $header) {
            $this->Cell($this->width, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        foreach ($items as $item) {
            $this->Cell($this->width, $this->height, $item['created_at']->format('D-M-y'), 1);

            // Extract name from description (e.g., "mubashir wasta /700:Day" -> "mubashir wasta")
            $name = explode('/', $item['description'])[0] ?? $item['description'];
            $this->Cell($this->width, $this->height, trim($name), 1);

            $this->Cell($this->width, $this->height, number_format($item['debit'], 2), 1, 0, 'L');
            $this->Cell($this->width, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'L');
            $this->Ln();
        }
    }



    public function phaseWiseAttendanceReport($title, $subtitle, $dates, $workers, $attendanceData, $totals, $info)
    {

        
        $this->SetMargins($this->m_left, $this->m_top, $this->m_right);
        $this->AddPage('L');

        // Set document information
        $this->SetCreator('Your Company Name');
        $this->SetAuthor('Your System');
        $this->SetSubject('Attendance Report');

        // Header Section
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 10, $title, 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, $subtitle, 0, 1, 'C');

        // Add a line separator
        $this->Ln(8);

        // Information Section
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0);
        $this->SetFillColor(240, 240, 240);

        // Create an information table
        $this->Cell(40, 8, 'Site:', 1, 0, 'L', true);
        $this->Cell(0, 8, $info['site_name'], 1, 1);
        $this->Cell(40, 8, 'Phase:', 1, 0, 'L', true);
        $this->Cell(0, 8, $info['phase_name'], 1, 1);
        $this->Cell(40, 8, 'Period:', 1, 0, 'L', true);
        $this->Cell(0, 8, $info['month_year'], 1, 1);

        $this->Ln(12);

        // Attendance Table
        $pageWidth = $this->GetPageWidth() - 4;
        $nameColWidth = 30;
        $dateColWidth = ($pageWidth - $nameColWidth - 10) / count($dates);

        // Table Header
        $this->SetFont('helvetica', 'B', 6);
        $this->SetFillColor(31, 73, 125);
        $this->SetTextColor(255);
        $this->SetLineWidth(0.3);

        // Name column
        $this->Cell($nameColWidth, 10, 'WORKER NAME', 1, 0, 'C', true);

        // Date columns with day names
        foreach ($dates as $date) {
            $this->Cell($dateColWidth, 10, $date['day'] . "\n" . Carbon::parse($date['date'])->format('d'), 1, 0, 'C', true);
        }

        // Totals column
        $this->Cell(10, 10, 'TOT', 1, 1, 'C', true);

        // Table Data
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetFont('helvetica', '', 8);

        $fill = false;
        foreach ($workers as $worker) {
            $this->SetFillColor($fill ? 240 : 255);
            $fill = !$fill;

            // Worker name with type indicator
            $name = $worker['name'];
            $this->Cell($nameColWidth, 8, $name, 'LR', 0, 'L', true);

            $totalPresent = 0;
            $totalAmount = 0;

            foreach ($dates as $date) {
                $attendance = '';
                $dateStr = $date['date'];
                if (isset($attendanceData[$worker['name']]['attendances'][$dateStr])) {
                    $present = $attendanceData[$worker['name']]['attendances'][$dateStr]['present'];
                    $price = $attendanceData[$worker['name']]['attendances'][$dateStr]['price'];

                    $attendance = $present ? 'P' : 'A';
                    if ($present) {
                        $totalPresent++;
                        $totalAmount += $price;
                    }
                }

                // Highlight weekends
                if ($date['is_weekend']) {
                    $this->SetFillColor(230, 230, 230);
                } else {
                    $this->SetFillColor($fill ? 240 : 255);
                }

                $this->Cell($dateColWidth, 8, $attendance, 'LR', 0, 'C', true);
            }

            // Total present days for this worker
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(10, 8, $totalPresent, 'LR', 1, 'C', true);
            $this->SetFont('helvetica', '', 8);
        }

        // Close the table
        $this->Cell($nameColWidth + (count($dates) * $dateColWidth) + 10, 0, '', 'T');
        $this->Ln(12);

        $this->AddPage('L');

        // Payment Summary Section
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(31, 73, 125);
        $this->Cell(0, 8, 'SUMMARY FOR ' . strtoupper($info['phase_name']), 0, 1, 'C');
        $this->Ln(5);

        // Create a summary table
        $this->SetFont('helvetica', '', 10);
        $this->SetFillColor(220, 230, 241);
        $this->SetTextColor(0);
        $this->SetDrawColor(150, 150, 150);

        // Table header
        $headerWidths = [80, 50, 50, 60, 53];
        $headers = ['Worker', 'Type', 'Days Worked', 'Avg Rate', 'Total Amount'];

        // Draw header row
        foreach ($headers as $key => $header) {
            $this->Cell($headerWidths[$key], 8, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Reset fill for data rows
        $this->SetFillColor(240, 240, 240);
        $fill = false;

        // Wasta totals
        foreach ($totals['wastas'] as $name => $data) {
            $this->Cell($headerWidths[0], 8, $name, 'LR', 0, 'L', $fill);
            $this->Cell($headerWidths[1], 8, 'Wasta', 'LR', 0, 'C', $fill);
            $this->Cell($headerWidths[2], 8, $data['present_days'] . ' days', 'LR', 0, 'C', $fill);
            $this->Cell($headerWidths[3], 8, number_format($data['avg_rate'], 2), 'LR', 0, 'R', $fill);
            $this->Cell($headerWidths[4], 8, number_format($data['total_amount'], 2), 'LR', 1, 'R', $fill);
            $fill = !$fill;
        }

        // Labour totals
        foreach ($totals['labours'] as $name => $data) {
            $this->Cell($headerWidths[0], 8, $name, 'LR', 0, 'L', $fill);
            $this->Cell($headerWidths[1], 8, 'Labour', 'LR', 0, 'C', $fill);
            $this->Cell($headerWidths[2], 8, $data['present_days'] . ' days', 'LR', 0, 'C', $fill);
            $this->Cell($headerWidths[3], 8, number_format($data['avg_rate'], 2), 'LR', 0, 'R', $fill);
            $this->Cell($headerWidths[4], 8, number_format($data['total_amount'], 2), 'LR', 1, 'R', $fill);
            $fill = !$fill;
        }

        // Close the table
        $this->Cell(array_sum($headerWidths), 0, '', 'T');
        $this->Ln(8);

        // Grand total
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(31, 73, 125);
        $this->Cell(array_sum(array_slice($headerWidths, 0, 3)), 10, 'TOTAL FOR PHASE:', 0, 0, 'R');
        $this->Cell($headerWidths[3] + $headerWidths[4], 10, number_format($totals['grand_total'], 2), 0, 1, 'R');
    }




}
