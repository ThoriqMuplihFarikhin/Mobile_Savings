<div class="space-y-5">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Verifikasi Nasabah Baru"
        subtitle="Setujui atau tolak pendaftaran nasabah dari kolektor."
        breadcrumb="Nasabah"
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="$refresh"
                class="min-h-11 h-[38px] inline-flex items-center gap-1.5 rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
            >
                <svg class="size-3.5 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Muat Ulang
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Banner Flash Notifikasi --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft border border-primary/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-danger-soft px-4 py-3 text-xs font-medium text-danger border border-danger/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tabel / Kartu Verifikasi --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Desktop Table --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium border-b border-hairline">
                        <th class="px-4 py-2.5">Nama</th>
                        <th class="px-4 py-2.5">No. HP</th>
                        <th class="px-4 py-2.5">Alamat</th>
                        <th class="px-4 py-2.5">Profil</th>
                        <th class="px-4 py-2.5">Didaftarkan Oleh</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($pending as $item)
                        @php
                            $isOffline = $item->user?->isOffline() || $item->user?->mode_akses === 'offline';
                        @endphp
                        <tr class="hover:bg-surface-2 transition text-ink">
                            <td class="px-4 py-3">
                                <div class="font-medium text-ink">{{ $item->nama }}</div>
                                <div class="text-xs text-mute mt-0.5">
                                    {{ $item->jenis_kelamin }} &middot; {{ $item->pekerjaan ?? '-' }}
                                    @if($item->tanggal_lahir)
                                        &middot; <span class="tabular-nums">Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body tabular-nums">
                                {{ $item->user?->no_hp ?? '— (offline)' }}
                            </td>
                            <td class="max-w-[220px] truncate px-4 py-3 text-xs text-mute">
                                {{ $item->alamat }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-admin.status-chip
                                    domain="mode_akses"
                                    :status="$isOffline ? 'offline' : 'digital'"
                                    :teks="$isOffline ? 'Offline' : 'Digital'"
                                />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">
                                {{ $item->didaftarkanOleh->name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="approve({{ $item->id }})"
                                        class="min-h-11 h-8 inline-flex items-center gap-1 rounded-[10px] bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Setujui
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="reject({{ $item->id }})"
                                        class="min-h-11 h-8 inline-flex items-center gap-1 rounded-[10px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                                    >
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <x-admin.empty-state
                                    title="Tidak ada nasabah yang perlu diverifikasi."
                                    description="Semua pendaftaran nasabah baru sudah diproses."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards (< md) --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($pending as $item)
                @php
                    $isOffline = $item->user?->isOffline() || $item->user?->mode_akses === 'offline';
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $item->nama }}</p>
                            <p class="text-xs text-mute mt-0.5">{{ $item->user?->no_hp ?? '— (offline)' }}</p>
                        </div>
                        <x-admin.status-chip
                            domain="mode_akses"
                            :status="$isOffline ? 'offline' : 'digital'"
                            :teks="$isOffline ? 'Offline' : 'Digital'"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-mute block">Alamat</span>
                            <span class="text-ink font-medium">{{ $item->alamat }}</span>
                        </div>
                        <div>
                            <span class="text-mute block">Didaftarkan Oleh</span>
                            <span class="text-ink font-medium">{{ $item->didaftarkanOleh->name ?? '-' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-mute block">Profil</span>
                            <span class="text-ink font-medium">
                                {{ $item->jenis_kelamin }} &middot; {{ $item->pekerjaan ?? '-' }}
                                @if ($item->tanggal_lahir)
                                    &middot; Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-hairline">
                        <button
                            type="button"
                            wire:click="approve({{ $item->id }})"
                            class="min-h-11 flex-1 h-9 inline-flex items-center justify-center gap-1 rounded-[10px] bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Setujui
                        </button>
                        <button
                            type="button"
                            wire:click="reject({{ $item->id }})"
                            class="min-h-11 flex-1 h-9 inline-flex items-center justify-center gap-1 rounded-[10px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                        >
                            Tolak
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4">
                    <x-admin.empty-state
                        title="Tidak ada nasabah yang perlu diverifikasi."
                        description="Semua pendaftaran nasabah baru sudah diproses."
                    />
                </div>
            @endforelse
        </div>

        @if($pending->count() > 0)
            <div class="flex items-center justify-between border-t border-hairline px-4 py-2.5 text-xs text-mute bg-surface">
                <span>Menampilkan {{ $pending->total() }} data pendaftaran</span>
                @if($pending->hasPages())
                    <div>{{ $pending->links() }}</div>
                @else
                    <span>Halaman 1 dari 1</span>
                @endif
            </div>
        @endif
    </div>
</div>