<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Log Aktivitas</h1>
        <p class="mt-1 text-sm text-mute">Audit trail seluruh aktivitas sistem.</p>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari keterangan..."
                class="h-10 w-full rounded-md border border-hairline bg-white py-0 pl-10 pr-4 text-sm text-ink placeholder-[#737373] focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
        </div>
        <select wire:model.live="aksiFilter"
            class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
            <option value="">Semua Aksi</option>
            <option value="setor">Setor</option>
            <option value="tarik">Tarik</option>
            <option value="verifikasi">Verifikasi</option>
            <option value="approve_penarikan">Approve Penarikan</option>
            <option value="komplain">Komplain</option>
            <option value="rekon">Rekonsiliasi</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Waktu</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">User</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Keterangan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($logs as $log)
                        <tr class="transition hover:bg-inset">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-body">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
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
                                        'setor' => 'bg-info-soft text-info',
                                        'tarik' => 'bg-danger-soft text-danger',
                                        'verifikasi' => 'bg-info-soft text-info',
                                        'approve_penarikan' => 'bg-warning-soft text-warning-deep',
                                        'komplain' => 'bg-inset text-body',
                                        default => 'bg-inset text-body'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full {{ $color }} px-2.5 py-0.5 text-xs">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $log->user->name ?? '-' }}</div>
                                <div class="text-xs text-mute">{{ ucfirst($log->user->role ?? '-') }}</div>
                            </td>
                            <td class="max-w-[300px] truncate px-4 py-3 text-sm text-body">{{ is_array($log->detail) ? json_encode($log->detail) : ($log->detail ?? '-') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-mute">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    <p class="text-sm text-mute">Tidak ada log aktivitas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $logs->links() }}</div>
    </div>
</div>