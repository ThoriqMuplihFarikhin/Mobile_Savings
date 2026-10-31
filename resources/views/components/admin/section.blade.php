@props([
    'title',
])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <h4 class="text-xs uppercase tracking-wider text-mute dark:text-subtle font-semibold mb-2">
        {{ $title }}
    </h4>
    <div class="space-y-2">
        {{ $slot }}
    </div>
</div>
