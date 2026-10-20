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

                $infoPencairan = $pencairan[(int) $item->id] ?? null;
                $bolehCair = $infoPencairan['boleh'] ?? false;
                $kodeCair = $bolehCair ? 'boleh' : ($infoPencairan['kode'] ?? 'belum_waktunya');
                $teksCair = $bolehCair ? 'Sudah Bisa Dicairkan' : ($infoPencairan['alasan'] ?? 'Belum waktunya');
            @endphp
            <article class="overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface" data-test="kartu-progres-paket">
                {{-- Pita header paket (DESIGN.md §7): tile-teal-bg, ilustrasi kado, nama paket --}}
                <div class="flex h-[110px] items-center justify-between gap-3 bg-tile-teal-bg px-4">
                    <x-ui.ilustrasi nama="kado" :ukuran="72" />
                    <div class="min-w-0 text-right">
                        <h2 class="truncate text-base font-semibold text-ink">{{ $item->produk->nama ?? '-' }}</h2>
                        <p class="mt-0.5 text-xs text-body">Mulai {{ $item->tanggal_mulai_ikut->translatedFormat('d M Y') }}</p>
                    </div>
                </div>

                <div class="p-4">
                    {{-- Cincin progres (DESIGN.md §7: 132px di halaman progres) --}}
                    <div class="flex items-center gap-4">
                        <x-ui.progress-ring :persen="$persentase" :ukuran="132" :lebar="12" :label="'Capaian paket '.$persentase.' persen'" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                <x-ui.status-chip domain="alert_paket" status="{{ $alertStatus }}" />
                                <x-ui.status-chip domain="pencairan" :status="$kodeCair" :teks="$teksCair" />
                            </div>
                            <p class="text-xs text-mute">Terkumpul</p>
                            <p class="text-2xl font-semibold tracking-tight text-ink">
                                <x-ui.rupiah :nilai="$item->total_aktual_terkumpul" />
                            </p>
                            @if ($targetAkhir)
                                <p class="mt-1 text-xs text-mute">dari target <x-ui.rupiah :nilai="$targetAkhir" /></p>
                                <p class="mt-1 text-xs font-semibold text-primary">
                                    Tinggal <x-ui.rupiah :nilai="$sisaTerkumpul" /> lagi
                                </p>
                            @else
                                <p class="mt-1 text-xs text-mute">Belum diatur admin</p>
                            @endif
                        </div>
                    </div>

                    {{-- Tunggakan: masalah nyata, jadi merah (DESIGN.md §2, §8) --}}
                    @if ($item->tunggakan > 0)
                        <div class="mt-4 flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>Tunggakan setoran {{ (int) $item->tunggakan }} hari.</span>
                        </div>
                    @endif

                    {{-- Terikat komitmen: info biru, bukan masalah --}}
                    @if ($item->keputusan_akhir === null)
                        <div class="mt-4 flex items-start gap-2 rounded-xl bg-info-soft p-3 text-xs text-info">
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
                        <div class="mt-4 rounded-2xl bg-surface-inset p-4 border border-hairline">
                            <p class="text-xs font-semibold text-ink">Isi paket</p>

                            @if ($progresMilik !== null && count($progresMilik['items']) > 0)
                                @if ($progresMilik['persenKeseluruhan'] !== null)
                                    @php
                                        $persenKeseluruhan = is_int($progresMilik['persenKeseluruhan'])
                                            ? $progresMilik['persenKeseluruhan']
                                            : rtrim(rtrim(number_format((float) $progresMilik['persenKeseluruhan'], 2, ',', ' '), '0'), ',');
                                    @endphp
                                    <p class="mt-1 text-xs text-mute">Progres pemenuhan barang: {{ $persenKeseluruhan }}%</p>
                                @endif

                                <ul class="mt-3 grid grid-cols-2 gap-2.5">
                                    @foreach ($progresMilik['items'] as $barang)
                                        @php
                                            $statusItem = $barang['status'] ?? 'belum';
                                            $chipStatus = match($statusItem) {
                                                'tercapai' => ['bg' => 'bg-primary-soft', 'text' => 'text-primary', 'label' => 'Tercapai'],
                                                'berjalan' => ['bg' => 'bg-info-soft', 'text' => 'text-info', 'label' => 'Berjalan'],
                                                default => ['bg' => 'bg-white dark:bg-surface', 'text' => 'text-mute', 'label' => 'Belum'],
                                            };
                                        @endphp
                                        <li class="flex flex-col items-center gap-1.5 rounded-xl bg-white dark:bg-surface p-3 border border-hairline text-center">
                                            @if ($barang['persen'] !== null)
                                                <x-ui.progress-ring :persen="(int) round($barang['persen'])" :ukuran="56" :centang="$barang['status'] === 'tercapai'" :label="$barang['nama'].', '.$barang['jumlah']" />
                                            @endif
                                            <span class="mt-1 text-xs font-semibold text-ink truncate w-full">{{ $barang['nama'] }}</span>
                                            <span class="text-xs text-mute">{{ $barang['jumlah'] }}</span>
                                            <span class="mt-0.5 rounded-full px-2 py-0.5 text-xs font-medium {{ $chipStatus['bg'] }} {{ $chipStatus['text'] }}">
                                                {{ $chipStatus['label'] }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif ($item->produk->isi_paket)
                                <ul class="mt-2 space-y-1.5">
                                    @foreach ($item->produk->isi_paket as $barang)
                                        <li class="flex items-center justify-between gap-3 text-xs">
                                            <span class="text-body font-medium">{{ $barang['nama'] ?? '-' }}</span>
                                            <span class="text-mute">{{ $barang['jumlah'] ?? '-' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($item->produk->uang_tunai)
                                <div class="mt-3 flex items-center justify-between gap-3 border-t border-hairline pt-3">
                                    <span class="text-xs text-body font-medium">Bonus uang tunai</span>
                                    <span class="text-xs font-semibold">
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
                                    <div class="rounded-xl bg-surface-inset p-3 border border-hairline">
                                        <dt class="text-xs text-mute">{{ $detail['label'] }}</dt>
                                        <dd class="mt-0.5 truncate font-medium text-ink text-xs">{{ $detail['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    @endif

                    {{-- Foto bukti serah terima (hanya pemilik, lewat controller berotorisasi) --}}
                    @if ($item->status_serah_terima === 'sudah_diterima' && $item->bukti_foto_url)
                        <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-primary hover:underline">
                            <flux:icon.camera class="size-4" aria-hidden="true" />
                            Lihat Foto Bukti Serah Terima
                        </a>
                    @endif

                    @php
                        $bolehPilihMetode = ($pencairan[(int) $item->id]['boleh'] ?? false) && $item->status_serah_terima !== 'sudah_diterima';
                    @endphp
                    @if ($bolehPilihMetode || $item->metode_pengambilan)
                        <div class="mt-4 rounded-2xl bg-surface-inset p-4 border border-hairline">
                            <p class="text-xs font-semibold text-ink">Metode pengambilan</p>
                            <p class="text-xs text-mute mt-0.5">Pilih bagaimana Anda ingin menerima paket ini.</p>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'ambil_sendiri')"
                                    class="flex items-center justify-between gap-2 rounded-xl p-3 border transition text-left {{ $item->metode_pengambilan === 'ambil_sendiri' ? 'border-primary bg-primary-soft text-primary font-semibold' : 'border-hairline bg-white text-ink hover:bg-surface dark:bg-surface' }}">
                                    <div>
                                        <p class="text-xs font-semibold">Ambil sendiri di kantor</p>
                                    </div>
                                    @if($item->metode_pengambilan === 'ambil_sendiri')
                                        <flux:icon.check-circle class="size-4 text-primary shrink-0" />
                                    @endif
                                </button>
                                <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'diantar_kolektor')"
                                    class="flex items-center justify-between gap-2 rounded-xl p-3 border transition text-left {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'border-primary bg-primary-soft text-primary font-semibold' : 'border-hairline bg-white text-ink hover:bg-surface dark:bg-surface' }}">
                                    <div>
                                        <p class="text-xs font-semibold">Diantar kolektor ke rumah</p>
                                    </div>
                                    @if($item->metode_pengambilan === 'diantar_kolektor')
                                        <flux:icon.check-circle class="size-4 text-primary shrink-0" />
                                    @endif
                                </button>
                            </div>
                        </div>
                    @endif

                    @if ($item->catatan_admin)
                        <div class="mt-4 rounded-xl bg-warning-soft p-3 text-xs text-warning-deep">
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
                <p class="mt-1 text-xs text-mute">Pilih paket tabungan untuk mulai menabung.</p>
                <a href="{{ route('nasabah.paket.pilih') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-xs font-medium text-on-primary transition hover:bg-primary-hover active:scale-95"
                   data-test="tombol-ikut-paket">
                    <flux:icon.plus-circle class="size-4" />
                    Pilih Paket Sekarang
                </a>
            </div>
        @endforelse
    </div>
</div>
