@props([
    'toolbar' => null,
    'head' => null,
    'body' => null,
    'cards' => null,
    'footer' => null,
    'count' => null,
    'total' => null,
    'pagination' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-[14px] border border-hairline bg-surface shadow-xs transition-colors']) }}>
    @if(isset($toolbar))
        {{ $toolbar }}
    @endif

    {{-- Tabel Desktop (≥ md) --}}
    <div class="overflow-x-auto">
        <table class="hidden md:table w-full text-left text-sm border-collapse">
            @if(isset($head))
                <thead>
                    <tr class="bg-surface-2 text-mute border-b border-hairline">
                        {{ $head }}
                    </tr>
                </thead>
            @endif
            @if(isset($body))
                <tbody class="divide-y divide-hairline">
                    {{ $body }}
                </tbody>
            @else
                {{ $slot }}
            @endif
        </table>
    </div>

    {{-- Daftar Kartu Mobile (< md) --}}
    @if(isset($cards))
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            {{ $cards }}
        </div>
    @endif

    {{-- Footer / Paginasi --}}
    @if(isset($footer))
        <div class="border-t border-hairline px-3.5 py-2.5 text-xs text-mute flex justify-between items-center bg-surface">
            {{ $footer }}
        </div>
    @elseif($count !== null || $total !== null || isset($pagination))
        <div class="border-t border-hairline px-3.5 py-2.5 text-xs text-mute flex flex-wrap justify-between items-center gap-2 bg-surface">
            @if($count !== null && $total !== null)
                <span>Menampilkan {{ $count }} dari {{ $total }} data</span>
            @elseif($count !== null)
                <span>Menampilkan {{ $count }} data</span>
            @else
                <span></span>
            @endif

            @if(isset($pagination))
                <div>{{ $pagination }}</div>
            @endif
        </div>
    @endif
</div>
