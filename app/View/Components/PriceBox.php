<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PriceBox extends Component
{
    public $product;
    public $viewType;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($product, $viewType = 'grid')
    {
        $this->product = $product;
        $this->viewType = $viewType;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.price-box');
    }
}
