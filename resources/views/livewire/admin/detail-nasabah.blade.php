<div class="space-y-6">
    {{-- Banner Flash Notifikasi --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft border border-primary/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-danger-soft px-4 py-3 text-xs font-medium text-danger border border-danger/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Detail Nasabah"
        subtitle="Informasi saldo dan riwayat transaksi"
        breadcrumb="Nasabah"
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="bukaDaftarPaket"
                class="min-h-11 h-[38px] inline-flex items-center gap-1.5 rounded-[10px] bg-accent px-4 text-xs font-semibold text-accent-foreground transition hover:opacity-90 cursor-pointer shadow-xs"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Daftarkan ke Paket
            </button>
            <button
                type="button"
                wire:click="confirmResetPin"
                class="min-h-11 h-[38px] inline-flex items-center gap-1.5 rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
                Reset PIN
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Kartu Profil Nasabah --}}
    <div class="rounded-[14px] border border-hairline bg-surface p-5 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-accent text-lg font-semibold text-accent-foreground shadow-xs">
                    {{ $user->initials() }}
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-ink">{{ $user->name }}</h2>
                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-mute">
                        <span class="inline-flex items-center gap-1">
                            <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            {{ $user->no_hp ?? '— (offline)' }}
                        </span>
                        @if($user->nasabahProfil?->alamat)
                            <span class="inline-flex items-center gap-1">
                                <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ $user->nasabahProfil->alamat }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-admin.status-chip :status="$user->mode_akses === 'offline' ? 'Offline' : 'Digital'" />
                <x-admin.status-chip :status="$user->status_akun === 'aktif' ? 'Aktif' : 'Terkunci'" />
            </div>
        </div>
    </div>

    {{-- Saldo Per Produk --}}
    <div class="space-y-3">
        <x-admin.section title="Saldo per Produk">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($saldoPerProduk as $sp)
                    <div class="rounded-[14px] border border-hairline bg-surface p-4 shadow-xs">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs text-mute font-medium">{{ $sp->produk->nama ?? '-' }}</span>
                                <p class="mt-1 text-xl font-bold tabular-nums text-ink">
                                    Rp {{ number_format($sp->saldo, 0, ',', '.') }}
                                </p>
                            </div>
                            <x-admin.status-chip :status="ucfirst($sp->produk->tipe ?? '-')" />
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 rounded-[14px] border border-hairline bg-surface p-6">
                        <x-admin.empty-state
                            title="Belum ada saldo"
                            description="Saldo produk akan muncul setelah nasabah melakukan setoran."
                        />
                    </div>
                @endforelse
            </div>
        </x-admin.section>
    </div>

    {{-- Kepesertaan & Status Komitmen --}}
    <div class="space-y-3">
        <x-admin.section title="Kepesertaan Paket">
            <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
                @forelse($kepesertaan as $item)
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-hairline p-4 last:border-b-0 hover:bg-surface-2 transition">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink">{{ $item->produk?->nama ?? '-' }}</p>
                            <p class="mt-0.5 text-xs text-mute">
                                Gabung {{ $item->tanggal_mulai_ikut?->translatedFormat('d M Y') }}
                                @if($item->keputusan_akhir)
                                    · {{ ucfirst(str_replace('_', ' ', $item->keputusan_akhir)) }}
                                @else
                                    · Aktif
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-mute block">Komitmen</span>
                            <span class="mt-0.5 text-xs font-semibold text-ink block">
                                @if($item->komitmen_disetujui_pada)
                                    {{ \Carbon\Carbon::parse($item->komitmen_disetujui_pada)->translatedFormat('d M Y') }}
                                    @if($item->komitmen_via)
                                        · {{ match ($item->komitmen_via) {
                                            'admin' => 'Admin',
                                            'kolektor' => 'Kolektor',
                                            default => 'Mandiri',
                                        } }}
                                    @endif
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        @if($item->bukti_foto_url)
                            <div class="w-full pt-2">
                                <a href="{{ route('serah-terima.bukti', $item) }}"
                                    @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('serah-terima.bukti', $item) }}', judul: 'Foto Bukti Serah Terima', keterangan: '{{ $item->nasabah?->name ?? $user->name }} · {{ $item->produk?->nama }}' })"
                                    class="text-xs font-semibold text-accent underline-offset-2 hover:underline cursor-pointer">
                                    Lihat Foto Bukti Serah Terima
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <x-admin.empty-state
                            title="Belum ada kepesertaan paket"
                            description="Nasabah ini belum terdaftar di produk tabungan paket."
                        />
                    </div>
                @endforelse
            </div>
        </x-admin.section>
    </div>

    {{-- Perkembangan Saldo --}}
    <div class="rounded-[14px] border border-hairline bg-surface p-5 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-ink">Perkembangan Saldo</h3>
                <p class="text-xs text-mute mt-0.5">
                    @if($periode === '90hari')
                        90 hari terakhir
                    @elseif($periode === 'tahun_ini')
                        Tahun {{ now()->year }}
                    @else
                        30 hari terakhir
                    @endif
                </p>
            </div>
            <div class="inline-flex rounded-[10px] p-[3px] bg-surface-2 border border-hairline gap-1">
                <button
                    type="button"
                    wire:click="$set('periode', '30hari')"
                    class="h-7 px-3 rounded-[7px] text-xs font-medium transition cursor-pointer {{ $periode === '30hari' ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink' }}"
                >
                    30 Hari
                </button>
                <button
                    type="button"
                    wire:click="$set('periode', '90hari')"
                    class="h-7 px-3 rounded-[7px] text-xs font-medium transition cursor-pointer {{ $periode === '90hari' ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink' }}"
                >
                    90 Hari
                </button>
                <button
                    type="button"
                    wire:click="$set('periode', 'tahun_ini')"
                    class="h-7 px-3 rounded-[7px] text-xs font-medium transition cursor-pointer {{ $periode === 'tahun_ini' ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink' }}"
                >
                    Tahun Ini
                </button>
            </div>
        </div>

        @if($chartData['hasData'])
            <div class="overflow-hidden pt-2">
                <svg viewBox="0 0 {{ $chartData['chartWidth'] }} {{ $chartData['chartHeight'] }}" class="h-40 w-full" role="img" aria-label="{{ $chartData['ariaLabel'] }}">
                    @foreach([0.25, 0.5, 0.75, 1] as $ratio)
                        <line x1="{{ $chartData['padding'] }}" y1="{{ $chartData['padding'] + $chartData['graphHeight'] * (1 - $ratio) }}" x2="{{ $chartData['chartWidth'] - $chartData['padding'] }}" y2="{{ $chartData['padding'] + $chartData['graphHeight'] * (1 - $ratio) }}" stroke="currentColor" stroke-opacity="0.15" stroke-width="0.5" />
                    @endforeach
                    <path d="{{ $chartData['areaD'] }}" fill="currentColor" fill-opacity="0.12" />
                    <path d="{{ $chartData['pathD'] }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    @if($chartData['lastPoint'])
                        <circle cx="{{ $chartData['lastPoint']['x'] }}" cy="{{ $chartData['lastPoint']['y'] }}" r="3" fill="currentColor" />
                    @endif
                </svg>
            </div>
        @else
            <x-admin.empty-state
                title="Belum ada data"
                description="Grafik perkembangan saldo akan muncul setelah ada transaksi."
            />
        @endif
    </div>

    {{-- Riwayat Transaksi --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        <div class="border-b border-hairline p-4">
            <h3 class="text-base font-semibold text-ink">Riwayat Transaksi</h3>
        </div>
        @if(count($riwayat) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium border-b border-hairline">
                            <th class="px-4 py-2.5">Tanggal</th>
                            <th class="px-4 py-2.5">Tipe</th>
                            <th class="px-5 py-3 font-medium text-right">Nominal</th>
                            <th class="px-4 py-2.5 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @foreach($riwayat as $item)
                            <tr class="hover:bg-surface-2 transition text-ink">
                                <td class="whitespace-nowrap px-4 py-3 text-mute text-xs">{{ $item['tanggal'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <x-admin.status-chip :status="$item['tipe']" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 tabular-nums text-right text-sm">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <x-admin.status-chip :status="ucfirst($item['status'])" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center">
                <x-admin.empty-state
                    title="Belum ada riwayat"
                    description="Riwayat setoran dan penarikan akan muncul di sini."
                />
            </div>
        @endif
    </div>

    {{-- Modal Konfirmasi Reset PIN --}}
    @if($tampilKonfirmasiResetPin)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-reset-pin-nasabah-title">
            <div wire:click="$set('tampilKonfirmasiResetPin', false)" class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-md rounded-[16px] border border-hairline bg-surface p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-danger-soft text-danger">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="modal-reset-pin-nasabah-title" class="text-base font-semibold text-ink">Reset PIN?</h3>
                            <p class="mt-0.5 text-xs text-mute">Tindakan ini memerlukan konfirmasi.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('tampilKonfirmasiResetPin', false)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-mute">PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya.</p>
                @error('reset_pin') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('tampilKonfirmasiResetPin', false)" class="min-h-11 h-[38px] rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer">Batal</button>
                    <button type="button" wire:click="resetPin" class="min-h-11 h-[38px] rounded-[10px] bg-danger px-4 text-xs font-semibold text-white transition hover:opacity-90 cursor-pointer shadow-xs">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Daftar ke Paket --}}
    @if($tampilDaftarPaket)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-daftar-paket-admin-title">
            <div wire:click="tutupDaftarPaket" class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-md rounded-[16px] border border-hairline bg-surface p-6 shadow-2xl">
                <div class="flex items-start justify-between border-b border-hairline pb-3">
                    <div>
                        <h3 id="modal-daftar-paket-admin-title" class="text-base font-semibold text-ink">Daftarkan ke Paket</h3>
                        <p class="mt-0.5 text-xs text-mute">Nasabah: <strong>{{ $user->name }}</strong> (D16).</p>
                    </div>
                    <button type="button" wire:click="tutupDaftarPaket"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label for="produkDaftarId" class="block text-xs font-medium text-ink">Pilih Paket</label>
                        <select id="produkDaftarId" wire:model.live="produkDaftarId" class="mt-1 h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent">
                            <option value="0">— Pilih paket —</option>
                            @foreach($paketTerbuka as $paket)
                                <option value="{{ $paket->id }}">{{ $paket->nama }} — Rp {{ number_format((float) ($paket->harga_per_hari ?? 0), 0, ',', '.') }}/hari</option>
                            @endforeach
                        </select>
                        @error('produkDaftarId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        @if($paketTerbuka->isEmpty())
                            <p class="mt-1 text-xs text-warning-deep">Semua paket aktif sudah diikuti nasabah ini atau tidak terbuka untuk pendaftaran.</p>
                        @endif
                    </div>

                    <div>
                        <label for="catatanDaftar" class="block text-xs font-medium text-ink">Catatan Pendaftaran</label>
                        <textarea id="catatanDaftar" wire:model="catatanDaftar" rows="2" placeholder="Contoh: nasabah menyetujui di kantor, membawa KTP." class="mt-1 w-full rounded-[10px] border border-hairline bg-surface px-3 py-2 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"></textarea>
                        @error('catatanDaftar') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                        <input type="checkbox" wire:model="setujuDaftar" class="mt-0.5 h-4 w-4 rounded border-hairline bg-surface text-accent focus:ring-accent" />
                        <span>Saya menyatakan nasabah <strong>{{ $user->name }}</strong> telah membaca, memahami, dan menyetujui komitmen paket ini (D16).</span>
                    </label>
                    @error('setujuDaftar') <p class="text-xs text-danger">{{ $message }}</p> @enderror

                    @if($pesanErrorDaftar)
                        <p class="rounded-[10px] border border-danger/30 bg-danger-soft px-3 py-2 text-xs text-danger">{{ $pesanErrorDaftar }}</p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="tutupDaftarPaket" class="min-h-11 h-[38px] rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer">Batal</button>
                    <button type="button" wire:click="daftarkanKePaket" wire:loading.attr="disabled" class="min-h-11 h-[38px] rounded-[10px] bg-accent px-4 text-xs font-semibold text-accent-foreground transition hover:opacity-90 cursor-pointer shadow-xs">Daftarkan</button>
                </div>
            </div>
        </div>
    @endif
</div>
