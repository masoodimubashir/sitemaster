<?php


namespace App\Http\Controllers\Admin;

use Fpdf\Fpdf;
use Illuminate\Support\Number;

class PDF extends Fpdf
{

    private int $height = 10;
    private int  $width = 47;

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

        // dd($headers, $data);

        if (empty($data)) {
            $this->Cell(0, 10, 'No site data available', 1);
            $this->Ln();
            return;
        }

        $this->Ln();
        $this->Ln();

        $this->Text(188 / 2, 40, 'SiteMaster');

        // Header row
        $this->Cell($this->width * 2, $this->height, $headers['box1'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($data['site_name']), 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box2'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $data['contact_no'], 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box3'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $data['service_charge'], 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box4'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $data['balance'], 1, 0, 'L'); // Default to 1000 if not set

        $this->Ln();
        $this->Ln();

        // Second header row
        $this->Cell($this->width * 2, $this->height, $headers['box5'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($data['site_owner_name']), 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box6'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($data['location']), 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box7'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $data['debit'], 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box8'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $data['credit'], 1, 0, 'L');

        $this->AddPage();
    }

    function siteTableData($phases)
    {

        if (empty($phases)) {
            $this->Cell(0, 10, 'No data available', 1);
            $this->Ln();
            return;
        }


        // dd($phases);



        $this->Ln();



        foreach ($phases as $key =>  $phase) {

            // dd($phase);

            $phase_total_service_charge_Amount = $this->getServiceChargeAmount($phase['phase_total'], $phase['site_service_charge']);
            $construction_total_amount_with_service_charge =  $this->getServiceChargeAmount($phase['construction_total_amount'], $phase['site_service_charge']) + $phase['construction_total_amount'];
            $square_footage_total_amount_with_service_charge =  $this->getServiceChargeAmount($phase['square_footage_total_amount'], $phase['site_service_charge']) + $phase['square_footage_total_amount'];
            $daily_expenses_total_amount_with_service_charge =  $this->getServiceChargeAmount($phase['daily_expenses_total_amount'], $phase['site_service_charge']) + $phase['daily_expenses_total_amount'];
            $daily_wagers_total_amount_with_service_charge =  $this->getServiceChargeAmount($phase['daily_wagers_total_amount'], $phase['site_service_charge']) + $phase['daily_wagers_total_amount'];

            $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
            $this->Cell($this->width, $this->height, 'Amount', 1, 0, 'L');
            $this->Cell($this->width, $this->height, 'Service Charge', 1, 0, 'L');
            $this->Cell($this->width, $this->height, 'Total', 1, 0, 'L');
            $this->Ln();

            $this->Cell($this->width, $this->height, 'Raw Material', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase['construction_total_amount'], 1, 0, 'L');
            $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $construction_total_amount_with_service_charge, 1, 0, 'L');

            $this->Ln();

            $this->Cell($this->width, $this->height, 'Square Footage', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase['square_footage_total_amount'], 1, 0, 'L');
            $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $square_footage_total_amount_with_service_charge, 1, 0, 'L');



            $this->Ln();

            $this->Cell($this->width, $this->height, 'Daily Expenses', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase['daily_expenses_total_amount'], 1, 0, 'L');
            $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $daily_expenses_total_amount_with_service_charge, 1, 0, 'L');



            $this->Ln();

            $this->Cell($this->width, $this->height, 'Wager', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase['daily_wagers_total_amount'], 1, 0, 'L');
            $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $daily_wagers_total_amount_with_service_charge, 1, 0, 'L');



            $this->Ln();

            $this->Cell($this->width, $this->height, 'Sub Total', 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase['phase_total'], 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase_total_service_charge_Amount, 1, 0, 'L');
            $this->Cell($this->width, $this->height, $phase['phase_total_with_service_charge'], 1, 0, 'L');


            if (!$phase['construction_material_billings']->isEmpty()) {

                $this->cell(47 * 4, 10, ucwords($phase['phase'] . ' Phase'), 0, 1, 'C');
                $this->cell(47 * 4, 10, ucwords('Construction Materials' . ' Phase'), 0, 1, 'C');

                foreach ($phase['construction_material_billings'] as $m =>  $materials) {
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

                    $this->Cell($this->width, $this->height, $materials->amount, 1);
                    $this->Ln();
                }
                // $this->AddPage();

            }

            if (!$phase['square_footage_bills']->isEmpty()) {

                $this->Ln();

                $this->cell(47 * 4, 10, ucwords("Square Footage Bills"), 0, 1, 'C');

                foreach ($phase['square_footage_bills'] as $s => $sqft) {
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
                    $this->Cell(31.3, $this->height, $sqft->multiplier * $sqft->price, 1);
                    $this->Ln();
                }
                // $this->AddPage();
            }

            if (!$phase['daily_expenses']->isEmpty()) {

                $this->Ln();

                $this->cell(47 * 4, 10, ucwords("Daily Expenses"), 0, 1, 'C');


                foreach ($phase['daily_expenses'] as $e => $expense) {
                    if ($e === 0) {
                        $this->Cell($this->width * 2, $this->height, 'Date', 1);
                        $this->Cell($this->width * 2, $this->height, 'Item Name', 1);
                        // $this->Cell($this->width * 2, $this->height, 'Bill Photo', 1);
                        $this->Ln();
                    }
                    $this->Cell($this->width * 2, $this->height, $expense->created_at, 1);
                    $this->Cell($this->width * 2, $this->height, $expense->price, 1);

                    // $this->Cell($this->width * 2, $this->height, $expense->bill_photo, 1);
                    $this->Ln();
                }
                // $this->AddPage();
            }

            if (!$phase['daily_wagers']->isEmpty()) {

                $this->Ln();

                $this->cell(47 * 4, 10, ucwords("Daily Wager"), 0, 1, 'C');


                foreach ($phase['daily_wagers'] as $d => $daily_wager) {
                    if ($d === 0) {
                        $this->Cell(62.45, $this->height, 'Date', 1);
                        $this->Cell(62.45, $this->height, 'Wager Name', 1);
                        $this->Cell(62.45, $this->height, 'Price Per Day', 1);
                        $this->Ln();
                    }
                    $this->Cell(62.45, $this->height, $expense->created_at, 1);
                    $this->Cell(62.45, $this->height, $daily_wager->wager_name, 1);
                    $this->Cell(62.45, $this->height, $daily_wager->price_per_day, 1);
                    $this->Ln();
                }
                // $this->AddPage();
            }

            if (!$phase['wager_attendances']->isEmpty()) {

                $this->Ln();

                $this->cell(47 * 4, 10, ucwords(" Attendance"), 0, 1, 'C');


                foreach ($phase['wager_attendances'] as $a => $attendance) {
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
                    $this->Cell($this->width, $this->height, $attendance->no_of_persons, 1);
                    // $this->AddPage();
                    // }
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
            $this->Cell(0, 10, 'No data available', 1);
            $this->Ln();
            return;
        }

        $this->Text(188 / 2, 40, 'SiteMaster');

        $this->Ln();
        $this->Ln();

        // Header row
        $this->Cell($this->width * 2, $this->height, $headers['box1'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($phases['phase_name']), 1, 0, 'L');

        $this->Ln();


        $this->Cell($this->width * 2, $this->height, $headers['box2'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($phases['site_name']), 1, 0, 'L');

        $this->Ln();


        $this->Cell($this->width * 2, $this->height, $headers['box3'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $phases['contact_no'], 1, 0, 'L');

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, $headers['box4'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, $phases['service_charge'], 1, 0, 'L');

        $this->Ln();

        // Second header row
        $this->Cell($this->width * 2, $this->height, $headers['box6'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($phases['site_owner_name']), 1, 0, 'L');

        $this->Ln();


        $this->Cell($this->width * 2, $this->height, $headers['box7'], 1, 0, 'L');
        $this->Cell($this->width * 2, $this->height, ucwords($phases['location']), 1, 0, 'L');


        $this->Ln();
        $this->Ln();


        $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
        $this->Cell($this->width, $this->height, 'Amount', 1, 0, 'L');
        $this->Cell($this->width, $this->height, 'Service Charge', 1, 0, 'L');
        $this->Cell($this->width, $this->height, 'Total', 1, 0, 'L');
        $this->Ln();



        // Get Service Charge Amount Of Tables And Total Phase Cost

        $total_service_charge_amount = $this->getServiceChargeAmount($phaseCosting['total_amount'], $phases['service_charge']);
        $total_service_charge_with_amount =  $total_service_charge_amount + $phaseCosting['total_amount'];
        $construction_total_amount_with_service_charge =  $this->getServiceChargeAmount($phaseCosting['construction_total_amount'], $phases['service_charge']) + $phaseCosting['construction_total_amount'];
        $square_footage_total_amount_with_service_charge =  $this->getServiceChargeAmount($phaseCosting['square_footage_total_amount'], $phases['service_charge']) + $phaseCosting['square_footage_total_amount'];
        $daily_expenses_total_amount_with_service_charge =  $this->getServiceChargeAmount($phaseCosting['daily_expenses_total_amount'], $phases['service_charge']) + $phaseCosting['daily_expenses_total_amount'];
        $daily_wagers_total_amount_with_service_charge =  $this->getServiceChargeAmount($phaseCosting['daily_wagers_total_amount'], $phases['service_charge']) + $phaseCosting['daily_wagers_total_amount'];

        $this->Cell($this->width, $this->height, 'Raw Material', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $phaseCosting['construction_total_amount'], 1, 0, 'L');
        $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $construction_total_amount_with_service_charge, 1, 0, 'L');



        $this->Ln();

        $this->Cell($this->width, $this->height, 'Square Footage', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $phaseCosting['square_footage_total_amount'], 1, 0, 'L');
        $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $square_footage_total_amount_with_service_charge, 1, 0, 'L');



        $this->Ln();

        $this->Cell($this->width, $this->height, 'Daily Expenses', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $phaseCosting['daily_expenses_total_amount'], 1, 0, 'L');
        $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $daily_expenses_total_amount_with_service_charge, 1, 0, 'L');



        $this->Ln();

        $this->Cell($this->width, $this->height, 'Wager', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $phaseCosting['daily_wagers_total_amount'], 1, 0, 'L');
        $this->Cell($this->width, $this->height, '.....', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $daily_wagers_total_amount_with_service_charge, 1, 0, 'L');



        $this->Ln();

        $this->Cell($this->width, $this->height, 'Sub Total', 1, 0, 'L');
        $this->Cell($this->width, $this->height, $phaseCosting['total_amount'], 1, 0, 'L');
        $this->Cell($this->width, $this->height, $total_service_charge_amount, 1, 0, 'L');
        $this->Cell($this->width, $this->height, $total_service_charge_with_amount, 1, 0, 'L');



        $this->Ln();





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


                $this->Cell(31.3, $this->height, $sqft_total_amount + $sqft_service_charge_amount , 1);
                $this->Ln();
            }
            // $this->AddPage();
        }

        if (!$phases['daily_expenses']->isEmpty()) {

            $this->Ln();

            $this->cell(47 * 4, 10, ucwords("Daily Expenses"), 0, 1, 'C');


            foreach ($phases['daily_expenses'] as $e => $expense) {
                if ($e === 0) {
                    $this->Cell($this->width * 2, $this->height, 'Date', 1);
                    $this->Cell($this->width * 2, $this->height, 'Item Name', 1);
                    // $this->Cell($this->width * 2, $this->height, 'Bill Photo', 1);
                    $this->Ln();
                }
                $this->Cell($this->width * 2, $this->height, $expense->created_at, 1);

                $expense_service_charge_amount = $this->getServiceChargeAmount($expense->price, $phases['service_charge']);

                $this->Cell($this->width * 2, $this->height, $expense->price + $expense_service_charge_amount, 1);

                // $this->Cell($this->width * 2, $this->height, $expense->bill_photo, 1);
                $this->Ln();
            }
            // $this->AddPage();
        }

        if (!$phases['daily_wagers']->isEmpty()) {

            $this->Ln();

            $this->cell(47 * 4, 10, ucwords("Daily Wager"), 0, 1, 'C');


            foreach ($phases['daily_wagers'] as $d => $daily_wager) {
                if ($d === 0) {
                    $this->Cell(62.45, $this->height, 'Date', 1);
                    $this->Cell(62.45, $this->height, 'Wager Name', 1);
                    $this->Cell(62.45, $this->height, 'Price Per Day', 1);
                    $this->Ln();
                }
                $this->Cell(62.45, $this->height, $expense->created_at, 1);
                $this->Cell(62.45, $this->height, $daily_wager->wager_name, 1);
                $this->Cell(62.45, $this->height, $daily_wager->price_per_day, 1);
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
                $this->Cell($this->width, $this->height, $attendance->no_of_persons, 1);
                // $this->AddPage();
                // }
                $this->Ln();
            }
        }


        // if ($key < count($phases) - 1) {
        //     $this->AddPage();
        // } else {
        //     return;
        // }

    }

