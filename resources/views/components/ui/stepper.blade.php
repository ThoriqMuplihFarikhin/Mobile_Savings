@props([
    'tahap' => [],
    'saatIni' => 0,
    'ditolak' => false,
])

@php
    /**
     * DESIGN.md §7 "Penanda tahap (stepper)": titik 24px dihubungkan garis 2px,
     * label 12px di bawahnya. Tahap tercapai = hijau + centang, belum = outline,
     * ditolak = merah tanpa centang tetapi labelnya tetap tertulis (§10: penanda
     * tahap tidak hanya mengandalkan warna). `saat-ini` adalah indeks tahap yang
     * sedang dijalani (0-based) dan dihitung sudah tercapai; `ditolak` mengubah
     * tahap itu menjadi merah. Status dibatalkan dan kedaluwarsa tidak memakai
     * stepper — pakai chip netral saja.
     */
    if (! is_array($tahap) || $tahap === []) {
        throw new \InvalidArgumentException('Tahap stepper tidak boleh kosong');
    }

    $daftarTahap = array_values($tahap);
    $jumlahTahap = count($daftarTahap);
    $saatIni = max(0, min($jumlahTahap - 1, (int) $saatIni));

    $statusTahap = array_map(
        fn (int $i): string => match (true) {
            $i < $saatIni => 'selesai',
            $i === $saatIni => $ditolak ? 'ditolak' : 'selesai',
            default => 'belum',
        },
        array_keys($daftarTahap)
    );

    /** Warna garis penghubung sebelum titik ke-n (garis mengikuti titik tujuan). */
    $garisSebelum = array_map(
        fn (string $status): string => match ($status) {
            'selesai' => 'bg-primary',
            'ditolak' => 'bg-danger',
            default => 'bg-hairline-strong',
        },
        $statusTahap
    );
@endphp

<ol data-test="stepper" {{ $attributes->merge(['class' => 'flex items-start gap-1']) }}>
    @foreach ($daftarTahap as $i => $nama)
        @php
            $status = $statusTahap[$i];

            $kelasTitik = match ($status) {
                'selesai' => 'bg-primary text-on-primary',
                'ditolak' => 'bg-danger text-on-primary',
                default => 'border-2 border-hairline-strong bg-white dark:bg-surface',
            };

            $kelasLabel = match ($status) {
                'selesai' => 'text-ink font-medium',
                'ditolak' => 'text-danger-deep dark:text-dark-danger-text',
                default => 'text-mute',
            };
        @endphp

        <li class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
            <div class="flex w-full items-center">
                <span class="h-0.5 flex-1 {{ $i === 0 ? '' : $garisSebelum[$i] }}" aria-hidden="true"></span>

                <span class="{{ $kelasTitik }} flex size-6 shrink-0 items-center justify-center rounded-full" data-tahap="{{ $status }}">
                    @if ($status === 'selesai')
                        <flux:icon.check class="size-3.5" />
                    @elseif ($status === 'ditolak')
                        <flux:icon.x-mark class="size-3.5" />
                    @endif
                </span>

                <span class="h-0.5 flex-1 {{ $i === $jumlahTahap - 1 ? '' : $garisSebelum[$i + 1] }}" aria-hidden="true"></span>
            </div>

            <span class="{{ $kelasLabel }} text-center text-xs leading-4">{{ $nama }}</span>
        </li>
    @endforeach
</ol>
