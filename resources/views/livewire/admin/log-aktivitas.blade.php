<div>
    <x-admin.page-header breadcrumb="Sistem" title="Log Aktivitas" subtitle="Audit trail seluruh aktivitas sistem." />

    <div class="overflow-hidden rounded-[14px] border border-hairline bg-surface shadow-xs">
        {{-- Toolbar Card Filter --}}
        <div class="border-b border-hairline p-3 sm:p-4 flex flex-wrap items-center justify-between gap-3">
            <div class="relative w-full sm:w-72">
                <flux:icon.magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-mute pointer-events-none" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari keterangan..."
                    class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface pl-9 pr-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>

            <div class="flex flex-wrap items-center gap-1 rounded-[10px] border border-hairline bg-surface-2 p-[3px]" role="group" aria-label="Filter Aksi">
                @foreach([
                    '' => 'Semua Aksi',
                    'setor' => 'Setor',
                    'tarik' => 'Tarik',
                    'verifikasi' => 'Verifikasi',
                    'approve_penarikan' => 'Approve Penarikan',
                    'komplain' => 'Komplain',
                    'rekon' => 'Rekonsiliasi',
                ] as $val => $txt)
                    <button type="button" wire:click="$set('aksiFilter', '{{ $val }}')"
                        class="h-7 px-3 rounded-[7px] text-xs transition cursor-pointer {{ $aksiFilter === $val ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink font-medium' }}">
                        {{ $txt }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-surface-2/40">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Waktu</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">User</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Keterangan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($logs as $log)
                        <tr class="transition hover:bg-surface-2/60">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">
                                {{ ($log->timestamp ?? $log->created_at)->translatedFormat('d M H.i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-surface-2 px-2.5 py-0.5 text-xs font-medium text-mute">
                                    {{ $this->formatAksi($log->aksi) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-ink font-normal">
                                {{ $log->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-body break-words max-w-md">
                                {{ $this->formatKeterangan($log) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-mute">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-xs text-mute">
                                Tidak ada log aktivitas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($logs as $log)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-mute">{{ ($log->timestamp ?? $log->created_at)->translatedFormat('d M H.i') }}</span>
                        <span class="inline-flex items-center rounded-full bg-surface-2 px-2.5 py-0.5 text-xs font-medium text-mute">
                            {{ $this->formatAksi($log->aksi) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-ink">{{ $log->user->name ?? '-' }}</span>
                        <span class="font-mono text-xs text-mute">{{ $log->ip_address ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-body break-words rounded-lg bg-surface-2/60 p-2.5">{{ $this->formatKeterangan($log) }}</p>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-mute">
                    Tidak ada log aktivitas.
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline px-4 py-3 text-xs text-mute">
            <div>
                Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data
            </div>
            @if($logs->hasPages())
                <div>
                    {{ $logs->links() }}
                </div>
            @else
                <div>
                    Halaman 1 dari 1
                </div>
            @endif
        </div>
    </div>
</div>
