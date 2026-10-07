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
        <label for="{{ $id }}" class="text-sm font-medium text-[#171717]">
            {{ $label }}@if ($wajib) <span aria-hidden="true" class="text-rose-600">*</span>@endif
        </label>
    @endif

    <div wire:ignore>
        <input type="text" id="{{ $id }}" value="{{ $nilai }}" autocomplete="off" spellcheck="false"
            placeholder="{{ $placeholder }}"
            x-data="pickerTanggal({{ \Illuminate\Support\Js::from($konfigurasi) }})"
            x-init="mulai()"
            data-tanggal-picker
            {{ $attributes->except(['wire:model.live', 'wire:model.defer', 'wire:model', 'id', 'value']) }} />
    </div>
</div>
