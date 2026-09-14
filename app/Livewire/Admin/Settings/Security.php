<?php

namespace App\Livewire\Admin\Settings;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
                'current_pin' => ['required', 'string', function ($attribute, $value, $fail) {
                    if (! Hash::check($value, Auth::user()->pin_hash)) {
                        $fail(__('PIN saat ini tidak sesuai.'));
                    }
                }],
                'pin' => ['required', 'string', 'digits:6', 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_pin', 'pin', 'pin_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'pin_hash' => Hash::make($validated['pin']),
            'harus_ganti_pin' => false,
        ]);

        $this->reset('current_pin', 'pin', 'pin_confirmation');

        Flux::toast(variant: 'success', text: 'PIN berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.admin.settings.security');
    }
}
