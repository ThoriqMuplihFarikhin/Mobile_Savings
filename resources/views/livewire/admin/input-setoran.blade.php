<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Catat Setoran"
        subtitle="Catat setoran nasabah secara langsung dari kantor (FR-5)."
    >
        <x-slot:actions>
            <a href="/admin/monitoring-setoran" wire:navigate
                class="inline-flex h-[38px] items-center gap-1.5 rounded-[10px] border border-hairline bg-surface px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset">
                <span>Monitoring Setoran</span>
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
                <div class="border-b border-hairline bg-inset/40 px-6 py-4">
                    <h3 class="text-sm font-semibold text-ink">Form Setoran Kantor</h3>
                    <p class="mt-0.5 text-xs text-mute">Uang diterima langsung oleh kantor, sehingga tidak menambah kas kolektor mana pun.</p>
                </div>

                <form wire:submit="submit" class="space-y-5 p-6">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Nasabah</label>

                        @if ($selectedNasabah)
                            <div class="flex items-center justify-between gap-3 rounded-[10px] border border-hairline bg-inset/40 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-ink">{{ $selectedNasabah['nama'] }}</p>
                                    <p class="truncate text-[11px] text-mute">{{ $selectedNasabah['alamat'] }}</p>
                                </div>
                                <button type="button" wire:click="gantiNasabah"
                                    class="shrink-0 text-xs font-medium text-primary hover:underline">
                                    Ganti
                                </button>
                            </div>
                        @else
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-mute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4" />
                                </svg>
                                <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                                    placeholder="Ketik nama nasabah..."
                                    class="h-[38px] w-full rounded-[10px] border border-hairline bg-white pl-9 pr-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                            </div>

                            <div class="mt-2 max-h-56 overflow-y-auto rounded-[10px] border border-hairline divide-y divide-hairline">
                                @forelse ($nasabahList as $n)
                                    <button type="button" wire:click="pilihNasabah({{ $n['id'] }})"
                                        class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left transition hover:bg-inset/40">
                                        <span class="truncate text-xs font-medium text-ink">{{ $n['nama'] }}</span>
                                        <span class="truncate text-[11px] text-mute">{{ $n['alamat'] }}</span>
                                    </button>
                                @empty
                                    <p class="px-4 py-6 text-center text-xs text-mute">Nasabah tidak ditemukan.</p>
                                @endforelse
                            </div>
                        @endif

                        @error('nasabahId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-ink">Produk Tabungan</label>
                            <select wire:model.live="produkId"
                                class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                                <option value="">Pilih produk</option>
                                @foreach ($produkList as $p)
                                    <option value="{{ $p['id'] }}">
                                        {{ $p['nama'] }} ({{ $p['tipe'] === 'paket' ? 'Paket Sembako' : 'Bebas' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('produkId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-ink">Nominal Setoran (Rp)</label>
                            <input type="number" wire:model.live="nominal" min="0" step="1"
                                placeholder="0"
                                class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs font-semibold tabular-nums text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                            @error('nominal') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

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

                        <div class="rounded-[10px] border border-hairline bg-surface p-3">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <span class="font-medium text-ink">
                                    Skema Paket: <strong>Rp {{ number_format($harianAdmin, 0, ',', '.') }}/hari</strong>
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
                                        class="rounded-[8px] border border-hairline bg-white px-2.5 py-1 text-xs font-medium text-ink shadow-xs transition hover:bg-inset active:scale-95 dark:bg-surface">
                                        {{ $ps['label'] }} (Rp {{ number_format($ps['nilai'], 0, ',', '.') }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-ink">Tanggal Transaksi</label>
                            <div class="flex h-[38px] items-center rounded-[10px] border border-hairline bg-inset/40 px-3 text-xs text-ink">
                                {{ \Illuminate\Support\Carbon::parse($tanggal_transaksi)->translatedFormat('d F Y') }}
                            </div>
                            <p class="mt-1 text-[11px] text-mute">Setoran admin dicatat pada tanggal hari ini.</p>
                            @error('tanggal_transaksi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-ink">Catatan</label>
                            <textarea wire:model="catatan" rows="2" placeholder="Opsional"
                                class="w-full rounded-[10px] border border-hairline bg-white px-3 py-2 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"></textarea>
                            @error('catatan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-hairline pt-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-primary px-6 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-60">
                            <span wire:loading.remove wire:target="submit">Catat Setoran</span>
                            <span wire:loading wire:target="submit">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
                <div class="border-b border-hairline bg-inset/40 px-6 py-4">
                    <h3 class="text-sm font-semibold text-ink">Setoran Hari Ini</h3>
                    <p class="mt-0.5 text-xs text-mute">Total Rp {{ number_format($totalHariIni, 0, ',', '.') }}</p>
                </div>
                <div class="divide-y divide-hairline">
                    @forelse ($riwayatHariIni as $riwayat)
                        <div class="px-5 py-3 transition hover:bg-inset/30">
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-xs font-semibold text-ink">{{ $riwayat->nasabah?->name ?? '-' }}</p>
                                <p class="shrink-0 tabular-nums text-xs font-semibold text-ink">Rp {{ number_format((float) $riwayat->nominal, 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-0.5 flex items-center justify-between gap-3 text-[11px] text-mute">
                                <p class="truncate">{{ $riwayat->produk?->nama ?? '-' }}</p>
                                <p class="shrink-0 tabular-nums">{{ $riwayat->created_at?->format('H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-xs text-mute">Belum ada setoran yang dicatat hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
