@extends('errors.layout')

@section('title', 'Akses Tidak Diizinkan')
@section('code', '403')
@section('icon-bg', 'bg-danger-soft text-danger-deep dark:bg-red-950/60 dark:text-red-400')

@section('icon')
    <svg class="size-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Zm0 13.036h.007v.008H12v-.008Z" />
    </svg>
@endsection

@section('heading', 'Akses tidak diizinkan')

@section('message', 'Anda tidak memiliki hak akses atau wewenang untuk membuka halaman ini.')

@section('actions')
    <a href="{{ url('/') }}"
       class="flex w-full items-center justify-center rounded-full bg-primary py-2.5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95">
        Ke beranda
    </a>
    <button type="button" onclick="window.history.length > 1 ? window.history.back() : (window.location.href='{{ url('/') }}')"
            class="flex w-full items-center justify-center rounded-full border border-hairline-strong bg-white py-2.5 text-xs font-semibold text-body transition hover:bg-inset active:scale-95 dark:border-hairline dark:bg-surface dark:text-zinc-200">
        Kembali ke halaman sebelumnya
    </button>
@endsection
