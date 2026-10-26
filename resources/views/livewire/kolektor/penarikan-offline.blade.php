<div x-data="{
    signaturePad: null,
    ttdTimer: null,
    tab: @js(in_array($buktiJenis, ['tanda_tangan', 'foto'], true) ? $buktiJenis : 'tanda_tangan'),
    initBukti() {
        const canvas = document.getElementById('canvas-bukti-penarikan');
        if (!canvas) return;
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        this.signaturePad = new SignaturePad(canvas);
        const kirim = () => this.kirimTandaTangan();
        canvas.addEventListener('mouseup', kirim);
        canvas.addEventListener('touchend', kirim);
    },
    kirimTandaTangan() {
        if (!this.signaturePad) return;
        clearTimeout(this.ttdTimer);
        this.ttdTimer = setTimeout(() => {
            const kosong = this.signaturePad.isEmpty();
            $wire.setBuktiBase64(kosong ? '' : this.signaturePad.toDataURL());
            if (!kosong) $wire.setBuktiJenis('tanda_tangan');
        }, 300);
    },
    pilihJenis(jenis) {
        this.tab = jenis;
        if (this.signaturePad) this.signaturePad.clear();
        $wire.setBuktiBase64('');
        $wire.setBuktiJenis(jenis);
    },
    hapusTandaTangan() {
        if (this.signaturePad) this.signaturePad.clear();
        $wire.setBuktiBase64('');
    },
    pilihFoto(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        if (file.size > 1572864) {
            $wire.setBuktiBase64('');
            alert('Ukuran foto maksimal 1,5 MB.');
            event.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            $wire.setBuktiBase64(reader.result);
            $wire.setBuktiJenis('foto');
        };
        reader.readAsDataURL(file);
    },
    async prosesPenarikan() {
        if (this.tab === 'tanda_tangan' && this.signaturePad && !this.signaturePad.isEmpty()) {
            await $wire.setBuktiBase64(this.signaturePad.toDataURL());
            await $wire.setBuktiJenis('tanda_tangan');
        }
        await $wire.call('submit');
    }
}" x-init="$nextTick(() => initBukti())" class="mx-auto max-w-2xl space-y-5 pb-8">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Penarikan offline</h1>
            <p class="mt-1 text-sm text-mute">Proses permintaan penarikan tabungan nasabah secara langsung.</p>
        </div>
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
            <flux:icon.arrow-down class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Card Verifikasi Penarikan --}}
    <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
        class="group flex items-center justify-between rounded-2xl border border-hairline bg-white p-4 transition hover:border-hairline-strong dark:bg-surface">
        <div class="flex min-w-0 items-center gap-3.5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft transition-transform group-hover:scale-105" aria-hidden="true">
                <flux:icon.check-badge class="size-5" />
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <p class="truncate text-sm font-semibold text-ink transition group-hover:text-primary">Verifikasi penarikan</p>
                    @if ($jumlahMenungguVerifikasi > 0)
                        <span class="flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-primary px-1.5 text-xs font-semibold text-on-primary">
                            {{ $jumlahMenungguVerifikasi }}
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 truncate text-xs text-mute">Serahkan uang dan verifikasi PIN nasabah</p>
            </div>
        </div>
        <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
    </a>

    {{-- Form Ajukan Penarikan Baru --}}
    <div class="rounded-2xl border border-hairline bg-white p-5 space-y-5 dark:bg-surface">
        <div>
            <h2 class="text-base font-semibold text-ink">Ajukan penarikan baru</h2>
            <p class="mt-0.5 text-xs text-mute">Isi formulir di bawah ini untuk memproses penarikan tabungan.</p>
        </div>

        <form wire:submit="submit" class="space-y-5">
            {{-- Pilih Nasabah --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-body">Nasabah</label>
                <select wire:model.live="nasabahId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">-- Pilih nasabah --</option>
                    @foreach($nasabahList as $nasabah)
                        <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp ?? '— (offline)' }})</option>
                    @endforeach
                </select>
                @error('nasabahId') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Saldo Saat Ini --}}
            @if($selectedNasabah)
                <div class="rounded-xl border border-hairline bg-inset p-4">
                    <p class="mb-2 text-sm font-medium text-mute">Saldo nasabah</p>
                    @if($selectedNasabah->user->saldoProduks->count() > 0)
                        <div class="space-y-2">
                            @foreach($selectedNasabah->user->saldoProduks as $s)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-body">{{ $s->produk->nama }}</span>
                                    <span class="text-sm font-semibold"><x-ui.rupiah :nilai="$s->saldo" /></span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-mute">Nasabah belum memiliki saldo.</p>
                    @endif
                </div>
            @endif

            {{-- Produk --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-body">Produk tabungan</label>
                <select wire:model.live="produkId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">-- Pilih produk --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->tipe === 'paket' ? 'Paket' : 'Bebas' }}){{ $produk->status !== 'aktif' ? ' (nonaktif)' : '' }}</option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Nominal --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-body">Nominal penarikan</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-semibold text-mute">Rp</span>
                    <input type="number" wire:model.live="nominal" min="0" step="0.01"
                        class="h-16 w-full rounded-xl border border-hairline-strong bg-white pr-4 pl-16 text-2xl font-semibold tabular-nums text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                        placeholder="0" />
                </div>
                @error('nominal') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Rincian Penarikan --}}
            @if($selectedNasabah && $nominal > 0)
                <div class="rounded-xl border border-hairline bg-inset p-4">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-mute">Nominal ditarik</span>
                        <span class="font-medium text-ink"><x-ui.rupiah :nilai="$nominal" /></span>
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                        <span class="text-mute">Komisi ({{ $persenKomisi }}%)</span>
                        <span class="font-medium"><x-ui.rupiah :nilai="$nominalKomisi" tanda="-" /></span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-hairline pt-3">
                        <span class="text-sm font-medium text-ink">Nasabah terima</span>
                        <span class="text-base font-semibold"><x-ui.rupiah :nilai="$nominalDiterima" /></span>
                    </div>
                </div>
            @endif

            {{-- Lokasi --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-body">Lokasi pengambilan</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border p-3.5 text-center transition
                        {{ ($lokasi ?? 'kantor') === 'kantor' ? 'border-primary bg-primary-soft font-semibold text-on-primary-soft' : 'border-hairline bg-inset text-body' }}">
                        <input type="radio" wire:model.live="lokasi" value="kantor" class="sr-only">
                        <span class="text-sm">Kantor utama</span>
                    </label>
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border p-3.5 text-center transition
                        {{ ($lokasi ?? '') === 'rumah_kolektor' ? 'border-primary bg-primary-soft font-semibold text-on-primary-soft' : 'border-hairline bg-inset text-body' }}">
                        <input type="radio" wire:model.live="lokasi" value="rumah_kolektor" class="sr-only">
                        <span class="text-sm">Rumah kolektor</span>
                    </label>
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-body">Catatan</label>
                <textarea wire:model="catatan" rows="2" maxlength="500"
                    placeholder="mis. buku tabungan dicoret, tanda tangan di buku"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"></textarea>
                <p class="mt-1 text-xs text-subtle">Wajib diisi untuk nasabah offline.</p>
                @error('catatan') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Bukti Penarikan (D24) --}}
            @if($selectedNasabah && $selectedNasabah->user->isOffline())
                <div class="rounded-xl border border-hairline bg-inset p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-body">Bukti penarikan</p>
                        <div class="flex gap-1">
                            <button type="button" x-on:click="pilihJenis('tanda_tangan')"
                                class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $buktiJenis !== 'foto' ? 'bg-primary text-on-primary' : 'border border-hairline bg-white text-body hover:bg-hairline dark:bg-surface' }}">
                                Tanda tangan
                            </button>
                            <button type="button" x-on:click="pilihJenis('foto')"
                                class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $buktiJenis === 'foto' ? 'bg-primary text-on-primary' : 'border border-hairline bg-white text-body hover:bg-hairline dark:bg-surface' }}">
                                Foto
                            </button>
                        </div>
                    </div>

                    <div x-show="tab === 'tanda_tangan'" x-cloak class="mt-3 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs text-mute">Minta nasabah menggambar tanda tangan di layar.</p>
                            <button type="button" x-on:click="hapusTandaTangan()"
                                class="text-xs font-medium text-primary underline underline-offset-4 hover:opacity-80">Hapus</button>
                        </div>
                        <canvas id="canvas-bukti-penarikan"
                            style="width:100%;height:176px;background:white;"
                            class="rounded-xl border-2 border-dashed border-hairline-strong"></canvas>
                    </div>

                    <div x-show="tab === 'foto'" x-cloak class="mt-3 space-y-2">
                        <label class="flex w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-hairline-strong bg-white p-6 text-center transition hover:border-primary">
                            <input type="file" accept="image/png,image/jpeg" capture="environment" x-on:change="pilihFoto($event)" class="sr-only">
                            <span class="text-sm font-medium text-ink">Ambil / pilih foto bukti</span>
                            <span class="mt-1 text-xs text-subtle">Buku tabungan dicoret atau nasabah menerima uang (maks. 1,5 MB).</span>
                        </label>
                        <img x-show="$wire.buktiBase64" :src="$wire.buktiBase64" alt="Preview bukti"
                            class="h-32 w-full rounded-xl border border-hairline bg-white object-contain">
                    </div>

                    @error('buktiBase64') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Pratinjau keputusan (D23) --}}
            @if($teksKeputusan = $this->keputusanTeks())
                <div class="flex items-start gap-2.5 rounded-xl border border-info-soft bg-info-soft px-3.5 py-3 text-sm font-medium text-info">
                    <flux:icon.information-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <span>{{ $teksKeputusan }}</span>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex h-12 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-base font-medium text-ink transition hover:bg-inset dark:bg-surface">
                    Batal
                </a>
                <button type="button" wire:loading.attr="disabled" x-on:click="prosesPenarikan()"
                    class="flex h-12 w-2/3 items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Proses penarikan</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>
