<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Handover Kolektor"
        subtitle="Pindahkan nasabah dari kolektor lama ke kolektor pengganti."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl border border-primary/20 bg-primary-soft p-3.5 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl border border-danger/20 bg-danger-soft p-3.5 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Grid Dua Kolom (Proses Handover & Riwayat Handover) --}}
    <div class="grid gap-6 lg:grid-cols-12 items-start">
        {{-- Kolom Kiri: Form Proses Handover --}}
        <div class="lg:col-span-7 rounded-2xl border border-hairline/80 bg-white dark:bg-surface p-5 sm:p-6 shadow-xs space-y-6">
            <div class="border-b border-hairline/60 pb-3">
                <h2 class="text-base font-semibold text-ink">Proses Handover</h2>
                <p class="mt-0.5 text-xs text-mute">Pilih kolektor asal dan kolektor baru penanggung jawab seluruh nasabah binaan.</p>
            </div>

            {{-- Pilihan Kolektor --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Kolektor lama (akan dinonaktifkan)</label>
                    <select wire:model.live="kolektorLamaId"
                        class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Pilih Kolektor Lama</option>
                        @foreach($kolektorList as $k)
                            <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-ink">Kolektor pengganti</label>
                    <select wire:model.live="kolektorBaruId" {{ empty($kolektorLamaId) ? 'disabled' : '' }}
                        class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Pilih Kolektor Pengganti</option>
                        @foreach($kolektorList as $k)
                            @if($k->id != $kolektorLamaId)
                                <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Checklist Handover --}}
            <div class="space-y-2 pt-1">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-mute">Checklist handover</h3>
                <div class="divide-y divide-dashed divide-hairline/60 rounded-xl border border-hairline/60 bg-inset/30 px-4 py-1 text-xs">
                    <div class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                        <span class="text-mute">Status kas</span>
                        <div>
                            @if($selectedKolektorLama)
                                @if($hasUnsettledCash)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-danger-soft px-2.5 py-0.5 text-xs font-medium text-danger">
                                        Masih ada kas belum disetor: Rp {{ number_format($unsettledCash, 0, ',', '.') }}. Handover DIBLOKIR
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-medium text-on-primary-soft">
                                        Semua kas sudah disetor ke kantor.
                                    </span>
                                @endif
                            @else
                                <span class="text-mute">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-mute">Nasabah aktif</span>
                        <b class="font-medium text-ink tabular-nums">
                            {{ $selectedKolektorLama ? count($nasabahList) . ' nasabah' : '-' }}
                        </b>
                    </div>
                </div>
            </div>

            {{-- Daftar Nasabah yang Akan Dipindahkan --}}
            <div class="space-y-2 pt-1">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-mute">Daftar nasabah yang akan dipindahkan</h3>
                <div class="rounded-xl border border-hairline/60 bg-inset/30 p-3.5">
                    @if($selectedKolektorLama)
                        @if(count($nasabahList) > 0)
                            <div class="max-h-56 divide-y divide-hairline/40 overflow-y-auto pr-1">
                                @foreach($nasabahList as $item)
                                    <div class="flex items-center justify-between py-2 text-xs">
                                        <div>
                                            <span class="font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</span>
                                            @if($item->tanggal_mulai_ditangani)
                                                <span class="block text-[11px] text-mute">Sejak {{ $item->tanggal_mulai_ditangani->translatedFormat('d M Y') }}</span>
                                            @endif
                                        </div>
                                        <x-admin.status-chip status="Aktif" />
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="py-2 text-xs text-mute">Tidak ada nasabah aktif yang ditugaskan ke kolektor ini.</p>
                        @endif
                    @else
                        <p class="py-2 text-xs text-mute">Pilih kolektor lama terlebih dahulu untuk melihat daftar nasabah.</p>
                    @endif
                </div>
            </div>

            {{-- Pesan Peringatan & Validasi --}}
            @if($kolektorLamaId && $kolektorBaruId && $kolektorLamaId == $kolektorBaruId)
                <div class="rounded-xl border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
                    Kolektor lama dan pengganti tidak boleh sama.
                </div>
            @elseif($hasUnsettledCash)
                <div class="rounded-xl border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
                    Setorkan kas ke kantor lewat Rekonsiliasi dulu sebelum melakukan handover.
                </div>
            @elseif($showConfirmation)
                <div class="rounded-xl border border-primary/20 bg-primary-soft p-3.5 text-xs text-on-primary-soft">
                    Semua <strong>{{ count($nasabahList) }}</strong> nasabah akan dialihkan ke <strong>{{ $kolektorList->where('id', $kolektorBaruId)->first()->name ?? '-' }}</strong>. Akun kolektor lama akan dinonaktifkan secara otomatis.
                </div>
            @endif

            {{-- Tombol Aksi --}}
            <div class="flex justify-end border-t border-hairline/60 pt-4">
                <button type="button" wire:click="processHandover" wire:loading.attr="disabled"
                    @if($showConfirmation) wire:confirm="Yakin ingin melakukan handover? Tindakan ini tidak dapat dibatalkan." @endif
                    @disabled(empty($kolektorLamaId) || empty($kolektorBaruId) || $hasUnsettledCash || count($nasabahList) === 0 || $kolektorLamaId == $kolektorBaruId)
                    class="min-h-11 inline-flex h-10 items-center justify-center rounded-xl bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-40 active:scale-[0.98]">
                    Proses Handover
                </button>
            </div>
        </div>

        {{-- Kolom Kanan: Riwayat Handover --}}
        <div class="lg:col-span-5 rounded-2xl border border-hairline/80 bg-white dark:bg-surface shadow-xs overflow-hidden">
            <div class="flex items-center justify-between border-b border-hairline/60 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Riwayat Handover</h2>
                    <p class="text-xs text-mute">Daftar perpindahan penugasan nasabah.</p>
                </div>
                <button type="button" wire:click="$refresh"
                    class="inline-flex h-8 items-center gap-1.5 rounded-xl border border-hairline/80 bg-white dark:bg-surface px-3 text-xs font-medium text-ink shadow-2xs hover:bg-inset transition active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span>Muat Ulang</span>
                </button>
            </div>

            {{-- Tabel Desktop --}}
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left">
                    <thead class="border-b border-hairline/60 bg-transparent text-[11px] font-semibold uppercase tracking-wider text-mute">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kolektor Lama</th>
                            <th class="px-4 py-3">Kolektor Baru</th>
                            <th class="px-4 py-3 text-center">Nasabah</th>
                            <th class="px-4 py-3">Diproses Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline/50">
                        @forelse($riwayat as $item)
                            <tr class="transition hover:bg-inset/30">
                                <td class="px-4 py-3 text-xs text-body whitespace-nowrap">{{ $item->tanggal_handover->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-xs font-medium text-ink">{{ $item->kolektorLama->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs font-medium text-ink">{{ $item->kolektorBaru->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center tabular-nums text-xs text-ink font-medium">{{ $item->jumlah_nasabah_dipindah }}</td>
                                <td class="px-4 py-3 text-xs text-body whitespace-nowrap">{{ $item->diprosesOleh->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="size-9 text-mute/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <p class="text-xs text-mute font-medium">Belum ada riwayat handover.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Kartu Mobile --}}
            <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                @forelse($riwayat as $item)
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-ink">{{ $item->kolektorLama->name ?? '-' }} &rarr; {{ $item->kolektorBaru->name ?? '-' }}</p>
                                <p class="text-[11px] text-mute">{{ $item->tanggal_handover->translatedFormat('d M Y') }}</p>
                            </div>
                            <x-admin.status-chip :status="$item->status_kas_saat_handover === 'lunas' ? 'Lunas' : 'Tunggakan'" />
                        </div>
                        <div class="grid grid-cols-2 gap-2 rounded-xl bg-inset/40 p-2.5 text-[11px]">
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
                    <div class="p-8 text-center text-xs text-mute">
                        Belum ada riwayat handover.
                    </div>
                @endforelse
            </div>

            {{-- Paginasi --}}
            @if($riwayat->hasPages())
                <div class="border-t border-hairline/60 px-4 py-3">{{ $riwayat->links() }}</div>
            @endif
        </div>
    </div>
</div>
