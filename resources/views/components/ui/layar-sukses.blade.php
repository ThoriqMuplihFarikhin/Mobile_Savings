@props([
    'judul' => 'Aksi Berhasil',
    'pesan' => '',
    'nominal' => null,
    'tautanTeks' => 'Selesai',
    'tautanRoute' => null,
])

<div data-test="layar-sukses" class="flex flex-col items-center justify-center text-center p-6 py-10 min-h-[60vh]">
    {{-- Lingkaran Centang Beranimasi Lembut --}}
    <div class="relative flex items-center justify-center mb-6">
        <div class="absolute h-28 w-28 rounded-full bg-primary-soft/50 animate-ping opacity-30"></div>
        <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-primary-soft border-4 border-white shadow-md text-primary">
            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
    </div>

    <h2 class="text-2xl font-bold tracking-tight text-ink">{{ $judul }}</h2>
    @if ($pesan)
        <p class="mt-2 text-sm text-mute max-w-xs">{{ $pesan }}</p>
    @endif

    @if ($nominal !== null)
        <div class="mt-6 w-full max-w-xs rounded-2xl bg-surface border border-hairline-warm p-4 shadow-sm">
            <span class="block text-xs text-mute mb-1">Nominal</span>
            <span class="text-2xl font-bold text-ink tabular-nums">
                <x-ui.rupiah :nilai="$nominal" />
            </span>
        </div>
    @endif

    {{ $slot }}

    @if ($tautanRoute)
        <div class="mt-8 w-full max-w-xs">
            <a href="{{ $tautanRoute }}" wire:navigate
               class="flex h-12 w-full items-center justify-center rounded-full bg-primary font-semibold text-on-primary shadow-md hover:bg-primary-hover transition">
                {{ $tautanTeks }}
            </a>
        </div>
    @endif
</div>
