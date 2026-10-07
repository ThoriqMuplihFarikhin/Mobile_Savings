<div class="mx-auto max-w-7xl space-y-6">
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.nasabah.index') }}" class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 transition hover:bg-slate-200 dark:hover:bg-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div>
                <h1 class="font-serif text-xl font-semibold text-text dark:text-white">Detail Nasabah</h1>
                <p class="text-xs text-text-muted dark:text-slate-400">Informasi saldo dan riwayat transaksi</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="bukaDaftarPaket" class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Daftarkan ke Paket
            </button>
            <button wire:click="confirmResetPin" class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                Reset PIN
            </button>
        </div>
    </div>

    {{-- Profile Card --}}
    <div class="card">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-navy-950 dark:bg-gold/20 text-lg font-bold text-white dark:text-gold">
                {{ $user->initials() }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="font-serif text-lg font-semibold text-text dark:text-white">{{ $user->name }}</h2>
                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-text-muted dark:text-slate-400">
                    <span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                        {{ $user->no_hp ?? '— (offline)' }}
                    </span>
                    @if($user->nasabahProfil?->alamat)
                        <span class="inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            {{ $user->nasabahProfil->alamat }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="shrink-0">
                @if($user->status_akun === 'aktif')
                    <span class="inline-flex items-center rounded-full bg-success-soft px-2.5 py-0.5 text-xs font-medium text-success">Aktif</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs font-medium text-danger">Terkunci</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Saldo Per Produk --}}
    <div>
        <h3 class="mb-3 font-serif text-sm font-semibold text-text dark:text-white">Saldo per Produk</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($saldoPerProduk as $sp)
                <div class="card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-text-muted dark:text-slate-400">{{ $sp->produk->nama ?? '-' }}</div>
                            <div class="mt-1 text-2xl font-serif font-semibold text-text dark:text-white">Rp {{ number_format($sp->saldo, 0, ',', '.') }}</div>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ ucfirst($sp->produk->tipe ?? '-') }}</span>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 card">
                    <x-admin.empty-state
                        icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>'
                        title="Belum ada saldo"
                        description="Saldo produk akan muncul setelah nasabah melakukan setoran." />
                </div>
            @endforelse
        </div>
    </div>

    {{-- Kepesertaan & Status Komitmen --}}
    <div>
        <h3 class="mb-3 font-serif text-sm font-semibold text-text dark:text-white">Kepesertaan Paket</h3>
        <div class="card p-0">
            @forelse($kepesertaan as $item)
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-border px-5 py-4 last:border-b-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-text dark:text-white">{{ $item->produk?->nama ?? '-' }}</p>
                        <p class="mt-0.5 text-xs text-text-muted dark:text-slate-400">
                            Gabung {{ $item->tanggal_mulai_ikut?->translatedFormat('d M Y') }}
                            @if($item->keputusan_akhir)
                                · {{ ucfirst(str_replace('_', ' ', $item->keputusan_akhir)) }}
                            @else
                                · Aktif
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Komitmen</p>
                        <p class="mt-0.5 text-xs font-semibold text-text dark:text-white">
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
                        </p>
                    </div>
                    @if($item->bukti_foto_url)
                        <div class="w-full">
                            <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank" rel="noopener noreferrer"
                                class="text-xs font-semibold text-navy-950 underline-offset-2 hover:underline dark:text-white">
                                Lihat Foto Bukti Serah Terima
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-5 py-4 text-xs text-text-muted dark:text-slate-400">Belum ada kepesertaan paket.</div>
            @endforelse
        </div>
    </div>

    {{-- Perkembangan Saldo --}}
    <div class="card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="font-serif text-base font-semibold text-text dark:text-white">Perkembangan Saldo</h3>
                <p class="text-xs text-text-muted dark:text-slate-400">
                    @if($periode === '90hari')
                        90 hari terakhir
                    @elseif($periode === 'tahun_ini')
                        Tahun {{ now()->year }}
                    @else
                        30 hari terakhir
                    @endif
                </p>
            </div>
            <div class="flex gap-1">
                <button wire:click="$set('periode', '30hari')" class="rounded-full px-3 py-1 text-xs font-medium transition {{ $periode === '30hari' ? 'bg-navy-950 dark:bg-gold/20 text-white dark:text-gold' : 'text-text-muted dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">30 Hari</button>
                <button wire:click="$set('periode', '90hari')" class="rounded-full px-3 py-1 text-xs font-medium transition {{ $periode === '90hari' ? 'bg-navy-950 dark:bg-gold/20 text-white dark:text-gold' : 'text-text-muted dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">90 Hari</button>
                <button wire:click="$set('periode', 'tahun_ini')" class="rounded-full px-3 py-1 text-xs font-medium transition {{ $periode === 'tahun_ini' ? 'bg-navy-950 dark:bg-gold/20 text-white dark:text-gold' : 'text-text-muted dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Tahun Ini</button>
            </div>
        </div>

        @if($chartData['hasData'])
            <div class="overflow-hidden">
                <svg viewBox="0 0 {{ $chartData['chartWidth'] }} {{ $chartData['chartHeight'] }}" class="h-40 w-full">
                    <defs>
                        <linearGradient id="gradientDetailArea" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#0A1626;stop-opacity:0.15" />
                            <stop offset="100%" style="stop-color:#0A1626;stop-opacity:0.01" />
                        </linearGradient>
                    </defs>
                    @foreach([0.25, 0.5, 0.75, 1] as $ratio)
                        <line x1="{{ $chartData['padding'] }}" y1="{{ $chartData['padding'] + $chartData['graphHeight'] * (1 - $ratio) }}" x2="{{ $chartData['chartWidth'] - $chartData['padding'] }}" y2="{{ $chartData['padding'] + $chartData['graphHeight'] * (1 - $ratio) }}" stroke="#e5e5e5" stroke-width="0.5" />
                    @endforeach
                    <path d="{{ $chartData['areaD'] }}" fill="url(#gradientDetailArea)" />
                    <path d="{{ $chartData['pathD'] }}" fill="none" stroke="#0A1626" stroke-width="2" stroke-linejoin="round" />
                    @if($chartData['lastPoint'])
                        <circle cx="{{ $chartData['lastPoint']['x'] }}" cy="{{ $chartData['lastPoint']['y'] }}" r="3" fill="#0A1626" />
                    @endif
                </svg>
            </div>
        @else
            <x-admin.empty-state
                icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>'
                title="Belum ada data"
                description="Grafik perkembangan saldo akan muncul setelah ada transaksi." />
        @endif
    </div>

    {{-- Riwayat Transaksi --}}
    <div class="card p-0">
        <div class="border-b border-border px-5 py-4">
            <h3 class="font-serif text-base font-semibold text-text dark:text-white">Riwayat Transaksi</h3>
        </div>
        @if(count($riwayat) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border text-xs text-text-muted dark:text-slate-400">
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium">Tipe</th>
                            <th class="px-5 py-3 font-medium">Nominal</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($riwayat as $item)
                            <tr class="text-text dark:text-white">
                                <td class="whitespace-nowrap px-5 py-3 text-text-muted dark:text-slate-400">{{ $item['tanggal'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    @if($item['tipe'] === 'Setoran')
                                        <span class="inline-flex items-center rounded-full bg-success-soft px-2 py-0.5 text-xs font-medium text-success">Setoran</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-danger-soft px-2 py-0.5 text-xs font-medium text-danger">Penarikan</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-sm">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    @if($item['status'] === 'tercatat' || $item['status'] === 'selesai')
                                        <span class="inline-flex items-center rounded-full bg-success-soft px-2 py-0.5 text-xs font-medium text-success">{{ ucfirst($item['status']) }}</span>
                                    @elseif($item['status'] === 'pending')
                                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">Pending</span>
                                    @else
                                        <span class="text-text-muted dark:text-slate-400">{{ ucfirst($item['status']) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty-state
                icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>'
                title="Belum ada riwayat"
                description="Riwayat setoran dan penarikan akan muncul di sini." />
        @endif
    </div>

    @if($tampilKonfirmasiResetPin)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Reset PIN?</h3>
                <p class="mt-2 text-sm text-gray-500">PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya.</p>
                @error('reset_pin') <p class="mt-2 text-sm text-[#ee0000]">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiResetPin', false)" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="resetPin" class="rounded-full bg-[#ee0000] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif

    @if($tampilDaftarPaket)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Daftarkan ke Paket</h3>
                <p class="mt-2 text-sm text-gray-500">Nasabah: <strong>{{ $user->name }}</strong>. Pendaftaran dicatat sebagai dibantu (D16) tanpa PIN, dengan persetujuan dan catatan wajib.</p>

                <div class="mt-4 space-y-3">
                    <div>
                        <label for="produkDaftarId" class="block text-xs font-medium text-gray-700">Pilih Paket</label>
                        <select id="produkDaftarId" wire:model.live="produkDaftarId" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="0">— Pilih paket —</option>
                            @foreach($paketTerbuka as $paket)
                                <option value="{{ $paket->id }}">{{ $paket->nama }} — Rp {{ number_format((float) ($paket->harga_per_hari ?? 0), 0, ',', '.') }}/hari</option>
                            @endforeach
                        </select>
                        @error('produkDaftarId') <p class="mt-1 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        @if($paketTerbuka->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">Semua paket aktif sudah diikuti nasabah ini atau tidak terbuka untuk pendaftaran.</p>
                        @endif
                    </div>

                    <div>
                        <label for="catatanDaftar" class="block text-xs font-medium text-gray-700">Catatan Pendaftaran</label>
                        <textarea id="catatanDaftar" wire:model="catatanDaftar" rows="2" placeholder="Contoh: nasabah menyetujui di kantor, membawa KTP." class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
                        @error('catatanDaftar') <p class="mt-1 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-start gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="setujuDaftar" class="mt-0.5 h-4 w-4 rounded border-gray-300" />
                        <span>Saya menyatakan nasabah <strong>{{ $user->name }}</strong> telah membaca, memahami, dan menyetujui komitmen paket ini (D16).</span>
                    </label>
                    @error('setujuDaftar') <p class="text-xs text-[#ee0000]">{{ $message }}</p> @enderror

                    @if($pesanErrorDaftar)
                        <p class="rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700">{{ $pesanErrorDaftar }}</p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="tutupDaftarPaket" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="daftarkanKePaket" wire:loading.attr="disabled" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Daftarkan</button>
                </div>
            </div>
        </div>
    @endif
</div>
