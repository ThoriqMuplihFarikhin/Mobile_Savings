<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Daftarkan Nasabah</h1>
            <p class="mt-1 text-sm text-[#888888]">Daftarkan nasabah baru untuk diverifikasi oleh admin.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Daftar Nasabah Baru
        </button>
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

    @if($showForm)
        <div class="mb-6 rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
            <div class="border-b border-[#ebebeb] px-6 py-4">
                <h3 class="text-sm font-semibold text-[#171717]">Form Pendaftaran Nasabah</h3>
            </div>
            <form wire:submit="submit" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Nama Lengkap</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('nama') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">No. HP</label>
                        <input type="text" wire:model="noHp" placeholder="08xxxxxxxxxx"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('noHp') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Alamat</label>
                        <textarea wire:model="alamat" rows="2"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"></textarea>
                        @error('alamat') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Tanggal Lahir</label>
                        <input type="date" wire:model="tanggalLahir"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('tanggalLahir') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Jenis Kelamin</label>
                        <select wire:model="jenisKelamin"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Pekerjaan</label>
                        <input type="text" wire:model="pekerjaan"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        Daftarkan
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($nasabahList as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-[#171717]">{{ $item->nama }}</div>
                                <div class="text-xs text-[#888888]">{{ $item->alamat }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->user->no_hp ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_pendaftaran === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Aktif</span>
                                @elseif($item->status_pendaftaran === 'pending_verifikasi')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Menunggu Verifikasi</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-[#888888]">{{ $item->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <p class="text-sm text-[#888888]">Belum ada nasabah yang didaftarkan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $nasabahList->links() }}</div>
    </div>
</div>
