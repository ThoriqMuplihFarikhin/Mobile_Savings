<div class="space-y-6">
    {{-- Judul halaman --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Notifikasi</h1>
            <p class="mt-1 text-sm text-mute">Pesan dan informasi terbaru untuk Anda.</p>
        </div>
        <button wire:click="markAllRead"
            class="inline-flex min-h-11 shrink-0 items-center gap-1.5 text-sm font-medium text-primary underline underline-offset-4">
            <flux:icon.check class="size-4" aria-hidden="true" />
            <span>Tandai semua dibaca</span>
        </button>
    </div>

    {{-- Daftar notifikasi --}}
    <div class="space-y-2">
        @forelse($notifikasi as $item)
            <button type="button" wire:click="markAsRead({{ $item->id }})"
                class="flex w-full items-start gap-3 rounded-[20px] p-4 text-left transition {{ $item->is_read ? 'bg-inset' : 'border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface' }}">
                {{-- §14 baris Notifikasi: jenis_notifikasi kini terdaftar lewat App\Support\JenisNotifikasi,
                    nilai hanya membedakan channel + pin_awal, jadi fallback ubin hijau. --}}
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    @if($item->is_read)
                        <flux:icon.bell-slash class="size-5" />
                    @else
                        <flux:icon.bell class="size-5" />
                    @endif
                </x-ui.tile>

                <span class="min-w-0 flex-1">
                    <span class="block text-base {{ $item->is_read ? 'text-mute' : 'font-medium text-ink' }}">
                        {{ $item->judul }}
                    </span>
                    <span class="mt-0.5 block text-sm leading-relaxed {{ $item->is_read ? 'text-mute' : 'text-body' }}">
                        {{ $item->pesan }}
                    </span>
                    <span class="mt-1 block text-sm text-mute">
                        {{ $item->created_at->diffForHumans() }}
                    </span>
                </span>

                @if(!$item->is_read)
                    <span class="mt-2 size-2 shrink-0 rounded-full bg-primary" aria-label="Belum dibaca"></span>
                @endif
            </button>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h2 class="mt-4 text-base font-semibold text-ink">Belum ada notifikasi</h2>
                <p class="mt-1 text-sm text-mute">Anda akan mendapat notifikasi untuk setiap aktivitas tabungan.</p>
            </div>
        @endforelse
    </div>

    @if($notifikasi->hasPages())
        <div>{{ $notifikasi->links() }}</div>
    @endif
</div>
