<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GeneralDetail extends Component
{
    /**
     * Create a new component instance.
     */


    public function __construct(public $balance = null)
    {
        $this->balance = $balance;

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.general-detail');
    }
}
