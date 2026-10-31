<div>
    <x-admin.page-header breadcrumb="Sistem" title="Log Aktivitas" subtitle="Audit trail seluruh aktivitas sistem." />

    <x-admin.toolbar class="mb-4">
        <x-slot:filters>
            <div class="relative w-full max-w-sm">
                <flux:icon.magnifying-glass class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-mute dark:text-zinc-400" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari keterangan..."
                    class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white pl-9 pr-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500" />
            </div>
            <select wire:model.live="aksiFilter"
                class="h-[38px] rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100">
                <option value="">Semua Aksi</option>
                <option value="setor">Setor</option>
                <option value="tarik">Penarikan</option>
                <option value="verifikasi">Verifikasi</option>
                <option value="approve_penarikan">Approve Penarikan</option>
                <option value="komplain">Komplain</option>
                <option value="rekon">Rekonsiliasi</option>
            </select>
        </x-slot:filters>
    </x-admin.toolbar>

    <div class="overflow-hidden rounded-[14px] border border-hairline-warm bg-white shadow-xs dark:border-hairline/60 dark:bg-surface">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline-warm bg-surface-alt/60 dark:border-hairline/60 dark:bg-zinc-800/50">
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute dark:text-zinc-400">Waktu</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute dark:text-zinc-400">Aksi</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute dark:text-zinc-400">User</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute dark:text-zinc-400">Keterangan</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute dark:text-zinc-400">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($logs as $log)
                        <tr class="transition hover:bg-surface-alt/40 dark:hover:bg-zinc-800/30">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body dark:text-zinc-300">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $label = match($log->aksi) {
                                        'setor' => 'Setor',
                                        'tarik' => 'Penarikan',
                                        'verifikasi' => 'Verifikasi',
                                        'approve_penarikan' => 'Approve',
                                        'komplain' => 'Komplain',
                                        'rekon' => 'Rekonsiliasi',
                                        default => ucfirst($log->aksi)
                                    };
                                    $color = match($log->aksi) {
                                        'setor' => 'bg-info-soft text-info border border-info/30',
                                        'tarik' => 'bg-danger-soft text-danger border border-danger/30',
                                        'verifikasi' => 'bg-info-soft text-info border border-info/30',
                                        'approve_penarikan' => 'bg-warning-soft text-warning-deep border border-warning/30',
                                        'rekon' => 'bg-primary-soft text-primary-deep border border-primary/30',
                                        default => 'bg-inset text-body border border-hairline'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full {{ $color }} px-2.5 py-0.5 text-[11px] font-semibold">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-semibold text-ink dark:text-zinc-100">{{ $log->user->name ?? '-' }}</div>
                                <div class="text-[11px] text-mute dark:text-zinc-400">{{ ucfirst($log->user->role ?? '-') }}</div>
                            </td>
                            <td class="max-w-[320px] truncate px-4 py-3 text-xs text-body dark:text-zinc-300" title="{{ is_array($log->detail) ? json_encode($log->detail) : ($log->detail ?? '-') }}">
                                {{ is_array($log->detail) ? json_encode($log->detail) : ($log->detail ?? '-') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-mute dark:text-zinc-400">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <x-admin.empty-state icon="document-text" title="Tidak Ada Log Aktivitas" description="Belum ada catatan aktivitas sistem yang sesuai dengan filter pencarian." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Kartu Mobile Fallback --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($logs as $log)
                @php
                    $label = match($log->aksi) {
                        'setor' => 'Setor',
                        'tarik' => 'Penarikan',
                        'verifikasi' => 'Verifikasi',
                        'approve_penarikan' => 'Approve',
                        'komplain' => 'Komplain',
                        'rekon' => 'Rekonsiliasi',
                        default => ucfirst($log->aksi)
                    };
                    $color = match($log->aksi) {
                        'setor' => 'bg-info-soft text-info border border-info/30',
                        'tarik' => 'bg-danger-soft text-danger border border-danger/30',
                        'verifikasi' => 'bg-info-soft text-info border border-info/30',
                        'approve_penarikan' => 'bg-warning-soft text-warning-deep border border-warning/30',
                        'rekon' => 'bg-primary-soft text-primary-deep border border-primary/30',
                        default => 'bg-inset text-body border border-hairline'
                    };
                @endphp
                <div class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-mute dark:text-zinc-400">{{ $log->created_at->translatedFormat('d M Y H:i') }}</span>
                        <span class="inline-flex items-center rounded-full {{ $color }} px-2.5 py-0.5 text-[11px] font-semibold">{{ $label }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-ink dark:text-zinc-100">{{ $log->user->name ?? '-' }}</p>
                            <p class="text-[11px] text-mute dark:text-zinc-400">{{ ucfirst($log->user->role ?? '-') }}</p>
                        </div>
                        <span class="text-xs text-mute dark:text-zinc-400">{{ $log->ip_address ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-body dark:text-zinc-300 break-words rounded-lg bg-surface-alt/50 p-2.5 dark:bg-zinc-800/40">{{ is_array($log->detail) ? json_encode($log->detail) : ($log->detail ?? '-') }}</p>
                </div>
            @empty
                <div class="p-8 text-center">
                    <x-admin.empty-state icon="document-text" title="Tidak Ada Log Aktivitas" description="Belum ada catatan aktivitas sistem yang sesuai dengan filter pencarian." />
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="border-t border-hairline px-4 py-3 bg-surface-alt/30 dark:bg-zinc-800/20">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>