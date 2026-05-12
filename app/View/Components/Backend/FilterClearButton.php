<?php

namespace App\View\Components\Backend;

use Illuminate\View\Component;

class FilterClearButton extends Component
{
    public string $route;

    public function __construct(string $route)
    {
        $this->route = $route;
    }

    public function render()
    {
        return view('backend.components.filter-clear-button');
    }
}

