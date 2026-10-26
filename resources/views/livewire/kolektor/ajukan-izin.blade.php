<div class="space-y-6 pb-8">
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="rounded-2xl border border-hairline bg-white p-5 dark:bg-surface">
        <h2 class="text-base font-semibold text-ink">Pengajuan baru</h2>
        <p class="mt-0.5 text-sm text-mute">Ajukan izin atau cuti, menunggu persetujuan admin.</p>

        <form wire:submit="ajukan" class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-body">Tanggal mulai</label>
                    <x-ui.tanggal wire:model="tanggalMulai" :min="now()->toDateString()"
                           class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                    @error('tanggalMulai') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-body">Tanggal selesai</label>
                    <x-ui.tanggal wire:model="tanggalSelesai" :min="$tanggalMulai ?: now()->toDateString()"
                           class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                    @error('tanggalSelesai') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-body" for="alasan-izin">Alasan</label>
                <textarea wire:model="alasan" rows="3" id="alasan-izin" placeholder="Contoh: acara keluarga di luar kota"
                          class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"></textarea>
                @error('alasan') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="ajukan"
                    class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                Kirim pengajuan
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-hairline bg-white p-5 dark:bg-surface">
        <h2 class="text-base font-semibold text-ink">Riwayat pengajuan</h2>

        <div class="mt-3 space-y-3">
            @forelse($daftarIzin as $izin)
                <div class="rounded-xl border border-hairline bg-inset p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">
                                {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                            </p>
                            <p class="mt-1 text-sm text-body">{{ $izin->alasan }}</p>
                            @if ($izin->catatan_admin)
                                <p class="mt-1.5 text-sm text-mute">Catatan admin: {{ $izin->catatan_admin }}</p>
                            @endif
                        </div>
                        <x-ui.status-chip class="shrink-0" domain="persetujuan" status="{{ $izin->status }}" />
                    </div>
                </div>
            @empty
                <p class="text-sm text-mute">Belum ada pengajuan izin.</p>
            @endforelse
        </div>
    </div>
</div>
