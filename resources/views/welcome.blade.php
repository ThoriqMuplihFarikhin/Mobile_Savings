<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-[#171717] antialiased">
        <div class="relative flex min-h-screen flex-col overflow-hidden">
            {{-- Vercel-style Atmospheric Mesh Backdrop --}}
            <div class="pointer-events-none absolute -top-40 left-1/2 -z-10 h-[500px] w-[1000px] -translate-x-1/2 opacity-30 blur-3xl"
                 style="background: radial-gradient(circle at 30% 30%, #007cf0 0%, #7928ca 40%, #ff0080 70%, transparent 100%);"></div>

            {{-- Navigation --}}
            <header class="fixed inset-x-0 top-0 z-50 border-b border-[#ebebeb] bg-white/80 backdrop-blur-lg">
                <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#171717] text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold tracking-tight text-[#171717]">Tabungan Digital</span>
                    </div>
                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                                Masuk System
                            </a>
                        @endauth
                    </nav>
                </div>
            </header>

            {{-- Hero Section --}}
            <main class="flex-1 pt-16">
                <div class="mx-auto w-full max-w-6xl px-6 py-20 lg:py-28">
                    <div class="max-w-3xl">
                        <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-[#fafafa] px-3.5 py-1 shadow-[inset_0_0_0_1px_#ebebeb]">
                            <span class="h-2 w-2 rounded-full bg-[#00b536]"></span>
                            <span class="font-mono text-xs text-[#888888]">Sistem Kolektor Keliling v1.0</span>
                        </div>

                        <h1 class="text-5xl font-semibold tracking-tight text-[#171717] sm:text-7xl">
                            Kelola tabungan nasabah secara digital.
                        </h1>

                        <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#4d4d4d]">
                            Platform modern untuk bisnis tabungan keliling. Setoran real-time, penarikan instan, dan pelacakan komprehensif — semuanya dalam satu dashboard.
                        </p>

                        <div class="mt-10 flex flex-wrap items-center gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center gap-2 rounded-full bg-[#171717] px-6 py-3 text-sm font-medium text-white transition hover:opacity-90">
                                    Buka Dashboard
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center gap-2 rounded-full bg-[#171717] px-6 py-3 text-sm font-medium text-white transition hover:opacity-90">
                                    Masuk ke Aplikasi
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </a>
                            @endauth
                        </div>
                    </div>

                    {{-- Feature Grid 3-Up --}}
                    <div class="mt-20 grid gap-6 border-t border-[#ebebeb] pt-16 sm:grid-cols-3">
                        <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#171717]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-base font-semibold text-[#171717]">Tabungan Bebas & Paket Sembako</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#888888]">Dua produk fleksibel: setor bebas nominal atau cicil harian tetap untuk paket sembako cair saat Lebaran.</p>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#171717]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <h3 class="text-base font-semibold text-[#171717]">Rekonsiliasi Kas Kolektor</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#888888]">Monitoring kas di tangan kolektor secara real-time. Pencocokan otomatis saat setor ke kantor dengan audit log lengkap.</p>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#171717]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            </div>
                            <h3 class="text-base font-semibold text-[#171717]">Notifikasi & Komplain Direct</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#888888]">Integrasi notifikasi WhatsApp & in-app. Nasabah memiliki jalur resmi pengajuan komplain yang dapat dilacak statusnya.</p>
                        </div>
                    </div>

                    {{-- Role Spotlight Section --}}
                    <div class="mt-24 border-t border-[#ebebeb] pt-16">
                        <div class="mb-10 max-w-2xl">
                            <span class="font-mono text-xs uppercase tracking-wider text-[#888888]">Tiga Hak Akses Peran</span>
                            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#171717]">Dirancang khusus untuk kebutuhan tiap pengguna.</h2>
                        </div>

                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="rounded-xl bg-[#fafafa] p-6 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <span class="rounded-full bg-[#d3e5ff] px-2.5 py-1 font-mono text-xs text-[#0070f3]">Role Nasabah</span>
                                <h4 class="mt-4 text-lg font-semibold text-[#171717]">Transparansi Saldo Real-Time</h4>
                                <ul class="mt-3 space-y-2 text-sm text-[#4d4d4d]">
                                    <li class="flex items-center gap-2"><span>✓</span> Cek saldo kapan saja tanpa perlu tunggu kolektor</li>
                                    <li class="flex items-center gap-2"><span>✓</span> Pantau progres cicilan paket sembako Lebaran</li>
                                    <li class="flex items-center gap-2"><span>✓</span> Ajukan penarikan dana & lacak komplain</li>
                                </ul>
                            </div>

                            <div class="rounded-xl bg-[#fafafa] p-6 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <span class="rounded-full bg-[#ffefcf] px-2.5 py-1 font-mono text-xs text-[#ab570a]">Role Kolektor</span>
                                <h4 class="mt-4 text-lg font-semibold text-[#171717]">Pencatatan Keliling Fleksibel</h4>
                                <ul class="mt-3 space-y-2 text-sm text-[#4d4d4d]">
                                    <li class="flex items-center gap-2"><span>✓</span> Input setoran langsung dari HP / catatan susulan</li>
                                    <li class="flex items-center gap-2"><span>✓</span> Jadwal kunjungan harian & daftar nasabah binaan</li>
                                    <li class="flex items-center gap-2"><span>✓</span> Fitur penyetoran kas ke kantor & penarikan offline</li>
                                </ul>
                            </div>

                            <div class="rounded-xl bg-[#171717] p-6 text-white shadow-lg">
                                <span class="rounded-full bg-[#d8ccf1] px-2.5 py-1 font-mono text-xs text-[#4c2889]">Role Admin</span>
                                <h4 class="mt-4 text-lg font-semibold text-white">Kontrol Pusat & Rekonsiliasi</h4>
                                <ul class="mt-3 space-y-2 text-sm text-[#a1a1a1]">
                                    <li class="flex items-center gap-2"><span class="text-white">✓</span> Approval penarikan & verifikasi nasabah baru</li>
                                    <li class="flex items-center gap-2"><span class="text-white">✓</span> Rekonsiliasi kas kolektor & audit trail log</li>
                                    <li class="flex items-center gap-2"><span class="text-white">✓</span> Kelola produk paket & laporan pengadaan sembako</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            {{-- Footer --}}
            <footer class="border-t border-[#ebebeb] py-8">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-6 text-sm text-[#888888]">
                    <div>
                        Tabungan Digital &copy; {{ date('Y') }} — Sistem Kolektor Keliling
                    </div>
                    <div class="font-mono text-xs">
                        PRD v1.0 &bull; Security Auth HP+PIN
                    </div>
                </div>
            </footer>
        </div>
        @fluxScripts
    </body>
</html>