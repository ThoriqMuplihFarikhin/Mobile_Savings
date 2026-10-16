<div class="mx-auto max-w-7xl space-y-6">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink dark:text-white">Tinjauan Penarikan Offline</h1>
        <p class="mt-1 text-sm text-mute dark:text-subtle">
            Penarikan yang diselesaikan langsung oleh kolektor wajib ditinjau pasca-fakta (D25).
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info" data-test="flash-sukses">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger" data-test="flash-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="space-y-3 rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#e5e5e5] dark:bg-inset dark:shadow-none dark:inset-ring dark:inset-ring-white/10" data-test="baris-filter">
        <div class="flex flex-wrap gap-2">
            @foreach (['belum' => 'Belum ditinjau', 'sesuai' => 'Sesuai', 'bermasalah' => 'Bermasalah', 'semua' => 'Semua'] as $value => $label)
                <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                    class="min-h-11 rounded-md px-4 py-2 text-sm font-medium transition {{ $statusFilter === $value ? 'bg-accent text-accent-foreground' : 'bg-inset text-body shadow-[inset_0_0_0_1px_#e5e5e5] hover:bg-white dark:bg-inset dark:text-body dark:shadow-none' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <label class="flex flex-col gap-1 text-xs font-medium text-mute dark:text-subtle">
                Kolektor
                <select wire:model.live="kolektorFilter"
                    class="min-h-11 rounded-md border border-hairline bg-white px-3 text-sm text-ink dark:border-hairline-strong dark:bg-surface dark:text-white">
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

    {{-- Tabel (md ke atas) --}}
    <div class="hidden overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5] md:block dark:bg-inset dark:shadow-none dark:inset-ring dark:inset-ring-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset dark:border-hairline-strong dark:bg-surface">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Waktu</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah / Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Nominal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Lokasi / Kas</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Catatan &amp; Bukti</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tinjauan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline dark:divide-hairline-strong">
                    @forelse ($penarikans as $item)
                        <tr class="transition hover:bg-inset dark:hover:bg-inset/40" data-test="baris-tinjauan-{{ $item->id }}">
                            <td class="px-4 py-3 text-sm text-body dark:text-body">
                                {{ optional($item->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-ink dark:text-white">{{ $item->dibayarOleh->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink dark:text-white">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-mute">{{ $item->produk->nama ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 tabular-nums text-right text-sm font-medium text-ink dark:text-white">
                                Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-body dark:text-body">
                                {{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}
                                <div class="text-xs {{ $item->mempengaruhi_kas ? 'text-danger' : 'text-subtle' }}">
                                    {{ $item->mempengaruhi_kas ? 'mengurangi kas' : 'tanpa dampak kas' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body dark:text-body">
                                <div class="max-w-[16rem] truncate">{{ $item->catatan_kolektor ?? '—' }}</div>
                                @if ($item->bukti_path)
                                    <a href="{{ route('penarikan.bukti', $item) }}" target="_blank" rel="noopener"
                                        class="text-xs font-semibold text-info hover:underline">Lihat bukti</a>
                                @else
                                    <span class="text-xs text-subtle">tanpa bukti</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($item->hasil_tinjauan === null)
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Belum ditinjau</span>
                                @elseif ($item->hasil_tinjauan === 'sesuai')
                                    <span class="inline-flex items-center rounded-full bg-primary-soft px-2.5 py-0.5 text-xs text-primary">Sesuai</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger-deep">Bermasalah</span>
                                @endif
                                @if ($item->ditinjau_oleh)
                                    <div class="mt-1 text-xs text-mute">oleh {{ $item->ditinjauOleh->name ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->hasil_tinjauan === null)
                                    <div class="flex flex-col gap-1.5">
                                        <button type="button" wire:click="sesuai({{ $item->id }})"
                                            wire:confirm="Tandai penarikan ini sesuai?"
                                            class="min-h-9 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-on-primary transition hover:bg-primary-hover">
                                            Sesuai
                                        </button>
                                        <button type="button" wire:click="pilihTinjauan({{ $item->id }})"
                                            class="min-h-9 rounded-lg bg-danger px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-danger-deep">
                                            Bermasalah
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-subtle">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-mute" data-test="tinjauan-kosong">
                                Tidak ada penarikan offline pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($penarikans->hasPages())
            <div class="border-t border-hairline px-4 py-3 dark:border-hairline-strong">
                {{ $penarikans->links() }}
            </div>
        @endif
    </div>

    {{-- Kartu (di bawah md) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($penarikans as $item)
            <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#e5e5e5] dark:bg-inset dark:shadow-none dark:inset-ring dark:inset-ring-white/10"
                data-test="kartu-tinjauan-{{ $item->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-ink dark:text-white">{{ $item->nasabah->name ?? '-' }}</p>
                        <p class="text-xs text-mute">{{ $item->dibayarOleh->name ?? '-' }} · {{ optional($item->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    <span class="shrink-0 tabular-nums text-sm font-semibold text-ink dark:text-white">Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}</span>
                </div>
                <p class="mt-2 text-xs text-body dark:text-body">{{ $item->catatan_kolektor ?? '—' }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    @if ($item->bukti_path)
                        <a href="{{ route('penarikan.bukti', $item) }}" target="_blank" rel="noopener" class="font-semibold text-info hover:underline">Lihat bukti</a>
                    @endif
                    @if ($item->hasil_tinjauan === null)
                        <span class="rounded-full bg-warning-soft px-2 py-0.5 font-medium text-warning-deep">Belum ditinjau</span>
                    @elseif ($item->hasil_tinjauan === 'sesuai')
                        <span class="rounded-full bg-primary-soft px-2 py-0.5 font-medium text-primary">Sesuai</span>
                    @else
                        <span class="rounded-full bg-danger-soft px-2 py-0.5 font-medium text-danger-deep">Bermasalah</span>
                    @endif
                </div>
                @if ($item->hasil_tinjauan === null)
                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click="sesuai({{ $item->id }})"
                            wire:confirm="Tandai penarikan ini sesuai?"
                            class="min-h-11 flex-1 rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-on-primary">Sesuai</button>
                        <button type="button" wire:click="pilihTinjauan({{ $item->id }})"
                            class="min-h-11 flex-1 rounded-lg bg-danger px-3 py-2 text-xs font-semibold text-white">Bermasalah</button>
                    </div>
                @endif
            </div>
        @empty
            @if ($penarikans->isEmpty())
                <p class="rounded-lg bg-white p-6 text-center text-sm text-mute dark:bg-inset dark:text-subtle" data-test="kartu-tinjauan-kosong">
                    Tidak ada penarikan offline pada filter ini.
                </p>
            @endif
        @endforelse
    </div>

    {{-- Formulir penandaan bermasalah --}}
    @if ($terpilihId !== null)
        <div class="rounded-lg bg-white p-5 shadow-[inset_0_0_0_1px_#e5e5e5] dark:bg-inset dark:shadow-none dark:inset-ring dark:inset-ring-white/10"
            data-test="form-bermasalah">
            <h2 class="text-base font-semibold text-ink dark:text-white">Tandai bermasalah</h2>
            <p class="mt-1 text-xs text-mute">Wajib mengisi alasan minimal 10 karakter.</p>

            <div class="mt-4 space-y-3">
                <div>
                    <label for="catatan-tinjauan" class="mb-1.5 block text-xs font-medium text-mute">Alasan</label>
                    <textarea id="catatan-tinjauan" wire:model="catatanTinjauan" rows="3" maxlength="255"
                        placeholder="mis. nominal tidak cocok dengan bukti tanda tangan"
                        class="w-full rounded-md border border-hairline px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/20 dark:border-hairline-strong dark:bg-surface dark:text-white"></textarea>
                    @error('catatanTinjauan')
                        <p class="mt-1 text-xs font-medium text-danger" data-test="error-tinjauan">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-start gap-2 text-sm text-body dark:text-body">
                    <input type="checkbox" wire:model="bekukanKolektor" class="mt-0.5 h-4 w-4 rounded border-hairline-strong">
                    <span>Bekukan penarikan offline langsung kolektor ini (disarankan).</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="bermasalah({{ $terpilihId }})"
                        class="min-h-11 rounded-md bg-danger px-4 py-2 text-sm font-semibold text-white transition hover:bg-danger-deep"
                        data-test="submit-bermasalah">
                        Simpan Bermasalah
                    </button>
                    <button type="button" wire:click="batalTinjauan"
                        class="min-h-11 rounded-md bg-inset px-4 py-2 text-sm font-semibold text-body transition hover:bg-hairline dark:bg-inset dark:text-body">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
