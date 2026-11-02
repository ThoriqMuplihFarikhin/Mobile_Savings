<div class="space-y-5">
    {{-- Header Top-Level Tab (konsisten dengan Nasabah Binaan Kolektor) --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Paket Tabungan</h1>
            <p class="mt-0.5 text-xs text-mute">Pantau perkembangan kepesertaan paket tabungan Anda.</p>
        </div>
        <a href="{{ route('nasabah.paket.pilih') }}" wire:navigate
           class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95"
           data-test="tombol-ikut-paket-header">
            <flux:icon.plus class="size-3.5" aria-hidden="true" />
            <span>+ Ikuti</span>
        </a>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-2xl bg-primary-soft p-3 text-xs text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-2 rounded-2xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Daftar kartu paket --}}
    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            @php
                $targetAkhir = $item->targetKepesertaan();
                $persentase = $targetAkhir ? min(100, (int) round(($item->total_aktual_terkumpul / $targetAkhir) * 100)) : 0;
                $alertStatus = $item->status_alert ?? 'normal';
                $sisaTerkumpul = $targetAkhir ? max(0, $targetAkhir - $item->total_aktual_terkumpul) : null;
                $isSudahDiterima = $item->status_serah_terima === 'sudah_diterima';

                $infoPencairan = $pencairan[(int) $item->id] ?? null;
                $bolehCair = $infoPencairan['boleh'] ?? false;
                $kodeCair = $isSudahDiterima ? 'sudah_diterima' : ($bolehCair ? 'boleh' : ($infoPencairan['kode'] ?? 'belum_waktunya'));
                $teksCair = $isSudahDiterima ? 'Sudah Diterima' : ($bolehCair ? 'Sudah Bisa Dicairkan' : 'Sedang Menabung');

                $progresPerKepesertaan = collect($progres)->keyBy('kepesertaan_id');
                $progresMilik = $progresPerKepesertaan->get((int) $item->id);

                $bolehPilihMetode = ($pencairan[(int) $item->id]['boleh'] ?? false) && !$isSudahDiterima;

                $tahapList = ['Menabung', 'Boleh Cair', 'Pengambilan', 'Diterima'];
                $saatIniTahap = match (true) {
                    $isSudahDiterima => 3,
                    !empty($item->metode_pengambilan) => 2,
                    $bolehCair => 1,
                    default => 0,
                };
            @endphp
            <article class="rounded-[24px] border border-hairline bg-white p-5 shadow-xs dark:bg-surface" data-test="kartu-progres-paket">
                {{-- Baris utama: Cincin progres di kiri + Info paket di kanan --}}
                <div class="flex items-start gap-3.5">
                    <div class="shrink-0 pt-0.5">
                        <x-ui.progress-ring :persen="$persentase" :ukuran="72" :lebar="8" :label="'Capaian paket '.$persentase.' persen'" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-base font-semibold text-ink leading-tight">{{ $item->produk->nama ?? '-' }}</h2>
                        <p class="mt-1 text-xs text-mute">
                            Terkumpul <span class="font-semibold text-ink"><x-ui.rupiah :nilai="$item->total_aktual_terkumpul" /></span>
                            @if ($targetAkhir)
                                dari <x-ui.rupiah :nilai="$targetAkhir" />
                            @endif
                        </p>
                        @if ($targetAkhir && $sisaTerkumpul > 0 && !$isSudahDiterima)
                            <p class="mt-0.5 text-xs font-semibold text-primary">
                                Tinggal <x-ui.rupiah :nilai="$sisaTerkumpul" /> lagi
                            </p>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="rounded-full bg-tile-teal-bg px-2 py-0.5 text-xs font-semibold text-tile-teal-fg">
                                Paket
                            </span>
                            <x-ui.status-chip domain="pencairan" :status="$kodeCair" :teks="$teksCair" />
                            @if($alertStatus !== 'normal' && !$isSudahDiterima)
                                <x-ui.status-chip domain="alert_paket" status="{{ $alertStatus }}" />
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tahapan Paket (x-ui.stepper) --}}
                <div class="mt-4 pt-3.5 border-t border-hairline">
                    <p class="text-xs font-medium text-mute mb-2.5">Tahapan Paket:</p>
                    <x-ui.stepper :tahap="$tahapList" :saat-ini="$saatIniTahap" />
                </div>

                {{-- Komitmen notice hanya untuk paket yang masih berjalan --}}
                @if (!$isSudahDiterima && $item->keputusan_akhir === null)
                    <div class="mt-3.5 flex items-start gap-2 rounded-2xl bg-surface-inset p-3 text-xs text-body border border-hairline">
                        <flux:icon.information-circle class="mt-0.5 size-4 shrink-0 text-primary" aria-hidden="true" />
                        <div>
                            <span class="font-medium text-ink">Anda terikat komitmen paket ini.</span>
                            <span class="text-mute">Pengambilan diproses setelah jatuh tempo.</span>
                        </div>
                    </div>
                @endif

                {{-- Tunggakan hanya jika ada masalah --}}
                @if ($item->tunggakan > 0)
                    <div class="mt-3 flex items-start gap-2 rounded-2xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                        <span>Tunggakan setoran {{ (int) $item->tunggakan }} hari.</span>
                    </div>
                @endif

                {{-- Isi paket --}}
                @if ($item->produk && $item->produk->isPaket() && ($item->produk->isi_paket || $item->produk->uang_tunai))
                    <div class="mt-3.5 rounded-2xl bg-surface-inset p-3.5 border border-hairline text-xs">
                        <div class="flex items-center justify-between font-semibold text-ink mb-2">
                            <span>Isi paket</span>
                            @if ($progresMilik !== null && $progresMilik['persenKeseluruhan'] !== null && !$isSudahDiterima)
                                <span class="text-xs text-mute font-normal">Pemenuhan: {{ (int) round($progresMilik['persenKeseluruhan']) }}%</span>
                            @endif
                        </div>
                        <div class="divide-y divide-hairline">
                            @if ($progresMilik !== null && count($progresMilik['items']) > 0)
                                @foreach ($progresMilik['items'] as $barang)
                                    <div class="flex items-center justify-between py-1.5">
                                        <span class="text-mute truncate">{{ $barang['nama'] }}</span>
                                        <span class="font-semibold text-ink shrink-0">&times; {{ $barang['jumlah'] }}</span>
                                    </div>
                                @endforeach
                            @elseif ($item->produk->isi_paket)
                                @foreach ($item->produk->isi_paket as $barang)
                                    <div class="flex items-center justify-between py-1.5">
                                        <span class="text-mute truncate">{{ $barang['nama'] ?? '-' }}</span>
                                        <span class="font-semibold text-ink shrink-0">&times; {{ $barang['jumlah'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            @endif

                            @php
                                $adaItemUangTunai = isset($progresMilik['items']) && collect($progresMilik['items'])->contains(fn($b) => stripos($b['nama'], 'uang tunai') !== false);
                            @endphp
                            @if ($item->produk->uang_tunai && !$adaItemUangTunai)
                                <div class="flex items-center justify-between py-1.5 border-t border-hairline">
                                    <span class="text-mute">Bonus uang tunai</span>
                                    <span class="font-semibold text-primary">
                                        <x-ui.rupiah :nilai="$item->produk->uang_tunai" tanda="+" />
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Metode pengambilan hanya saat boleh cair dan belum diterima --}}
                @if ($bolehPilihMetode)
                    <div class="mt-3.5 rounded-2xl bg-surface-inset p-3.5 border border-hairline">
                        <p class="text-xs font-semibold text-ink">Metode pengambilan</p>
                        <p class="text-xs text-mute mt-0.5 mb-2.5">Pilih bagaimana Anda ingin menerima paket ini:</p>
                        <div class="space-y-2">
                            <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'ambil_sendiri')"
                                class="flex w-full items-center gap-3 rounded-2xl border p-3 text-left transition {{ $item->metode_pengambilan === 'ambil_sendiri' ? 'border-primary bg-primary-soft/30 text-ink' : 'border-hairline bg-white hover:bg-surface-inset text-ink dark:bg-surface' }}">
                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 {{ $item->metode_pengambilan === 'ambil_sendiri' ? 'border-primary' : 'border-hairline-strong bg-white' }}">
                                    @if($item->metode_pengambilan === 'ambil_sendiri')
                                        <span class="size-2.5 rounded-full bg-primary"></span>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-ink">Ambil sendiri di kantor</p>
                                    <p class="text-xs text-mute">Ambil langsung ke kantor utama</p>
                                </div>
                            </button>
                            <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'diantar_kolektor')"
                                class="flex w-full items-center gap-3 rounded-2xl border p-3 text-left transition {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'border-primary bg-primary-soft/30 text-ink' : 'border-hairline bg-white hover:bg-surface-inset text-ink dark:bg-surface' }}">
                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'border-primary' : 'border-hairline-strong bg-white' }}">
                                    @if($item->metode_pengambilan === 'diantar_kolektor')
                                        <span class="size-2.5 rounded-full bg-primary"></span>
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-ink">Diantar kolektor ke rumah</p>
                                    <p class="text-xs text-mute">Diserahkan saat kunjungan kolektor</p>
                                </div>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Rincian spesifikasi & jadwal --}}
                <div class="mt-3.5 rounded-2xl bg-surface-inset p-3.5 border border-hairline divide-y divide-hairline text-xs">
                    @if ($item->komitmen_disetujui_pada)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-mute">Komitmen</span>
                            <span class="font-medium text-ink">
                                {{ \Carbon\Carbon::parse($item->komitmen_disetujui_pada)->translatedFormat('d M Y') }}
                                @if($item->komitmen_via)
                                    &middot; {{ ucfirst($item->komitmen_via) }}
                                @endif
                            </span>
                        </div>
                    @endif
                    @if ($item->produk && $item->produk->tanggal_boleh_cair)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-mute">Tanggal Boleh Cair</span>
                            <span class="font-medium text-ink">
                                {{ \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    @endif
                    @if ($item->metode_pengambilan && !$bolehPilihMetode)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-mute">Pengambilan</span>
                            <span class="font-medium text-ink">
                                {{ $item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri di Kantor' : 'Diantar Kolektor ke Rumah' }}
                            </span>
                        </div>
                    @endif
                    @if ($item->status_serah_terima)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-mute">Serah Terima</span>
                            <span class="font-medium text-ink">
                                {{ $isSudahDiterima ? 'Sudah Diterima' : 'Belum Diterima' }}
                            </span>
                        </div>
                    @endif
                    @if ($item->keputusan_akhir)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-mute">Keputusan</span>
                            <span class="font-medium text-ink">
                                {{ ucfirst(str_replace('_', ' ', $item->keputusan_akhir)) }}
                            </span>
                        </div>
                    @endif
                    @if ($isSudahDiterima && $item->bukti_foto_url)
                        <div class="flex items-center justify-between py-2 border-t border-hairline">
                            <span class="text-mute">Bukti Penyerahan</span>
                            <a href="{{ route('serah-terima.bukti', $item) }}"
                               @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('serah-terima.bukti', $item) }}', judul: 'Foto Bukti Serah Terima', keterangan: '{{ $item->produk?->nama }} · Diterima oleh {{ $item->diterima_oleh }}' })"
                               class="inline-flex items-center gap-1.5 font-semibold text-primary hover:underline cursor-pointer">
                                <flux:icon.camera class="size-3.5" />
                                <span>Lihat Foto Bukti Serah Terima</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Catatan admin --}}
                @if ($item->catatan_admin)
                    <div class="mt-3 rounded-xl bg-surface-inset p-3 text-xs text-mute border border-hairline">
                        <span class="font-medium text-ink">Catatan admin:</span> {{ $item->catatan_admin }}
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-[24px] border border-hairline bg-white p-8 text-center shadow-xs dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <p class="mt-4 text-base font-semibold text-ink">Anda belum terdaftar dalam paket apa pun.</p>
                <p class="mt-1 text-xs text-mute">Pilih paket tabungan untuk mulai menabung.</p>
                <a href="{{ route('nasabah.paket.pilih') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-xs font-semibold text-on-primary transition hover:bg-primary-hover active:scale-95"
                   data-test="tombol-ikut-paket">
                    <flux:icon.plus class="size-4" />
                    <span>Pilih Paket Sekarang</span>
                </a>
            </div>
        @endforelse

        {{-- Tombol ikuti paket baru di bagian bawah bila ada paket yang sudah diikuti --}}
        @if($kepesertaan->isNotEmpty())
            <div class="pt-1">
                <a href="{{ route('nasabah.paket.pilih') }}" wire:navigate
                   class="flex h-12 w-full items-center justify-center gap-2 rounded-full border border-primary/30 bg-white text-sm font-semibold text-primary transition hover:bg-primary-soft active:scale-[0.98] dark:bg-surface">
                    <flux:icon.plus class="size-4" />
                    <span>Ikuti paket baru</span>
                </a>
            </div>
        @endif
    </div>
</div>

