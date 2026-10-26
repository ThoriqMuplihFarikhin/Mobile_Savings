<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Nasabah binaan</h1>
        <p class="mt-1 text-sm text-mute">Daftar nasabah tabungan yang Anda tangani di lapangan.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="mb-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
        <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <p class="text-xs text-mute">Total binaan</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-ink">{{ $totalNasabah }}</p>
            <p class="mt-0.5 text-xs text-subtle">Nasabah aktif</p>
        </div>

        <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <p class="text-xs text-mute">Saldo dikelola</p>
            <p class="mt-1 text-xl font-semibold">
                <x-ui.rupiah :nilai="$totalSaldoDikelola" />
            </p>
            <p class="mt-0.5 text-xs text-subtle">Total simpanan</p>
        </div>

        <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <p class="text-xs text-mute">Ada tunggakan</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-warning-deep">{{ $totalNasabahTunggakan }}</p>
            <p class="mt-0.5 flex items-center gap-1 text-xs font-medium text-warning-deep">
                <flux:icon.exclamation-triangle class="size-3.5" aria-hidden="true" />
                Perlu ditagih
            </p>
        </div>

        <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <p class="text-xs text-mute">Binaan offline</p>
            <p class="mt-1 text-xl font-semibold tabular-nums text-ink">{{ $totalNasabahOffline }}</p>
            <p class="mt-0.5 text-xs text-subtle">Tanpa aplikasi</p>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    <div class="mb-5 space-y-3">
        <div class="flex items-center rounded-xl border border-hairline-strong bg-white px-3 py-3 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 dark:bg-surface">
            <flux:icon.magnifying-glass class="mr-2 size-4 shrink-0 text-mute" aria-hidden="true" />
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama nasabah, alamat, atau no HP..."
                   class="w-full border-none bg-transparent p-0 text-sm text-ink placeholder:text-subtle focus:outline-none">
            @if($search)
                <button type="button" wire:click="$set('search', '')" aria-label="Hapus pencarian" class="text-mute hover:text-ink">
                    <flux:icon.x-mark class="size-4" />
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <button type="button" wire:click="$set('filterTunggakan', 'semua')"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $filterTunggakan === 'semua' ? 'border border-primary bg-primary-soft text-on-primary-soft' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                Semua nasabah ({{ $totalNasabah }})
            </button>
            <button type="button" wire:click="$set('filterTunggakan', 'tunggakan')"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition {{ $filterTunggakan === 'tunggakan' ? 'border border-warning bg-warning-soft text-warning-deep' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                <flux:icon.exclamation-triangle class="size-3.5" aria-hidden="true" />
                Ada tunggakan ({{ $totalNasabahTunggakan }})
            </button>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" wire:click="$set('filterMode', 'semua')"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $filterMode === 'semua' ? 'border border-primary bg-primary-soft text-on-primary-soft' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                Semua mode
            </button>
            <button type="button" wire:click="$set('filterMode', 'digital')"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $filterMode === 'digital' ? 'border border-info bg-info-soft text-info' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                Digital
            </button>
            <button type="button" wire:click="$set('filterMode', 'offline')"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $filterMode === 'offline' ? 'border border-hairline-strong bg-inset text-ink' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
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
            <div class="rounded-2xl border border-hairline bg-white p-4 transition hover:border-hairline-strong dark:bg-surface">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-on-primary-soft" aria-hidden="true">
                            {{ strtoupper(substr($profil->nama, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-sm font-semibold text-ink">
                                    {{ $profil->nama }}
                                </h3>
                                <x-ui.status-chip domain="pendaftaran" status="{{ $profil->status_pendaftaran }}" />
                                @if($userNasabah && $userNasabah->isOffline())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-inset px-2 py-1 text-xs font-medium text-body">
                                        <flux:icon.signal-slash class="size-3.5" aria-hidden="true" />
                                        Mode Offline
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 flex items-center gap-1 truncate text-sm text-mute">
                                <flux:icon.map-pin class="size-4 shrink-0" aria-hidden="true" />
                                <span class="truncate">{{ $profil->alamat ?? 'Alamat belum diisi' }}</span>
                            </p>
                            @if($userNasabah && $userNasabah->no_hp)
                                <div class="mt-1 flex items-center gap-3">
                                    <a href="tel:{{ $userNasabah->no_hp }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                                        <flux:icon.device-phone-mobile class="size-4" aria-hidden="true" />
                                        <span class="tabular-nums">{{ $userNasabah->no_hp }}</span>
                                    </a>
                                    @php
                                        $cleanHp = preg_replace('/[^0-9]/', '', $userNasabah->no_hp);
                                        if (str_starts_with($cleanHp, '0')) {
                                            $cleanHp = '62' . substr($cleanHp, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanHp }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                                        <flux:icon.chat-bubble-left-right class="size-4" aria-hidden="true" />
                                        WA
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Saldo Nasabah Badge --}}
                    <div class="shrink-0 text-right">
                        <span class="block text-xs text-mute">Total saldo</span>
                        <span class="text-sm font-semibold">
                            <x-ui.rupiah :nilai="$totalSaldoNasabah" />
                        </span>
                    </div>
                </div>

                {{-- Products & Tunggakan Info --}}
                @if($userNasabah && ($userNasabah->saldoProduks->count() > 0 || ($tunggakanActive && $tunggakanActive->tunggakan > 0)))
                    <div class="mb-3 space-y-1.5 border-t border-hairline pt-3">
                        @if($tunggakanActive && $tunggakanActive->tunggakan > 0)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-warning-soft bg-warning-soft p-2.5">
                                <span class="flex min-w-0 items-center gap-1.5 text-xs font-semibold text-warning-deep">
                                    <flux:icon.exclamation-triangle class="size-4 shrink-0" aria-hidden="true" />
                                    <span class="truncate">Tunggakan {{ $tunggakanActive->produk->nama ?? 'Paket' }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold tabular-nums text-warning-deep">
                                    {{ $tunggakanActive->tunggakan }} hari
                                </span>
                            </div>
                        @endif

                        @if($userNasabah->saldoProduks->count() > 0)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($userNasabah->saldoProduks as $sp)
                                    <span class="rounded-full bg-inset px-2.5 py-1 text-xs text-body">
                                        {{ $sp->produk->nama }}: <strong class="font-semibold text-ink"><x-ui.rupiah :nilai="$sp->saldo" /></strong>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Atur Hari Kunjungan --}}
                <div class="mt-3 border-t border-hairline pt-3">
                    <p class="text-xs text-mute">Hari kunjungan</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $index => $namaHari)
                            @php
                                $nomorHari = $index + 1;
                                $terpilih = in_array($nomorHari, $hariKunjungan[$profil->user_id] ?? [], true);
                            @endphp
                            <button type="button"
                                    wire:click="toggleHariKunjungan({{ $profil->user_id }}, {{ $nomorHari }})"
                                    title="{{ $namaHari }}"
                                    aria-label="{{ $namaHari }}"
                                    aria-pressed="{{ $terpilih ? 'true' : 'false' }}"
                                    class="flex size-11 items-center justify-center rounded-full text-xs font-medium transition {{ $terpilih ? 'bg-primary text-on-primary' : 'bg-inset text-body hover:bg-hairline' }}">
                                {{ substr($namaHari, 0, 1) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-hairline pt-3">
                    <span class="text-xs text-subtle">
                        @if($profil->tanggal_lahir)
                            Lahir: {{ \Carbon\Carbon::parse($profil->tanggal_lahir)->translatedFormat('d M Y') }}
                        @else
                            Terdaftar aktif
                        @endif
                    </span>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                wire:click="bukaDaftarPaket({{ $profil->user_id }})"
                                class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3 py-2 text-sm font-medium text-ink transition hover:bg-inset dark:bg-surface">
                            <flux:icon.plus class="size-4" aria-hidden="true" />
                            Daftar paket
                        </button>

                        <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-full bg-primary px-3 py-2 text-sm font-medium text-on-primary transition hover:bg-primary-hover">
                            <flux:icon.plus-circle class="size-4" aria-hidden="true" />
                            Input setoran
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-hairline bg-white py-16 text-center dark:bg-surface">
                <flux:icon.users class="mx-auto mb-2 size-10 text-mute" aria-hidden="true" />
                <p class="text-sm font-semibold text-ink">Belum ada nasabah binaan</p>
                <p class="mt-1 text-sm text-mute">Nasabah yang ditugaskan oleh admin akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($nasabahList->hasPages())
        <div class="mt-5">{{ $nasabahList->links() }}</div>
    @endif

    @if($tampilDaftarPaket)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl border border-hairline bg-white p-5 dark:bg-surface">
                <h3 class="text-lg font-semibold text-ink">Daftarkan ke paket</h3>
                <p class="mt-1 text-sm text-mute">Pendaftaran dicatat atas nama kolektor (via kolektor) dengan persetujuan dan catatan wajib (D16).</p>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="produkDaftarId" class="mb-1.5 block text-sm font-medium text-body">Pilih paket</label>
                        <select id="produkDaftarId" wire:model.live="produkDaftarId"
                            class="mt-0 w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                            <option value="0">— Pilih paket —</option>
                            @foreach($paketTerbuka as $paket)
                                <option value="{{ $paket->id }}">{{ $paket->nama }} — Rp {{ number_format((float) ($paket->harga_per_hari ?? 0), 0, ',', '.') }}/hari</option>
                            @endforeach
                        </select>
                        @error('produkDaftarId') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                        @if($paketTerbuka->isEmpty())
                            <p class="mt-2 flex items-start gap-1.5 rounded-xl border border-warning-soft bg-warning-soft px-3 py-2 text-sm text-warning-deep">
                                <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                <span>Tidak ada paket terbuka untuk nasabah ini (sudah diikuti atau pendaftaran ditutup).</span>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="catatanDaftar" class="mb-1.5 block text-sm font-medium text-body">Catatan pendaftaran</label>
                        <textarea id="catatanDaftar" wire:model="catatanDaftar" rows="2" placeholder="Contoh: nasabah setuju saat kunjungan, membaca komitmen."
                            class="mt-0 w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"></textarea>
                        @error('catatanDaftar') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-sm text-body">
                        <input type="checkbox" wire:model="setujuDaftar" class="mt-0.5 size-4 shrink-0 rounded accent-primary" />
                        <span>Nasabah telah membaca, memahami, dan menyetujui komitmen paket ini (D16).</span>
                    </label>
                    @error('setujuDaftar') <p class="flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror

                    @if($pesanErrorDaftar)
                        <p class="flex items-start gap-1.5 rounded-xl border border-danger-soft bg-danger-soft px-3 py-2 text-sm text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
                            <flux:icon.exclamation-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>{{ $pesanErrorDaftar }}</span>
                        </p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="tutupDaftarPaket"
                        class="inline-flex h-11 items-center rounded-full border border-hairline-strong bg-white px-5 text-sm font-medium text-ink transition hover:bg-inset dark:bg-surface">
                        Batal
                    </button>
                    <button wire:click="daftarkanKePaket" wire:loading.attr="disabled"
                        class="inline-flex h-11 items-center rounded-full bg-primary px-5 text-sm font-medium text-on-primary transition hover:bg-primary-hover disabled:opacity-50">
                        Daftarkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
