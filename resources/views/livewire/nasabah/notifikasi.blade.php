<div class="mx-auto max-w-3xl space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Notifikasi</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Pesan dan informasi terbaru untuk Anda.</p>
        </div>
        <button wire:click="markAllRead"
            class="flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 active:scale-95">
            <flux:icon.check class="size-4 text-indigo-600" />
            <span>Tandai Semua Dibaca</span>
        </button>
    </div>

    {{-- Notification List --}}
    <div class="space-y-2">
        @forelse($notifikasi as $item)
            <div wire:click="markAsRead({{ $item->id }})"
                class="group flex cursor-pointer items-start gap-4 rounded-3xl p-4 transition {{ $item->is_read ? 'bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-100 dark:border-zinc-700/40' : 'bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700/60 shadow-sm hover:shadow-md' }}">
                {{-- Icon --}}
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $item->is_read ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-400 dark:text-zinc-500' : 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' }}">
                    @if($item->is_read)
                        <flux:icon.bell-slash class="size-5" />
                    @else
                        <flux:icon.bell class="size-5" />
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold {{ $item->is_read ? 'text-zinc-500 dark:text-zinc-400' : 'text-zinc-900 dark:text-white' }}">
                        {{ $item->judul }}
                    </p>
                    <p class="mt-0.5 text-xs leading-relaxed {{ $item->is_read ? 'text-zinc-400 dark:text-zinc-500' : 'text-zinc-600 dark:text-zinc-300' }}">
                        {{ $item->pesan }}
                    </p>
                    <p class="mt-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                        {{ $item->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Unread Indicator --}}
                @if(!$item->is_read)
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500 shadow-[0_0_6px_rgba(99,102,241,0.5)]"></span>
                @endif
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.bell class="size-7" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Tidak Ada Notifikasi</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Anda akan mendapat notifikasi untuk setiap aktivitas tabungan.</p>
            </div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
        <div class="mt-4">{{ $notifikasi->links() }}</div>
    @endif
</div>