    function supplierPaymentTable($supplier)
    {
        if ($supplier->paymentSuppliers->isEmpty()) {
            $this->Cell(0, 10, 'No Data Awailable', 0, 0, 'C');
            $this->Ln();
            return;
        }


        $this->Cell($this->width * 4, $this->height, 'Payment History', 0, 0, 'C');

        $this->Ln();
        $this->Ln();

        foreach ($supplier->paymentSuppliers as $key => $payment) {
            if ($key === 0) {

                $this->Cell($this->width, $this->height, 'Date', 1);
                $this->Cell($this->width, $this->height, 'Site Name', 1);
                $this->Cell($this->width, $this->height, 'Site Owner', 1);
                $this->Cell($this->width, $this->height, 'Amount', 1);
                $this->Ln();
            }
            $this->Cell($this->width, $this->height, $payment->created_at->format('D-M'), 1);
            $this->Cell($this->width, $this->height, $payment->site->site_name, 1);
            $this->Cell($this->width, $this->height, ucwords($payment->site->site_owner_name), 1);
            $this->Cell($this->width, $this->height, $payment->amount, 1);
        }
    }

    function sitePaymentTable($site)
    {

        $this->Text(188 / 2, 40, 'SiteMaster');

        $this->Ln();
        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Site Name', 1, 0,);
        $this->Cell($this->width * 2, $this->height, $site->site_name, 1, 0,);

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Location', 1, 0,);
        $this->Cell($this->width * 2, $this->height, $site->location, 1, 0,);

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Contact No', 1, 0,);
        $this->Cell($this->width * 2, $this->height, $site->contact_no, 1, 0,);

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Service Charge', 1, 0,);
        $this->Cell($this->width * 2, $this->height, $site->service_charge, 1, 0,);

        $this->Ln();

        $this->Cell($this->width * 2, $this->height, 'Site Owner', 1, 0,);
        $this->Cell($this->width * 2, $this->height, $site->site_owner_name, 1, 0,);

        $this->Ln();
        $this->Ln();


        foreach ($site->paymeentSuppliers as $key => $site_payment) {

            if ($key === 0) {
                $this->Cell($this->width * 1.34, $this->height, 'Date', 1, 0,);
                $this->Cell($this->width * 1.34, $this->height, 'Supplier', 1, 0,);
                $this->Cell($this->width * 1.34, $this->height, 'Amount', 1, 0,);
                $this->Ln();
            }

            $this->Cell($this->width * 1.34, $this->height, $site_payment->created_at->format('D-M-y'), 1, 0,);
            $this->Cell($this->width * 1.34, $this->height, $site_payment->supplier->name, 1, 0,);
            $this->Cell($this->width * 1.34, $this->height, $site_payment->amount, 1, 0,);
        }
    }

