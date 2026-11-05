@extends('errors.layout')

@section('title', 'Sesi Sudah Berakhir')
@section('code', '419')
@section('icon-bg', 'bg-warning-soft text-warning-deep dark:bg-warning-soft/30 dark:text-warning-deep')

@section('icon')
    <svg class="size-10" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
    </svg>
@endsection

@section('heading', 'Sesi sudah berakhir')

@section('message', 'Demi keamanan akun Anda, sesi telah kedaluwarsa. Data yang belum tersimpan aman di perangkat.')

@section('actions')
    <a href="{{ route('login') }}"
       class="min-h-11 flex w-full items-center justify-center rounded-xl bg-primary px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-primary-hover active:scale-95 cursor-pointer">
        Masuk lagi
    </a>
    <a href="{{ url('/') }}"
       class="min-h-11 flex w-full items-center justify-center rounded-xl border border-hairline bg-surface px-4 py-2.5 text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
        Ke beranda
    </a>
@endsection
