<?php

namespace App\Livewire\Admin\Settings;

use App\Actions\Pin\UbahPinAction;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Security extends Component
{
    public string $current_pin = '';

    public string $pin = '';

    public string $pin_confirmation = '';

    public function updatePin(): void
    {
        try {
            $validated = $this->validate([
                'current_pin' => ['required', 'string', 'digits:6'],
                'pin' => ['required', 'string', 'digits:6', 'confirmed'],
            ]);

            app(UbahPinAction::class)->execute(
                Auth::user(),
                $validated['current_pin'],
                $validated['pin'],
            );
        } catch (ValidationException $e) {
            $this->reset('current_pin', 'pin', 'pin_confirmation');

            throw $e;
        }

        $this->reset('current_pin', 'pin', 'pin_confirmation');

        Flux::toast(variant: 'success', text: 'PIN berhasil diperbarui.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.security');
    }
}
