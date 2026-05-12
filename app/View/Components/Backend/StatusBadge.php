<?php

namespace App\View\Components\Backend;

use Illuminate\View\Component;

class StatusBadge extends Component
{
    public $value;
    public $type;
    public $color;
    public $label;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $type, $value = null, $color = null, $label = null)
    {
        $this->type  = $type;
        $this->value = $value;
        $this->color = $color;
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('backend.components.status-badge');
    }
}
