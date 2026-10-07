<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Progres Paket</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Pantau perkembangan kepesertaan paket tabungan Anda.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
            <flux:icon.shopping-bag class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-semibold text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            @php
                $targetAkhir = $item->targetKepesertaan();
                $persentase = $targetAkhir ? min(100, round(($item->total_aktual_terkumpul / $targetAkhir) * 100)) : 0;
                $alertStatus = $item->status_alert ?? 'normal';
                $barGradient = match($alertStatus) {
                    'normal'     => 'from-emerald-500 to-teal-400',
                    'peringatan' => 'from-amber-400 to-orange-500',
                    default      => 'from-rose-500 to-red-600',
                };
                $statusBadge = match($alertStatus) {
                    'normal'     => ['label' => 'On Track', 'class' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60'],
                    'peringatan' => ['label' => 'Peringatan', 'class' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60'],
                    default      => ['label' => 'Perlu Review', 'class' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60'],
                };
            @endphp
            <div class="rounded-3xl bg-white dark:bg-zinc-800 overflow-hidden shadow-xs border border-zinc-200/80 dark:border-zinc-700/80">
                {{-- Card Top Hero --}}
                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-800 to-zinc-900 p-5 text-white dark:from-zinc-950 dark:to-zinc-900">
                    <div class="absolute -right-8 -top-8 h-36 w-36 rounded-full bg-indigo-500/20 blur-2xl pointer-events-none"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-white leading-tight">{{ $item->produk->nama ?? '-' }}</h3>
                            <p class="mt-0.5 text-[11px] text-indigo-200/80 font-mono">Mulai: {{ $item->tanggal_mulai_ikut->translatedFormat('d M Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-[10px] font-bold {{ $statusBadge['class'] }}">
                            {{ $statusBadge['label'] }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mt-5">
                        <div class="flex items-end justify-between mb-2">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 mb-0.5">Terkumpul</p>
                                <p class="font-mono text-xl font-bold text-white">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 mb-0.5">Target</p>
                                <p class="font-mono text-xs font-semibold text-indigo-100">
                                    {{ $targetAkhir ? 'Rp '.number_format($targetAkhir, 0, ',', '.') : 'Belum diatur admin' }}
                                </p>
                            </div>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-white/20">
                            <div class="h-full rounded-full bg-gradient-to-r {{ $barGradient }} transition-all duration-700 ease-out shadow-[0_0_10px_rgba(16,185,129,0.4)]"
                                style="width: {{ $persentase }}%"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-[11px] text-indigo-200">Progres Capaian</span>
                            <span class="text-xs font-bold {{ $persentase >= 100 ? 'text-emerald-400' : 'text-white' }}">{{ $persentase }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Tunggakan Warning --}}
                @if($item->tunggakan > 0)
                    <div class="mx-5 mt-4 flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 p-3.5 border border-rose-200/60 dark:border-rose-800/40">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400">
                            <flux:icon.exclamation-triangle class="size-4" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-rose-700 dark:text-rose-400">Tunggakan Setoran</p>
                            <p class="text-xs font-mono font-bold text-rose-600 dark:text-rose-300 mt-0.5">{{ $item->tunggakan }} hari</p>
                        </div>
                    </div>
                @endif

                {{-- Terikat Komitmen (tanpa tombol keluar, bagian 9.3 butir 5) --}}
                @if($item->keputusan_akhir === null)
                    <div class="mx-5 mt-4 flex items-center gap-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 p-3.5 border border-indigo-200/70 dark:border-indigo-800/50">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300">
                            <flux:icon.package class="size-4" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-indigo-800 dark:text-indigo-200">Anda terikat komitmen paket ini</p>
                            <p class="text-[11px] text-indigo-600 dark:text-indigo-300 mt-0.5">Paket tidak dapat ditinggalkan di tengah jalan; pengambilan diproses setelah jatuh tempo. Hubungi admin bila ada kendala.</p>
                        </div>
                    </div>
                @endif

                {{-- Isi Paket & Bonus Tunai --}}
                @php
                    $progresPerKepesertaan = collect($progres)->keyBy('kepesertaan_id');
                    $progresMilik = $progresPerKepesertaan->get((int) $item->id);
                @endphp
                @if($item->produk && $item->produk->isPaket() && ($item->produk->isi_paket || $item->produk->uang_tunai))
                    <div class="mx-5 mt-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-4 border border-zinc-200/60 dark:border-zinc-700/40">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">Isi Paket</p>

                        @if($progresMilik !== null && $progresMilik['persenKeseluruhan'] !== null)
                            <div class="mb-3">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-zinc-500 dark:text-zinc-400">Progres Pemenuhan Barang</span>
                                    <span class="font-mono font-bold text-zinc-900 dark:text-white">{{ rtrim(rtrim(number_format($progresMilik['persenKeseluruhan'], 2, ',', '.'), '0'), ',') }}%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $progresMilik['persenKeseluruhan']) }}%"></div>
                                </div>
                            </div>
                        @endif

                        @if($progresMilik !== null && count($progresMilik['items']) > 0)
                            <ul class="space-y-1.5">
                                @foreach($progresMilik['items'] as $barang)
                                    <li class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $barang['nama'] }}</span>
                                        <span class="flex items-center gap-2">
                                            @if($barang['status'] === 'tercapai')
                                                <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Tercapai</span>
                                            @elseif($barang['status'] === 'berjalan')
                                                <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ rtrim(rtrim(number_format($barang['persen'], 2, ',', '.'), '0'), ',') }}%</span>
                                            @endif
                                            <span class="font-mono font-semibold text-zinc-900 dark:text-white">
                                                {{ $barang['jumlah'] }}
                                            </span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @elseif($item->produk->isi_paket)
                            <ul class="space-y-1.5">
                                @foreach($item->produk->isi_paket as $barang)
                                    <li class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $barang['nama'] }}</span>
                                        <span class="font-mono font-semibold text-zinc-900 dark:text-white">
                                            {{ $barang['jumlah'] }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($item->produk->uang_tunai)
                            <div class="mt-2.5 flex items-center justify-between border-t border-zinc-200/60 dark:border-zinc-700/40 pt-2.5">
                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">+ Bonus Uang Tunai</span>
                                <span class="font-mono text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                    Rp {{ number_format($item->produk->uang_tunai, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Detail Info --}}
                @if($item->produk && $item->produk->isPaket())
                    @php
                        $details = array_filter([
                            $item->produk->tanggal_boleh_cair ? ['label' => 'Boleh Cair', 'value' => \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y')] : null,
                            $item->keputusan_akhir           ? ['label' => 'Keputusan', 'value' => ucfirst(str_replace('_', ' ', $item->keputusan_akhir))] : null,
                            $item->metode_pengambilan        ? ['label' => 'Pengambilan', 'value' => $item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Diantar Kolektor'] : null,
                            $item->status_serah_terima       ? ['label' => 'Serah Terima', 'value' => $item->status_serah_terima === 'sudah_diterima' ? 'Sudah Diterima' : 'Belum'] : null,
                            $item->komitmen_disetujui_pada   ? ['label' => 'Komitmen', 'value' => \Carbon\Carbon::parse($item->komitmen_disetujui_pada)->translatedFormat('d M Y').($item->komitmen_via ? ' · '.ucfirst($item->komitmen_via) : '')] : null,
                        ]);
                    @endphp
                    @if(count($details) > 0)
                        <div class="grid grid-cols-2 gap-2 border-t border-zinc-100 dark:border-zinc-700/60 mx-5 mt-4 pt-4 pb-2">
                            @foreach($details as $detail)
                                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-3 border border-zinc-200/60 dark:border-zinc-700/40">
                                    <p class="text-[10px] font-bold uppercase text-zinc-400 dark:text-zinc-500">{{ $detail['label'] }}</p>
                                    <p class="mt-0.5 text-xs font-bold text-zinc-900 dark:text-white truncate">{{ $detail['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Foto bukti serah terima (hanya pemilik, lewat controller berotorisasi) --}}
                @if($item->status_serah_terima === 'sudah_diterima' && $item->bukti_foto_url)
                    <div class="mx-5 mt-4">
                        <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-2.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-950/70 transition">
                            Lihat Foto Bukti Serah Terima
                        </a>
                    </div>
                @endif

                {{-- Pilih Metode Pengambilan --}}
                @if($pencairan[$item->id]['boleh'] ?? false)
                    <div class="mx-5 mt-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 p-4 border border-indigo-200/60 dark:border-indigo-800/40">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Metode Pengambilan</p>
                        @if($item->metode_pengambilan)
                            <p class="mt-1 text-xs font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Diantar Kolektor' }} — menunggu penyerahan oleh admin/kolektor.
                            </p>
                        @else
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Pilih cara Anda menerima paket ini:</p>
                        @endif
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'ambil_sendiri')"
                                class="rounded-xl border px-3 py-2.5 text-[11px] font-bold transition cursor-pointer {{ $item->metode_pengambilan === 'ambil_sendiri'
                                    ? 'bg-indigo-600 border-indigo-600 text-white'
                                    : 'bg-white dark:bg-zinc-800 border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40' }}">
                                Ambil Sendiri
                            </button>
                            <button type="button" wire:click="pilihMetodePengambilan({{ $item->id }}, 'diantar_kolektor')"
                                class="rounded-xl border px-3 py-2.5 text-[11px] font-bold transition cursor-pointer {{ $item->metode_pengambilan === 'diantar_kolektor'
                                    ? 'bg-indigo-600 border-indigo-600 text-white'
                                    : 'bg-white dark:bg-zinc-800 border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40' }}">
                                Diantar Kolektor
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Admin Note --}}
                @if($item->catatan_admin)
                    <div class="mx-5 mb-5 mt-3 rounded-2xl bg-amber-50 dark:bg-amber-950/30 p-3.5 border border-amber-200/60 dark:border-amber-800/40">
                        <p class="text-[10px] font-bold uppercase text-amber-700 dark:text-amber-400 mb-0.5">Catatan Admin</p>
                        <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed">{{ $item->catatan_admin }}</p>
                    </div>
                @else
                    <div class="pb-5"></div>
                @endif
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.shopping-bag class="size-7 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Belum Ada Kepesertaan Paket</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Tanya kolektor Anda tentang pilihan paket tabungan.</p>
            </div>
        @endforelse
    </div>
</div>


