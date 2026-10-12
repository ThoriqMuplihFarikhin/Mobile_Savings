<div class="mx-auto max-w-7xl space-y-6">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Tinjauan Penarikan Offline</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
            Penarikan yang diselesaikan langsung oleh kolektor wajib ditinjau pasca-fakta (D25).
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600" data-test="flash-sukses">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]" data-test="flash-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="space-y-3 rounded-xl bg-white p-4 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb] dark:bg-zinc-800 dark:shadow-none dark:inset-ring dark:inset-ring-white/10" data-test="baris-filter">
        <div class="flex flex-wrap gap-2">
            @foreach (['belum' => 'Belum ditinjau', 'sesuai' => 'Sesuai', 'bermasalah' => 'Bermasalah', 'semua' => 'Semua'] as $value => $label)
                <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                    class="min-h-11 rounded-full px-4 py-2 text-sm font-medium transition {{ $statusFilter === $value ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white dark:bg-zinc-700 dark:text-zinc-200 dark:shadow-none' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <label class="flex flex-col gap-1 text-xs font-medium text-gray-500 dark:text-slate-400">
                Kolektor
                <select wire:model.live="kolektorFilter"
                    class="min-h-11 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
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
    <div class="hidden overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb] md:block dark:bg-zinc-800 dark:shadow-none dark:inset-ring dark:inset-ring-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Waktu</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah / Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nominal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Lokasi / Kas</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Catatan &amp; Bukti</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Tinjauan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb] dark:divide-zinc-700">
                    @forelse ($penarikans as $item)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-700/40" data-test="baris-tinjauan-{{ $item->id }}">
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">
                                {{ optional($item->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item->dibayarOleh->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->produk->nama ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono text-sm font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">
                                {{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}
                                <div class="text-xs {{ $item->mempengaruhi_kas ? 'text-rose-600' : 'text-gray-400' }}">
                                    {{ $item->mempengaruhi_kas ? 'mengurangi kas' : 'tanpa dampak kas' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">
                                <div class="max-w-[16rem] truncate">{{ $item->catatan_kolektor ?? '—' }}</div>
                                @if ($item->bukti_path)
                                    <a href="{{ route('penarikan.bukti', $item) }}" target="_blank" rel="noopener"
                                        class="text-xs font-semibold text-indigo-600 hover:underline">Lihat bukti</a>
                                @else
                                    <span class="text-xs text-gray-400">tanpa bukti</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($item->hasil_tinjauan === null)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Belum ditinjau</span>
                                @elseif ($item->hasil_tinjauan === 'sesuai')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 font-mono text-xs text-emerald-700">Sesuai</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 font-mono text-xs text-rose-700">Bermasalah</span>
                                @endif
                                @if ($item->ditinjau_oleh)
                                    <div class="mt-1 text-xs text-gray-500">oleh {{ $item->ditinjauOleh->name ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->hasil_tinjauan === null)
                                    <div class="flex flex-col gap-1.5">
                                        <button type="button" wire:click="sesuai({{ $item->id }})"
                                            wire:confirm="Tandai penarikan ini sesuai?"
                                            class="min-h-9 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                            Sesuai
                                        </button>
                                        <button type="button" wire:click="pilihTinjauan({{ $item->id }})"
                                            class="min-h-9 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700">
                                            Bermasalah
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500" data-test="tinjauan-kosong">
                                Tidak ada penarikan offline pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($penarikans->hasPages())
            <div class="border-t border-[#ebebeb] px-4 py-3 dark:border-zinc-700">
                {{ $penarikans->links() }}
            </div>
        @endif
    </div>

    {{-- Kartu (di bawah md) --}}
    <div class="space-y-3 md:hidden">
        @forelse ($penarikans as $item)
            <div class="rounded-xl bg-white p-4 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb] dark:bg-zinc-800 dark:shadow-none dark:inset-ring dark:inset-ring-white/10"
                data-test="kartu-tinjauan-{{ $item->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->nasabah->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $item->dibayarOleh->name ?? '-' }} · {{ optional($item->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    <span class="shrink-0 font-mono text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}</span>
                </div>
                <p class="mt-2 text-xs text-gray-600 dark:text-slate-300">{{ $item->catatan_kolektor ?? '—' }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    @if ($item->bukti_path)
                        <a href="{{ route('penarikan.bukti', $item) }}" target="_blank" rel="noopener" class="font-semibold text-indigo-600 hover:underline">Lihat bukti</a>
                    @endif
                    @if ($item->hasil_tinjauan === null)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 font-medium text-[#ab570a]">Belum ditinjau</span>
                    @elseif ($item->hasil_tinjauan === 'sesuai')
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-medium text-emerald-700">Sesuai</span>
                    @else
                        <span class="rounded-full bg-rose-100 px-2 py-0.5 font-medium text-rose-700">Bermasalah</span>
                    @endif
                </div>
                @if ($item->hasil_tinjauan === null)
                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click="sesuai({{ $item->id }})"
                            wire:confirm="Tandai penarikan ini sesuai?"
                            class="min-h-11 flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Sesuai</button>
                        <button type="button" wire:click="pilihTinjauan({{ $item->id }})"
                            class="min-h-11 flex-1 rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Bermasalah</button>
                    </div>
                @endif
            </div>
        @empty
            @if ($penarikans->isEmpty())
                <p class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 dark:bg-zinc-800 dark:text-slate-400" data-test="kartu-tinjauan-kosong">
                    Tidak ada penarikan offline pada filter ini.
                </p>
            @endif
        @endforelse
    </div>

    {{-- Formulir penandaan bermasalah --}}
    @if ($terpilihId !== null)
        <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb] dark:bg-zinc-800 dark:shadow-none dark:inset-ring dark:inset-ring-white/10"
            data-test="form-bermasalah">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Tandai bermasalah</h2>
            <p class="mt-1 text-xs text-gray-500">Wajib mengisi alasan minimal 10 karakter.</p>

            <div class="mt-4 space-y-3">
                <div>
                    <label for="catatan-tinjauan" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">Alasan</label>
                    <textarea id="catatan-tinjauan" wire:model="catatanTinjauan" rows="3" maxlength="255"
                        placeholder="mis. nominal tidak cocok dengan bukti tanda tangan"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white"></textarea>
                    @error('catatanTinjauan')
                        <p class="mt-1 text-xs font-medium text-rose-600" data-test="error-tinjauan">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-slate-300">
                    <input type="checkbox" wire:model="bekukanKolektor" class="mt-0.5 h-4 w-4 rounded border-gray-300">
                    <span>Bekukan penarikan offline langsung kolektor ini (disarankan).</span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="bermasalah({{ $terpilihId }})"
                        class="min-h-11 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700"
                        data-test="submit-bermasalah">
                        Simpan Bermasalah
                    </button>
                    <button type="button" wire:click="batalTinjauan"
                        class="min-h-11 rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-zinc-700 dark:text-zinc-200">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
