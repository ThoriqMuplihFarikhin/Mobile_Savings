<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="pb-28 lg:pb-8">
        {{ $slot }}
    </flux:main>

    @if(auth()->user()?->isAdmin())
        @include('layouts.app.admin-bottom-nav')
    @endif
</x-layouts::app.sidebar>
