<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-3 border-b border-hairline p-3 sm:px-3.5 bg-surface transition-colors']) }}>
    @if(isset($filters) || isset($actions))
        <div class="flex flex-wrap items-center gap-2">
            {{ $filters ?? '' }}
        </div>
        @if(isset($actions))
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    @else
        {{ $slot }}
    @endif
</div>
