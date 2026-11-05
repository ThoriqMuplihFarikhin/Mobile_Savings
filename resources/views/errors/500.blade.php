@extends('errors.layout')

@section('title', 'Ada Gangguan di Server')
@section('code', '500')
@section('icon-bg', 'bg-warning-soft text-warning-deep dark:bg-warning-soft/30 dark:text-warning-deep')

@section('icon')
    <svg class="size-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
@endsection

@section('heading', 'Ada gangguan di server')

@section('message', 'Bukan salah Anda. Tim kami sedang menangani kendala ini. Silakan coba beberapa saat lagi.')

@section('actions')
    <button type="button" onclick="window.location.reload()"
            class="min-h-11 flex w-full items-center justify-center rounded-xl bg-primary px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-primary-hover active:scale-95 cursor-pointer">
        Coba lagi
    </button>
    <a href="{{ url('/') }}"
       class="min-h-11 flex w-full items-center justify-center rounded-xl border border-hairline bg-surface px-4 py-2.5 text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
        Ke beranda
    </a>
@endsection
