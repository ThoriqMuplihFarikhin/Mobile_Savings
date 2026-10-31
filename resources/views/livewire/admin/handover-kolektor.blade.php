<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Handover Kolektor"
        subtitle="Pindahkan nasabah dari kolektor lama ke kolektor pengganti."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Form Proses Handover --}}
    <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
        <div class="border-b border-hairline bg-inset/40 px-6 py-4">
            <h3 class="text-sm font-semibold text-ink">Proses Handover Penugasan</h3>
            <p class="text-xs text-mute">Pilih kolektor asal dan kolektor baru penanggung jawab seluruh nasabah binaan.</p>
        </div>
        <div class="p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Kolektor Lama (Akan Dinonaktifkan)</label>
                    <select wire:model.live="kolektorLamaId"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                        <option value="">Pilih Kolektor Lama</option>
                        @foreach($kolektorList as $k)
                            <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Kolektor Pengganti</label>
                    <select wire:model.live="kolektorBaruId" {{ empty($kolektorLamaId) || $hasUnsettledCash ? 'disabled' : '' }}
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-surface">
                        <option value="">Pilih Kolektor Pengganti</option>
                        @foreach($kolektorList as $k)
                            @if($k->id != $kolektorLamaId)
                                <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            @if($selectedKolektorLama)
                <div class="mt-6 space-y-4">
                    <div class="rounded-[10px] border border-hairline bg-surface p-4">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-mute">Checklist Kelayakan Handover</h4>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                @if($hasUnsettledCash)
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-danger-soft text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </div>
                                @else
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs font-semibold text-ink">Status Kas</p>
                                    @if($hasUnsettledCash)
                                        <p class="mt-0.5 text-xs text-danger">Masih ada kas belum disetor ke kantor: <span class="tabular-nums font-semibold">Rp {{ number_format($unsettledCash, 0, ',', '.') }}</span></p>
                                        <p class="mt-0.5 text-[11px] text-danger">Handover <strong>DIBLOKIR</strong>. Kolektor harus menyetor kas terlebih dahulu melalui rekonsiliasi kantor.</p>
                                    @else
                                        <p class="mt-0.5 text-xs text-primary">Semua kas sudah disetor bersih ke kantor.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                @if($nasabahList->count() > 0)
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                @else
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border border-hairline bg-inset text-mute">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs font-semibold text-ink">Nasabah Aktif</p>
                                    <p class="mt-0.5 text-xs text-body">{{ $nasabahList->count() }} nasabah binaan akan dipindahkan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($nasabahList->count() > 0)
                        <div class="rounded-[10px] border border-hairline bg-surface p-4">
                            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-mute">Daftar Nasabah yang Akan Dipindahkan</h4>
                            <div class="max-h-48 space-y-1.5 overflow-y-auto">
                                @foreach($nasabahList as $item)
                                    <div class="flex items-center justify-between rounded-[8px] bg-inset/40 px-3 py-2 text-xs">
                                        <span class="font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</span>
                                        <span class="text-[11px] text-mute">Sejak {{ $item->tanggal_mulai_ditangani->translatedFormat('d M Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($showConfirmation)
                        <div class="rounded-[10px] border border-primary/20 bg-primary-soft p-4 text-xs text-on-primary-soft">
                            <p class="leading-relaxed">
                                Semua <strong>{{ $nasabahList->count() }}</strong> nasabah akan dipindahkan ke <strong>{{ $kolektorList->where('id', $kolektorBaruId)->first()->name ?? '-' }}</strong>.
                                Akun kolektor lama akan dinonaktifkan secara otomatis. Lanjutkan?
                            </p>
                            <button wire:click="processHandover" wire:loading.attr="disabled" wire:confirm="Yakin ingin melakukan handover? Tindakan ini tidak dapat dibatalkan."
                                class="mt-3 inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-50">
                                Proses Handover Sekarang
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Riwayat Handover --}}
    <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
        <div class="flex items-center justify-between border-b border-hairline px-6 py-4">
            <h3 class="text-sm font-semibold text-ink">Riwayat Handover</h3>
            <button type="button" wire:click="$refresh"
                class="inline-flex h-[32px] items-center gap-1.5 rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink shadow-xs transition hover:bg-inset">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Muat Ulang</span>
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset/50">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Kolektor Lama</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Kolektor Baru</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-mute">Nasabah Dipindah</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Status Kas</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-inset/30">
                            <td class="px-4 py-3 text-xs text-body">{{ $item->tanggal_handover->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $item->kolektorLama->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $item->kolektorBaru->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center tabular-nums text-xs text-body">{{ $item->jumlah_nasabah_dipindah }}</td>
                            <td class="px-4 py-3">
                                <x-admin.status-chip
                                    :label="$item->status_kas_saat_handover === 'lunas' ? 'Lunas' : 'Tunggakan'"
                                    :variant="$item->status_kas_saat_handover === 'lunas' ? 'success' : 'danger'"
                                />
                            </td>
                            <td class="px-4 py-3 text-xs text-body">{{ $item->diprosesOleh->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    <p class="text-xs text-mute">Belum ada riwayat handover.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($riwayat as $item)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $item->kolektorLama->name ?? '-' }} &rarr; {{ $item->kolektorBaru->name ?? '-' }}</p>
                            <p class="text-[11px] text-mute">{{ $item->tanggal_handover->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-admin.status-chip
                            :label="$item->status_kas_saat_handover === 'lunas' ? 'Lunas' : 'Tunggakan'"
                            :variant="$item->status_kas_saat_handover === 'lunas' ? 'success' : 'danger'"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 rounded-[10px] bg-inset/40 p-2.5 text-[11px]">
                        <div>
                            <span class="block text-mute">Nasabah Dipindah</span>
                            <span class="tabular-nums font-semibold text-ink">{{ $item->jumlah_nasabah_dipindah }} nasabah</span>
                        </div>
                        <div>
                            <span class="block text-mute">Diproses Oleh</span>
                            <span class="font-medium text-body">{{ $item->diprosesOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-xs text-mute">Belum ada riwayat handover.</p>
                </div>
            @endforelse
        </div>
        @if($riwayat->hasPages())
            <div class="border-t border-hairline px-4 py-3">{{ $riwayat->links() }}</div>
        @endif
    </div>
</div>