    function ledgerTable($ledgers, $total_paid, $total_due, $total_balance)
    {

        $this->SetMargins(3, 0, 3);

        $this->Text(188 / 2, 40, 'Ledger Report');

        $this->Ln();
        $this->Ln();

        $this->Cell($this->width * 2.18, $this->height, 'Total Balance', 1, 0,);
        $this->Cell($this->width * 2.18, $this->height, $total_balance, 1, 0,);

        $this->Ln();

        $this->Cell($this->width * 2.18, $this->height, 'Total Due', 1, 0,);
        $this->Cell($this->width * 2.18, $this->height, $total_due, 1, 0,);

        $this->Ln();

        $this->Cell($this->width * 2.18, $this->height, 'Total Paid', 1, 0,);
        $this->Cell($this->width * 2.18, $this->height, $total_paid, 1, 0,);

        $this->Ln();

        // $this->Cell($this->width * 2.18, $this->height, 'Ongoing Site ', 1, 0,);
        // $this->Cell($this->width * 2.18, $this->height, $is_ongoing_count, 1, 0,);

        // $this->Ln();

        // $this->Cell($this->width * 2.18, $this->height, 'Closed Site', 1, 0,);
        // $this->Cell($this->width * 2.18, $this->height, $is_not_ongoing_count, 1, 0,);

        $this->Ln();

        foreach ($ledgers as $key => $ledger) {

            $this->SetFont('', '', 8);

            // $this->Cell($this->width / 1.5, $this->height, $key, 1, 0,);

            if ($key === 1) {

                $this->Cell($this->width / 1.5, $this->height, 'Date', 1, 0,);
                $this->Cell($this->width / 1.15, $this->height, 'Supplier', 1, 0,);
                $this->Cell($this->width / 2.5, $this->height, 'Phase', 1, 0,);
                $this->Cell($this->width / 2, $this->height, 'Site', 1, 0,);
                $this->Cell($this->width / 1.15, $this->height, 'Type', 1, 0,);
                $this->Cell($this->width / 2.9, $this->height, 'Information', 1, 0,);
                $this->Cell($this->width / 2.5, $this->height, 'Debit', 1, 0,);
                $this->Cell($this->width / 3.2, $this->height, 'Credit', 1, 0,);
                $this->Ln();
            }

            $this->Cell($this->width / 1.5, $this->height, $ledger['created_at'], 1, 0,);
            $this->Cell($this->width / 1.15, $this->height, $ledger['category'] === 'Daily Expense' ? $ledger['category'] : ucwords($ledger['supplier']), 1, 0,);
            $this->Cell($this->width / 2.5, $this->height, ucwords($ledger['phase']), 1, 0,);
            $this->Cell($this->width / 2, $this->height, ucwords($ledger['site']), 1, 0,);
            $this->Cell($this->width / 1.15, $this->height, $ledger['category'], 1, 0,);
            $this->Cell($this->width / 2.9, $this->height, ucwords($ledger['description']), 1, 0,);
            $this->Cell($this->width / 2.5, $this->height, $ledger['debit'], 1, 0,);
            $this->Cell($this->width / 3.2, $this->height, $ledger['credit'], 1, 0,);
            $this->Ln();
        }
    }

    private function getServiceChargeAmount($amount, $serviceCharge)
    {

        $serviceChargeAmount = 0;

        $serviceChargeAmount = ($serviceCharge / 100) * $amount;

        return $serviceChargeAmount;
    }
}
