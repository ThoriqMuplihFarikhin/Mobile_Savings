<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Appearance settings')] class extends Component {
    public function render()
    {
        return view('pages.settings.⚡appearance')
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

    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-pages::settings.layout>
</section>
