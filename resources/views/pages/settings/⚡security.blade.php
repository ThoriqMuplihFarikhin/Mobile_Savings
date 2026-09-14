<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Security settings')] class extends Component {
    public string $current_pin = '';
    public string $pin = '';
    public string $pin_confirmation = '';

    public function mount(): void
    {
    }

    public function updatePin(): void
    {
        try {
            $validated = $this->validate([
                'current_pin' => ['required', 'string', function ($attribute, $value, $fail) {
                    if (! Hash::check($value, Auth::user()->pin_hash)) {
                        $fail(__('The provided PIN does not match your current PIN.'));
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
        ]);

        $this->reset('current_pin', 'pin', 'pin_confirmation');

        Flux::toast(variant: 'success', text: __('PIN updated.'));
    }

    public function render()
    {
        return view('pages.settings.⚡security')
            ->layout('layouts.mobile');
    }
}; ?>

<section class="w-full">
    @php
        $backRoute = match(Auth::user()->role) {
            'nasabah' => route('nasabah.pengaturan.index'),
            'kolektor' => route('kolektor.pengaturan.index'),
            default => route('dashboard'),
        };
    @endphp

    {{-- Back button --}}
    <a href="{{ $backRoute }}" wire:navigate
       class="mb-4 inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#fafafa] text-[#171717] shadow-[inset_0_0_0_1px_#ebebeb] transition hover:bg-[#ebebeb] dark:bg-zinc-700 dark:text-white dark:shadow-[inset_0_0_0_1px_#3f3f46] dark:hover:bg-zinc-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
    </a>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#ffefcf] text-[#ab570a]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
        </div>
        <div>
            <h1 class="text-lg font-bold text-[#171717] dark:text-white">Ganti PIN</h1>
            <p class="text-sm text-[#888888] dark:text-zinc-400">Pastikan PIN Anda aman dan mudah diingat.</p>
        </div>
    </div>

    {{-- Form --}}
    <form wire:submit="updatePin" class="space-y-5">
        {{-- PIN Saat Ini --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">PIN Saat Ini</label>
            <input type="password" wire:model="current_pin" required
                   maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="off"
                   class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm tracking-[0.5em] text-[#171717] focus:border-[#ab570a] focus:outline-none focus:ring-2 focus:ring-[#ab570a]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-[#ab570a] dark:focus:ring-[#ab570a]/20" />
            @error('current_pin') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
        </div>

        {{-- PIN Baru --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">PIN Baru</label>
            <input type="password" wire:model="pin" required
                   maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="off"
                   class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm tracking-[0.5em] text-[#171717] focus:border-[#ab570a] focus:outline-none focus:ring-2 focus:ring-[#ab570a]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-[#ab570a] dark:focus:ring-[#ab570a]/20" />
            @error('pin') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
        </div>

        {{-- Konfirmasi PIN --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">Konfirmasi PIN Baru</label>
            <input type="password" wire:model="pin_confirmation" required
                   maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="off"
                   class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm tracking-[0.5em] text-[#171717] focus:border-[#ab570a] focus:outline-none focus:ring-2 focus:ring-[#ab570a]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-[#ab570a] dark:focus:ring-[#ab570a]/20" />
            @error('pin_confirmation') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
        </div>

        {{-- Tombol Update --}}
        <button type="submit"
                class="w-full rounded-xl bg-[#ffefcf] py-3 text-sm font-semibold text-[#ab570a] transition hover:bg-[#ffe4b5] dark:bg-[#ab570a]/20 dark:text-[#ffefcf] dark:hover:bg-[#ab570a]/30">
            Update PIN
        </button>
    </form>
</section>
