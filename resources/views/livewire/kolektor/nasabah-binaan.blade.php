<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-5 flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Nasabah binaan</h1>
            <p class="mt-0.5 text-xs text-mute">Daftar nasabah tabungan yang Anda tangani di lapangan.</p>
        </div>
        <a href="{{ route('kolektor.daftar-nasabah.index') }}" wire:navigate
           class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95">
            <flux:icon.plus class="size-3.5" aria-hidden="true" />
            <span>+ Daftar</span>
        </a>
    </div>

    {{-- Summary Cards (st4 style: compact 2x2 grid matching prototype) --}}
    <div class="mb-5 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-2.5">
        <div class="flex flex-col justify-between rounded-[20px] border border-hairline bg-white p-3 shadow-xs dark:bg-surface">
            <div class="flex items-center justify-between gap-1">
                <span class="text-xs font-medium text-mute">Total binaan</span>
                <flux:icon.users class="size-3.5 shrink-0 text-mute" aria-hidden="true" />
            </div>
            <p class="mt-1 text-base font-semibold tracking-tight tabular-nums text-ink sm:text-lg">
                {{ $totalNasabah }}
            </p>
        </div>

        <div class="flex flex-col justify-between rounded-[20px] border border-hairline bg-white p-3 shadow-xs dark:bg-surface">
            <div class="flex items-center justify-between gap-1">
                <span class="text-xs font-medium text-mute">Saldo dikelola</span>
                <flux:icon.wallet class="size-3.5 shrink-0 text-primary" aria-hidden="true" />
            </div>
            <p class="mt-1 text-base font-semibold tracking-tight text-ink tabular-nums sm:text-lg truncate">
                <x-ui.rupiah :nilai="$totalSaldoDikelola" />
            </p>
        </div>

        <div class="flex flex-col justify-between rounded-[20px] border border-hairline bg-white p-3 shadow-xs dark:bg-surface">
            <div class="flex items-center justify-between gap-1">
                <span class="text-xs font-medium text-mute">Ada tunggakan</span>
                <flux:icon.exclamation-triangle class="size-3.5 shrink-0 {{ $totalNasabahTunggakan > 0 ? 'text-warning-deep' : 'text-mute' }}" aria-hidden="true" />
            </div>
            <p class="mt-1 text-base font-semibold tracking-tight tabular-nums sm:text-lg {{ $totalNasabahTunggakan > 0 ? 'text-warning-deep' : 'text-ink' }}">
                {{ $totalNasabahTunggakan }}
            </p>
        </div>

        <div class="flex flex-col justify-between rounded-[20px] border border-hairline bg-white p-3 shadow-xs dark:bg-surface">
            <div class="flex items-center justify-between gap-1">
                <span class="text-xs font-medium text-mute">Binaan offline</span>
                <flux:icon.signal-slash class="size-3.5 shrink-0 text-mute" aria-hidden="true" />
            </div>
            <p class="mt-1 text-base font-semibold tracking-tight tabular-nums text-ink sm:text-lg">
                {{ $totalNasabahOffline }}
            </p>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="mb-5 space-y-3">
        <div class="flex items-center rounded-xl border border-hairline-strong bg-white px-3.5 py-2.5 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 dark:bg-surface">
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

        {{-- Single Unified Filter Chips Bar --}}
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="$set('filterTunggakan', 'semua'); $set('filterMode', 'semua')"
                class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $filterTunggakan === 'semua' && $filterMode === 'semua' ? 'border border-primary bg-primary-soft text-on-primary-soft shadow-xs' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                Semua ({{ $totalNasabah }})
            </button>

            <button type="button" wire:click="$set('filterTunggakan', 'tunggakan'); $set('filterMode', 'semua')"
                class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $filterTunggakan === 'tunggakan' ? 'border border-warning bg-warning-soft text-warning-deep shadow-xs' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                <flux:icon.exclamation-triangle class="size-3.5" aria-hidden="true" />
                Ada tunggakan ({{ $totalNasabahTunggakan }})
            </button>

            <button type="button" wire:click="$set('filterTunggakan', 'semua'); $set('filterMode', 'offline')"
                class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $filterMode === 'offline' ? 'border border-hairline-strong bg-inset text-ink shadow-xs' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                <flux:icon.signal-slash class="size-3.5" aria-hidden="true" />
                Offline ({{ $totalNasabahOffline }})
            </button>

            <button type="button" wire:click="$set('filterTunggakan', 'semua'); $set('filterMode', 'digital')"
                class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $filterMode === 'digital' ? 'border border-info bg-info-soft text-info shadow-xs' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                Digital
            </button>
        </div>
    </div>

    {{-- Nasabah Cards List --}}
    <div class="space-y-4">
        @forelse($nasabahList as $profil)
            @php
                $userNasabah = $profil->user;
                $totalSaldoNasabah = $userNasabah ? $userNasabah->saldoProduks->sum('saldo') : 0;
                $tunggakanActive = $userNasabah ? $userNasabah->kepesertaanPakets->whereNull('keputusan_akhir')->first() : null;
            @endphp
            <div class="rounded-2xl border border-hairline bg-white p-4.5 shadow-xs transition hover:border-hairline-strong dark:bg-surface">
                {{-- Header Nasabah: Identitas & Status --}}
                <div class="flex items-start gap-3.5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-on-primary-soft" aria-hidden="true">
                        {{ strtoupper(substr($profil->nama, 0, 2)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-base font-semibold text-ink">
                                {{ $profil->nama }}
                            </h2>
                            <x-ui.status-chip domain="pendaftaran" status="{{ $profil->status_pendaftaran }}" />
                            @if($userNasabah && $userNasabah->isOffline())
                                <span class="inline-flex items-center gap-1 rounded-full bg-inset px-2.5 py-0.5 text-xs font-medium text-body">
                                    <flux:icon.signal-slash class="size-3" aria-hidden="true" />
                                    Mode Offline
                                </span>
                            @endif
                        </div>

                        <p class="mt-1 flex items-center gap-1.5 text-xs text-mute">
                            <flux:icon.map-pin class="size-3.5 shrink-0" aria-hidden="true" />
                            <span class="truncate">{{ $profil->alamat ?? 'Alamat belum diisi' }}</span>
                        </p>

                        @if($userNasabah && $userNasabah->no_hp)
                            <div class="mt-1.5 flex items-center gap-2.5">
                                <a href="tel:{{ $userNasabah->no_hp }}" class="inline-flex items-center gap-1 text-xs text-mute hover:text-primary">
                                    <flux:icon.device-phone-mobile class="size-3.5" aria-hidden="true" />
                                    <span class="tabular-nums">{{ $userNasabah->no_hp }}</span>
                                </a>
                                @php
                                    $cleanHp = preg_replace('/[^0-9]/', '', $userNasabah->no_hp);
                                    if (str_starts_with($cleanHp, '0')) {
                                        $cleanHp = '62' . substr($cleanHp, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanHp }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-semibold text-primary transition hover:bg-primary hover:text-white">
                                    <flux:icon.chat-bubble-left-right class="size-3" aria-hidden="true" />
                                    WA
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Box Rincian Saldo & Produk --}}
                <div class="mt-3.5 rounded-xl border border-hairline bg-inset p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-mute">Total saldo tabungan</span>
                        <span class="text-base font-semibold text-primary tabular-nums">
                            <x-ui.rupiah :nilai="$totalSaldoNasabah" />
                        </span>
                    </div>

                    @if($tunggakanActive && $tunggakanActive->tunggakan > 0)
                        <div class="flex items-center justify-between rounded-lg border border-warning-soft bg-warning-soft px-3 py-2 text-xs">
                            <span class="flex items-center gap-1.5 font-semibold text-warning-deep">
                                <flux:icon.exclamation-triangle class="size-3.5 shrink-0" aria-hidden="true" />
                                Tunggakan {{ $tunggakanActive->produk->nama ?? 'Paket' }}
                            </span>
                            <span class="font-semibold tabular-nums text-warning-deep">
                                {{ $tunggakanActive->tunggakan }} hari
                            </span>
                        </div>
                    @endif

                    @if($userNasabah && $userNasabah->saldoProduks->count() > 0)
                        <div class="flex flex-wrap gap-1.5 border-t border-hairline pt-2">
                            @foreach($userNasabah->saldoProduks as $sp)
                                <span class="rounded-lg border border-hairline bg-white px-2.5 py-1 text-xs text-body dark:bg-surface">
                                    {{ $sp->produk->nama }}: <strong class="font-semibold text-ink"><x-ui.rupiah :nilai="$sp->saldo" /></strong>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Baris Pengaturan Hari Kunjungan --}}
                <div class="mt-3.5 flex items-center justify-between gap-2 border-t border-hairline pt-3">
                    <span class="text-xs text-mute shrink-0">Hari kunjungan:</span>
                    <div class="flex items-center gap-1">
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
                                    class="flex size-8 items-center justify-center rounded-full text-xs font-semibold transition active:scale-90 {{ $terpilih ? 'bg-primary text-on-primary shadow-xs' : 'bg-inset text-mute hover:bg-hairline hover:text-ink' }}">
                                {{ substr($namaHari, 0, 1) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Baris Tombol Aksi --}}
                <div class="mt-3.5 flex flex-wrap items-center justify-end gap-2 border-t border-hairline pt-3">
                    <button type="button"
                            wire:click="bukaDaftarPaket({{ $profil->user_id }})"
                            class="inline-flex h-10 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 text-xs font-semibold text-ink transition hover:bg-inset active:scale-95 dark:bg-surface">
                        <flux:icon.plus class="size-3.5" aria-hidden="true" />
                        Daftar paket
                    </button>

                    <a href="{{ route('kolektor.penarikan-offline.index', ['nasabah_id' => $profil->user_id]) }}" wire:navigate
                       class="inline-flex h-10 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 text-xs font-semibold text-ink transition hover:bg-inset active:scale-95 dark:bg-surface">
                        <flux:icon.arrow-down class="size-3.5" aria-hidden="true" />
                        Tarik tunai
                    </a>

                    <a href="{{ route('kolektor.setoran.index', ['nasabah_id' => $profil->user_id]) }}" wire:navigate
                       class="inline-flex h-10 items-center gap-1.5 rounded-full bg-primary px-4 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95">
                        <flux:icon.plus-circle class="size-3.5" aria-hidden="true" />
                        Input setoran
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-hairline bg-white py-16 text-center dark:bg-surface">
                <flux:icon.users class="mx-auto mb-2 size-10 text-mute" aria-hidden="true" />
                <p class="text-sm font-semibold text-ink">Belum ada nasabah binaan</p>
                <p class="mt-1 text-xs text-mute">Nasabah yang ditugaskan oleh admin akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($nasabahList->hasPages())
        <div class="mt-5">{{ $nasabahList->links() }}</div>
    @endif

    {{-- Modal Pendaftaran Paket --}}
    @if($tampilDaftarPaket)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             role="dialog" aria-modal="true" aria-labelledby="modal-paket-title">
            <div class="fixed inset-0" wire:click="tutupDaftarPaket" aria-hidden="true"></div>

            <div class="relative w-full max-w-md rounded-[24px] border border-hairline bg-surface p-5 sm:p-6 shadow-md dark:bg-surface max-h-[92vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-hairline/80">
                    <div>
                        <h3 id="modal-paket-title" class="text-base font-semibold text-ink dark:text-zinc-100">Daftarkan ke paket</h3>
                        <p class="mt-0.5 text-xs text-mute dark:text-zinc-400">Pendaftaran dicatat atas nama kolektor dengan komitmen wajib (D16).</p>
                    </div>
                    <button type="button" wire:click="tutupDaftarPaket" aria-label="Tutup"
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="produkDaftarId" class="mb-1.5 block text-xs font-medium text-body dark:text-zinc-300">Pilih paket tabungan</label>
                        <select id="produkDaftarId" wire:model.live="produkDaftarId"
                            class="w-full rounded-xl border border-hairline-strong bg-surface px-3.5 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800 dark:text-zinc-100 dark:border-zinc-700">
                            <option value="0">— Pilih paket —</option>
                            @foreach($paketTerbuka as $paket)
                                <option value="{{ $paket->id }}">{{ $paket->nama }} — Rp {{ number_format((float) ($paket->harga_per_hari ?? 0), 0, ',', '.') }}/hari</option>
                            @endforeach
                        </select>
                        @error('produkDaftarId') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                        @if($paketTerbuka->isEmpty())
                            <p class="mt-2 flex items-start gap-1.5 rounded-xl border border-warning-soft bg-warning-soft px-3 py-2 text-xs text-warning-deep dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/50">
                                <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                <span>Tidak ada paket terbuka untuk nasabah ini (sudah diikuti atau pendaftaran ditutup).</span>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label for="catatanDaftar" class="mb-1.5 block text-xs font-medium text-body dark:text-zinc-300">Catatan pendaftaran</label>
                        <textarea id="catatanDaftar" wire:model="catatanDaftar" rows="2" placeholder="Contoh: nasabah setuju saat kunjungan, membaca komitmen."
                            class="w-full rounded-xl border border-hairline-strong bg-surface px-3.5 py-2.5 text-sm text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800 dark:text-zinc-100 dark:border-zinc-700 dark:placeholder:text-zinc-500"></textarea>
                        @error('catatanDaftar') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-xs text-body dark:text-zinc-300 cursor-pointer">
                        <input type="checkbox" wire:model="setujuDaftar" class="mt-0.5 size-4 shrink-0 rounded border-hairline-strong text-primary focus:ring-primary" />
                        <span>Nasabah telah membaca, memahami, dan menyetujui komitmen paket ini (D16).</span>
                    </label>
                    @error('setujuDaftar') <p class="flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror

                    @if($pesanErrorDaftar)
                        <p class="flex items-start gap-1.5 rounded-xl border border-danger-soft bg-danger-soft px-3 py-2 text-xs text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
                            <flux:icon.exclamation-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>{{ $pesanErrorDaftar }}</span>
                        </p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-2.5">
                    <button type="button" wire:click="tutupDaftarPaket"
                        class="inline-flex h-11 min-h-[44px] items-center rounded-full border border-hairline-strong bg-surface px-5 text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700">
                        Batal
                    </button>
                    <button type="button" wire:click="daftarkanKePaket" wire:loading.attr="disabled"
                        class="inline-flex h-11 min-h-[44px] items-center rounded-full bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95 disabled:opacity-50">
                        Daftarkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
