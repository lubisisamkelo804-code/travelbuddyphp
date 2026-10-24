<?php

namespace App\View\Composers;

use App\Support\TravelRail;
use Illuminate\View\View;

class RailComposer
{
    public function __construct(private TravelRail $rail)
    {
    }

    public function compose(View $view): void
    {
        $view->with('rail', $this->rail->build());
    }
}