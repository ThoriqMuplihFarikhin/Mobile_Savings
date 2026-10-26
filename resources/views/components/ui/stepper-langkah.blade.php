@props([
    'langkahAktif' => 1,
    'totalLangkah' => 2,
])

<div data-test="stepper-langkah" {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }}>
    @for ($i = 1; $i <= $totalLangkah; $i++)
        <span class="h-1.5 rounded-full transition-all duration-300 {{ $i === (int)$langkahAktif ? 'w-6 bg-primary' : 'w-2 bg-hairline-strong' }}"></span>
    @endfor
</div>
