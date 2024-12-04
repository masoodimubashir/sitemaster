<?php

namespace App\Charts;

use App\Models\PaymentSupplier;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class PaymentChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct(private $payments)
    {
        parent::__construct();

        $monthlyTotals = PaymentSupplier::where('verified_by_admin', 1)->selectRaw('SUM(amount) as total_amount, DATE_FORMAT(created_at, "%M-%y") as month')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $labels = $monthlyTotals->pluck('month')->toArray();
        $data = $monthlyTotals->pluck('total_amount')->toArray();

        $this->labels($labels);

        $this->dataset('Monthly Payments', 'bar', $data)
            ->backgroundColor('rgb(255, 255, 255)');
            // ->borderColor('rgba(54, 162, 235, 1)')
            // ->borderWidth(1);
    }
}
