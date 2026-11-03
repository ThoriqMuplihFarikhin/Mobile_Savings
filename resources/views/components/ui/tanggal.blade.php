@props([
    'mode' => 'tanggal',
    'min' => null,
    'max' => null,
    'placeholder' => null,
    'label' => null,
    'wajib' => false,
    'nilai' => null,
])

@php
    if (! in_array($mode, ['tanggal', 'bulan', 'rentang'], true)) {
        throw new \InvalidArgumentException("Mode tanggal tidak dikenal: {$mode}");
    }

    $atribut = $attributes->getAttributes();
    $wireModel = $atribut['wire:model.live'] ?? $atribut['wire:model.defer'] ?? $atribut['wire:model'] ?? null;
    $hidup = isset($atribut['wire:model.live']);
    $id = $atribut['id'] ?? 'tanggal-'.substr(md5((string) ($wireModel ?? uniqid())), 0, 8);

    $placeholder = $placeholder ?? match ($mode) {
        'bulan' => 'Pilih bulan',
        'rentang' => 'Pilih rentang',
        default => 'Pilih tanggal',
    };

    $konfigurasi = [
        'mode' => $mode,
        'model' => $wireModel,
        'live' => $hidup,
        'min' => $min,
        'max' => $max,
    ];
@endphp

<div @if ($label !== null && $label !== '') class="flex flex-col gap-1.5" @endif>
    @if ($label !== null && $label !== '')
        <label for="{{ $id }}" class="text-sm font-medium text-body">
            {{ $label }}@if ($wajib) <span aria-hidden="true" class="text-danger">*</span>@endif
        </label>
    @endif

    <div wire:ignore class="relative flex items-center">
        <input type="text" id="{{ $id }}" value="{{ $nilai }}" autocomplete="off" spellcheck="false"
            placeholder="{{ $placeholder }}"
            x-data="pickerTanggal({{ \Illuminate\Support\Js::from($konfigurasi) }})"
            x-init="mulai()"
            data-tanggal-picker
            {{ $attributes->except(['wire:model.live', 'wire:model.defer', 'wire:model', 'id', 'value']) }} />
        <div class="pointer-events-none absolute right-3 flex items-center text-mute" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>
</div>
