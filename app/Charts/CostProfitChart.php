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
    public function __construct(private  $revenue, private  $profit)
    {
        parent::__construct();

        $this->labels(['Revenue', 'Profit']);

        $this->dataset('Site Balance Due Chart', 'pie', [$revenue, $profit])
            ->backgroundColor([
                '#FFFF00',
                '#000075',
            ]);
    }
}
