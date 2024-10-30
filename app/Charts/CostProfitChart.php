<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class CostProfitChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct(private  $expense, private  $revenue)
    {
        parent::__construct();

        $this->labels(['Revenue', 'Expense']);

        $this->dataset('Site Balance Due Chart', 'pie', [$revenue, $expense])
            ->backgroundColor([
                '#FFFF00',
                '#000075',
            ]);
    }
}