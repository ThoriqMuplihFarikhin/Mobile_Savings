<div class="space-y-6">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Progres paket</h1>
        <p class="mt-1 text-sm text-mute">Pantau perkembangan kepesertaan paket tabungan Anda.</p>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-xl bg-primary-soft p-3 text-sm text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-sm text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            @php
                $targetAkhir = $item->targetKepesertaan();
                $persentase = $targetAkhir ? min(100, round(($item->total_aktual_terkumpul / $targetAkhir) * 100)) : 0;
                $alertStatus = $item->status_alert ?? 'normal';

                // DESIGN.md §7: kalimat penyemangat "Tinggal Rp X lagi" dihitung
                // dari target dan total_aktual_terkumpul yang sudah ada di baris
                // ini; disembunyikan bila target belum diatur admin.
                $sisaTerkumpul = $targetAkhir ? max(0, $targetAkhir - $item->total_aktual_terkumpul) : null;
            @endphp
            <article class="overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                {{-- Pita header paket (DESIGN.md §7): tile-teal-bg, ilustrasi kado, nama paket --}}
                <div class="flex h-[110px] items-center justify-between gap-3 bg-tile-teal-bg px-4">
                    <x-ui.ilustrasi nama="kado" :ukuran="72" />
                    <div class="min-w-0 text-right">
                        <h2 class="truncate text-base font-semibold text-ink">{{ $item->produk->nama ?? '-' }}</h2>
                        <p class="mt-0.5 text-xs text-body">Mulai {{ $item->tanggal_mulai_ikut->translatedFormat('d M Y') }}</p>
                    </div>
                </div>

                <div class="p-4">
                    {{-- Cincin progres (DESIGN.md §7:132px di halaman progres) --}}
                    <div class="flex items-center gap-4">
                        <x-ui.progress-ring :persen="$persentase" :ukuran="132" :lebar="12" :label="'Capaian paket '.$persentase.' persen'" />
                        <div class="min-w-0 flex-1">
                            <x-ui.status-chip domain="alert_paket" status="{{ $alertStatus }}" />
                            <p class="mt-2 text-xs text-mute">Terkumpul</p>
                            <p class="text-2xl font-semibold tracking-tight text-ink">
                                <x-ui.rupiah :nilai="$item->total_aktual_terkumpul" />
                            </p>
                            @if ($targetAkhir)
                                <p class="mt-1 text-xs text-mute">dari target <x-ui.rupiah :nilai="$targetAkhir" /></p>
                                <p class="mt-1 text-sm font-semibold text-primary">
                                    Tinggal <x-ui.rupiah :nilai="$sisaTerkumpul" /> lagi
                                </p>
                            @else
                                <p class="mt-1 text-xs text-mute">Belum diatur admin</p>
                            @endif
                        </div>
                    </div>

                    {{-- Tunggakan: masalah nyata, jadi merah (DESIGN.md §2, §8) --}}
                    @if ($item->tunggakan > 0)
                        <div class="mt-4 flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-sm text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>Tunggakan setoran {{ (int) $item->tunggakan }} hari.</span>
                        </div>
                    @endif

                    {{-- Terikat komitmen: info biru, bukan masalah --}}
                    @if ($item->keputusan_akhir === null)
                        <div class="mt-4 flex items-start gap-2 rounded-xl bg-info-soft p-3 text-sm text-info">
                            <flux:icon.package class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>
                                <span class="font-medium">Anda terikat komitmen paket ini.</span>
                                Paket tidak dapat ditinggalkan di tengah jalan; pengambilan diproses setelah jatuh tempo. Hubungi admin bila ada kendala.
                            </span>
                        </div>
                    @endif

                    @php
                        $progresPerKepesertaan = collect($progres)->keyBy('kepesertaan_id');
                        $progresMilik = $progresPerKepesertaan->get((int) $item->id);
                    @endphp
                    {{-- Isi paket dengan lencana per barang (DESIGN.md §7) --}}
                    @if ($item->produk && $item->produk->isPaket() && ($item->produk->isi_paket || $item->produk->uang_tunai))
                        <div class="mt-4 rounded-[14px] bg-inset p-4">
                            <p class="text-sm font-medium text-body">Isi paket</p>

                            @if ($progresMilik !== null && count($progresMilik['items']) > 0)
                                @if ($progresMilik['persenKeseluruhan'] !== null)
                                    @php
                                        $persenKeseluruhan = is_int($progresMilik['persenKeseluruhan'])
                                            ? $progresMilik['persenKeseluruhan']
                                            : rtrim(rtrim(number_format((float) $progresMilik['persenKeseluruhan'], 2, ',', ' '), '0'), ',');
                                    @endphp
                                    <p class="mt-1 text-xs text-mute">Progres pemenuhan barang: {{ $persenKeseluruhan }}%</p>
                                @endif

                                <ul class="mt-3 grid grid-cols-2 gap-3">
                                    @foreach ($progresMilik['items'] as $barang)
                                        <li class="flex flex-col items-center gap-1 text-center">
                                            @if ($barang['persen'] !== null)
                                                <x-ui.progress-ring :persen="(int) round($barang['persen'])" :ukuran="56" :centang="$barang['status'] === 'tercapai'" :label="$barang['nama'].', '.$barang['jumlah']" />
                                            @endif
                                            <span class="mt-1 text-[13px] font-medium text-ink">{{ $barang['nama'] }}</span>
                                            <span class="text-xs text-mute">{{ $barang['jumlah'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif ($item->produk->isi_paket)
                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($item->produk->isi_paket as $barang)
                                        <li class="flex items-center justify-between gap-3 text-sm">
                                            <span class="text-body">{{ $barang['nama'] ?? '-' }}</span>
                                            <span class="font-medium text-ink">{{ $barang['jumlah'] ?? '-' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($item->produk->uang_tunai)
                                <div class="mt-3 flex items-center justify-between gap-3 border-t border-hairline-warm pt-3">
                                    <span class="text-sm text-body">Bonus uang tunai</span>
                                    <span class="text-sm font-semibold">
                                        <x-ui.rupiah :nilai="$item->produk->uang_tunai" tanda="+" />
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($item->produk && $item->produk->isPaket())
                        @php
                            $detailInfo = array_filter([
                                $item->produk->tanggal_boleh_cair ? ['label' => 'Boleh Cair', 'value' => \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y')] : null,
                                $item->keputusan_akhir ? ['label' => 'Keputusan', 'value' => ucfirst(str_replace('_', ' ', $item->keputusan_akhir))] : null,
                                $item->metode_pengambilan ? ['label' => 'Pengambilan', 'value' => $item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Diantar Kolektor'] : null,
                                $item->status_serah_terima ? ['label' => 'Serah Terima', 'value' => $item->status_serah_terima === 'sudah_diterima' ? 'Sudah Diterima' : 'Belum'] : null,
                                $item->komitmen_disetujui_pada ? ['label' => 'Komitmen', 'value' => \Carbon\Carbon::parse($item->komitmen_disetujui_pada)->translatedFormat('d M Y').($item->komitmen_via ? ' · '.ucfirst($item->komitmen_via) : '')] : null,
                            ]);
                        @endphp
                        @if (count($detailInfo) > 0)
                            <dl class="mt-4 grid grid-cols-2 gap-2">
                                @foreach ($detailInfo as $detail)
                                    <div class="rounded-[14px] bg-inset p-3">
                                        <dt class="text-xs text-mute">{{ $detail['label'] }}</dt>
                                        <dd class="mt-0.5 truncate font-medium text-ink">{{ $detail['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    @endif

                    {{-- Foto bukti serah terima (hanya pemilik, lewat controller berotorisasi) --}}
                    @if ($item->status_serah_terima === 'sudah_diterima' && $item->bukti_foto_url)
                        <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-primary">
                            <flux:icon.camera class="size-4" aria-hidden="true" />
                            Lihat Foto Bukti Serah Terima
                        </a>
                    @endif

                    @if ($item->metode_pengambilan && in_array($item->status, ['boleh', 'sudah_diserahkan'], true))
                        <div class="mt-4 rounded-[14px] bg-inset p-3">
                            <p class="text-sm font-medium text-body">Metode pengambilan</p>
                            <div class="mt-2 space-y-2">
                                <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'ambil_sendiri')" @class([
                                    'w-full rounded-full px-4 py-3 text-sm font-medium transition',
                                    'bg-primary text-on-primary' => $item->metode_pengambilan === 'ambil_sendiri',
                                    'bg-white text-body border border-hairline-strong' => $item->metode_pengambilan !== 'ambil_sendiri',
                                ])>
                                    Ambil sendiri di kantor
                                </button>
                                <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'diantar_kolektor')" @class([
                                    'w-full rounded-full px-4 py-3 text-sm font-medium transition',
                                    'bg-primary text-on-primary' => $item->metode_pengambilan === 'diantar_kolektor',
                                    'bg-white text-body border border-hairline-strong' => $item->metode_pengambilan !== 'diantar_kolektor',
                                ])>
                                    Diantar kolektor ke rumah
                                </button>
                            </div>
                        </div>
                    @endif

                    @if ($item->catatan_admin)
                        <div class="mt-4 rounded-[14px] bg-warning-soft p-3 text-sm text-warning-deep">
                            <span class="font-medium">Catatan admin:</span> {{ $item->catatan_admin }}
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <p class="mt-4 text-base font-semibold text-ink">Anda belum terdaftar dalam paket apa pun.</p>
                <p class="mt-1 text-sm text-mute">Pilih paket tabungan untuk mulai menabung.</p>
            </div>
        @endforelse
    </div>
</div>
