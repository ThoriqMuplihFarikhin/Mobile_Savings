@props([
    'judul' => '',
    'keterangan' => '',
    'nominal' => 0,
    'tanda' => null,
    'kategori' => 'hijau',
    'statusDomain' => null,
    'statusNilai' => null,
    'statusTeks' => null,
    'aksiHref' => null,
    'aksiLabel' => null,
])

<div data-test="baris-riwayat" {{ $attributes->merge(['class' => 'flex items-center justify-between min-h-[64px] py-3 px-1 border-b border-hairline last:border-none transition hover:bg-surface-inset/40']) }}>
    <div class="flex items-center gap-3 min-w-0 flex-1 pr-3">
        <x-ui.tile :kategori="$kategori" :ukuran="40" class="shrink-0">
            {{ $slot }}
        </x-ui.tile>
        <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-ink leading-tight truncate">{{ $judul }}</span>
            <span class="text-xs text-mute mt-0.5 truncate">{{ $keterangan }}</span>
        </div>
    </div>

    <div class="flex flex-col items-end gap-0.5 shrink-0 text-right">
        <span class="text-sm font-semibold tabular-nums {{ $tanda === '+' ? 'text-primary' : 'text-ink' }}">
            <x-ui.rupiah :nilai="$nominal" :tanda="$tanda" />
        </span>
        @if ($statusDomain && $statusNilai)
            <x-ui.status-chip :domain="$statusDomain" :status="$statusNilai" :teks="$statusTeks" />
        @endif
        @if ($aksiHref && $aksiLabel)
            <a href="{{ $aksiHref }}"
               @if(str_contains($aksiHref, 'struk'))
                   @click.prevent="$dispatch('buka-struk', { url: '{{ $aksiHref }}' })"
               @endif
               class="text-xs font-semibold text-primary hover:underline leading-tight cursor-pointer">
                {{ $aksiLabel }}
            </a>
        @endif
    </div>
</div>
