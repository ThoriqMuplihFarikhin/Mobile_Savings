@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('icon-bg', 'bg-inset text-mute dark:bg-zinc-800 dark:text-zinc-400')

@section('icon')
    <svg class="size-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
    </svg>
@endsection

@section('heading', 'Halaman tidak ditemukan')

@section('message', 'Alamat yang Anda tuju mungkin salah, telah dihapus, atau sudah dipindahkan.')

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
