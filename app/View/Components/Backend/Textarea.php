<?php

namespace App\View\Components\Backend;

use Illuminate\View\Component;

class Textarea extends Component
{
    public $name;
    public $value;
    public $rows;
    public $label;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $value = null, $rows = 3, $label = null)
    {
        $this->name  = $name;
        $this->value = $value;
        $this->rows  = $rows;
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('backend.components.textarea');
    }
}
