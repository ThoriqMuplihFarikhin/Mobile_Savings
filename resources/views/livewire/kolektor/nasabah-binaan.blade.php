<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">Nasabah Binaan</h1>
        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Daftar nasabah tabungan yang Anda tangani di lapangan.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="mb-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-3.5 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Binaan</p>
            <p class="mt-1 font-mono text-lg font-bold text-zinc-900 dark:text-white">{{ $totalNasabah }}</p>
            <p class="mt-0.5 text-[10px] text-zinc-400">Nasabah aktif</p>
        </div>

        <div class="rounded-2xl bg-white p-3.5 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Saldo Dikelola</p>
            <p class="mt-1 font-mono text-sm font-bold text-blue-600 dark:text-blue-400 truncate">
                Rp {{ number_format($totalSaldoDikelola, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[10px] text-zinc-400">Total simpanan</p>
        </div>

        <div class="rounded-2xl bg-white p-3.5 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Ada Tunggakan</p>
            <p class="mt-1 font-mono text-lg font-bold text-amber-600 dark:text-amber-400">{{ $totalNasabahTunggakan }}</p>
            <p class="mt-0.5 text-[10px] text-amber-500 font-medium">Perlu ditagih</p>
        </div>

        <div class="rounded-2xl bg-white p-3.5 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Binaan Offline</p>
            <p class="mt-1 font-mono text-lg font-bold text-zinc-900 dark:text-white">{{ $totalNasabahOffline }}</p>
            <p class="mt-0.5 text-[10px] text-zinc-400">Tanpa aplikasi</p>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    <div class="mb-5 space-y-3">
        <div class="flex items-center rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-xs shadow-2xs focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/10">
            <flux:icon.magnifying-glass class="size-4 text-zinc-400 shrink-0 mr-2" />
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama nasabah, alamat, atau no HP..."
                   class="w-full bg-transparent text-xs text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none border-none p-0">
            @if($search)
                <button type="button" wire:click="$set('search', '')" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <flux:icon.x-mark class="size-4" />
                </button>
            @endif
        </div>

        <div class="flex items-center gap-1.5">
            <button type="button" wire:click="$set('filterTunggakan', 'semua')"
                class="rounded-full px-3 py-1 text-[11px] font-semibold transition {{ $filterTunggakan === 'semua' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-2xs' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50' }}">
                Semua Nasabah ({{ $totalNasabah }})
            </button>
            <button type="button" wire:click="$set('filterTunggakan', 'tunggakan')"
                class="rounded-full px-3 py-1 text-[11px] font-semibold transition {{ $filterTunggakan === 'tunggakan' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-white dark:bg-zinc-800 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-amber-50' }}">
                ⚠️ Ada Tunggakan ({{ $totalNasabahTunggakan }})
            </button>
        </div>

        <div class="flex items-center gap-1.5">
            <button type="button" wire:click="$set('filterMode', 'semua')"
                class="rounded-full px-3 py-1 text-[11px] font-semibold transition {{ $filterMode === 'semua' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-2xs' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50' }}">
                Semua Mode
            </button>
            <button type="button" wire:click="$set('filterMode', 'digital')"
                class="rounded-full px-3 py-1 text-[11px] font-semibold transition {{ $filterMode === 'digital' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white dark:bg-zinc-800 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900 hover:bg-blue-50' }}">
                Digital
            </button>
            <button type="button" wire:click="$set('filterMode', 'offline')"
                class="rounded-full px-3 py-1 text-[11px] font-semibold transition {{ $filterMode === 'offline' ? 'bg-zinc-900 text-white shadow-2xs' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50' }}">
                Offline
            </button>
        </div>
    </div>

    {{-- Nasabah Card List --}}
    <div class="space-y-3">
        @forelse($nasabahList as $profil)
            @php
                $userNasabah = $profil->user;
                $totalSaldoNasabah = $userNasabah ? $userNasabah->saldoProduks->sum('saldo') : 0;
                $tunggakanActive = $userNasabah ? $userNasabah->kepesertaanPakets->whereNull('keputusan_akhir')->first() : null;
            @endphp
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80 transition hover:border-zinc-300 dark:hover:border-zinc-600">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 text-xs font-bold shadow-2xs">
                            {{ strtoupper(substr($profil->nama, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold text-zinc-900 dark:text-white truncate">
                                    {{ $profil->nama }}
                                </h3>
                                <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">
                                    Aktif
                                </span>
                                @if($userNasabah && $userNasabah->isOffline())
                                    <span class="rounded-full bg-zinc-200 dark:bg-zinc-700 px-2 py-0.5 text-[10px] font-bold text-zinc-700 dark:text-zinc-200">
                                        Mode Offline
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                📍 {{ $profil->alamat ?? 'Alamat belum diisi' }}
                            </p>
                            @if($userNasabah && $userNasabah->no_hp)
                                <div class="flex items-center gap-2 mt-1">
                                    <a href="tel:{{ $userNasabah->no_hp }}" class="inline-flex items-center gap-1 text-[11px] text-blue-600 dark:text-blue-400 hover:underline">
                                        📞 {{ $userNasabah->no_hp }}
                                    </a>
                                    @php
                                        $cleanHp = preg_replace('/[^0-9]/', '', $userNasabah->no_hp);
                                        if (str_starts_with($cleanHp, '0')) {
                                            $cleanHp = '62' . substr($cleanHp, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanHp }}" target="_blank" class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        💬 WA
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Saldo Nasabah Badge --}}
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-zinc-400 block">Total Saldo</span>
                        <span class="font-mono text-xs font-bold text-zinc-900 dark:text-white">
                            Rp {{ number_format($totalSaldoNasabah, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Products & Tunggakan Info --}}
                @if($userNasabah && ($userNasabah->saldoProduks->count() > 0 || ($tunggakanActive && $tunggakanActive->tunggakan > 0)))
                    <div class="mb-3 space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-700/50">
                        @if($tunggakanActive && $tunggakanActive->tunggakan > 0)
                            <div class="flex items-center justify-between rounded-xl bg-amber-50 dark:bg-amber-950/40 p-2 border border-amber-200 dark:border-amber-800/60">
                                <span class="text-[11px] font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1">
                                    ⚠️ Tunggakan {{ $tunggakanActive->produk->nama ?? 'Paket' }}
                                </span>
                                <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-300">
                                    {{ $tunggakanActive->tunggakan }} hari
                                </span>
                            </div>
                        @endif

                        @if($userNasabah->saldoProduks->count() > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($userNasabah->saldoProduks as $sp)
                                    <span class="rounded-lg bg-zinc-100 dark:bg-zinc-700/60 px-2 py-0.5 text-[10px] font-medium text-zinc-600 dark:text-zinc-300">
                                        {{ $sp->produk->nama }}: <strong>Rp {{ number_format($sp->saldo, 0, ',', '.') }}</strong>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Atur Hari Kunjungan --}}
                <div class="mt-3 pt-2 border-t border-zinc-100 dark:border-zinc-700/50 flex items-center justify-between gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 shrink-0">Hari Kunjungan</span>
                    <div class="flex gap-1">
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $index => $namaHari)
                            @php
                                $nomorHari = $index + 1;
                                $terpilih = in_array($nomorHari, $hariKunjungan[$profil->user_id] ?? [], true);
                            @endphp
                            <button type="button"
                                    wire:click="toggleHariKunjungan({{ $profil->user_id }}, {{ $nomorHari }})"
                                    title="{{ $namaHari }}"
                                    class="h-6 w-6 rounded-full text-[10px] font-bold transition {{ $terpilih ? 'bg-blue-600 text-white shadow-2xs' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-700/60 dark:hover:bg-zinc-600' }}">
                                {{ substr($namaHari, 0, 1) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="pt-2 border-t border-zinc-100 dark:border-zinc-700/50 flex items-center justify-between">
                    <span class="text-[10px] text-zinc-400">
                        @if($profil->tanggal_lahir)
                            Lahir: {{ \Carbon\Carbon::parse($profil->tanggal_lahir)->translatedFormat('d M Y') }}
                        @else
                            Terdaftar Aktif
                        @endif
                    </span>

                    <div class="flex items-center gap-1.5">
                        <button type="button"
                                wire:click="bukaDaftarPaket({{ $profil->user_id }})"
                                class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-bold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition flex items-center gap-1">
                            <flux:icon.plus class="size-3.5" />
                            Daftar Paket
                        </button>

                        <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
                           class="rounded-xl bg-zinc-900 dark:bg-white px-3 py-1.5 text-xs font-bold text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-100 transition flex items-center gap-1">
                            <flux:icon.plus-circle class="size-3.5" />
                            Input Setoran
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                <flux:icon.users class="size-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" />
                <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Belum ada nasabah binaan</p>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-0.5">Nasabah yang ditugaskan oleh admin akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($nasabahList->hasPages())
        <div class="mt-5">{{ $nasabahList->links() }}</div>
    @endif

    @if($tampilDaftarPaket)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftarkan ke Paket</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-zinc-400">Pendaftaran dicatat atas nama kolektor (via kolektor) dengan persetujuan dan catatan wajib (D16).</p>

                <div class="mt-4 space-y-3">
                    <div>
                        <label for="produkDaftarId" class="block text-xs font-medium text-gray-700 dark:text-zinc-300">Pilih Paket</label>
                        <select id="produkDaftarId" wire:model.live="produkDaftarId" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-blue-500 focus:outline-none">
                            <option value="0">— Pilih paket —</option>
                            @foreach($paketTerbuka as $paket)
                                <option value="{{ $paket->id }}">{{ $paket->nama }} — Rp {{ number_format((float) ($paket->harga_per_hari ?? 0), 0, ',', '.') }}/hari</option>
                            @endforeach
                        </select>
                        @error('produkDaftarId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @if($paketTerbuka->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">Tidak ada paket terbuka untuk nasabah ini (sudah diikuti atau pendaftaran ditutup).</p>
                        @endif
                    </div>

                    <div>
                        <label for="catatanDaftar" class="block text-xs font-medium text-gray-700 dark:text-zinc-300">Catatan Pendaftaran</label>
                        <textarea id="catatanDaftar" wire:model="catatanDaftar" rows="2" placeholder="Contoh: nasabah setuju saat kunjungan, membaca komitmen." class="mt-1 w-full rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-blue-500 focus:outline-none"></textarea>
                        @error('catatanDaftar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model="setujuDaftar" class="mt-0.5 h-4 w-4 rounded border-gray-300" />
                        <span>Nasabah telah membaca, memahami, dan menyetujui komitmen paket ini (D16).</span>
                    </label>
                    @error('setujuDaftar') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    @if($pesanErrorDaftar)
                        <p class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300">{{ $pesanErrorDaftar }}</p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="tutupDaftarPaket" class="rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white transition hover:bg-zinc-50 dark:hover:bg-zinc-800">Batal</button>
                    <button wire:click="daftarkanKePaket" wire:loading.attr="disabled" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Daftarkan</button>
                </div>
            </div>
        </div>
    @endif
</div>