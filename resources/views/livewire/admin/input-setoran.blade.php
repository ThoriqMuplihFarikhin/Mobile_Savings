<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Catat Setoran"
        subtitle="Catat setoran nasabah secara langsung dari kantor (FR-5)."
    >
        <x-slot:actions>
            <a href="/admin/monitoring-setoran" wire:navigate
                class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-hairline/80 bg-white dark:bg-surface px-4 text-xs font-semibold text-ink shadow-2xs transition hover:bg-inset active:scale-[0.98]">
                <span>Monitoring Setoran</span>
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl border border-primary/20 bg-primary-soft p-3.5 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl border border-danger/20 bg-danger-soft p-3.5 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Grid Dua Kolom (Form Setoran & Setoran Hari Ini) --}}
    <div class="grid gap-6 lg:grid-cols-12 items-start">
        {{-- Kolom Kiri: Form Setoran --}}
        <div class="lg:col-span-7 rounded-2xl border border-hairline/80 bg-white dark:bg-surface p-5 sm:p-6 shadow-xs space-y-6">
            <div class="border-b border-hairline/60 pb-3">
                <h2 class="text-base font-semibold text-ink">Form Setoran</h2>
                <div class="mt-2.5 rounded-xl border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
                    Uang diterima langsung oleh kantor, sehingga tidak menambah kas kolektor mana pun.
                </div>
            </div>

            <form wire:submit="submit" class="space-y-5">
                {{-- Pemilihan Nasabah --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Nasabah</label>

                    @if ($selectedNasabah)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-hairline bg-inset/40 p-3.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary font-bold text-sm uppercase">
                                    {{ mb_substr($selectedNasabah['nama'], 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-ink">{{ $selectedNasabah['nama'] }}</p>
                                    <p class="truncate text-[11px] text-mute">{{ $selectedNasabah['alamat'] }}</p>
                                </div>
                            </div>
                            <button type="button" wire:click="gantiNasabah"
                                class="shrink-0 rounded-xl border border-hairline/80 bg-white dark:bg-surface px-3 py-1.5 text-xs font-medium text-ink shadow-2xs hover:bg-inset transition active:scale-[0.98]">
                                Ganti nasabah
                            </button>
                        </div>
                    @else
                        <div class="space-y-2">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-mute">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                </div>
                                <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                                    placeholder="Ketik nama nasabah..."
                                    class="h-10 w-full rounded-xl border border-hairline bg-surface pl-10 pr-4 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                            </div>

                            <div class="max-h-56 divide-y divide-hairline/50 overflow-y-auto rounded-xl border border-hairline/70 bg-surface/40">
                                @forelse ($nasabahList as $n)
                                    <button type="button" wire:click="pilihNasabah({{ $n['id'] }})"
                                        class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left transition hover:bg-inset text-xs">
                                        <span class="font-medium text-ink truncate">{{ $n['nama'] }}</span>
                                        <span class="text-[11px] text-mute shrink-0">{{ $n['alamat'] }}</span>
                                    </button>
                                @empty
                                    <p class="px-4 py-6 text-center text-xs text-mute">Nasabah tidak ditemukan.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @error('nasabahId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- Detail Transaksi (Ditampilkan bila nasabah sudah dipilih atau selalu tersedia) --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Produk Tabungan</label>
                        <select wire:model.live="produkId"
                            class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">Pilih produk...</option>
                            @foreach ($produkList as $p)
                                <option value="{{ $p['id'] }}">
                                    {{ $p['nama'] }} ({{ $p['tipe'] === 'paket' ? 'Paket Sembako' : 'Bebas' }})
                                </option>
                            @endforeach
                        </select>
                        @error('produkId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Tanggal Transaksi</label>
                        <div class="flex h-10 items-center rounded-xl border border-hairline bg-inset/40 px-3.5 text-xs text-ink">
                            {{ \Illuminate\Support\Carbon::parse($tanggal_transaksi)->translatedFormat('d F Y') }}
                        </div>
                    </div>
                </div>

                {{-- Input Nominal & Quick Presets --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Nominal Setoran (Rp)</label>
                    <input type="number" wire:model.live="nominal" min="0" step="1"
                        placeholder="0"
                        class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs font-semibold tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('nominal') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror

                    {{-- Tombol Cepat Nominal Prototipe HTML --}}
                    <div class="mt-2.5 flex flex-wrap gap-2">
                        @foreach([10000, 20000, 30000, 50000, 100000] as $nomPreset)
                            <button type="button" wire:click="setNominalAngka({{ $nomPreset }})"
                                class="rounded-xl border border-hairline/80 bg-white dark:bg-surface px-3 py-1.5 text-xs font-medium text-ink shadow-2xs hover:bg-inset transition active:scale-95">
                                Rp {{ number_format($nomPreset, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Presets Khusus Paket Tabungan --}}
                    @php
                        $adminProduk = $this->selectedProduk;
                        $isAdminPaket = $adminProduk && $adminProduk->isPaket() && (int) $adminProduk->harga_per_hari > 0;
                    @endphp

                    @if($isAdminPaket)
                        @php
                            $harianAdmin = (int) $adminProduk->harga_per_hari;
                            $nominalAdminInt = (int) $nominal;
                            $hariAdmin = $harianAdmin > 0 ? (int) floor($nominalAdminInt / $harianAdmin) : 0;
                            $sisaAdmin = $harianAdmin > 0 ? $nominalAdminInt % $harianAdmin : 0;
                            $presetsAdmin = [
                                ['label' => '1 Hari', 'nilai' => $harianAdmin * 1],
                                ['label' => '2 Hari', 'nilai' => $harianAdmin * 2],
                                ['label' => '3 Hari', 'nilai' => $harianAdmin * 3],
                                ['label' => '1 Minggu', 'nilai' => $harianAdmin * 7],
                                ['label' => '2 Minggu', 'nilai' => $harianAdmin * 14],
                                ['label' => '1 Bulan', 'nilai' => $harianAdmin * 30],
                            ];
                        @endphp

                        <div class="mt-2.5 rounded-xl border border-hairline/60 bg-inset/30 p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                <span class="text-mute">
                                    Skema Paket: <strong class="text-ink">Rp {{ number_format($harianAdmin, 0, ',', '.') }}/hari</strong>
                                </span>
                                @if($nominalAdminInt > 0 && $hariAdmin > 0)
                                    <span class="font-semibold text-primary">
                                        Setara: {{ $hariAdmin }} Hari Cicilan{{ $sisaAdmin > 0 ? ' (+Lebihan Rp '.number_format($sisaAdmin, 0, ',', '.').')' : '' }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($presetsAdmin as $ps)
                                    <button type="button" wire:key="preset-admin-{{ $ps['nilai'] }}" wire:click="setNominalAngka({{ $ps['nilai'] }})"
                                        class="rounded-lg border border-hairline/70 bg-white dark:bg-surface px-2.5 py-1 text-xs font-medium text-ink shadow-2xs hover:bg-inset transition active:scale-95">
                                        {{ $ps['label'] }} (Rp {{ number_format($ps['nilai'], 0, ',', '.') }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <p class="mt-1.5 text-[11px] text-mute">Setoran admin dicatat pada tanggal hari ini.</p>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Catatan</label>
                    <textarea wire:model="catatan" rows="2" placeholder="Opsional"
                        class="w-full rounded-xl border border-hairline bg-surface px-3.5 py-2.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"></textarea>
                    @error('catatan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- Tombol Submit --}}
                <div class="flex items-center justify-end border-t border-hairline/60 pt-4">
                    <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                        class="min-h-11 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-60 active:scale-[0.98]">
                        <span wire:loading.remove wire:target="submit">Catat Setoran</span>
                        <span wire:loading wire:target="submit">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Kolom Kanan: Setoran Hari Ini --}}
        <div class="lg:col-span-5 rounded-2xl border border-hairline/80 bg-white dark:bg-surface shadow-xs overflow-hidden">
            <div class="flex items-center justify-between border-b border-hairline/60 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Setoran Hari Ini</h2>
                    <p class="text-xs text-mute">Total: <strong class="text-ink tabular-nums">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</strong></p>
                </div>
            </div>

            {{-- Tabel Desktop --}}
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left">
                    <thead class="border-b border-hairline/60 bg-transparent text-[11px] font-semibold uppercase tracking-wider text-mute">
                        <tr>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3">Nasabah</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline/50">
                        @forelse ($riwayatHariIni as $riwayat)
                            <tr class="transition hover:bg-inset/30">
                                <td class="px-4 py-3 text-xs text-mute tabular-nums whitespace-nowrap">{{ $riwayat->created_at?->format('H:i') }}</td>
                                <td class="px-4 py-3 text-xs font-medium text-ink">{{ $riwayat->nasabah?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-body">{{ $riwayat->produk?->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs font-semibold text-ink tabular-nums text-right whitespace-nowrap">Rp {{ number_format((float) $riwayat->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="size-9 text-mute/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <p class="text-xs text-mute font-medium">Belum ada setoran yang dicatat hari ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Kartu Mobile --}}
            <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                @forelse ($riwayatHariIni as $riwayat)
                    <div class="p-4 space-y-1.5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-xs font-semibold text-ink">{{ $riwayat->nasabah?->name ?? '-' }}</p>
                            <p class="shrink-0 tabular-nums text-xs font-semibold text-ink">Rp {{ number_format((float) $riwayat->nominal, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-[11px] text-mute">
                            <p class="truncate">{{ $riwayat->produk?->nama ?? '-' }}</p>
                            <p class="shrink-0 tabular-nums">{{ $riwayat->created_at?->format('H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-mute">
                        Belum ada setoran yang dicatat hari ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
