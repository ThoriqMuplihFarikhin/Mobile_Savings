<div class="mx-auto max-w-4xl space-y-6 pb-24">
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Daftarkan nasabah</h1>
            <p class="mt-1 text-sm text-mute">Daftarkan nasabah baru di lapangan untuk diverifikasi admin.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex shrink-0 items-center justify-center gap-2 px-5 py-3 text-sm font-medium transition active:scale-95 {{ $showForm ? 'rounded-full border border-hairline-strong bg-white text-ink hover:bg-inset dark:bg-surface' : 'rounded-full bg-primary text-on-primary hover:bg-primary-hover' }}">
            <flux:icon.plus class="size-4" aria-hidden="true" />
            {{ $showForm ? 'Tutup form' : 'Daftar nasabah baru' }}
        </button>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Form Section (Collapsible) --}}
    @if($showForm)
        <div class="overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
            <div class="flex items-center gap-3 border-b border-hairline bg-inset px-5 py-4">
                <div class="flex size-9 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                    <flux:icon.user-plus class="size-5" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-ink">Form pendaftaran nasabah</h2>
                    <p class="text-sm text-mute">Isi data calon nasabah secara lengkap</p>
                </div>
            </div>

            <form wire:submit="submit" class="space-y-5 p-5">
                <label class="flex items-start gap-2 text-sm text-body">
                    <input type="checkbox" wire:model.live="modeOffline" class="mt-0.5 size-4 shrink-0 accent-primary" />
                    Nasabah tidak memakai aplikasi / tidak punya HP (mode offline)
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-body" for="nama-lengkap">Nama lengkap</label>
                        <input type="text" wire:model="nama" id="nama-lengkap" placeholder="Contoh: Budi Santoso"
                            class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                        @error('nama') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                    </div>
                    @if(! $modeOffline)
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-body" for="no-hp">No. HP (WhatsApp)</label>
                        <input type="text" wire:model="noHp" id="no-hp" placeholder="08xxxxxxxxxx" inputmode="tel"
                            class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base tabular-nums text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                        @error('noHp') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-body" for="alamat-lengkap">Alamat lengkap</label>
                        <textarea wire:model="alamat" rows="2" id="alamat-lengkap" placeholder="Jl. Raya No. 123, RT 01/RW 02"
                            class="w-full rounded-xl border border-hairline-strong bg-white px-4 py-3 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"></textarea>
                        @error('alamat') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-body">Tanggal lahir</label>
                        <x-ui.tanggal wire:model="tanggalLahir" :max="now()->toDateString()"
                            class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                        @error('tanggalLahir') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-body" for="jenis-kelamin">Jenis kelamin</label>
                        <select wire:model="jenisKelamin" id="jenis-kelamin"
                            class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-body" for="pekerjaan">Pekerjaan</label>
                        <input type="text" wire:model="pekerjaan" id="pekerjaan" placeholder="Contoh: Pedagang atau wiraswasta"
                            class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove>Daftarkan nasabah</span>
                        <span wire:loading>Memproses...</span>
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="inline-flex h-12 items-center justify-center rounded-full border border-hairline-strong bg-white px-5 text-base font-medium text-ink transition hover:bg-inset dark:bg-surface">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Registered Nasabah List --}}
    <div class="overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
        <div class="flex items-center justify-between gap-3 border-b border-hairline px-5 py-4">
            <h2 class="text-sm font-medium text-ink">Nasabah yang Anda daftarkan</h2>
            <span class="rounded-full bg-inset px-2.5 py-1 text-xs font-medium text-body">Total: {{ $nasabahList->total() }} nasabah</span>
        </div>

        <div class="divide-y divide-hairline">
            @forelse($nasabahList as $item)
                <div class="flex min-h-16 items-start justify-between gap-3 px-4 py-3.5">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-on-primary-soft" aria-hidden="true">
                            {{ strtoupper(substr($item->nama, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-base font-medium text-ink">{{ $item->nama }}</p>
                            <p class="mt-0.5 truncate text-sm text-mute">{{ $item->alamat }}</p>
                            <p class="mt-0.5 truncate text-sm text-mute">{{ $item->user?->no_hp ?? '— (offline)' }}</p>
                            @if($item->user?->isOffline())
                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-inset px-2 py-1 text-xs font-medium text-body">
                                    <flux:icon.signal-slash class="size-3.5" aria-hidden="true" />
                                    Mode Offline
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <x-ui.status-chip domain="pendaftaran" status="{{ $item->status_pendaftaran }}" />
                        <p class="mt-1.5 text-xs text-mute">{{ $item->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center gap-2 px-5 py-14">
                    <div class="flex size-12 items-center justify-center rounded-full bg-inset text-mute" aria-hidden="true">
                        <flux:icon.users class="size-6" />
                    </div>
                    <p class="text-sm font-medium text-mute">Belum ada nasabah yang Anda daftarkan.</p>
                </div>
            @endforelse
        </div>
        @if($nasabahList->hasPages())
            <div class="border-t border-hairline px-4 py-3">
                {{ $nasabahList->links() }}
            </div>
        @endif
    </div>
</div>
