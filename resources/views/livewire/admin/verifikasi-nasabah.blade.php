<div class="space-y-6">
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
                class="min-h-11 h-[38px] inline-flex items-center gap-1.5 rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm">
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
                        <tr class="hover:bg-surface-2 transition text-ink">
                            <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $item->nama }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-mute">{{ $item->user->no_hp ?? '— (offline)' }}</td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-mute">{{ $item->alamat }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-mute">
                                @if ($item->tanggal_lahir)
                                    <div>Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }}</div>
                                @endif
                                <div>{{ ucfirst($item->jenis_kelamin) }} · {{ $item->pekerjaan ?? '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-mute">{{ $item->didaftarkanOleh->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="approve({{ $item->id }})"
                                        class="min-h-11 h-8 inline-flex items-center gap-1 rounded-[8px] bg-accent px-3 text-xs font-semibold text-accent-foreground transition hover:opacity-90 cursor-pointer shadow-xs"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Setuju
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="reject({{ $item->id }})"
                                        class="min-h-11 h-8 inline-flex items-center gap-1 rounded-[8px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:opacity-90 cursor-pointer"
                                    >
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
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
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $item->nama }}</p>
                            <p class="text-xs text-mute">{{ $item->user->no_hp ?? '— (offline)' }}</p>
                        </div>
                        <span class="rounded-full bg-warning-soft px-2 py-0.5 text-xs font-semibold text-warning-deep">
                            Menunggu
                        </span>
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
                                @if ($item->tanggal_lahir)
                                    Lahir: {{ $item->tanggal_lahir->format('d/m/Y') }} ·
                                @endif
                                {{ ucfirst($item->jenis_kelamin) }} · {{ $item->pekerjaan ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-hairline">
                        <button
                            type="button"
                            wire:click="approve({{ $item->id }})"
                            class="min-h-11 flex-1 h-9 inline-flex items-center justify-center gap-1 rounded-[10px] bg-accent px-3 text-xs font-semibold text-accent-foreground transition hover:opacity-90 cursor-pointer shadow-xs"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Setuju
                        </button>
                        <button
                            type="button"
                            wire:click="reject({{ $item->id }})"
                            class="min-h-11 flex-1 h-9 inline-flex items-center justify-center gap-1 rounded-[10px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:opacity-90 cursor-pointer"
                        >
                            Tolak
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <x-admin.empty-state
                        title="Tidak ada nasabah yang perlu diverifikasi."
                        description="Semua pendaftaran nasabah baru sudah diproses."
                    />
                </div>
            @endforelse
        </div>

        @if($pending->count() > 0)
            <div class="flex items-center justify-between border-t border-hairline px-4 py-2.5 text-xs text-mute bg-surface-2">
                <span>Menampilkan {{ $pending->count() }} data pendaftaran</span>
            </div>
        @endif
    </div>
</div>