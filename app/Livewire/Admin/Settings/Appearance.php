<?php

namespace App\Livewire\Admin\Settings;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Appearance extends Component
{
    public function render(): View
    {
        return view('livewire.admin.settings.appearance');
    }
}
