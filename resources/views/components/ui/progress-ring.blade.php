@props([
    'persen' => 0,
    'ukuran' => 88,
    'lebar' => 10,
    'label' => null,
    'centang' => false,
])

@php
    /**
     * DESIGN.md §7 "Cincin progres, lencana, dan streak": track primary-soft, isi
     * primary, dan persentase di tengah dengan ukuran teks ¼
     * diameter serta bobot 600. Wajib role="img" dan aria-label berisi persen
     * (§10); angka di tengah hanya hiasan, jadi aria-hidden. `centang` mengganti
     * angka di tengah dengan ikon centang untuk lencana barang yang tercapai
     * (§7); aria-label memakai `label` dari view, dengan fallback "Tercapai".
     */
    $persen = max(0, min(100, (int) $persen));
    $ukuran = max(24, (int) $ukuran);
    $lebar = max(1, min($ukuran - 2, (int) $lebar));
    $centang = (bool) $centang;

    $jari = ($ukuran - $lebar) / 2;
    $keliling = 2 * pi() * $jari;
    $panjangIsi = $keliling * $persen / 100;
    $pusat = $ukuran / 2;

    $aria = is_string($label) && $label !== '' ? $label : ($centang ? 'Tercapai' : $persen.' persen');
    $ukuranTeks = (int) round($ukuran / 4);
@endphp

<span data-test="progress-ring" class="relative inline-flex items-center justify-center" style="width: {{ $ukuran }}px; height: {{ $ukuran }}px">
    <svg width="{{ $ukuran }}" height="{{ $ukuran }}" viewBox="0 0 {{ $ukuran }} {{ $ukuran }}" fill="none" role="img" aria-label="{{ $aria }}">
        <circle class="stroke-primary-soft" cx="{{ $pusat }}" cy="{{ $pusat }}" r="{{ $jari }}" stroke-width="{{ $lebar }}" />
        <circle class="stroke-primary" cx="{{ $pusat }}" cy="{{ $pusat }}" r="{{ $jari }}" stroke-width="{{ $lebar }}" stroke-linecap="round"
            stroke-dasharray="{{ $keliling }}" stroke-dashoffset="{{ $keliling - $panjangIsi }}"
            transform="rotate(-90 {{ $pusat }} {{ $pusat }})" />
    </svg>
    @if ($centang)
        <svg width="{{ $ukuranTeks + 4 }}" height="{{ $ukuranTeks + 4 }}" viewBox="0 0 16 16" fill="none" aria-hidden="true" class="absolute">
            <path d="M3.5 8.5 6.5 11.5 12.5 4.5" class="stroke-primary" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    @else
        <span aria-hidden="true" class="absolute font-semibold tabular-nums text-primary" style="font-size: {{ $ukuranTeks }}px">{{ $persen }}%</span>
    @endif
</span>
