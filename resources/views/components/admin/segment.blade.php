@props([
    'options' => [],
    'selected' => null,
])

<div {{ $attributes->merge(['class' => 'inline-flex flex-wrap gap-1 rounded-[10px] border border-hairline bg-surface-2 p-[3px] transition-colors']) }} role="group">
    @if(!empty($options))
        @php
            $isAssoc = array_keys($options) !== range(0, count($options) - 1);
        @endphp
        @foreach($options as $val => $label)
            @php
                $key = $isAssoc ? $val : $label;
                $text = $label;
                $isActive = (string)$selected === (string)$key;
                $wireModel = $attributes->wire('model')->value();
            @endphp
            <button
                type="button"
                @if($wireModel)
                    wire:click="$set('{{ $wireModel }}', '{{ $key }}')"
                @endif
                @class([
                    'h-7 rounded-[7px] px-3 text-xs font-medium transition cursor-pointer select-none',
                    'bg-surface font-semibold text-ink shadow-xs' => $isActive,
                    'text-mute hover:text-ink' => ! $isActive,
                ])
            >
                {{ $text }}
            </button>
        @endforeach
    @else
        {{ $slot }}
    @endif
</div>
