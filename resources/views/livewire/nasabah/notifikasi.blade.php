<div class="space-y-6">
    {{-- Judul halaman & Tombol aksi --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Notifikasi</h1>
            <p class="mt-1 text-sm text-mute">Pesan dan informasi terbaru untuk Anda.</p>
        </div>
        <button wire:click="markAllRead"
            class="inline-flex min-h-10 shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-medium text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-surface">
            <flux:icon.check class="size-3.5 text-primary" aria-hidden="true" />
            <span>Tandai semua dibaca</span>
        </button>
    </div>

    {{-- Daftar notifikasi --}}
    <div class="space-y-2.5">
        @forelse($notifikasi as $item)
            @php
                $judulLower = strtolower($item->judul);
                $kategoriNotif = match(true) {
                    str_contains($judulLower, 'setoran') => 'hijau',
                    str_contains($judulLower, 'penarikan') => 'biru',
                    str_contains($judulLower, 'paket') || str_contains($judulLower, 'komitmen') => 'teal',
                    default => 'kuning',
                };
            @endphp
            <button type="button" wire:click="markAsRead({{ $item->id }})"
                class="flex w-full items-start gap-3 rounded-2xl p-4 text-left transition {{ $item->is_read ? 'border border-hairline bg-surface-inset opacity-80' : 'border border-hairline-warm border-l-4 border-l-primary bg-white shadow-sm dark:bg-surface' }}">
                <x-ui.tile :kategori="$kategoriNotif" ukuran="40" aria-hidden="true">
                    @if(str_contains($judulLower, 'setoran'))
                        <flux:icon.arrow-down-left class="size-4" />
                    @elseif(str_contains($judulLower, 'penarikan'))
                        <flux:icon.arrow-up-right class="size-4" />
                    @elseif(str_contains($judulLower, 'paket') || str_contains($judulLower, 'komitmen'))
                        <flux:icon.shopping-bag class="size-4" />
                    @else
                        <flux:icon.bell class="size-4" />
                    @endif
                </x-ui.tile>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="block text-sm {{ $item->is_read ? 'text-mute font-medium' : 'font-semibold text-ink' }} truncate">
                            {{ $item->judul }}
                        </span>
                        @if(!$item->is_read)
                            <span class="size-2 shrink-0 rounded-full bg-primary" aria-label="Belum dibaca"></span>
                        @endif
                    </div>
                    <span class="mt-0.5 block text-xs leading-relaxed {{ $item->is_read ? 'text-mute' : 'text-body' }}">
                        {{ $item->pesan }}
                    </span>
                    <span class="mt-1.5 block text-xs text-mute">
                        {{ $item->created_at->diffForHumans() }}
                    </span>
                </div>
            </button>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h2 class="mt-4 text-base font-semibold text-ink">Belum ada notifikasi</h2>
                <p class="mt-1 text-xs text-mute">Anda akan mendapat notifikasi untuk setiap aktivitas tabungan.</p>
            </div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
        <div>{{ $notifikasi->links() }}</div>
    @endif
</div>
