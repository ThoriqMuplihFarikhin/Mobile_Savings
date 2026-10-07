<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Verifikasi Nasabah Baru</h1>
        <p class="mt-1 text-sm text-gray-500">Setujui atau tolak pendaftaran nasabah dari kolektor.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
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

    <div class="mb-4 flex flex-wrap gap-2">
        <button type="button" wire:click="$refresh"
            class="min-h-11 inline-flex items-center gap-1.5 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
            Muat Ulang
        </button>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Alamat</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Profil</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Didaftarkan Oleh</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($pending as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->user->no_hp ?? '— (offline)' }}</td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-sm text-gray-600">{{ $item->alamat }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if ($item->tanggal_lahir)
                                    <div>Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}</div>
                                @endif
                                <div>{{ $item->jenis_kelamin }} · {{ $item->pekerjaan ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->didaftarkanOleh->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button wire:click="approve({{ $item->id }})" class="min-h-11 btn-primary"
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Setuju
                                    </button>
                                    <button wire:click="reject({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-gray-500">Tidak ada nasabah yang perlu diverifikasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
            @forelse($pending as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item->nama }}</p>
                            <p class="text-xs text-gray-500">{{ $item->user->no_hp ?? '— (offline)' }}</p>
                        </div>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Alamat</span>
                            <span class="font-mono text-sm text-gray-600">{{ $item->alamat }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Profil</span>
                            <span class="font-mono text-sm text-gray-600">
                                @if ($item->tanggal_lahir)
                                    <div>Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}</div>
                                @endif
                                <div>{{ $item->jenis_kelamin }} · {{ $item->pekerjaan ?? '-' }}</div>
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Didaftarkan Oleh</span>
                            <span class="font-mono text-sm text-gray-600">{{ $item->didaftarkanOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-2">
                            <button wire:click="approve({{ $item->id }})" class="min-h-11 btn-primary"
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Setuju
                            </button>
                            <button wire:click="reject({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-gray-500">Tidak ada nasabah yang perlu diverifikasi.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $pending->links() }}</div>
    </div>
</div>