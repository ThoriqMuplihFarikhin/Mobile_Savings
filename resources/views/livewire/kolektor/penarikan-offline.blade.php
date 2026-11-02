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
}" x-init="$nextTick(() => initBukti())" class="mx-auto max-w-2xl pb-12">
    {{-- Header --}}
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Penarikan offline</h1>
            <p class="mt-0.5 text-xs text-mute">Proses penarikan saldo nasabah binaan di lapangan.</p>
        </div>
        <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
           class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-semibold text-ink transition hover:bg-inset active:scale-95 dark:bg-surface">
            <flux:icon.check-badge class="size-3.5" aria-hidden="true" />
            <span>Verifikasi</span>
            @if ($jumlahMenungguVerifikasi > 0)
                <span class="flex size-4.5 items-center justify-center rounded-full bg-primary text-xs font-semibold text-on-primary">
                    {{ $jumlahMenungguVerifikasi }}
                </span>
            @endif
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-hairline bg-primary-soft px-4 py-3 text-xs font-semibold text-on-primary-soft shadow-xs">
            <flux:icon.check-circle class="size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-danger-soft bg-danger-soft px-4 py-3 text-xs font-semibold text-danger-deep shadow-xs dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Card Akses Cepat Verifikasi Penarikan --}}
    <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
        class="mb-4 flex items-center justify-between rounded-[20px] border border-hairline bg-white p-3.5 shadow-xs transition hover:bg-inset active:scale-[0.99] dark:bg-surface">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                <flux:icon.check-badge class="size-5" />
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <b class="truncate text-sm font-semibold text-ink">Verifikasi penarikan</b>
                    @if ($jumlahMenungguVerifikasi > 0)
                        <span class="inline-flex items-center rounded-full bg-warning-soft px-2 py-0.5 text-xs font-semibold text-warning-deep">
                            {{ $jumlahMenungguVerifikasi }} menunggu
                        </span>
                    @endif
                </div>
                <p class="mt-0.5 truncate text-xs text-mute">Serahkan uang dan verifikasi PIN nasabah</p>
            </div>
        </div>
        <flux:icon.chevron-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
    </a>

    {{-- Form Ajukan Penarikan Baru --}}
    <div class="rounded-[20px] border border-hairline bg-white p-4 shadow-xs space-y-4 dark:bg-surface">
        <form wire:submit="submit" class="space-y-4">
            {{-- 1. Pilih Nasabah --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-mute">1. NASABAH BINAAN</label>
                <select wire:model.live="nasabahId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-3 text-sm text-ink focus:border-primary focus:outline-none dark:bg-surface">
                    <option value="">-- Pilih nasabah --</option>
                    @foreach($nasabahList as $nasabah)
                        <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp ?? 'Offline' }})</option>
                    @endforeach
                </select>
                @error('nasabahId') <p class="mt-1.5 flex items-center gap-1 text-xs text-danger-deep"><flux:icon.exclamation-circle class="size-3.5 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Saldo Saat Ini --}}
            @if($selectedNasabah)
                <div class="rounded-xl border border-hairline bg-inset p-3.5">
                    <span class="text-xs font-medium text-mute block mb-1.5">Saldo rekening nasabah</span>
                    @if($selectedNasabah->user->saldoProduks->count() > 0)
                        <div class="space-y-1.5">
                            @foreach($selectedNasabah->user->saldoProduks as $s)
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <span class="text-body">{{ $s->produk->nama }}</span>
                                    <span class="font-semibold text-ink"><x-ui.rupiah :nilai="$s->saldo" /></span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-mute">Nasabah belum memiliki saldo tersimpan.</p>
                    @endif
                </div>
            @endif

            {{-- 2. Pilih Produk Tabungan --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-mute">2. PRODUK TABUNGAN</label>
                <select wire:model.live="produkId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-3 text-sm text-ink focus:border-primary focus:outline-none dark:bg-surface">
                    <option value="">-- Pilih produk tabungan --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->tipe === 'paket' ? 'Paket' : 'Bebas' }}){{ $produk->status !== 'aktif' ? ' (nonaktif)' : '' }}</option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1.5 flex items-center gap-1 text-xs text-danger-deep"><flux:icon.exclamation-circle class="size-3.5 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- 3. Nominal Penarikan --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="text-xs font-semibold text-mute">3. NOMINAL PENARIKAN</label>
                    @if($nominal)
                        <button type="button" wire:click="$set('nominal', '')" class="text-xs font-medium text-mute hover:text-ink hover:underline">
                            Reset nominal
                        </button>
                    @endif
                </div>

                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-semibold text-mute">Rp</span>
                    <input type="number" wire:model.live="nominal" min="0" step="0.01"
                        class="h-14 w-full rounded-xl border border-hairline-strong bg-white pr-4 pl-16 text-2xl font-semibold tabular-nums text-ink focus:border-primary focus:outline-none dark:bg-surface"
                        placeholder="0" />
                </div>

                {{-- Quick Presets Chips --}}
                <div class="mt-2.5 flex flex-wrap gap-2">
                    <button type="button" wire:click="setNominal(100000)"
                        class="rounded-full border border-hairline bg-inset px-3.5 py-1.5 text-xs font-semibold text-body transition hover:bg-hairline hover:text-ink active:scale-95">
                        +100 rb
                    </button>
                    <button type="button" wire:click="setNominal(200000)"
                        class="rounded-full border border-hairline bg-inset px-3.5 py-1.5 text-xs font-semibold text-body transition hover:bg-hairline hover:text-ink active:scale-95">
                        +200 rb
                    </button>
                    <button type="button" wire:click="setNominal(500000)"
                        class="rounded-full border border-hairline bg-inset px-3.5 py-1.5 text-xs font-semibold text-body transition hover:bg-hairline hover:text-ink active:scale-95">
                        +500 rb
                    </button>
                    @if ($selectedNasabah && $produkId)
                        <button type="button" wire:click="tarikHabis"
                            class="rounded-full border border-primary/30 bg-primary-soft px-3.5 py-1.5 text-xs font-semibold text-on-primary-soft transition hover:bg-primary/20 active:scale-95">
                            Tarik habis
                        </button>
                    @endif
                </div>
                @error('nominal') <p class="mt-1.5 flex items-center gap-1 text-xs text-danger-deep"><flux:icon.exclamation-circle class="size-3.5 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Rincian Perhitungan Komisi & Penerimaan --}}
            @if($selectedNasabah && $nominal > 0)
                <div class="rounded-xl border border-hairline bg-inset p-3.5 text-xs">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-mute">Nominal ditarik</span>
                        <span class="font-medium text-ink"><x-ui.rupiah :nilai="$nominal" /></span>
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <span class="text-mute">Komisi ({{ $persenKomisi }}%)</span>
                        <span class="font-medium text-ink"><x-ui.rupiah :nilai="$nominalKomisi" tanda="-" /></span>
                    </div>
                    <div class="mt-2.5 flex items-center justify-between gap-3 border-t border-hairline pt-2.5 text-sm">
                        <span class="font-semibold text-ink">Nasabah terima</span>
                        <span class="font-semibold text-ink"><x-ui.rupiah :nilai="$nominalDiterima" /></span>
                    </div>
                </div>
            @endif

            {{-- 4. Lokasi Pengambilan --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-mute">4. LOKASI PENGAMBILAN</label>
                <div class="grid grid-cols-2 gap-2.5">
                    <label class="flex h-11 cursor-pointer items-center justify-center rounded-xl border text-xs font-semibold transition
                        {{ ($lokasi ?? 'kantor') === 'kantor' ? 'border-primary bg-primary text-on-primary shadow-xs' : 'border-hairline bg-inset text-mute hover:bg-hairline' }}">
                        <input type="radio" wire:model.live="lokasi" value="kantor" class="sr-only">
                        <span>Kantor utama</span>
                    </label>
                    <label class="flex h-11 cursor-pointer items-center justify-center rounded-xl border text-xs font-semibold transition
                        {{ ($lokasi ?? '') === 'rumah_kolektor' ? 'border-primary bg-primary text-on-primary shadow-xs' : 'border-hairline bg-inset text-mute hover:bg-hairline' }}">
                        <input type="radio" wire:model.live="lokasi" value="rumah_kolektor" class="sr-only">
                        <span>Rumah kolektor</span>
                    </label>
                </div>
            </div>

            {{-- 5. Catatan --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-mute">5. CATATAN</label>
                <textarea wire:model="catatan" rows="2" maxlength="500"
                    placeholder="mis. buku tabungan dicoret, tanda tangan di buku"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-2.5 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none dark:bg-surface"></textarea>
                <p class="mt-1 text-xs text-subtle">Wajib diisi untuk nasabah mode offline.</p>
                @error('catatan') <p class="mt-1 flex items-center gap-1 text-xs text-danger-deep"><flux:icon.exclamation-circle class="size-3.5 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- 6. Bukti Penarikan (D24) --}}
            @if($selectedNasabah && $selectedNasabah->user->isOffline())
                <div class="rounded-xl border border-hairline bg-inset p-3.5">
                    <div class="flex items-center justify-between gap-3 mb-2.5">
                        <span class="text-xs font-semibold text-ink">Bukti penarikan offline</span>
                        <div class="flex gap-1">
                            <button type="button" x-on:click="pilihJenis('tanda_tangan')"
                                class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $buktiJenis !== 'foto' ? 'bg-primary text-on-primary' : 'border border-hairline bg-white text-body hover:bg-hairline dark:bg-surface' }}">
                                Tanda tangan
                            </button>
                            <button type="button" x-on:click="pilihJenis('foto')"
                                class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $buktiJenis === 'foto' ? 'bg-primary text-on-primary' : 'border border-hairline bg-white text-body hover:bg-hairline dark:bg-surface' }}">
                                Foto
                            </button>
                        </div>
                    </div>

                    <div x-show="tab === 'tanda_tangan'" x-cloak class="space-y-2">
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-mute">Goreskan tanda tangan nasabah</span>
                            <button type="button" x-on:click="hapusTandaTangan()"
                                class="font-semibold text-primary hover:underline">Hapus</button>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-hairline bg-white dark:bg-surface">
                            <canvas id="canvas-bukti-penarikan"
                                style="width:100%;height:130px;display:block;"
                                class="touch-none"></canvas>
                        </div>
                    </div>

                    <div x-show="tab === 'foto'" x-cloak class="space-y-2">
                        <label class="flex w-full cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-hairline-strong bg-white p-5 text-center transition hover:border-primary dark:bg-surface">
                            <input type="file" accept="image/png,image/jpeg" capture="environment" x-on:change="pilihFoto($event)" class="sr-only">
                            <flux:icon.camera class="size-6 text-mute mb-1" />
                            <span class="text-xs font-semibold text-ink">Ambil / pilih foto bukti</span>
                            <span class="mt-0.5 text-xs text-subtle">Buku tabungan dicoret atau penyerahan uang (maks. 1,5 MB)</span>
                        </label>
                        <img x-show="$wire.buktiBase64" :src="$wire.buktiBase64" alt="Preview bukti"
                            class="h-32 w-full rounded-xl border border-hairline bg-white object-contain">
                    </div>

                    @error('buktiBase64') <p class="mt-1.5 text-xs text-danger-deep">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Pratinjau Keputusan (D23) --}}
            @if($teksKeputusan = $this->keputusanTeks())
                <div class="flex items-start gap-2.5 rounded-xl border border-info-soft bg-info-soft px-3 py-2.5 text-xs font-medium text-info">
                    <flux:icon.information-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                    <span>{{ $teksKeputusan }}</span>
                </div>
            @endif

            {{-- Tombol Aksi Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex h-12 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">
                    Batal
                </a>
                <button type="button" wire:loading.attr="disabled" x-on:click="prosesPenarikan()"
                    class="flex h-12 w-2/3 items-center justify-center gap-2 rounded-full bg-primary text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95 disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Proses penarikan</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>
