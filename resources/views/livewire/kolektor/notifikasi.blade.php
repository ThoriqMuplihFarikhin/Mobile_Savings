<div class="mx-auto max-w-2xl space-y-4 pb-20">
    {{-- Top Header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" wire:navigate
               class="flex size-10 shrink-0 items-center justify-center rounded-full border border-hairline-strong bg-white text-ink transition hover:bg-inset active:scale-95 dark:bg-surface dark:text-zinc-200"
               aria-label="Kembali ke beranda">
                <flux:icon.arrow-left class="size-5" />
            </a>
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-ink dark:text-zinc-100">Notifikasi</h1>
                <p class="text-xs text-mute">
                    @if($unreadCount > 0)
                        {{ $unreadCount }} pesan belum dibaca
                    @else
                        Semua pesan telah dibaca
                    @endif
                </p>
            </div>
        </div>

        @if($unreadCount > 0)
            <button type="button" wire:click="markAllRead"
                class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset active:scale-95 dark:bg-surface dark:text-zinc-200">
                <flux:icon.check class="size-3.5 text-primary" aria-hidden="true" />
                <span>Tandai dibaca</span>
            </button>
        @endif
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="space-y-2">
        @forelse($notifikasi as $item)
            @php
                $judulLower = strtolower($item->judul);
                $kategoriUbin = match(true) {
                    str_contains($judulLower, 'penarikan') => 'biru',
                    str_contains($judulLower, 'setoran') || str_contains($judulLower, 'kantor') => 'hijau',
                    str_contains($judulLower, 'tunggakan') || str_contains($judulLower, 'peringatan') => 'kuning',
                    str_contains($judulLower, 'jadwal') || str_contains($judulLower, 'rute') => 'teal',
                    default => 'netral',
                };
            @endphp
            <button type="button" wire:click="markAsRead({{ $item->id }})"
                class="flex w-full items-start gap-3 rounded-[20px] p-3.5 text-left transition {{ $item->is_read ? 'border border-hairline-warm bg-white/70 opacity-85 dark:border-hairline/60 dark:bg-surface/70' : 'border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface' }}">
                <x-ui.tile :kategori="$item->is_read ? 'netral' : $kategoriUbin" ukuran="40" aria-hidden="true">
                    @if(str_contains($judulLower, 'setoran'))
                        <flux:icon.arrow-down-tray class="size-4" />
                    @elseif(str_contains($judulLower, 'penarikan'))
                        <flux:icon.arrow-up-tray class="size-4" />
                    @elseif(str_contains($judulLower, 'jadwal'))
                        <flux:icon.calendar-days class="size-4" />
                    @else
                        <flux:icon.bell class="size-4" />
                    @endif
                </x-ui.tile>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <p class="truncate text-sm {{ $item->is_read ? 'text-mute font-medium' : 'font-semibold text-ink dark:text-zinc-100' }}">
                            {{ $item->judul }}
                        </p>
                        @if(!$item->is_read)
                            <span class="size-2 shrink-0 rounded-full bg-primary" aria-label="Belum dibaca"></span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs leading-relaxed {{ $item->is_read ? 'text-mute' : 'text-body dark:text-zinc-300' }}">
                        {{ $item->pesan }}
                    </p>
                    <p class="mt-1 text-xs text-mute">
                        {{ $item->created_at?->diffForHumans() ?? '-' }}
                    </p>
                </div>
            </button>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-inset text-mute dark:bg-zinc-800" aria-hidden="true">
                    <flux:icon.bell-slash class="size-6" />
                </div>
                <p class="mt-3 text-sm font-semibold text-ink dark:text-zinc-100">Belum ada notifikasi</p>
                <p class="mt-1 text-xs text-mute">Pemberitahuan aktivitas setoran, penarikan, dan jadwal akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
        <div class="pt-2">
            {{ $notifikasi->links() }}
        </div>
    @endif
</div>
