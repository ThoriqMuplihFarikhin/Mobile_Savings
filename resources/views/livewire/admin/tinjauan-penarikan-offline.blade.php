<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Tinjauan Penarikan Offline"
        subtitle="Tinjau pasca-fakta penarikan langsung oleh kolektor."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft px-4 py-3 text-sm text-on-primary-soft" data-test="flash-sukses">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger" data-test="flash-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Strip KPI (D25) --}}
    <x-admin.kpi-grid cols="3">
        <x-admin.kpi-card
            label="Belum Ditinjau"
            :value="$hitungKpi['belum'] ?? 0"
            sub="Perlu tindakan"
            :variant="($hitungKpi['belum'] ?? 0) > 0 ? 'warn' : 'default'"
        />
        <x-admin.kpi-card
            label="Sesuai"
            :value="$hitungKpi['sesuai'] ?? 0"
            sub="Tervalidasi admin"
        />
        <x-admin.kpi-card
            label="Bermasalah"
            :value="$hitungKpi['bermasalah'] ?? 0"
            sub="Perlu investigasi"
            :variant="($hitungKpi['bermasalah'] ?? 0) > 0 ? 'error' : 'default'"
        />
    </x-admin.kpi-grid>

    {{-- Filter Toolbar --}}
    <x-admin.toolbar>
        <div class="w-full space-y-3" data-test="baris-filter">
            <div class="flex flex-wrap gap-2">
                @php
                    $statusTabs = [
                        'belum' => ['label' => 'Belum ditinjau', 'hitung' => $hitungKpi['belum'] ?? 0],
                        'sesuai' => ['label' => 'Sesuai', 'hitung' => $hitungKpi['sesuai'] ?? 0],
                        'bermasalah' => ['label' => 'Bermasalah', 'hitung' => $hitungKpi['bermasalah'] ?? 0],
                        'semua' => ['label' => 'Semua', 'hitung' => null],
                    ];
                @endphp
                @foreach ($statusTabs as $value => $tab)
                    <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                        class="inline-flex h-[38px] items-center gap-1.5 rounded-[10px] px-3.5 text-xs font-medium transition {{ $statusFilter === $value ? 'bg-[var(--color-accent)] text-white shadow-xs' : 'border border-[var(--color-admin-border)] bg-[var(--color-surface)] text-[var(--color-admin-text)] hover:bg-[var(--color-surface-2)]' }}">
                        <span>{{ $tab['label'] }}</span>
                        @if($tab['hitung'] !== null && $tab['hitung'] > 0)
                            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-semibold {{ $statusFilter === $value ? 'bg-white/20 text-white' : 'bg-[var(--color-surface-2)] text-[var(--color-admin-mute)]' }}">
                                {{ $tab['hitung'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-end gap-3 pt-1 border-t border-[var(--color-admin-border)]">
                <label class="flex flex-col gap-1 text-xs font-medium text-[var(--color-admin-mute)]">
                    Kolektor
                    <select wire:model.live="kolektorFilter"
                        class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                        <option value="">Semua kolektor</option>
                        @foreach ($kolektors as $kolektor)
                            <option value="{{ $kolektor->id }}">{{ $kolektor->name }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="min-w-[11rem] flex-1">
                    <x-ui.tanggal wire:model.live="dariTanggal" label="Dari tanggal" :max="now()->toDateString()" />
                </div>
                <div class="min-w-[11rem] flex-1">
                    <x-ui.tanggal wire:model.live="sampaiTanggal" label="Sampai tanggal" :min="$dariTanggal ?: null" :max="now()->toDateString()" />
                </div>
            </div>
        </div>
    </x-admin.toolbar>

    {{-- Tabel (md ke atas) --}}
    <div class="hidden overflow-hidden rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs md:block">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Waktu</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Nasabah / Produk</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute text-right">Nominal</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Lokasi / Kas</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Catatan &amp; Bukti</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Tinjauan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse ($penarikans as $item)
                        <tr class="transition hover:bg-[var(--color-surface-2)]" data-test="baris-tinjauan-{{ $item->id }}">
                            <td class="px-4 py-3 text-xs text-[var(--color-admin-mute)]">
                                {{ optional($item->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-[var(--color-admin-text)]">{{ $item->dibayarOleh->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-[var(--color-admin-text)]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[var(--color-admin-mute)]">{{ $item->produk->nama ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 tabular-nums text-right text-sm font-medium text-ink dark:text-white">
                                Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-[var(--color-admin-text)]">
                                <div>{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</div>
                                <div class="text-[11px] {{ $item->mempengaruhi_kas ? 'text-red-600 dark:text-red-400 font-medium' : 'text-[var(--color-admin-mute)]' }}">
                                    {{ $item->mempengaruhi_kas ? 'mengurangi kas' : 'tanpa dampak kas' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-[var(--color-admin-text)]">
                                <div class="max-w-[16rem] truncate">{{ $item->catatan_kolektor ?? '—' }}</div>
                                @if ($item->bukti_path)
                                    <a href="{{ route('penarikan.bukti', $item) }}"
                                        @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                        class="text-xs font-medium text-[var(--color-accent)] hover:underline cursor-pointer">Lihat bukti</a>
                                @else
                                    <span class="text-xs text-[var(--color-admin-mute)]">tanpa bukti</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->hasil_tinjauan === null)
                                    <x-admin.status-chip status="Belum ditinjau" />
                                @elseif ($item->hasil_tinjauan === 'sesuai')
                                    <x-admin.status-chip status="Sesuai" />
                                @else
                                    <x-admin.status-chip status="Bermasalah" />
                                @endif
                                @if ($item->ditinjau_oleh)
                                    <div class="mt-1 text-[11px] text-[var(--color-admin-mute)]">oleh {{ $item->ditinjauOleh->name ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($item->hasil_tinjauan === null)
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="sesuai({{ $item->id }})"
                                            wire:confirm="Tandai penarikan ini sesuai?"
                                            class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">
                                            Sesuai
                                        </button>
                                        <button type="button" wire:click="pilihTinjauan({{ $item->id }})"
                                            class="inline-flex h-8 items-center rounded-[8px] border border-red-500/20 bg-red-500/10 px-3 text-xs font-medium text-red-600 transition hover:bg-red-500/20">
                                            Bermasalah
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-[var(--color-admin-mute)]">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-[var(--color-admin-mute)]" data-test="tinjauan-kosong">
                                Tidak ada penarikan offline pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($penarikans->hasPages())
            <div class="border-t border-[var(--color-admin-border)] px-4 py-3">
                {{ $penarikans->links() }}
            </div>
        @endif
    </div>

    {{-- Kartu Mobile (di bawah md) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($penarikans as $item)
            <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-4 shadow-xs space-y-3"
                data-test="kartu-tinjauan-{{ $item->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-[var(--color-admin-text)]">{{ $item->nasabah->name ?? '-' }}</p>
                        <p class="text-xs text-[var(--color-admin-mute)]">{{ $item->dibayarOleh->name ?? '-' }} · {{ optional($item->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    <span class="shrink-0 tabular-nums font-semibold text-[var(--color-admin-text)]">Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs text-[var(--color-admin-text)] bg-[var(--color-surface-2)] p-2.5 rounded-[8px] border border-[var(--color-admin-border)]">
                    <div class="text-[var(--color-admin-mute)] mb-0.5">{{ $item->produk->nama ?? '-' }} · {{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</div>
                    <div>{{ $item->catatan_kolektor ?? '—' }}</div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-1">
                    <div>
                        @if ($item->bukti_path)
                            <a href="{{ route('penarikan.bukti', $item) }}"
                                @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                class="font-medium text-[var(--color-accent)] hover:underline cursor-pointer">Lihat bukti</a>
                        @endif
                    </div>
                    <div>
                        @if ($item->hasil_tinjauan === null)
                            <x-admin.status-chip status="Belum ditinjau" />
                        @elseif ($item->hasil_tinjauan === 'sesuai')
                            <x-admin.status-chip status="Sesuai" />
                        @else
                            <x-admin.status-chip status="Bermasalah" />
                        @endif
                    </div>
                </div>
                @if ($item->hasil_tinjauan === null)
                    <div class="flex gap-2 pt-1 border-t border-[var(--color-admin-border)]">
                        <button type="button" wire:click="sesuai({{ $item->id }})"
                            wire:confirm="Tandai penarikan ini sesuai?"
                            class="h-[38px] flex-1 rounded-[10px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">Sesuai</button>
                        <button type="button" wire:click="pilihTinjauan({{ $item->id }})"
                            class="h-[38px] flex-1 rounded-[10px] border border-red-500/20 bg-red-500/10 px-3 text-xs font-medium text-red-600 transition hover:bg-red-500/20">Bermasalah</button>
                    </div>
                @endif
            </div>
        @empty
            @if ($penarikans->isEmpty())
                <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-6 text-center text-sm text-[var(--color-admin-mute)]" data-test="kartu-tinjauan-kosong">
                    Tidak ada penarikan offline pada filter ini.
                </div>
            @endif
        @endforelse
    </div>

    {{-- Formulir penandaan bermasalah --}}
    @if ($terpilihId !== null)
        <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-5 shadow-xs"
            data-test="form-bermasalah">
            <h2 class="text-base font-semibold text-[var(--color-admin-text)]">Tandai bermasalah</h2>
            <p class="mt-1 text-xs text-[var(--color-admin-mute)]">Wajib mengisi alasan minimal 10 karakter.</p>

            <div class="mt-4 space-y-3">
                <div>
                    <label for="catatan-tinjauan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Alasan</label>
                    <textarea id="catatan-tinjauan" wire:model="catatanTinjauan" rows="3" maxlength="255"
                        placeholder="mis. nominal tidak cocok dengan bukti tanda tangan"
                        class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"></textarea>
                    @error('catatanTinjauan')
                        <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400" data-test="error-tinjauan">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-start gap-2 text-sm text-[var(--color-admin-text)]">
                    <input type="checkbox" wire:model="bekukanKolektor" class="mt-0.5 h-4 w-4 rounded border-[var(--color-admin-border)]">
                    <span>Bekukan penarikan offline langsung kolektor ini (disarankan).</span>
                </label>

                <div class="flex flex-wrap gap-2 pt-2">
                    <button type="button" wire:click="bermasalah({{ $terpilihId }})"
                        class="h-[38px] rounded-[10px] bg-red-600 px-4 text-xs font-medium text-white transition hover:bg-red-700"
                        data-test="submit-bermasalah">
                        Simpan Bermasalah
                    </button>
                    <button type="button" wire:click="batalTinjauan"
                        class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

