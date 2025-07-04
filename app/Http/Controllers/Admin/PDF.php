<?php


namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Fpdf\Fpdf;
use Illuminate\Support\Number;

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



    function LoadData($data)
    {
        dd($data);
        return $data;
    }

    function Header()
    {
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(81, 177, 225);
        $this->cell(47 * 2, 10, 'Address:' . ' Model Town A, Sopore', 0, 0, 'L');
        $this->cell(47 * 2, 10, 'Contact No: ' . +919797230468, 0, 0, 'R');
        $this->Ln(20);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(81, 177, 225);
        $this->Cell(47 * 2, 10, 'Developed By Py.Sync PVT LTD ', 0, 0, 'L');
        $this->Cell(47 * 2, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    public function infoTable(array $headers, array $data)
    {
        if (empty($data)) {
            $this->Cell(0, 10, 'No site data available', 1);
            $this->Ln();
            return;
        }

        $this->Ln();
        $this->Ln();

        // Set title styling
        $this->SetTextColor(0, 170, 183);
        $this->Text(188 / 2, 40, 'SiteMaster');

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

        $this->AddPage();
    }

    public function siteTableData($ledgersGroupedByPhase)
    {

        // Check if data is empty
        if (empty($ledgersGroupedByPhase)) {
            $this->Cell(0, 10, 'No data available', 1, 0, 'C');
            $this->Ln();
            $this->Output('site_financial_report.pdf', 'I');
            return;
        }

        // Process each phase
        foreach ($ledgersGroupedByPhase as $phaseData) {
            // Phase Header
            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('', 'B', 12);
            $this->Cell(0, 10, strtoupper($phaseData['phase']) . ' PHASE', 1, 1, 'C', true);
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
        $this->SetFont('', '', 11); // Set uniform font (no bold)
        $this->SetTextColor(51, 51, 51); // Default text color

        if (empty($phases)) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No data available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        // Title Section
        $this->Cell(0, 10, 'SiteMaster', 0, 1, 'C');
        $this->Ln(5);

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
            $this->Cell($this->width * 1, $this->height, number_format($amount, 2), 1, 0, 'R');
            $this->Cell($this->width * 1, $this->height, number_format($serviceCharge, 2), 1, 0, 'R');
            $this->Cell($this->width * 1, $this->height, number_format($total, 2), 1, 1, 'R');

            $totalAmount += $amount;
            $totalServiceCharge += $serviceCharge;
        }

        // Subtotal Row
        $this->SetFillColor(245, 245, 245);
        $this->Cell($this->width * 1, $this->height, 'Sub Total', 1);
        $this->Cell($this->width * 1, $this->height, number_format($totalAmount, 2), 1, 0, 'R');
        $this->Cell($this->width * 1, $this->height, number_format($totalServiceCharge, 2), 1, 0, 'R');
        $this->Cell($this->width * 1, $this->height, number_format($totalAmount + $totalServiceCharge, 2), 1, 1, 'R');

        $this->AddPage();

        // Construction Materials Table
        if (!$phases['construction_material_billings']->isEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1, 10, 'Construction Materials', 0, 1, 'C');

            // Header
            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Item Name', 'Supplier', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.5, $this->height, $header, 1, 0, 'C', true);
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
                $this->Cell($this->width / 1.5, $this->height, number_format($amount, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.5, $this->height, number_format($charge, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.5, $this->height, number_format($amount + $charge, 2), 1, 1, 'R');
            }
        }

        // Square Footage Bills Table
        if (!$phases['square_footage_bills']->isEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1, 10, 'Square Footage Bills', 0, 1, 'C');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Work Type', 'Supplier', 'Price', 'Multiplier', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.75, $this->height, $header, 1, 0, 'C', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['square_footage_bills'] as $sqft) {
                $total = $sqft->price * $sqft->multiplier;
                $charge = $this->getServiceChargeAmount($total, $phases['service_charge']);
                $this->Cell($this->width / 1.75, $this->height, date('Y-m-d', strtotime($sqft->created_at)), 1);
                $this->Cell($this->width / 1.75, $this->height, $sqft->wager_name, 1);
                $this->Cell($this->width / 1.75, $this->height, $sqft->supplier->name ?? '-', 1);
                $this->Cell($this->width / 1.75, $this->height, number_format($sqft->price, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.75, $this->height, $sqft->multiplier, 1, 0, 'R');
                $this->Cell($this->width / 1.75, $this->height, number_format($charge, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.75, $this->height, number_format($total + $charge, 2), 1, 1, 'R');
            }
        }

        // Daily Expenses Table
        if (!$phases['daily_expenses']->isEmpty()) {
            $this->Ln(5);
            $this->Cell($this->width / 1.25, 10, 'Daily Expenses', 0, 1, 'C');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Item Name', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.25, $this->height, $header, 1, 0, 'C', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['daily_expenses'] as $expense) {
                $charge = $this->getServiceChargeAmount($expense->price, $phases['service_charge']);
                $this->Cell($this->width / 1.25, $this->height, date('Y-m-d', strtotime($expense->created_at)), 1);
                $this->Cell($this->width / 1.25, $this->height, $expense->item_name, 1);
                $this->Cell($this->width / 1.25, $this->height, number_format($expense->price, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.25, $this->height, number_format($charge, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.25, $this->height, number_format($expense->price + $charge, 2), 1, 1, 'R');
            }
        }

        // Daily Wasta Table
        if (!empty($phases['daily_wastas'])) {
            $this->Ln(5);
            $this->Cell($this->width / 1.25, 10, 'Daily Wastas', 0, 1, 'C');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Wasta Name', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.25, $this->height, $header, 1, 0, 'C', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['daily_wastas'] as $wasta) {
                $charge = $this->getServiceChargeAmount($wasta->price, $phases['service_charge']);
                $this->Cell($this->width / 1.25, $this->height, date('Y-m-d', strtotime($wasta->created_at)), 1);
                $this->Cell($this->width / 1.25, $this->height, $wasta->wasta_name, 1);
                $this->Cell($this->width / 1.25, $this->height, number_format($wasta->price, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.25, $this->height, number_format($charge, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.25, $this->height, number_format($wasta->price + $charge, 2), 1, 1, 'R');
            }
        }

        // Daily Labour Table
        if (!empty($phases['daily_labours'])) {
            $this->Ln(5);
            $this->Cell($this->width / 1.25, 10, 'Daily Labours', 0, 1, 'C');

            $this->SetFillColor(0, 170, 183);
            $this->SetTextColor(255);
            $headers = ['Date', 'Labour Name', 'Amount', 'Service Charge', 'Total'];
            foreach ($headers as $header) {
                $this->Cell($this->width / 1.25, $this->height, $header, 1, 0, 'C', true);
            }
            $this->Ln();

            $this->SetTextColor(51, 51, 51);
            foreach ($phases['daily_labours'] as $labour) {
                $charge = $this->getServiceChargeAmount($labour->price, $phases['service_charge']);
                $this->Cell($this->width / 1.25, $this->height, date('Y-m-d', strtotime($labour->created_at)), 1);
                $this->Cell($this->width / 1.25, $this->height, $labour->labour_name, 1);
                $this->Cell($this->width / 1.25, $this->height, number_format($labour->price, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.25, $this->height, number_format($charge, 2), 1, 0, 'R');
                $this->Cell($this->width / 1.25, $this->height, number_format($labour->price + $charge, 2), 1, 1, 'R');
            }
        }
    }


    function supplierPaymentTable($supplier)
    {

        $this->setMargins($this->m_left, $this->m_top, $this->m_right);

        if ($supplier->payments->isEmpty()) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No Data Available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        // Title Section
        $this->SetTextColor(0, 170, 183);
        $this->Cell($this->width * 4, $this->height, 'Payment History', 0, 0, 'C');
        $this->Ln(10);

        // Table Headers
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);

        $headers = ['Date', 'Site Name', 'Site Owner', 'Supplier', 'Amount'];
        foreach ($headers as $header) {
            $this->Cell($this->width / 1.15, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Table Content
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        foreach ($supplier->payments as $payment) {
            $this->Cell($this->width / 1.15, $this->height, $payment->created_at->format('D-M'), 1, 0, 'C', true);
            $this->Cell($this->width / 1.15, $this->height, $payment->site->site_name ?? '--', 1, 0, 'L', true);
            $this->Cell($this->width / 1.15, $this->height, ucwords($payment->site->site_owner_name ?? '--'), 1, 0, 'L', true);
            $this->Cell($this->width / 1.15, $this->height, ucwords($payment->supplier->name), 1, 0, 'L', true);
            $this->Cell($this->width / 1.15, $this->height, number_format($payment->amount, 2), 1, 1, 'R', true);
        }
    }

    function sitePaymentTable($site)
    {

        if (!$site) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No Data Available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        if (count($site->payments) <= 0) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No Data Available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        // Title Section
        $this->SetTextColor(0, 170, 183);
        $this->Text(188 / 2, 40, 'SiteMaster');
        $this->Ln(15);

        // Site Details Section
        $this->SetFillColor($this->c_f_r, $this->c_f_g, $this->c_f_b);
        // $this->SetTextColor(255, 255, 255);

        // Site Information Headers
        $site_details = [
            'Site Name' => $site->site_name,
            'Location' => $site->location,
            'Contact No' => $site->contact_no,
            'Service Charge' => $site->service_charge,
            'Site Owner' => $site->site_owner_name
        ];

        foreach ($site_details as $label => $value) {

            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(51, 51, 51);
            $this->Cell($this->width * 2, $this->height, $label, 1, 0, 'L', true);

            $this->Cell($this->width * 2, $this->height, ucwords($value), 1, 0, 'L', true);

            $this->Ln();
        }

        // Add spacing before table headers
        $this->Ln(5);

        // Set header styling
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);

        // Table Headers with teal background
        $headers = ['Date', 'Supplier', 'Amount'];
        foreach ($headers as $header) {
            $this->Cell($this->width * 1.34, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Table Content
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        foreach ($site->payments as $site_payment) {
            $this->Cell($this->width * 1.34, $this->height, $site_payment->created_at->format('d-m-y'), 1, 0, 'C', true);
            $this->Cell($this->width * 1.34, $this->height, ucwords($site_payment->supplier->name ?? '-'), 1, 0, 'L', true);
            $this->Cell($this->width * 1.34, $this->height, number_format($site_payment->amount, 2), 1, 1, 'R', true);
        }
    }

    public function ledgerTable($ledgers, $total_paid, $total_due, $total_balance, $effective_balance, $service_charge_amount)
    {
        // Set top margin before rendering
        $this->SetMargins($this->m_left, $this->m_top, $this->m_right);
        $this->SetY($this->m_top); // Respect top margin

        // Title
        $this->SetTextColor(0, 170, 183);
        $this->SetFontSize(16);
        $this->SetXY(10, 20); // Adjust position
        $this->Cell(0, 10, 'SiteMaster', 0, 1, 'C');

        $this->Ln(10); // Add space before table

        $this->SetFontSize($this->font_size);

        // Column widths (transaction_type removed)
        $columns = [
            'date' => $this->width / 2.9,
            'supplier' => $this->width / 1.55,
            'site' => $this->width / 1.4,
            'phase' => $this->width / 1.9,
            'category' => $this->width / 2,
            'description' => $this->width / 1.4,
            'debit' => $this->width / 2.2,
            'credit' => $this->width / 2.2,
        ];

        // Table header (transaction_type removed)
        $this->SetFillColor(81, 177, 225);
        $this->SetTextColor(255, 255, 255);

        foreach (['Date', 'Supplier Name', 'Site Name', 'Phase', 'Type', 'Narration', 'Debit', 'Credit'] as $i => $label) {
            $this->Cell(array_values($columns)[$i], $this->height, $label, 1, 0, 'L', true);
        }
        $this->Ln();

        // Table content
        $this->SetTextColor($this->r, $this->g, $this->b);

        foreach ($ledgers as $ledger) {
            $x = $this->GetX();
            $y = $this->GetY();
            $cellHeight = 6;

            $supplierText = ucwords($ledger['supplier']);
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
            $this->Cell($columns['phase'], $maxHeight, ucwords($ledger['phase']), 1);
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
            $this->Cell($this->width, $this->height, number_format($amount, 2), 1, 0, 'R');
            $this->Cell($this->width, $this->height, number_format($serviceCharge, 2), 1, 0, 'R');
            $this->Cell($this->width, $this->height, number_format($total, 2), 1, 0, 'R');
            $this->Ln();
        }

        // Phase Total
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($this->width, $this->height, 'Phase Total', 1);
        $this->Cell($this->width * 3, $this->height, number_format($phaseSubtotal, 2), 1, 0, 'R');
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
            $this->Cell($this->width, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'R');
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
            $this->Cell(31.3, $this->height, number_format($item['debit'], 2), 1, 0, 'R');
            $this->Cell(31.3, $this->height, '1', 1, 0, 'R'); // Assuming multiplier is always 1
            $this->Cell(31.3, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'R');
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
            $this->Cell($this->width * 1.33, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'R');
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

            $this->Cell($this->width, $this->height, number_format($item['debit'], 2), 1, 0, 'R');
            $this->Cell($this->width, $this->height, number_format($item['total_amount_with_service_charge'], 2), 1, 0, 'R');
            $this->Ln();
        }
    }


    // Add this method to your PDF class
    public function phaseWiseAttendanceReport($title, $subtitle, $dates, $workers, $attendanceData, $totals, $info)
    {


        $this->AddPage('L');
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 10, $title, 0, 1, 'C');
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 8, $subtitle, 0, 1, 'C');

        // Display site and phase info
        $this->Ln(5);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 8, 'Site: ' . $info['site_name'], 0, 1);
        $this->Cell(0, 8, 'Phase: ' . $info['phase_name'], 0, 1);
        $this->Cell(0, 8, 'Period: ' . $info['month_year'], 0, 1);
        $this->Ln(10);

        // Calculate column widths (use full page width)
        $pageWidth = $this->GetPageWidth() - 20; // Leave 10mm margins on each side
        $nameColWidth = 22; // Fixed width for worker names
        $dateColWidth = ($pageWidth - $nameColWidth) / count($dates); // Removed remarks column

        // Table header
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);

        // Name column
        $this->Cell($nameColWidth, 8, 'NAME', 1, 0, 'C', true);

        // Date columns
        foreach ($dates as $date) {
            $dateFormatted = Carbon::parse($date)->format('d');
            $this->Cell($dateColWidth, 8, $dateFormatted, 1, 0, 'C', true);
        }
        $this->Ln();

        // Table data
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetFont('helvetica', '', 8);

        foreach ($workers as $worker) {
            $this->Cell($nameColWidth, 6, $worker['name'], 'LR', 0, 'L');

            foreach ($dates as $date) {
                $this->Cell($dateColWidth, 6, $attendanceData[$date][$worker['name']] ?? 0, 'LR', 0, 'C');
            }
            $this->Ln();
        }

        // Close the table
        $this->Cell($nameColWidth + (count($dates) * $dateColWidth), 0, '', 'T');
        $this->Ln(10);

        // Totals section
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 8, 'TOTALS FOR ' . strtoupper($info['phase_name']), 0, 1);   

        // Wasta totals
        foreach ($totals['wastas'] as $name => $data) {
            $this->Cell(40, 6, $name . ' (Wasta):', 0, 0);
            $this->Cell(20, 6, $data['present_days'] . ' days', 0, 0);
            $this->Cell(30, 6, number_format($data['total_amount']), 0, 1);
        }

        // Labour totals
        foreach ($totals['labours'] as $name => $data) {
            $this->Cell(40, 6, $name . ' (Labour):', 0, 0);
            $this->Cell(20, 6, $data['present_days'] . ' days', 0, 0);
            $this->Cell(30, 6, number_format($data['total_amount']), 0, 1);
        }

        // Grand total
        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(40, 8, 'PHASE TOTAL:', 0, 0);
        $this->Cell(30, 8, number_format($totals['grand_total']), 0, 1);

        // Footer
        $this->Ln(10);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 8, 'Generated on: ' . Carbon::now()->format('d-M-Y h:i A'), 0, 1);
        $this->Ln(5);
    }


}
