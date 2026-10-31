<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Kas di Tangan Kolektor"
        subtitle="Pantau uang tunai yang belum disetorkan kolektor ke kantor."
    >
        <x-slot:actions>
            <a href="/admin/rekonsiliasi" wire:navigate
                class="inline-flex h-[38px] items-center rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                Buka Rekonsiliasi
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- KPI Cards --}}
    <x-admin.kpi-grid cols="3">
        <x-admin.kpi-card
            label="Total kas"
            :value="'Rp ' . number_format($totalKas, 0, ',', '.')"
            sub="Seluruh kolektor aktif"
        />
        <x-admin.kpi-card
            label="Kolektor melewati batas"
            :value="$jumlahLewatBatas"
            sub="Perlu tindakan"
            :variant="$jumlahLewatBatas > 0 ? 'error' : 'default'"
        />
        <x-admin.kpi-card
            label="Batas kas / hari"
            :value="'Rp ' . number_format((int) \App\Models\AdminSetting::get('batas_kas_kolektor', '0'), 0, ',', '.') . ' / ' . (int) \App\Models\AdminSetting::get('batas_hari_kas', '0') . ' hari'"
            sub="Sesuai pengaturan"
        />
    </x-admin.kpi-grid>

    {{-- Tabel Desktop --}}
    <div class="overflow-hidden rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs">
        <div class="border-b border-[var(--color-admin-border)] px-5 py-3.5">
            <h3 class="text-sm font-semibold text-[var(--color-admin-text)]">Kas Per Kolektor</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Setoran Masuk</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Penarikan Tunai</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kas di Tangan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Transaksi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Umur Tertua</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Pending</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Akumulasi Selisih</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse($daftarKas as $baris)
                        <tr @class(['transition hover:bg-[var(--color-surface-2)]', 'bg-warning-soft/40' => $baris['lewat_batas']])>
                            <td class="px-4 py-3">
                                <div class="font-medium text-[var(--color-admin-text)] flex items-center gap-1.5">
                                    <span>{{ $baris['nama'] }}</span>
                                    @if($baris['terkunci'])
                                        <span class="inline-flex items-center rounded-full bg-red-500/10 px-2 py-0.5 text-[11px] font-semibold text-red-600 dark:text-red-400">Terkunci</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-[var(--color-admin-text)]">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center tabular-nums text-[var(--color-admin-text)]">{{ $baris['jumlah_transaksi'] }}</td>
                            <td class="px-4 py-3 text-center tabular-nums text-[var(--color-admin-text)]">{{ $baris['umur_terlama_hari'] }} hari</td>
                            <td class="px-4 py-3 text-center tabular-nums text-[var(--color-admin-text)]">{{ $baris['pengajuan_pending'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium {{ $baris['selisih_kumulatif'] != 0 ? 'text-red-600 dark:text-red-400' : 'text-[var(--color-admin-text)]' }}">
                                {{ $baris['selisih_kumulatif'] >= 0 ? '+' : '' }}Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($baris['lewat_batas'])
                                    <x-admin.status-chip status="Lewat Batas" />
                                @else
                                    <x-admin.status-chip status="Aman" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-0">
                                <x-admin.empty-state
                                    title="Belum ada kolektor aktif"
                                    description="Data kas di tangan akan muncul saat kolektor mulai menerima setoran."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-kas" class="divide-y divide-hairline md:hidden">
            @forelse($daftarKas as $baris)
                <div @class(['p-4 space-y-3', 'bg-warning-soft/40' => $baris['lewat_batas']])>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-medium text-[var(--color-admin-text)] flex items-center gap-1.5">
                                <span>{{ $baris['nama'] }}</span>
                                @if($baris['terkunci'])
                                    <span class="inline-flex items-center rounded-full bg-red-500/10 px-2 py-0.5 text-[11px] font-semibold text-red-600 dark:text-red-400">Terkunci</span>
                                @endif
                            </div>
                            <div class="text-xs text-[var(--color-admin-mute)] mt-0.5">{{ $baris['jumlah_transaksi'] }} transaksi · Umur {{ $baris['umur_terlama_hari'] }} hari</div>
                        </div>
                        @if($baris['lewat_batas'])
                            <x-admin.status-chip status="Lewat Batas" />
                        @else
                            <x-admin.status-chip status="Aman" />
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-b border-[var(--color-admin-border)] py-2">
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Setoran Masuk:</span>
                            <div class="font-medium tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Penarikan Tunai:</span>
                            <div class="font-medium tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Kas di Tangan:</span>
                            <div class="font-semibold tabular-nums text-sm text-[var(--color-accent)]">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Akumulasi Selisih:</span>
                            <div class="font-medium tabular-nums {{ $baris['selisih_kumulatif'] != 0 ? 'text-red-600 dark:text-red-400' : 'text-[var(--color-admin-text)]' }}">
                                {{ $baris['selisih_kumulatif'] >= 0 ? '+' : '' }}Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6">
                    <x-admin.empty-state
                        title="Belum ada kolektor aktif"
                        description="Data kas di tangan akan muncul saat kolektor mulai menerima setoran."
                    />
                </div>
            @endforelse
        </div>
    </div>
</div>

