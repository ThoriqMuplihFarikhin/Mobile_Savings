<div class="space-y-6">
    {{-- Judul halaman --}}
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Pusat bantuan dan komplain</h1>
            <p class="mt-1 text-sm text-mute">Ajukan keluhan atau pertanyaan terkait layanan tabungan Anda.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex min-h-12 shrink-0 items-center gap-2 rounded-full border border-hairline-strong bg-white px-4 text-base font-medium text-ink transition hover:bg-inset dark:bg-surface">
            <flux:icon.plus class="size-5" aria-hidden="true" />
            <span>Buat tiket</span>
        </button>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-xl bg-primary-soft p-3 text-sm text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Formulir tiket --}}
    @if($showForm)
        <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-hairline-warm pb-3">
                <h2 class="text-base font-medium text-ink">Form pengajuan tiket</h2>
                <button type="button" wire:click="toggleForm" class="min-h-11 text-sm font-medium text-primary underline underline-offset-4">Tutup</button>
            </div>

            <form wire:submit="submit" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-body" for="kategori-kendala">Kategori kendala</label>
                    <select id="kategori-kendala" wire:model="kategori"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                        <option value="saldo">Permasalahan saldo</option>
                        <option value="barang_paket">Pencairan paket / barang</option>
                        <option value="penarikan">Penarikan tabungan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-body" for="transaksi-terkait">Transaksi terkait <span class="font-normal text-mute">(opsional)</span></label>
                    <select id="transaksi-terkait" wire:model="transaksiTerkaitId"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                        <option value="">Tidak ada transaksi terkait</option>
                        @foreach($riwayatTransaksi as $trx)
                            {{-- Pengecualian §3: teks <option> tidak bisa memuat komponen, jadi format angka ditulis langsung. --}}
                            <option value="{{ $trx->id }}">
                                {{ $trx->tanggal_transaksi->translatedFormat('d M Y') }} — {{ $trx->produk->nama ?? '-' }} — Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-body" for="deskripsi-komplain">Deskripsi detail</label>
                    <textarea id="deskripsi-komplain" wire:model="deskripsi" rows="4"
                        class="w-full rounded-xl border border-hairline-strong bg-white p-4 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                        placeholder="Jelaskan kendala Anda secara rinci..."></textarea>
                    @error('deskripsi') <p class="mt-1 flex items-center gap-1 text-sm text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="toggleForm"
                        class="flex h-12 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-base font-medium text-ink transition hover:bg-inset dark:bg-surface">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex h-12 w-2/3 items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
                        Kirim tiket
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Daftar tiket --}}
    <div class="space-y-3">
        @forelse($komplains as $item)
            @php
                $kategoriLabel = match($item->kategori) {
                    'saldo'       => 'Saldo',
                    'barang_paket'=> 'Barang paket',
                    'penarikan'   => 'Penarikan',
                    default       => 'Lainnya'
                };
            @endphp
            <article class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-inset px-2 py-0.5 text-xs font-medium text-body">{{ $kategoriLabel }}</span>
                            <span class="text-sm text-mute">{{ $item->tanggal_dibuat->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <p class="pt-1 text-sm leading-relaxed text-body">{{ $item->deskripsi }}</p>
                    </div>
                    <x-ui.status-chip class="shrink-0" domain="komplain" status="{{ $item->status }}" />
                </div>

                @php
                    /**
                     * DESIGN.md §7 penanda tahap: domain komplain berurutan
                     * Dikirim → Diproses → Selesai (baru/diproses/selesai),
                     * dihitung dari status tanpa query baru.
                     */
                    $tahapSaatIni = match ($item->status) {
                        'baru' => 0,
                        'diproses' => 1,
                        'selesai' => 2,
                        default => null,
                    };
                @endphp
                @if ($tahapSaatIni !== null)
                    <x-ui.stepper class="mt-3" :tahap="['Dikirim', 'Diproses', 'Selesai']" :saat-ini="$tahapSaatIni" />
                @endif

                @if($item->catatan_penyelesaian)
                    <div class="mt-3 rounded-[14px] bg-inset p-3">
                        <p class="flex items-center gap-1.5 text-sm font-medium text-ink">
                            <flux:icon.check-circle class="size-4 shrink-0" aria-hidden="true" />
                            Tanggapan admin
                        </p>
                        <p class="mt-1 pl-6 text-sm leading-relaxed text-body">{{ $item->catatan_penyelesaian }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h2 class="mt-4 text-base font-semibold text-ink">Belum ada tiket komplain</h2>
                <p class="mt-1 text-sm text-mute">Ajukan tiket bila Anda menemukan kendala pada layanan tabungan.</p>
            </div>
        @endforelse
    </div>

    @if($komplains->hasPages())
        <div>{{ $komplains->links() }}</div>
    @endif
</div>
