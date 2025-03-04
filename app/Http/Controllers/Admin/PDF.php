<?php


namespace App\Http\Controllers\Admin;

use Fpdf\Fpdf;
use Illuminate\Support\Number;

class PDF extends Fpdf
{

    private int $height = 6;

    private int  $width = 47;

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

    function infoTable(array $headers, array $data)
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

        $this->Cell($this->width * 2, $this->height, $headers['box2'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['contact_no'], 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box3'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['service_charge'], 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box4'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['balance'], 1, 0, 'L', true);
        $this->Ln();
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box5'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, ucwords($data['site_owner_name']), 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box6'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, ucwords($data['location']), 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box7'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['debit'], 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box8'], 1, 0, 'L', true);
        $this->Cell($this->width * 2, $this->height, $data['credit'], 1, 0, 'L', true);
        $this->Ln();

        $this->AddPage();

        // Summary section

    }

    function siteTableData($phases)
    {

        if (empty($phases)) {
            $this->Cell(0, 10, 'No data available', 1, 0, 'C');
            $this->Ln();
            return;
        }

        $this->Ln();

        foreach ($phases as $key =>  $phase) {

            $this->SetTextColor(0, 170, 183);
            $this->Cell(47 * 4, 10, ucwords($phase['phase']), 0, 1, 'C');

            $this->Ln(5);

            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(51, 51, 51);
            $this->Cell($this->width, $this->height, 'Phase Costing', 1, 0, 'L', true);
            $this->Ln();

            $phase_total_service_charge_Amount = $this->getServiceChargeAmount($phase['phase_total'], $phase['site_service_charge']);
            $construction_total_amount_with_service_charge = $this->getServiceChargeAmount($phase['construction_total_amount'], $phase['site_service_charge']) + $phase['construction_total_amount'];
            $square_footage_total_amount_with_service_charge = $this->getServiceChargeAmount($phase['square_footage_total_amount'], $phase['site_service_charge']) + $phase['square_footage_total_amount'];
            $daily_expenses_total_amount_with_service_charge = $this->getServiceChargeAmount($phase['daily_expenses_total_amount'], $phase['site_service_charge']) + $phase['daily_expenses_total_amount'];
            $daily_wagers_total_amount_with_service_charge = $this->getServiceChargeAmount($phase['daily_wagers_total_amount'], $phase['site_service_charge']) + $phase['daily_wagers_total_amount'];

            // Table header styling
            $this->SetFillColor($this->c_f_r, $this->c_f_g, $this->c_f_b);
            $this->SetTextColor(255, 255, 255);
            $this->Cell($this->width, $this->height, 'Description', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, 'Amount', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, 'Service Charge', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, 'Total', 1, 0, 'L', true);
            $this->Ln();

            // Table content styling
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(51, 51, 51);

            // Raw Material Row
            $this->Cell($this->width, $this->height, 'Raw Material', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, number_format($phase['construction_total_amount'], 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, '-', 1, 0, 'C', true);
            $this->Cell($this->width, $this->height, number_format($construction_total_amount_with_service_charge, 2), 1, 0, 'R', true);
            $this->Ln();

            // Square Footage Row
            $this->Cell($this->width, $this->height, 'Square Footage', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, number_format($phase['square_footage_total_amount'], 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, '-', 1, 0, 'C', true);
            $this->Cell($this->width, $this->height, number_format($square_footage_total_amount_with_service_charge, 2), 1, 0, 'R', true);
            $this->Ln();

            // Daily Expenses Row
            $this->Cell($this->width, $this->height, 'Daily Expenses', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, number_format($phase['daily_expenses_total_amount'], 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, '-', 1, 0, 'C', true);
            $this->Cell($this->width, $this->height, number_format($daily_expenses_total_amount_with_service_charge, 2), 1, 0, 'R', true);
            $this->Ln();

            // Wager Row
            $this->Cell($this->width, $this->height, 'Wager', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, number_format($phase['daily_wagers_total_amount'], 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, '-', 1, 0, 'C', true);
            $this->Cell($this->width, $this->height, number_format($daily_wagers_total_amount_with_service_charge, 2), 1, 0, 'R', true);
            $this->Ln();

            // Total Row styling
            $this->SetFillColor(245, 245, 245);
            $this->Cell($this->width, $this->height, 'Sub Total', 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, number_format($phase['phase_total'], 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, number_format($phase_total_service_charge_Amount, 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, number_format($phase['phase_total_with_service_charge'], 2), 1, 0, 'R', true);

            $this->Ln();
            if (!$phase['construction_material_billings']->isEmpty()) {

                // Header styling
                $this->SetTextColor(0, 170, 183);
                $this->cell(47 * 4, 10, ucwords('Construction Materials Phase'), 0, 1, 'C');

                // Table header styling
                $this->SetFillColor(0, 170, 183);
                $this->SetTextColor(255, 255, 255);

                // Column headers
                $headers = ['Date', 'Item Name', 'Supplier', 'Price'];
                foreach ($headers as $header) {
                    $this->Cell($this->width, $this->height, $header, 1, 0, 'C', true);
                }
                $this->Ln();

                // Table content styling
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(51, 51, 51);

                // Data rows
                foreach ($phase['construction_material_billings'] as $material) {
                    $material_total = $this->getServiceChargeAmount($material->amount, $phase['site_service_charge']) + $material->amount;

                    $this->Cell($this->width, $this->height, $material->created_at->format('D-M-y'), 1, 0, 'C', true);
                    $this->Cell($this->width, $this->height, $material->item_name, 1, 0, 'L', true);
                    $this->Cell($this->width, $this->height, $material->supplier->name, 1, 0, 'L', true);
                    $this->Cell($this->width, $this->height, number_format($material_total, 2), 1, 0, 'R', true);
                    $this->Ln();
                }
            }


            if (!$phase['square_footage_bills']->isEmpty()) {
                $this->Ln(5);

                // Header styling
                $this->SetTextColor(0, 170, 183);
                $this->cell(47 * 4, 10, ucwords("Square Footage Bills"), 0, 1, 'C');

                // Table header styling
                $this->SetFillColor(0, 170, 183);
                $this->SetTextColor(255, 255, 255);

                // Column headers
                $headers = ['Date', 'Work Type', 'Supplier', 'Price', 'Multiplier', 'Total Price'];
                foreach ($headers as $header) {
                    $this->Cell(31.3, $this->height, $header, 1, 0, 'C', true);
                }
                $this->Ln();

                // Table content styling
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(51, 51, 51);

                // Data rows
                foreach ($phase['square_footage_bills'] as $sqft) {
                    $sqft_total_price = $sqft->multiplier * $sqft->price;
                    $sqft_service_charge_amount = $this->getServiceChargeAmount($sqft_total_price, $phase['site_service_charge']);
                    $total_with_service = $sqft_total_price + $sqft_service_charge_amount;

                    $this->Cell(31.3, $this->height, $sqft->created_at->format('D-M-y'), 1, 0, 'C', true);
                    $this->Cell(31.3, $this->height, ucwords($sqft->wager_name), 1, 0, 'L', true);
                    $this->Cell(31.3, $this->height, $sqft->supplier->name, 1, 0, 'L', true);
                    $this->Cell(31.3, $this->height, number_format($sqft->price, 2), 1, 0, 'R', true);
                    $this->Cell(31.3, $this->height, number_format($sqft->multiplier, 2), 1, 0, 'R', true);
                    $this->Cell(31.3, $this->height, number_format($total_with_service, 2), 1, 0, 'R', true);
                    $this->Ln();
                }
            }


            if (!$phase['daily_expenses']->isEmpty()) {
                $this->Ln(5);

                // Header styling
                $this->SetTextColor(0, 170, 183);
                $this->cell(47 * 4, 10, ucwords("Daily Expenses"), 0, 1, 'C');

                // Table header styling
                $this->SetFillColor(0, 170, 183);
                $this->SetTextColor(255, 255, 255);

                // Column headers
                $headers = ['Date', 'Item Name', 'Price'];
                foreach ($headers as $header) {
                    $this->Cell($this->width * 1.33, $this->height, $header, 1, 0, 'C', true);
                }
                $this->Ln();

                // Table content styling
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(51, 51, 51);

                // Data rows
                foreach ($phase['daily_expenses'] as $expense) {
                    $expense_service_charge_amount = $this->getServiceChargeAmount($expense->price, $phase['site_service_charge']);
                    $total_amount = $expense->price + $expense_service_charge_amount;

                    $this->Cell($this->width * 1.33, $this->height, $expense->created_at->format('D-M-y'), 1, 0, 'C', true);
                    $this->Cell($this->width * 1.33, $this->height, ucwords($expense->item_name), 1, 0, 'L', true);
                    $this->Cell($this->width * 1.33, $this->height, number_format($total_amount, 2), 1, 0, 'R', true);
                    $this->Ln();
                }
            }


            if (!$phase['daily_wagers']->isEmpty()) {
                $this->Ln(5);

                // Daily Wager Header
                $this->SetTextColor(0, 170, 183);
                $this->cell(47 * 4, 10, ucwords("Daily Wager"), 0, 1, 'C');

                // Table Header Styling
                $this->SetFillColor(0, 170, 183);
                $this->SetTextColor(255, 255, 255);

                // Daily Wager Headers
                $wager_headers = ['Date', 'Wager Name', 'Price Per Day', 'Price Per Wager'];
                foreach ($wager_headers as $header) {
                    $this->Cell($this->width, $this->height, $header, 1, 0, 'C', true);
                }
                $this->Ln();

                // Daily Wager Content Styling
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(51, 51, 51);

                foreach ($phase['daily_wagers'] as $daily_wager) {
                    $wager_service_charge_amount = $this->getServiceChargeAmount($daily_wager->wager_total, $phase['site_service_charge']);
                    $total_amount = $daily_wager->wager_total + $wager_service_charge_amount;

                    $this->Cell($this->width, $this->height, $daily_wager->created_at->format('D-M-y'), 1, 0, 'C', true);
                    $this->Cell($this->width, $this->height, ucwords($daily_wager->wager_name), 1, 0, 'L', true);
                    $this->Cell($this->width, $this->height, number_format($daily_wager->price_per_day, 2), 1, 0, 'R', true);
                    $this->Cell($this->width, $this->height, number_format($total_amount, 2), 1, 0, 'R', true);
                    $this->Ln();
                }
            }

            if (!$phase['wager_attendances']->isEmpty()) {
                $this->Ln(5);

                // Attendance Header
                $this->SetTextColor(0, 170, 183);
                $this->cell(47 * 4, 10, ucwords("Attendance"), 0, 1, 'C');

                // Table Header Styling
                $this->SetFillColor(0, 170, 183);
                $this->SetTextColor(255, 255, 255);

                // Attendance Headers
                $attendance_headers = ['Date', 'No Of Persons', 'Wager Name', 'Supplier'];
                foreach ($attendance_headers as $header) {
                    $this->Cell($this->width, $this->height, $header, 1, 0, 'C', true);
                }
                $this->Ln();

                // Attendance Content Styling
                $this->SetFillColor(255, 255, 255);
                $this->SetTextColor(51, 51, 51);

                foreach ($phase['wager_attendances'] as $attendance) {
                    $this->Cell($this->width, $this->height, $attendance->created_at->format('D-M-y'), 1, 0, 'C', true);
                    $this->Cell($this->width, $this->height, $attendance->no_of_persons, 1, 0, 'C', true);
                    $this->Cell($this->width, $this->height, ucwords($attendance->dailyWager->wager_name), 1, 0, 'L', true);
                    $this->Cell($this->width, $this->height, ucwords($attendance->dailyWager->supplier->name), 1, 0, 'L', true);
                    $this->Ln();
                }
            }


            if ($key < count($phases) - 1) {
                $this->AddPage();
            } else {
                return;
            }
        }
    }

    function phaseTableData($headers, $phases, $phaseCosting)
    {


        if (empty($phases)) {
            $this->SetFillColor(245, 245, 245);
            $this->Cell(0, 10, 'No data available', 1, 0, 'C', true);
            $this->Ln();
            return;
        }

        // Title Section
        $this->SetTextColor(0, 170, 183);
        $this->Text(188 / 2, 40, 'SiteMaster');
        $this->Ln(15);

        // Phase Details Section
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(51, 51, 51);

        // First Information Block
        $phase_details = [
            $headers['box1'] => $phases['phase_name'],
            $headers['box2'] => $phases['site_name'],
            $headers['box3'] => $phases['contact_no'],
            $headers['box4'] => $phases['service_charge']
        ];

        foreach ($phase_details as $label => $value) {
            $this->Cell($this->width * 2, $this->height, $label, 1, 0, 'L', true);
           
            $this->Cell($this->width * 2, $this->height, ucwords($value), 1, 0, 'L', true);
           
            $this->Ln();
        }

        // Second Information Block
        $owner_details = [
            $headers['box6'] => $phases['site_owner_name'],
            $headers['box7'] => $phases['location']
        ];

        foreach ($owner_details as $label => $value) {
            $this->Cell($this->width * 2, $this->height, $label, 1, 0, 'L', true);
            $this->Cell($this->width * 2, $this->height, ucwords($value), 1, 0, 'L', true);
            $this->Ln();
        }

        $this->Ln(5);

        // Costing Table Headers
        $this->SetFillColor(0, 170, 183);
        $this->SetTextColor(255, 255, 255);
        $cost_headers = ['.....', 'Amount', 'Service Charge', 'Total'];
        foreach ($cost_headers as $header) {
            $this->Cell($this->width, $this->height, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        // Calculate Service Charges
        $total_service_charge_amount = $this->getServiceChargeAmount($phaseCosting['total_amount'], $phases['service_charge']);
        $total_service_charge_with_amount = $total_service_charge_amount + $phaseCosting['total_amount'];
        $construction_total_amount_with_service_charge = $this->getServiceChargeAmount($phaseCosting['construction_total_amount'], $phases['service_charge']) + $phaseCosting['construction_total_amount'];
        $square_footage_total_amount_with_service_charge = $this->getServiceChargeAmount($phaseCosting['square_footage_total_amount'], $phases['service_charge']) + $phaseCosting['square_footage_total_amount'];
        $daily_expenses_total_amount_with_service_charge = $this->getServiceChargeAmount($phaseCosting['daily_expenses_total_amount'], $phases['service_charge']) + $phaseCosting['daily_expenses_total_amount'];
        $daily_wagers_total_amount_with_service_charge = $this->getServiceChargeAmount($phaseCosting['daily_wagers_total_amount'], $phases['service_charge']) + $phaseCosting['daily_wagers_total_amount'];

        // Costing Details
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(51, 51, 51);

        $cost_rows = [
            'Raw Material' => [$phaseCosting['construction_total_amount'], '.....', $construction_total_amount_with_service_charge],
            'Square Footage' => [$phaseCosting['square_footage_total_amount'], '.....', $square_footage_total_amount_with_service_charge],
            'Daily Expenses' => [$phaseCosting['daily_expenses_total_amount'], '.....', $daily_expenses_total_amount_with_service_charge],
            'Wager' => [$phaseCosting['daily_wagers_total_amount'], '.....', $daily_wagers_total_amount_with_service_charge]
        ];

        foreach ($cost_rows as $label => $values) {
            $this->Cell($this->width, $this->height, $label, 1, 0, 'L', true);
            $this->Cell($this->width, $this->height, number_format($values[0], 2), 1, 0, 'R', true);
            $this->Cell($this->width, $this->height, $values[1], 1, 0, 'C', true);
            $this->Cell($this->width, $this->height, number_format($values[2], 2), 1, 0, 'R', true);
            $this->Ln();
        }

        // Total Row
        $this->SetFillColor(245, 245, 245);
        $this->Cell($this->width, $this->height, 'Sub Total', 1, 0, 'L', true);
        $this->Cell($this->width, $this->height, number_format($phaseCosting['total_amount'], 2), 1, 0, 'R', true);
        $this->Cell($this->width, $this->height, number_format($total_service_charge_amount, 2), 1, 0, 'R', true);
        $this->Cell($this->width, $this->height, number_format($total_service_charge_with_amount, 2), 1, 0, 'R', true);

        $this->AddPage();



        if (!$phases['construction_material_billings']->isEmpty()) {

            // $this->cell(47 * 4, 10, ucwords($phases['phase_name'] . ' Phase'), 0, 1, 'C');
            $this->cell(47 * 4, 10, ucwords('Construction Materials'), 0, 1, 'C');

            foreach ($phases['construction_material_billings'] as $m =>  $materials) {
                if ($m === 0) {
                    $this->Cell($this->width, $this->height, 'Date', 1);
                    $this->Cell($this->width, $this->height, 'Item Name', 1);
                    $this->Cell($this->width, $this->height, 'Supplier', 1);
                    $this->Cell($this->width, $this->height, 'Price', 1);
                    $this->Ln();
                }

                $this->Cell($this->width, $this->height, $materials->created_at->format('D-M-y'), 1);
                // $this->Cell($this->width, $this->height, asset($materials->item_image_path), 1);

                $this->Cell($this->width, $this->height, $materials->item_name, 1);
                $this->Cell($this->width, $this->height, $materials->supplier->name, 1);

                $material_service_charge_amount = $this->getServiceChargeAmount($materials->amount, $phases['service_charge']);
                $this->Cell($this->width, $this->height, $materials->amount + $material_service_charge_amount, 1);
                $this->Ln();
            }
            // $this->AddPage();

        }

        if (!$phases['square_footage_bills']->isEmpty()) {

            $this->Ln();

            $this->cell(47 * 4, 10, ucwords("Square Footage Bills"), 0, 1, 'C');

            foreach ($phases['square_footage_bills'] as $s => $sqft) {
                if ($s === 0) {
                    $this->Cell(31.3, $this->height, 'Date', 1);
                    $this->Cell(31.3, $this->height, 'Work Type', 1);
                    $this->Cell(31.3, $this->height, 'Supplier', 1);
                    $this->Cell(31.3, $this->height, 'Price', 1);
                    $this->Cell(31.3, $this->height, 'Multiplier', 1);
                    $this->Cell(31.3, $this->height, 'Total Price', 1);
                    $this->Ln();
                }
                $this->Cell(31.3, $this->height, $sqft->created_at->format('D-M-y'), 1);
                $this->Cell(31.3, $this->height, $sqft->wager_name, 1);
                $this->Cell(31.3, $this->height, $sqft->supplier->name, 1);
                $this->Cell(31.3, $this->height, $sqft->price, 1);
                $this->Cell(31.3, $this->height, $sqft->multiplier, 1);

                $sqft_total_amount = $sqft->multiplier * $sqft->price;
                $sqft_service_charge_amount = $this->getServiceChargeAmount($sqft_total_amount, $phases['service_charge']);


                $this->Cell(31.3, $this->height, $sqft_total_amount + $sqft_service_charge_amount, 1);
                $this->Ln();
            }
            // $this->AddPage();
        }

        if (!$phases['daily_expenses']->isEmpty()) {

            $this->Ln();

            $this->cell(47 * 4, 10, ucwords("Daily Expenses"), 0, 1, 'C');


            foreach ($phases['daily_expenses'] as $e => $expense) {
                if ($e === 0) {
                    $this->Cell($this->width * 1.33, $this->height, 'Date', 1);
                    $this->Cell($this->width * 1.33, $this->height, 'Item Name', 1);
                    $this->Cell($this->width * 1.33, $this->height, 'Price', 1);
                    $this->Ln();
                }
                $this->Cell($this->width * 1.33, $this->height, $expense->created_at, 1);

                $this->Cell($this->width * 1.33, $this->height, $expense->item_name, 1);

                $expense_service_charge_amount = $this->getServiceChargeAmount($expense->price, $phases['service_charge']);

                $this->Cell($this->width * 1.33, $this->height, $expense->price + $expense_service_charge_amount, 1);

                $this->Ln();
            }
        }

        if (!$phases['daily_wagers']->isEmpty()) {

            $this->Ln();

            $this->cell(47 * 4, 10, ucwords("Daily Wager"), 0, 1, 'C');


            foreach ($phases['daily_wagers'] as $d => $daily_wager) {
                if ($d === 0) {
                    $this->Cell($this->width, $this->height, 'Date', 1);
                    $this->Cell($this->width, $this->height, 'Wager Name', 1);
                    $this->Cell($this->width, $this->height, 'Price Per Wager', 1);
                    $this->Cell($this->width, $this->height, 'Total Price', 1);
                    $this->Ln();
                }
                $this->Cell($this->width, $this->height, $daily_wager->created_at, 1);
                $this->Cell($this->width, $this->height, $daily_wager->wager_name, 1);
                $this->Cell($this->width, $this->height, $daily_wager->price_per_day, 1);

                $wager_service_charge = $this->getServiceChargeAmount($daily_wager->wager_total, $phases['service_charge']);

                $this->Cell($this->width, $this->height, $wager_service_charge + $daily_wager->wager_total, 1);
                $this->Ln();
            }
            // $this->AddPage();
        }

        if (!$phases['wager_attendances']->isEmpty()) {

            $this->Ln();

            $this->cell(47 * 4, 10, ucwords(" Attendance"), 0, 1, 'C');

            foreach ($phases['wager_attendances'] as $a => $attendance) {
                if ($a === 0) {
                    $this->Cell($this->width, $this->height, 'Date', 1);
                    $this->Cell($this->width, $this->height, 'No Of Persons', 1);
                    $this->Cell($this->width, $this->height, 'Wager Name', 1);
                    $this->Cell($this->width, $this->height, 'Suppier', 1);
                    $this->Ln();
                }
                // if ($k > 0 ) {
                $this->Cell($this->width, $this->height, $attendance->created_at, 1);
                $this->Cell($this->width, $this->height, $attendance->no_of_persons, 1);
                $this->Cell($this->width, $this->height, $attendance->dailyWager->wager_name, 1);
                $this->Cell($this->width, $this->height, $attendance->dailyWager->supplier->name, 1);
                // $this->AddPage();
                // }
                $this->Ln();
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

    function ledgerTable($ledgers, $total_paid, $total_due, $total_balance, $effective_balance)
    {

        $this->SetTextColor(0, 170, 183);
        $this->Text(188 / 2, 40, 'SiteMaster');

        $this->SetMargins($this->m_left, $this->m_top, $this->m_right);
        $this->SetFontSize($this->font_size);

        $this->Ln();
        $this->Ln();

        // Summary section styling
        $this->SetFillColor($this->fill_r, $this->fill_g, $this->fill_b);
        $this->SetTextColor($this->r, $this->g, $this->b);

        // Summary cells
        $this->Cell($this->width * 2.18, $this->height, 'Total Balance', 1, 0, 'L', true);
        $this->Cell($this->width * 2.18, $this->height, $total_balance, 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2.18, $this->height, 'Total Due', 1, 0, 'L', true);
        $this->Cell($this->width * 2.18, $this->height, $total_due, 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2.18, $this->height, 'Total Paid', 1, 0, 'L', true);
        $this->Cell($this->width * 2.18, $this->height, $total_paid, 1, 0, 'L', true);
        $this->Ln();

        $this->Cell($this->width * 2.18, $this->height, 'Effective Balance', 1, 0, 'L', true);
        $this->Cell($this->width * 2.18, $this->height, $effective_balance, 1, 0, 'L', true);
        $this->Ln();
        $this->Ln();

        // Table header styling
        $this->SetFillColor(81, 177, 225);
        $this->SetTextColor(255, 255, 255);

        // Table headers
        $this->Cell($this->width / 3.5, $this->height, 'Date', 1, 0, 'L', true);
        $this->Cell($this->width / 1.7, $this->height, 'Transaction Type', 1, 0, 'L', true);
        $this->Cell($this->width / 1.55, $this->height, 'Supplier Name', 1, 0, 'L', true);
        $this->Cell($this->width / 1.68, $this->height, 'Site Name', 1, 0, 'L', true);
        $this->Cell($this->width / 2, $this->height, 'Phase', 1, 0, 'L', true);
        $this->Cell($this->width / 2.6, $this->height, 'Type', 1, 0, 'L', true);
        $this->Cell($this->width / 1.7, $this->height, 'Narration', 1, 0, 'L', true);
        $this->Cell($this->width / 2.6, $this->height, 'Debit', 1, 0, 'L', true);
        $this->Cell($this->width / 2.6, $this->height, 'Credit', 1, 0, 'L', true);
        $this->Ln();

        // Table content styling
        $this->SetTextColor($this->r, $this->g, $this->b);

        // Table content
        foreach ($ledgers as $ledger) {
            $cellHeight = 6;
            $maxHeight = 0;

            // Calculate max height needed for wrapped text
            $descriptionWidth = $this->GetStringWidth(ucwords($ledger['description']));
            $supplierWidth = $this->GetStringWidth(ucwords($ledger['supplier']));

            $descriptionHeight = ceil($descriptionWidth / ($this->width / 1.7)) * $cellHeight;
            $supplierHeight = ceil($supplierWidth / ($this->width / 1.55)) * $cellHeight;

            $maxHeight = max($cellHeight, $descriptionHeight, $supplierHeight);

            $x = $this->GetX();
            $y = $this->GetY();

            $this->Cell($this->width / 3.5, $maxHeight, $ledger['created_at']->format('d-M-y'), 1, 0, 'L');
            $this->Cell($this->width / 1.7, $maxHeight, ucwords($ledger['transaction_type']), 1, 0, 'L');

            // MultiCell for supplier name
            $this->MultiCell($this->width / 1.55, $cellHeight, ucwords($ledger['supplier']), 1, 'L');
            $this->SetXY($x + ($this->width / 3.5) + ($this->width / 1.7) + ($this->width / 1.55), $y);

            $this->Cell($this->width / 1.68, $maxHeight, ucwords($ledger['site']), 1, 0, 'L');
            $this->Cell($this->width / 2, $maxHeight, ucwords($ledger['phase']), 1, 0, 'L');
            $this->Cell($this->width / 2.6, $maxHeight, $ledger['category'], 1, 0, 'L');

            // MultiCell for description
            $x = $this->GetX();
            $this->MultiCell($this->width / 1.7, $cellHeight, ucwords($ledger['description']), 1, 'L');
            $this->SetXY($x + ($this->width / 1.7), $y);

            $this->Cell($this->width / 2.6, $maxHeight, $ledger['debit'], 1, 0, 'L');
            $this->Cell($this->width / 2.6, $maxHeight, $ledger['credit'], 1, 1, 'L');
        }
    }


    private function getServiceChargeAmount($amount, $serviceCharge)
    {

        $serviceChargeAmount = 0;

        $serviceChargeAmount = ($serviceCharge / 100) * $amount;

        return $serviceChargeAmount;
    }
}
