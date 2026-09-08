<div>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Notifikasi</h1>
            <p class="mt-1 text-sm text-[#888888]">Pesan dan informasi terbaru untuk Anda.</p>
        </div>
        <button wire:click="markAllRead"
            class="shrink-0 text-sm text-[#0070f3] underline-offset-2 hover:underline">
            Tandai semua dibaca
        </button>
    </div>

    {{-- Notification List --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        @forelse($notifikasi as $item)
            <div wire:click="markAsRead({{ $item->id }})"
                class="group flex cursor-pointer items-start gap-4 px-5 py-4 transition hover:bg-[#fafafa]
                    {{ !$loop->last ? 'border-b border-[#ebebeb]' : '' }}">
                {{-- Icon --}}
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                    {{ $item->is_read ? 'bg-[#fafafa] text-[#888888]' : 'bg-[#d3e5ff] text-[#0070f3]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#171717] {{ $item->is_read ? 'opacity-70' : '' }}">
                        {{ $item->judul }}
                    </p>
                    <p class="mt-0.5 text-sm text-[#4d4d4d] {{ $item->is_read ? 'opacity-60' : '' }}">
                        {{ $item->pesan }}
                    </p>
                    <p class="mt-1.5 font-mono text-xs text-[#888888]">
                        {{ $item->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Unread dot --}}
                @if(!$item->is_read)
                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[#0070f3]"></span>
                @endif
            </div>
        @empty
            <div class="px-5 py-16">
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#a1a1a1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#171717]">Tidak ada notifikasi</p>
                        <p class="mt-1 text-sm text-[#888888]">Anda akan mendapat notifikasi untuk setiap aktivitas tabungan.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifikasi->hasPages())
        <div class="mt-4">{{ $notifikasi->links() }}</div>
    @endif
</div>
