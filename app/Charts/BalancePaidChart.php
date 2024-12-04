<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class BalancePaidChart extends Chart
{


    /**
     * Initializes the chart.
     *
     * @return void
     */

    public function __construct(private int $balance, private int $paid)
    {
        parent::__construct();

        $this->labels(['Balance', 'Paid']);



        $this->dataset('', 'doughnut', [$balance, $paid])
            ->backgroundColor([
                'rgb(144, 238, 144)',
                '#750000',
            ]);
    }
}
