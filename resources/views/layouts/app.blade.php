@if(auth()->user()?->isAdmin())
    <x-layouts::admin :title="$title ?? null">
        {{ $slot }}
    </x-layouts::admin>
@else
    <x-layouts::app.sidebar :title="$title ?? null">
        <flux:main class="pb-28 lg:pb-8">
            {{ $slot }}
        </flux:main>
    </x-layouts::app.sidebar>
@endif

