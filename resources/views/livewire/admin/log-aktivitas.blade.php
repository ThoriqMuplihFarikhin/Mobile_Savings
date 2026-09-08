<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Log Aktivitas</h1>
        <p class="mt-1 text-sm text-[#888888]">Audit trail seluruh aktivitas sistem.</p>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#888888]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari keterangan..."
                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-4 text-sm text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
        </div>
        <select wire:model.live="aksiFilter"
            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
            <option value="">Semua Aksi</option>
            <option value="setor">Setor</option>
            <option value="tarik">Tarik</option>
            <option value="verifikasi">Verifikasi</option>
            <option value="approve_penarikan">Approve Penarikan</option>
            <option value="komplain">Komplain</option>
            <option value="rekon">Rekonsiliasi</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Waktu</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Aksi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">User</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Keterangan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($logs as $log)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-[#4d4d4d]">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
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
                                        'setor' => 'bg-[#d3e5ff] text-[#0761d1]',
                                        'tarik' => 'bg-[#f7d4d6] text-[#c50000]',
                                        'verifikasi' => 'bg-[#d3e5ff] text-[#0761d1]',
                                        'approve_penarikan' => 'bg-[#ffefcf] text-[#ab570a]',
                                        'komplain' => 'bg-[#d8ccf1] text-[#4c2889]',
                                        default => 'bg-[#fafafa] text-[#4d4d4d]'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full {{ $color }} px-2.5 py-0.5 font-mono text-xs">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-[#171717]">{{ $log->user->name ?? '-' }}</div>
                                <div class="text-xs text-[#888888]">{{ ucfirst($log->user->role ?? '-') }}</div>
                            </td>
                            <td class="max-w-[300px] truncate px-4 py-3 text-sm text-[#4d4d4d]">{{ is_array($log->detail) ? json_encode($log->detail) : ($log->detail ?? '-') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-[#888888]">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    <p class="text-sm text-[#888888]">Tidak ada log aktivitas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $logs->links() }}</div>
    </div>
</div>