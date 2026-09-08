<div class="mx-auto max-w-2xl">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Penarikan Offline</h1>
        <p class="mt-1 text-sm text-[#888888]">Proses penarikan tabungan nasabah secara offline.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#d3e5ff] px-4 py-3 text-sm text-[#0761d1]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-[#171717]">Form Penarikan Offline</h3>
        </div>
        <form wire:submit="submit" class="space-y-5 p-6">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Nasabah</label>
                <select wire:model="nasabahId"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                    <option value="">Pilih Nasabah</option>
                    @foreach($nasabahList as $nasabah)
                        <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp }})</option>
                    @endforeach
                </select>
                @error('nasabahId') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
            </div>

            @if($selectedNasabah)
                <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Saldo Saat Ini</p>
                    @if($selectedNasabah->user->saldoProduks->count() > 0)
                        <div class="mt-2 space-y-2">
                            @foreach($selectedNasabah->user->saldoProduks as $s)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-[#4d4d4d]">{{ $s->produk->nama }}</span>
                                    <span class="font-mono text-sm font-medium text-[#171717]">Rp {{ number_format($s->saldo, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm text-[#888888]">Belum ada saldo.</p>
                    @endif
                </div>
            @endif

            <div>
                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Produk Tabungan</label>
                <select wire:model="produkId"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                    <option value="">Pilih Produk</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->tipe === 'paket' ? 'Paket' : 'Bebas' }})</option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Nominal Penarikan</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-sm text-[#888888]">Rp</span>
                    <input type="number" wire:model="nominal" min="10000"
                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                        placeholder="0" />
                </div>
                @error('nominal') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
            </div>

            @if($selectedNasabah && $nominal > 0)
                <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Rincian Penarikan</p>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-[#4d4d4d]">Nominal diminta</span>
                            <span class="font-mono text-[#171717]">Rp {{ number_format($nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-[#4d4d4d]">Komisi ({{ $persenKomisi }}%)</span>
                            <span class="font-mono text-[#ee0000]">- Rp {{ number_format($nominalKomisi, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-[#ebebeb] pt-3">
                        <span class="text-sm font-medium text-[#171717]">Anda terima</span>
                        <span class="font-mono text-sm font-semibold text-[#0070f3]">Rp {{ number_format($nominalDiterima, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <div>
                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Lokasi Pengambilan</label>
                <select wire:model="lokasi"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                    <option value="kantor">Kantor</option>
                    <option value="rumah_kolektor">Rumah Kolektor</option>
                </select>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit" wire:loading.attr="disabled"
                    class="flex-1 rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Ajukan Penarikan</span>
                    <span wire:loading wire:target="submit">Mengirim...</span>
                </button>
                <a href="{{ route('dashboard') }}"
                    class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>