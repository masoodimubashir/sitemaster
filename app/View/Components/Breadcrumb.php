<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public $breadcrumbs;
    public $names;
    public $urls;

    public function __construct($names = [], $urls = [])
    {
        $this->names = $names;
        $this->urls = $urls;
        $this->breadcrumbs = $this->generateBreadcrumbs();
    }

    private function generateBreadcrumbs()
    {
        $breadcrumbs = [];



        // Generate breadcrumbs from passed parameters
        foreach ($this->names as $index => $name) {
            $breadcrumbs[] = [
                'name' => $name,
                'url' => $this->urls[$index] ?? '#',
                'active' => $index === count($this->names) - 1
            ];
        }

        // dd($breadcrumbs);

        return $breadcrumbs;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
