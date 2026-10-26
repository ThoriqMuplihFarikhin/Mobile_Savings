<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Verifikasi Nasabah Baru</h1>
        <p class="mt-1 text-sm text-mute">Setujui atau tolak pendaftaran nasabah dari kolektor.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        <button type="button" wire:click="$refresh"
            class="min-h-11 inline-flex items-center gap-1.5 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
            Muat Ulang
        </button>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Alamat</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Profil</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Didaftarkan Oleh</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($pending as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3 text-sm font-medium text-ink">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->user->no_hp ?? '— (offline)' }}</td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-sm text-body">{{ $item->alamat }}</td>
                            <td class="px-4 py-3 text-sm text-body">
                                @if ($item->tanggal_lahir)
                                    <div>Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}</div>
                                @endif
                                <div>{{ $item->jenis_kelamin }} · {{ $item->pekerjaan ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->didaftarkanOleh->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button wire:click="approve({{ $item->id }})" class="min-h-11 btn-primary"
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Setuju
                                    </button>
                                    <button wire:click="reject({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada nasabah yang perlu diverifikasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($pending as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nama }}</p>
                            <p class="text-xs text-mute">{{ $item->user->no_hp ?? '— (offline)' }}</p>
                        </div>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Alamat</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->alamat }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Profil</span>
                            <span class="tabular-nums text-sm text-body">
                                @if ($item->tanggal_lahir)
                                    <div>Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}</div>
                                @endif
                                <div>{{ $item->jenis_kelamin }} · {{ $item->pekerjaan ?? '-' }}</div>
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Didaftarkan Oleh</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->didaftarkanOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-2">
                            <button wire:click="approve({{ $item->id }})" class="min-h-11 btn-primary"
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Setuju
                            </button>
                            <button wire:click="reject({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-mute">Tidak ada nasabah yang perlu diverifikasi.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $pending->links() }}</div>
    </div>
</div>