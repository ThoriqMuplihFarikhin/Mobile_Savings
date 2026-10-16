<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Registrasi Nasabah</h1>
            <p class="mt-1 text-sm text-mute">Daftarkan nasabah baru.</p>
        </div>
        <button wire:click="toggleForm"
            class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Daftar Nasabah Baru
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($showForm)
        <div class="mb-6 rounded-lg bg-inset shadow-[inset_0_0_0_1px_#e5e5e5]">
            <div class="border-b border-hairline px-6 py-4">
                <h3 class="text-sm font-semibold text-ink">Form Registrasi Nasabah</h3>
            </div>
            <form wire:submit="submit" class="space-y-4 p-6">
                <label class="flex items-center gap-2 text-sm font-medium text-ink">
                    <input type="checkbox" wire:model.live="modeOffline"
                        class="h-4 w-4 rounded border-hairline text-info focus:ring-ink" />
                    Nasabah tidak memakai aplikasi / tidak punya HP (mode offline)
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nama Lengkap</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        @error('nama') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    @if(! $modeOffline)
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">No. HP</label>
                        <input type="text" wire:model="noHp" placeholder="08xxxxxxxxxx"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        @error('noHp') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-ink">Alamat</label>
                        <textarea wire:model="alamat" rows="2"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"></textarea>
                        @error('alamat') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Tanggal Lahir</label>
                        <x-ui.tanggal wire:model="tanggalLahir" :max="now()->toDateString()"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        @error('tanggalLahir') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Jenis Kelamin</label>
                        <select wire:model="jenisKelamin"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Pekerjaan</label>
                        <input type="text" wire:model="pekerjaan"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="btn-primary">
                        Daftarkan
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($nasabah as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $item->name }}</div>
                                <div class="text-xs text-mute">{{ $item->nasabahProfil->alamat ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->no_hp ?? '— (offline)' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_akun === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Terkunci</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-mute">{{ $item->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <p class="text-sm text-mute">Belum ada nasabah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $nasabah->links() }}</div>
    </div>
</div>