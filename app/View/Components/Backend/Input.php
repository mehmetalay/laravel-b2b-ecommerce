<?php

namespace App\View\Components\Backend;

use Illuminate\View\Component;

class Input extends Component
{
    public $id, $label, $value, $type, $required;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $type, $value = null, $required = false, $label = null)
    {
        $this->id = $id;
        $this->label = $label;
        $this->type = $type;
        $this->value = $value;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('backend.components.input');
    }
}
