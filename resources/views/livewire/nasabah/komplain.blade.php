<div class="flex flex-col gap-4 pb-6">
    {{-- Header navigasi kembali & judul --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" wire:navigate
           aria-label="Kembali ke Beranda"
           class="flex size-11 shrink-0 items-center justify-center rounded-full border border-hairline bg-surface text-ink transition hover:bg-surface-inset active:scale-95">
            <flux:icon.arrow-left class="size-5" />
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-semibold tracking-tight text-ink leading-tight">Pusat bantuan &amp; komplain</h1>
            <p class="text-xs text-mute truncate">Ajukan keluhan atau pertanyaan terkait layanan tabungan Anda.</p>
        </div>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-xl bg-primary-soft p-3 text-xs text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Pintasan Bantuan WhatsApp (Acuan nasabah_referensi.html #komplain) --}}
    @php
        $nomorWa = \App\Models\AdminSetting::get('nomor_wa_bantuan', config('services.admin_wa', '081234567890'));
        $nomorWaClean = preg_replace('/[^0-9]/', '', (string)$nomorWa);
        if (str_starts_with($nomorWaClean, '0')) {
            $nomorWaClean = '62' . substr($nomorWaClean, 1);
        }
    @endphp
    @if($nomorWaClean)
        <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-[14px] bg-primary-soft text-primary">
                    <flux:icon.chat-bubble-left-right class="size-5" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">Butuh bantuan cepat?</p>
                    <p class="text-xs text-mute truncate">Hubungi admin resmi melalui WhatsApp</p>
                </div>
            </div>
            <a href="https://wa.me/{{ $nomorWaClean }}?text={{ urlencode('Halo Admin, saya nasabah KawalDuit ingin meminta bantuan.') }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-full bg-primary-soft px-3.5 text-xs font-semibold text-primary transition hover:bg-primary hover:text-white active:scale-95">
                <span>Chat WA</span>
                <flux:icon.arrow-up-right class="size-3.5" />
            </a>
        </div>
    @endif

    {{-- Tombol Buat Tiket CTA --}}
    @if(!$showForm)
        <button type="button" wire:click="toggleForm"
            class="flex h-11 w-full items-center justify-center gap-2 rounded-full bg-primary text-xs font-semibold text-on-primary transition hover:bg-primary-hover active:scale-[0.98] shadow-xs">
            <flux:icon.plus class="size-4" aria-hidden="true" />
            <span>Buat tiket komplain</span>
        </button>
    @endif

    {{-- Formulir pengajuan tiket (Acuan nasabah_referensi.html #k-f) --}}
    @if($showForm)
        <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-hairline pb-3">
                <h2 class="text-sm font-semibold text-ink">Form pengajuan tiket</h2>
                <button type="button" wire:click="toggleForm" class="text-xs font-medium text-mute hover:text-ink transition">
                    Tutup
                </button>
            </div>

            <form wire:submit="submit" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-body">Kategori kendala</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            'saldo' => 'Permasalahan saldo',
                            'barang_paket' => 'Pencairan paket / barang',
                            'penarikan' => 'Penarikan tabungan',
                            'lainnya' => 'Lainnya',
                        ] as $val => $label)
                            <button type="button" wire:click="$set('kategori', '{{ $val }}')"
                                class="h-9 rounded-full border px-3.5 text-xs font-medium transition {{ $kategori === $val ? 'border-primary bg-primary text-on-primary font-semibold shadow-xs' : 'border-hairline bg-surface text-body hover:bg-surface-inset' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    <select id="kategori-kendala" wire:model="kategori" class="sr-only">
                        <option value="saldo">Permasalahan saldo</option>
                        <option value="barang_paket">Pencairan paket / barang</option>
                        <option value="penarikan">Penarikan tabungan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-body" for="transaksi-terkait">
                        Transaksi terkait <span class="text-mute">(opsional)</span>
                    </label>
                    <select id="transaksi-terkait" wire:model="transaksiTerkaitId"
                        class="w-full rounded-xl border border-hairline bg-surface px-3 py-2.5 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Tidak ada transaksi terkait</option>
                        @foreach($riwayatTransaksi as $trx)
                            <option value="{{ $trx->id }}">
                                {{ $trx->tanggal_transaksi->translatedFormat('d M Y') }} — {{ $trx->produk->nama ?? '-' }} — Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-body" for="deskripsi-komplain">
                        Deskripsi detail
                    </label>
                    <textarea id="deskripsi-komplain" wire:model="deskripsi" rows="4"
                        class="w-full rounded-xl border border-hairline bg-surface p-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        placeholder="Jelaskan kendala Anda secara rinci..."></textarea>
                    @error('deskripsi')
                        <p class="mt-1 flex items-center gap-1 text-xs text-danger dark:text-dark-danger-text">
                            <flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="toggleForm"
                        class="flex h-11 w-1/3 items-center justify-center rounded-full border border-hairline bg-surface text-xs font-medium text-ink transition hover:bg-surface-inset">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex h-11 w-2/3 items-center justify-center rounded-full bg-primary text-xs font-semibold text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
                        Kirim tiket
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Daftar Riwayat Komplain (Acuan nasabah_referensi.html #k-l) --}}
    <div class="flex flex-col gap-3">
        <div class="px-1">
            <h2 class="text-sm font-semibold text-ink">Riwayat komplain</h2>
        </div>

        @if($komplains->isNotEmpty())
            <div class="rounded-[24px] border border-hairline bg-surface px-4 divide-y divide-hairline shadow-xs">
                @foreach($komplains as $item)
                    @php
                        $kategoriLabel = match($item->kategori) {
                            'saldo'        => 'Permasalahan saldo',
                            'barang_paket' => 'Pencairan paket / barang',
                            'penarikan'    => 'Penarikan tabungan',
                            default        => 'Lainnya'
                        };
                        $tahapSaatIni = match ($item->status) {
                            'baru' => 0,
                            'diproses' => 1,
                            'selesai' => 2,
                            default => null,
                        };
                    @endphp
                    <article class="py-4" data-test="kartu-komplain">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-surface-inset px-2.5 py-0.5 text-xs font-medium text-body border border-hairline">
                                        {{ $kategoriLabel }}
                                    </span>
                                    <span class="text-xs text-mute">
                                        {{ $item->tanggal_dibuat->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </div>
                                <p class="mt-2 text-xs leading-relaxed text-ink">{{ $item->deskripsi }}</p>
                            </div>
                            <x-ui.status-chip class="shrink-0" domain="komplain" status="{{ $item->status }}" />
                        </div>

                        @if ($tahapSaatIni !== null)
                            <x-ui.stepper class="mt-3.5" :tahap="['Dikirim', 'Diproses', 'Selesai']" :saat-ini="$tahapSaatIni" />
                        @endif

                        @if($item->catatan_penyelesaian)
                            <div class="mt-3.5 rounded-xl bg-surface-inset p-3 border border-hairline">
                                <p class="flex items-center gap-1.5 text-xs font-semibold text-ink">
                                    <flux:icon.check-circle class="size-3.5 shrink-0 text-primary" aria-hidden="true" />
                                    <span>Tanggapan admin</span>
                                </p>
                                <p class="mt-1 pl-5 text-xs leading-relaxed text-body">{{ $item->catatan_penyelesaian }}</p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-[24px] border border-hairline bg-surface p-8 text-center shadow-xs">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h3 class="mt-4 text-base font-semibold text-ink">Belum ada tiket komplain</h3>
                <p class="mt-1 text-xs text-mute">Ajukan tiket bila Anda menemukan kendala pada layanan tabungan.</p>
            </div>
        @endif
    </div>

    @if($komplains->hasPages())
        <div class="pt-1">{{ $komplains->links() }}</div>
    @endif
</div>
