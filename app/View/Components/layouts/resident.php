<?php

namespace App\View\Components\layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class resident extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $title = null
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.layouts.resident');
    }
}
