<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabungan Digital - Sistem Kolektor Keliling Modern & Aman</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #fafaf9;
        }

        /* Glassmorphism custom effects */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }

        .glass-card-dark {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Senior Large Text Mode Overrides */
        .large-text-mode h1 { font-size: 3.5rem !important; line-height: 1.15 !important; }
        .large-text-mode h2 { font-size: 2.5rem !important; }
        .large-text-mode h3 { font-size: 1.75rem !important; }
        .large-text-mode p, .large-text-mode li, .large-text-mode button, .large-text-mode label, .large-text-mode span { font-size: 1.25rem !important; line-height: 1.6 !important; }
        .large-text-mode .badge-text { font-size: 1rem !important; }

        /* Custom range slider styling */
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 24px;
            width: 24px;
            border-radius: 50%;
            background: #16a34a;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            transition: transform 0.1s ease;
        }
        input[type=range]::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }
    </style>
</head>
<body id="bodyRoot" class="min-h-screen text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Floating Header Navigation -->
    <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300 border-b border-stone-200/80 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Brand Logo -->
            <a href="#" class="flex items-center gap-3 group">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white shadow-md shadow-brand-500/20 transition-transform group-hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold tracking-tight text-slate-900 leading-tight">Tabungan<span class="text-brand-600">Digital</span></span>
                    <span class="text-xs font-medium text-stone-500">Kolektor Keliling Amanah</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-base font-semibold text-slate-700">
                <a href="#simulasi" class="transition hover:text-brand-600">Simulasi</a>
                <a href="#peran" class="transition hover:text-brand-600">Peran Pengguna</a>
                <a href="#fitur" class="transition hover:text-brand-600">Fitur Utama</a>
                <a href="#testimoni" class="transition hover:text-brand-600">Cerita Kami</a>
                <a href="#faq" class="transition hover:text-brand-600">Bantuan (FAQ)</a>
            </nav>

            <!-- Accessibility & Action Buttons -->
            <div class="flex items-center gap-3">
                <!-- Text Size Toggle Button for Elderly Friendly Accessibility -->
                <button id="textSizeToggleBtn" onclick="toggleTextSize()" aria-label="Ubah Ukuran Teks" class="flex items-center gap-1.5 rounded-full border border-stone-300 bg-stone-100 px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-stone-200 active:scale-95 shadow-sm" title="Mode Teks Besar Ramah Orang Tua">
                    <span class="font-bold text-sm">A<sup>+</sup></span>
                    <span class="hidden sm:inline badge-text">Teks Besar</span>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-600 hover:shadow-brand-500/25 active:scale-95">
                        Ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-600 hover:shadow-brand-500/25 active:scale-95">
                        Masuk Sistem
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="inline-flex md:hidden items-center justify-center p-2 rounded-xl text-slate-700 hover:bg-stone-100 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Nav -->
        <div id="mobileMenu" class="hidden md:hidden border-b border-stone-200 bg-white px-6 py-4 space-y-3">
            <a href="#simulasi" onclick="toggleMobileMenu()" class="block font-medium text-slate-700 hover:text-brand-600">Simulasi Tabungan</a>
            <a href="#peran" onclick="toggleMobileMenu()" class="block font-medium text-slate-700 hover:text-brand-600">Peran Pengguna</a>
            <a href="#fitur" onclick="toggleMobileMenu()" class="block font-medium text-slate-700 hover:text-brand-600">Fitur Utama</a>
            <a href="#testimoni" onclick="toggleMobileMenu()" class="block font-medium text-slate-700 hover:text-brand-600">Cerita Pengguna</a>
            <a href="#faq" onclick="toggleMobileMenu()" class="block font-medium text-slate-700 hover:text-brand-600">Pertanyaan FAQ</a>
            <div class="pt-2">
                @auth
                    <a href="{{ route('dashboard') }}" onclick="toggleMobileMenu()" class="block w-full text-center rounded-xl bg-slate-900 py-2.5 text-white font-medium">Ke Dashboard</a>
                @else
                    <a href="{{ route('login') }}" onclick="toggleMobileMenu()" class="block w-full text-center rounded-xl bg-slate-900 py-2.5 text-white font-medium">Masuk Sistem</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 md:pt-40 md:pb-28 overflow-hidden">
        <!-- Background Soft Gradients -->
        <div class="pointer-events-none absolute -top-24 left-1/2 -z-10 h-[600px] w-[1000px] -translate-x-1/2 opacity-40 blur-3xl"
             style="background: radial-gradient(circle at 40% 30%, #86efac 0%, #a5f3fc 35%, #c084fc 70%, transparent 100%);"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Text Content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-1.5 border border-emerald-200/80 shadow-sm">
                        <span class="flex h-2.5 w-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                        <span class="font-semibold text-xs sm:text-sm text-brand-700">Sistem Kolektor Keliling Terpercaya &amp; Transparan</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.15]">
                        Nabung Lebih <span class="bg-gradient-to-r from-brand-600 to-teal-600 bg-clip-text text-transparent">Mudah, Aman</span> &amp; Berkah Untuk Masa Depan
                    </h1>

                    <p class="text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Tidak perlu repot ke bank! Setor tabungan harian langsung ke petugas kolektor resmi di rumah Anda. Saldo tercatat <strong class="text-slate-800">real-time di HP</strong>, aman, dan tanpa biaya tersembunyi.
                    </p>

                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="#simulasi" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-brand-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700 hover:scale-[1.02] active:scale-95">
                            <span>Hitung Simulasi Hasil</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="#peran" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border-2 border-stone-300 bg-white px-7 py-3.5 text-base font-bold text-slate-800 transition hover:bg-stone-50 hover:border-slate-400 active:scale-95 shadow-sm">
                            <span>Pelajari Cara Kerja</span>
                        </a>
                    </div>

                    <!-- Static Highlights (tanpa angka bisnis — D10) -->
                    <div class="pt-6 grid grid-cols-3 gap-4 border-t border-stone-200/80 text-center lg:text-left">
                        <div class="space-y-1">
                            <p class="text-2xl font-extrabold text-slate-900">Real-time</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-500">Saldo Selalu Terupdate</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-2xl font-extrabold text-slate-900">Aman</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-500">PIN + Audit Trail</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-2xl font-extrabold text-slate-900">Transparan</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-500">Struk Digital Otomatis</p>
                        </div>
                    </div>
                </div>

                <!-- Right Visual Mockup Card (Interactive Live Preview Card) -->
                <div class="lg:col-span-5 relative">
                    <!-- Decorative background ring -->
                    <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-emerald-400 to-indigo-400 opacity-30 blur-xl"></div>
                    
                    <div class="relative glass-card rounded-3xl p-6 sm:p-8 shadow-2xl border border-white">
                        <!-- Top Header Mockup -->
                        <div class="flex items-center justify-between border-b border-stone-200/80 pb-4 mb-5">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-brand-100 border border-brand-300 flex items-center justify-center text-brand-700 font-bold">
                                    @auth
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    @else
                                        IB
                                    @endauth
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">Selamat datang,</p>
                                    <p class="text-base font-bold text-slate-900">
                                        @auth
                                            {{ auth()->user()->name }}
                                        @else
                                            Ibu Nurhaliza
                                        @endauth
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                @auth
                                    {{ ucfirst(auth()->user()->role) }} Aktif
                                @else
                                    Nasabah Aktif
                                @endauth
                            </span>
                        </div>

                        <!-- Main Balance Card -->
                        <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 p-6 text-white shadow-xl relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-emerald-500/20 blur-2xl"></div>
                            <p class="text-xs font-medium text-slate-300">
                                @auth
                                    Total Saldo Tabungan Anda
                                @else
                                    Contoh Tampilan: Total Saldo Tabungan
                                @endauth
                            </p>
                            <p class="text-3xl font-extrabold tracking-tight text-emerald-400 mt-1 mb-4">
                                @auth
                                    @php
                                        $userSaldo = auth()->user()->saldoProduks ? (float) auth()->user()->saldoProduks->sum('saldo') : 0.0;
                                    @endphp
                                    Rp {{ number_format($userSaldo, 0, ',', '.') }}
                                @else
                                    Rp 4.850.000
                                @endauth
                            </p>

                            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-700/80 text-xs">
                                <div>
                                    <span class="text-slate-400 block">Tabungan Bebas</span>
                                    <span class="font-semibold text-white">
                                        @auth
                                            @php
                                                $saldoBebas = auth()->user()->saldoProduks ? (float) auth()->user()->saldoProduks->filter(fn($s) => $s->produk?->isBebas())->sum('saldo') : 0.0;
                                            @endphp
                                            Rp {{ number_format($saldoBebas, 0, ',', '.') }}
                                        @else
                                            Rp 2.350.000
                                        @endauth
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Paket Sembako Lebaran</span>
                                    <span class="font-semibold text-white">
                                        @auth
                                            @php
                                                $saldoPaket = auth()->user()->saldoProduks ? (float) auth()->user()->saldoProduks->filter(fn($s) => $s->produk?->isPaket())->sum('saldo') : 0.0;
                                            @endphp
                                            Rp {{ number_format($saldoPaket, 0, ',', '.') }}
                                        @else
                                            Rp 2.500.000
                                        @endauth
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Simulation -->
                        <div class="mt-5 space-y-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Aktivitas Terakhir</p>
                            
                            <div class="flex items-center justify-between rounded-xl bg-stone-50 p-3 border border-stone-200/70">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">✓</div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">Setoran Kolektor (Mas Rudi)</p>
                                        <p class="text-[11px] text-slate-500">Hari ini, 10:15 WIB</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">+ Rp 20.000</span>
                            </div>

                            <div class="flex items-center justify-between rounded-xl bg-stone-50 p-3 border border-stone-200/70">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">Struk Notifikasi WhatsApp</p>
                                        <p class="text-[11px] text-slate-500">Terkirim Otomatis</p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">Sukses</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Interactive Calculator Section -->
    <section id="simulasi" class="py-20 bg-stone-100/80 border-y border-stone-200">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="rounded-full bg-brand-100 px-3.5 py-1 text-xs font-bold text-brand-800 uppercase tracking-wide">Kalkulator Pintar</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Simulasi Hasil Tabungan Anda</h2>
                <p class="mt-3 text-base sm:text-lg text-slate-600">Geser nominal setoran harian di bawah ini dan lihat betapa cepatnya tabungan Anda terkumpul!</p>
            </div>

            <!-- Calculator Glass Card Container -->
            <div class="glass-card rounded-3xl p-6 sm:p-10 shadow-xl border border-white">
                <div class="grid lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Left Options / Input controls -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Product Type Selector -->
                        <div>
                            <label class="block text-sm font-bold text-slate-800 mb-3">Pilih Jenis Tabungan / Paket:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="productSelectorContainer">
                                @if(isset($produkList) && $produkList->count() > 0)
                                    @foreach($produkList as $idx => $p)
                                        <button id="btnProduct{{ $p->id }}" onclick="selectDbProduct({{ $p->id }}, this)" class="product-btn flex flex-col items-center justify-center rounded-2xl border-2 {{ $idx === 0 ? 'border-brand-600 bg-emerald-50/60' : 'border-stone-200 bg-white' }} p-4 text-center transition hover:bg-emerald-50 active:scale-98">
                                            <span class="text-brand-600 mb-1">
                                                @if($p->isPaket())
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                @else
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                                @endif
                                            </span>
                                            <span class="text-sm font-bold text-slate-900">{{ $p->nama }}</span>
                                            <span class="text-xs text-slate-500 mt-0.5">
                                                @if($p->isPaket())
                                                    Rp {{ number_format($p->harga_per_hari ?? $p->minimal_setor, 0, ',', '.') }}/hari
                                                @else
                                                    Setor Bebas &amp; Tarik Kapan Saja
                                                @endif
                                            </span>
                                        </button>
                                    @endforeach
                                @else
                                    <button id="btnTabunganBebas" onclick="setProductType('bebas')" class="product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-brand-600 bg-emerald-50/60 p-4 text-center transition hover:bg-emerald-50 active:scale-98">
                                        <span class="text-brand-600 mb-1">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                        </span>
                                        <span class="text-sm font-bold text-slate-900">Tabungan Bebas</span>
                                        <span class="text-xs text-slate-500 mt-0.5">Setor Bebas &amp; Tarik Kapan Saja</span>
                                    </button>
                                    <button id="btnPaketSembako" onclick="setProductType('sembako')" class="product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-stone-200 bg-white p-4 text-center transition hover:bg-stone-50 active:scale-98">
                                        <span class="text-brand-600 mb-1">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </span>
                                        <span class="text-sm font-bold text-slate-900">Paket Sembako Lebaran</span>
                                        <span class="text-xs text-slate-500 mt-0.5">Rutin Harian + Bonus Sembako</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Daily Amount Slider -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label for="dailyRange" class="text-sm font-bold text-slate-800">Setoran Harian Anda:</label>
                                <span id="dailyAmountText" class="text-2xl font-black text-brand-600">Rp 20.000</span>
                            </div>
                            <input type="range" id="dailyRange" min="5000" max="100000" step="5000" value="20000" oninput="updateCalculator()" class="w-full h-3 bg-stone-200 rounded-lg appearance-none cursor-pointer accent-brand-600">
                            <div class="flex justify-between text-xs font-semibold text-slate-400">
                                <span>Rp 5.000 / hari</span>
                                <span>Rp 50.000</span>
                                <span>Rp 100.000 / hari</span>
                            </div>
                        </div>

                        <!-- Duration Selector -->
                        <div>
                            <label class="block text-sm font-bold text-slate-800 mb-3">Pilih Jangka Waktu:</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button onclick="setDuration(30, this)" class="dur-btn rounded-xl border border-stone-300 py-2.5 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-stone-50">30 Hari (1 Bln)</button>
                                <button onclick="setDuration(180, this)" class="dur-btn rounded-xl border border-stone-300 py-2.5 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-stone-50">6 Bulan</button>
                                <button onclick="setDuration(365, this)" class="dur-btn active-dur rounded-xl border-2 border-brand-600 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-600 shadow-sm">1 Tahun / Lebaran</button>
                            </div>
                        </div>

                    </div>

                    <!-- Right Result Output Card -->
                    <div class="lg:col-span-5 rounded-2xl bg-slate-900 p-6 sm:p-8 text-white space-y-6 shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 h-36 w-36 rounded-full bg-emerald-500/10 blur-3xl"></div>
                        
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider text-emerald-400">Estimasi Hasil Tabungan</span>
                            <p id="calcTotalAmount" class="text-4xl sm:text-5xl font-black text-white mt-2 tracking-tight">Rp 7.300.000</p>
                            <p id="calcSubText" class="text-xs text-slate-400 mt-1">Terkumpul dalam 365 hari penyetoran</p>
                        </div>

                        <!-- Bonus / Perk Info -->
                        <div id="bonusContainer" class="rounded-xl bg-slate-800/90 border border-slate-700 p-4 space-y-2">
                            <p class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V6a2 2 0 10-2 2h2zm9 3H3v10a2 2 0 002 2h14a2 2 0 002-2V11z"/></svg>
                                Bonus Spesial Termasuk:
                            </p>
                            <p id="bonusText" class="text-xs text-slate-200 leading-relaxed">
                                Paket Sembako Lengkap (Beras 10kg, Minyak Goreng 2L, Gula, Sirup) cair menjelang Hari Raya Lebaran!
                            </p>
                        </div>

                        @auth
                            <a href="{{ route('dashboard') }}" class="block w-full text-center rounded-xl bg-gradient-to-r from-brand-500 to-teal-500 py-3.5 text-sm font-bold text-white shadow-lg transition hover:from-brand-600 hover:to-teal-600 active:scale-95">
                                Ke Dashboard Saya
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="block w-full text-center rounded-xl bg-gradient-to-r from-brand-500 to-teal-500 py-3.5 text-sm font-bold text-white shadow-lg transition hover:from-brand-600 hover:to-teal-600 active:scale-95">
                                Mulai Menabung Sekarang
                            </a>
                        @endauth
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- User Roles Section (Interactive Tabs) -->
    <section id="peran" class="py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="rounded-full bg-indigo-100 px-3.5 py-1 text-xs font-bold text-indigo-800 uppercase tracking-wide">3 Hak Akses Peran</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Dirancang Sesuai Kebutuhan Pengguna</h2>
                <p class="mt-3 text-base sm:text-lg text-slate-600">Satu sistem terpadu yang mempermudah Nasabah, Kolektor Keliling, dan Admin Pengelola.</p>
            </div>

            <!-- Role Selector Tabs -->
            <div class="flex justify-center mb-10">
                <div class="inline-flex rounded-2xl bg-stone-100 p-1.5 border border-stone-200">
                    <button onclick="switchRole('nasabah')" id="tab-nasabah" class="role-tab flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition bg-white text-slate-900 shadow-sm">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Role Nasabah</span>
                    </button>
                    <button onclick="switchRole('kolektor')" id="tab-kolektor" class="role-tab flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition text-slate-600 hover:text-slate-900">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Role Kolektor</span>
                    </button>
                    <button onclick="switchRole('admin')" id="tab-admin" class="role-tab flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition text-slate-600 hover:text-slate-900">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h5m-5 0V10m0 0h5m-5 0H7m5 0v11"/></svg>
                        <span>Role Admin</span>
                    </button>
                </div>
            </div>

            <!-- Role Content Panels -->
            <div class="max-w-4xl mx-auto">
                <!-- Nasabah Content -->
                <div id="role-nasabah" class="role-content glass-card rounded-3xl p-8 sm:p-10 border border-stone-200 shadow-xl grid md:grid-cols-2 gap-8 items-center">
                    <div class="space-y-4">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Transparansi Saldo Real-Time</span>
                        <h3 class="text-2xl font-bold text-slate-900">Kemudahan &amp; Rasa Aman Untuk Nasabah</h3>
                        <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                            Nasabah dapat mengecek perkembangan tabungannya kapan saja dari HP. Cocok untuk ibu rumah tangga maupun pelajar yang ingin disiplin menabung.
                        </p>
                        <ul class="space-y-2 text-sm text-slate-700 font-medium">
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold text-xs">✓</span>
                                Cek saldo real-time tanpa perlu menunggu buku tabungan fisik.
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold text-xs">✓</span>
                                Terima bukti setoran digital otomatis via WhatsApp.
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold text-xs">✓</span>
                                Pengajuan penarikan dana instan &amp; jalur komplain direct.
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-2xl bg-gradient-to-tr from-emerald-50 to-teal-50 p-6 border border-emerald-100 space-y-3">
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-stone-200/80">
                            <p class="text-xs font-semibold text-slate-500">Notifikasi WA Masuk</p>
                            <p class="text-xs font-bold text-slate-800 mt-1">"Setoran Rp 20.000 berhasil dicatat oleh Kolektor Rudi. Total Saldo: Rp 1.420.000."</p>
                        </div>
                        <div class="bg-brand-600 text-white p-4 rounded-xl shadow-md">
                            <p class="text-xs opacity-80">Progres Paket Sembako Lebaran</p>
                            <div class="w-full bg-brand-800 rounded-full h-2.5 mt-2">
                                <div class="bg-white h-2.5 rounded-full" style="width: 75%"></div>
                            </div>
                            <p class="text-[11px] mt-1.5 font-medium">75% Terkumpul - Siap dicairkan H-7 Lebaran</p>
                        </div>
                    </div>
                </div>

                <!-- Kolektor Content -->
                <div id="role-kolektor" class="role-content hidden glass-card rounded-3xl p-8 sm:p-10 border border-stone-200 shadow-xl grid md:grid-cols-2 gap-8 items-center">
                    <div class="space-y-4">
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Pencatatan Keliling Cepat</span>
                        <h3 class="text-2xl font-bold text-slate-900">Alat Kerja Praktis Bagi Petugas Keliling</h3>
                        <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                            Petugas kolektor tidak lagi dipusingkan dengan pencatatan manual kertas yang rawan hilang. Cukup bawa HP, input angka, dan data otomatis tersinkronisasi.
                        </p>
                        <ul class="space-y-2 text-sm text-slate-700 font-medium">
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-800 font-bold text-xs">✓</span>
                                Input setoran cepat kurang dari 10 detik per nasabah.
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-800 font-bold text-xs">✓</span>
                                Jadwal rute kunjungan harian &amp; daftar nasabah binaan lengkap.
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-800 font-bold text-xs">✓</span>
                                Fitur penyetoran kas ke kantor yang mudah dihitung.
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-2xl bg-stone-900 p-6 text-white space-y-3">
                        <p class="text-xs font-bold text-amber-400 uppercase">Dashboard Kolektor</p>
                        <div class="bg-slate-800 p-3 rounded-xl border border-slate-700 flex justify-between items-center">
                            <div>
                                <p class="text-xs text-slate-400">Total Kas Di Tangan</p>
                                <p class="text-lg font-bold text-white">Rp 2.450.000</p>
                            </div>
                            <span class="text-xs bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2.5 py-1 rounded-lg">32 Nasabah</span>
                        </div>
                        <button class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold py-2.5 rounded-xl transition">
                            + Catat Setoran Baru
                        </button>
                    </div>
                </div>

                <!-- Admin Content -->
                <div id="role-admin" class="role-content hidden glass-card rounded-3xl p-8 sm:p-10 border border-stone-200 shadow-xl grid md:grid-cols-2 gap-8 items-center">
                    <div class="space-y-4">
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">Pusat Kontrol &amp; Keuangan</span>
                        <h3 class="text-2xl font-bold text-slate-900">Pengawasan Penuh &amp; Audit Trail Aman</h3>
                        <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                            Pemilik bisnis dan manajemen kantor memiliki kendali penuh atas arus kas, kinerja kolektor, serta laporan pengadaan paket sembako.
                        </p>
                        <ul class="space-y-2 text-sm text-slate-700 font-medium">
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs">✓</span>
                                Approval penarikan saldo &amp; verifikasi pendaftaran nasabah.
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs">✓</span>
                                Rekonsiliasi kas kolektor harian &amp; riwayat audit trail.
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-indigo-800 font-bold text-xs">✓</span>
                                Laporan pengadaan barang sembako akurat.
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-2xl bg-slate-950 p-6 text-white space-y-3">
                        <p class="text-xs font-bold text-indigo-400 uppercase">Audit &amp; Rekonsiliasi</p>
                        <div class="space-y-2">
                            <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 flex justify-between items-center text-xs">
                                <span>Kas Kolektor Rudi (Sesuai)</span>
                                <span class="text-emerald-400 font-bold">Rp 2.450.000 ✓</span>
                            </div>
                            <div class="bg-slate-900 p-3 rounded-xl border border-slate-800 flex justify-between items-center text-xs">
                                <span>Permohonan Penarikan Ibu Ani</span>
                                <span class="text-amber-400 font-bold">Butuh Approval</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Feature Grid Section -->
    <section id="fitur" class="py-20 bg-stone-100/70 border-t border-stone-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="rounded-full bg-brand-100 px-3.5 py-1 text-xs font-bold text-brand-800 uppercase tracking-wide">Keunggulan Utama</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Mengapa Memilih Tabungan Digital?</h2>
                <p class="mt-3 text-base sm:text-lg text-slate-600">Teknologi modern yang dirancang sederhana agar mudah dipakai oleh siapa saja.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1 -->
                <div class="glass-card rounded-2xl p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl border border-stone-200">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-100 text-brand-700 flex items-center justify-center mb-5 shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Setoran Real-time</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Saat kolektor mencatat di HP, saldo nasabah langsung terupdate saat itu juga tanpa penundaan.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="glass-card rounded-2xl p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl border border-stone-200">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center mb-5 shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Notifikasi WA &amp; Struk</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Struk bukti transaksi dikirim otomatis ke WhatsApp nasabah. Aman, jujur, dan tidak bisa dipalsukan.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="glass-card rounded-2xl p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl border border-stone-200">
                    <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center mb-5 shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Rekonsiliasi Kas Aman</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Manajemen pencocokan uang fisik dan digital harian memastikan kas aman di tangan kolektor.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="glass-card rounded-2xl p-6 transition duration-300 hover:-translate-y-1 hover:shadow-xl border border-stone-200">
                    <div class="h-12 w-12 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center mb-5 shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Layanan Aduan Direct</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Ada pertanyaan atau ketidaksesuaian? Nasabah dapat mengajukan aduan langsung ke admin kantor.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section id="testimoni" class="py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="rounded-full bg-brand-100 px-3.5 py-1 text-xs font-bold text-brand-800 uppercase tracking-wide">Cerita Pengguna</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Dipercaya Oleh Warga &amp; Petugas</h2>
                <p class="mt-3 text-base sm:text-lg text-slate-600">Pengalaman nyata dari nasabah dan kolektor yang merasakan kemudahannya.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                
                <!-- Testimonial 1 (Senior / Housewife) -->
                <div class="rounded-3xl bg-stone-50 p-8 border border-stone-200/80 shadow-sm flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex text-amber-400 gap-1">★★★★★</div>
                        <p class="text-slate-700 leading-relaxed italic text-sm sm:text-base">
                            "Dulu sering lupa catat di buku harian. Sekarang tiap Mas Kolektor datang, langsung dapat notifikasi WA. Pas Lebaran kemarin sembako datang tepat waktu. Tenang sekali!"
                        </p>
                    </div>
                    <div class="pt-6 mt-6 border-t border-stone-200 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-brand-200 flex items-center justify-center text-brand-800 font-bold text-sm">
                            IB
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Ibu Maryam (54 th)</p>
                            <p class="text-xs text-slate-500">Nasabah Paket Sembako</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 (Youth / Student) -->
                <div class="rounded-3xl bg-stone-50 p-8 border border-stone-200/80 shadow-sm flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex text-amber-400 gap-1">★★★★★</div>
                        <p class="text-slate-700 leading-relaxed italic text-sm sm:text-base">
                            "Seru banget bisa cek saldo dari HP. Saya nabung Rp 10.000 sehari dari sisa uang jalar, tau-tau pas akhir tahun dapet Rp 3 Juta lebih. Tampilannya keren &amp; gampang dipahami."
                        </p>
                    </div>
                    <div class="pt-6 mt-6 border-t border-stone-200 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-indigo-200 flex items-center justify-center text-indigo-800 font-bold text-sm">
                            RD
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Rian Dwi (19 th)</p>
                            <p class="text-xs text-slate-500">Pelajar &amp; Nasabah Bebas</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 (Collector) -->
                <div class="rounded-3xl bg-stone-50 p-8 border border-stone-200/80 shadow-sm flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex text-amber-400 gap-1">★★★★★</div>
                        <p class="text-slate-700 leading-relaxed italic text-sm sm:text-base">
                            "Kerja jadi jauh lebih cepat. Tidak takut selisih hitung kas di sore hari karena semuanya tercatat otomatis di aplikasi. Ibu-ibu nasabah juga makin percaya."
                        </p>
                    </div>
                    <div class="pt-6 mt-6 border-t border-stone-200 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-teal-200 flex items-center justify-center text-teal-800 font-bold text-sm">
                            RK
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Mas Rudi Kurniawan</p>
                            <p class="text-xs text-slate-500">Petugas Kolektor Keliling</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-stone-100/80 border-t border-stone-200">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="rounded-full bg-indigo-100 px-3.5 py-1 text-xs font-bold text-indigo-800 uppercase tracking-wide">Pusat Informasi</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Pertanyaan Sering Diajukan (FAQ)</h2>
                <p class="mt-3 text-base text-slate-600">Temukan jawaban cepat mengenai keamanan dan cara penggunaan sistem.</p>
            </div>

            <!-- Accordion Items -->
            <div class="space-y-4">
                
                <!-- FAQ 1 -->
                <div class="rounded-2xl bg-white border border-stone-200 overflow-hidden shadow-sm">
                    <button onclick="toggleFaq(1)" class="w-full flex justify-between items-center p-5 text-left font-bold text-slate-900 text-base sm:text-lg hover:bg-stone-50 transition">
                        <span>Apakah aman menabung melalui petugas kolektor keliling?</span>
                        <span id="faq-icon-1" class="text-xl font-bold text-brand-600 transition-transform duration-200">+</span>
                    </button>
                    <div id="faq-answer-1" class="hidden p-5 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-stone-100">
                        Sangat aman! Setiap kali Anda menyerahkan uang setoran kepada petugas kolektor resmi kami, petugas akan menginputnya di aplikasi dan Anda langsung menerima pesan konfirmasi WhatsApp beserta struk digital saat itu juga.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="rounded-2xl bg-white border border-stone-200 overflow-hidden shadow-sm">
                    <button onclick="toggleFaq(2)" class="w-full flex justify-between items-center p-5 text-left font-bold text-slate-900 text-base sm:text-lg hover:bg-stone-50 transition">
                        <span>Bagaimana jika saya orang tua yang kurang mengerti smartphone?</span>
                        <span id="faq-icon-2" class="text-xl font-bold text-brand-600 transition-transform duration-200">+</span>
                    </button>
                    <div id="faq-answer-2" class="hidden p-5 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-stone-100">
                        Jangan khawatir! Petugas kolektor kami dilatih untuk melayani secara ramah dan transparan. Anda juga bisa meminta bantuan anak/keluarga untuk mengecek saldo di HP, atau cukup mengecek pesan WhatsApp yang otomatis masuk setiap kali setor.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="rounded-2xl bg-white border border-stone-200 overflow-hidden shadow-sm">
                    <button onclick="toggleFaq(3)" class="w-full flex justify-between items-center p-5 text-left font-bold text-slate-900 text-base sm:text-lg hover:bg-stone-50 transition">
                        <span>Kapan Paket Sembako Lebaran bisa diambil?</span>
                        <span id="faq-icon-3" class="text-xl font-bold text-brand-600 transition-transform duration-200">+</span>
                    </button>
                    <div id="faq-answer-3" class="hidden p-5 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-stone-100">
                        Paket Sembako Lebaran disalurkan serentak H-10 sampai H-5 sebelum Hari Raya Idul Fitri langsung disatukan dan diantarkan ke rumah Anda oleh petugas kolektor.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="rounded-2xl bg-white border border-stone-200 overflow-hidden shadow-sm">
                    <button onclick="toggleFaq(4)" class="w-full flex justify-between items-center p-5 text-left font-bold text-slate-900 text-base sm:text-lg hover:bg-stone-50 transition">
                        <span>Bisakah saya menarik uang sebelum jangka waktu berakhir?</span>
                        <span id="faq-icon-4" class="text-xl font-bold text-brand-600 transition-transform duration-200">+</span>
                    </button>
                    <div id="faq-answer-4" class="hidden p-5 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-stone-100">
                        Untuk <strong>Tabungan Bebas</strong>, dana dapat ditarik sewaktu-waktu sesuai kebutuhan melalui pengajuan di aplikasi atau via petugas kolektor. Sedangkan untuk <strong>Paket Sembako</strong> dirancang khusus pencairan saat Lebaran.
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Call To Action & Footer -->
    <footer class="bg-slate-950 text-slate-400 pt-16 pb-12 border-t border-slate-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            <!-- Bottom CTA Banner -->
            <div class="rounded-3xl bg-gradient-to-r from-brand-600 to-teal-700 p-8 sm:p-12 text-white mb-16 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-2 text-center md:text-left">
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Siap Memulai Tabungan Masa Depan Anda?</h3>
                    <p class="text-emerald-100 text-sm sm:text-base max-w-xl">Nikmati kemudahan mencatat tabungan secara digital bersama kolektor keliling terpercaya.</p>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-8 py-4 text-sm font-bold text-white shadow-xl transition hover:bg-slate-800 active:scale-95 whitespace-nowrap">
                        Ke Dashboard Saya
                    </a>
                @else
                    {{-- Tiga portal login terpisah (D19 butir 5); nasabah tidak bisa daftar sendiri (FR-1) --}}
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-xl transition hover:bg-emerald-500 active:scale-95 whitespace-nowrap">
                            Masuk Nasabah
                        </a>
                        <a href="{{ route('login.kolektor') }}" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-xl transition hover:bg-blue-500 active:scale-95 whitespace-nowrap">
                            Masuk Kolektor
                        </a>
                        <a href="{{ route('login.admin') }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white shadow-xl transition hover:bg-slate-800 active:scale-95 whitespace-nowrap">
                            Masuk Admin
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Footer Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-800">
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500 text-white font-bold">
                            T
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight">Tabungan Digital</span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-sm leading-relaxed">
                        Platform manajemen tabungan keliling modern, amanah, dan terintegrasi untuk masyarakat Indonesia.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wider mb-4">Navigasi</p>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#simulasi" class="hover:text-white transition">Simulasi Tabungan</a></li>
                        <li><a href="#peran" class="hover:text-white transition">Fitur Peran</a></li>
                        <li><a href="#fitur" class="hover:text-white transition">Keunggulan</a></li>
                        <li><a href="#faq" class="hover:text-white transition">Bantuan FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-bold text-white uppercase tracking-wider mb-4">Keamanan</p>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        Keamanan diawasi dengan otentikasi PIN + Nomor HP, Struk WhatsApp real-time, dan audit trail terpusat.
                    </p>
                    <span class="inline-block rounded-md bg-slate-900 px-3 py-1 text-xs font-mono text-emerald-400 border border-slate-800">
                        PRD v1.0 &bull; Verified Auth
                    </span>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; 2026 Tabungan Digital — Sistem Kolektor Keliling. Hak Cipta Dilindungi.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-slate-400 transition">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-slate-400 transition">Syarat &amp; Ketentuan</a>
                </div>
            </div>

        </div>
    </footer>

    <!-- Interactive Logic -->
    <script>
        // State Variables
        const dbProducts = @json($produkList ?? []);
        let selectedDbProduct = dbProducts.length > 0 ? dbProducts[0] : null;
        let currentProduct = selectedDbProduct ? selectedDbProduct.tipe : 'bebas'; 
        let currentDurationDays = 365;

        // Toggle Text Size Mode (Senior Accessibility Feature)
        function toggleTextSize() {
            const body = document.getElementById('bodyRoot');
            const btn = document.getElementById('textSizeToggleBtn');
            body.classList.toggle('large-text-mode');
            
            if (body.classList.contains('large-text-mode')) {
                btn.classList.add('bg-brand-600', 'text-white', 'border-brand-700');
                btn.classList.remove('bg-stone-100', 'text-slate-700');
            } else {
                btn.classList.remove('bg-brand-600', 'text-white', 'border-brand-700');
                btn.classList.add('bg-stone-100', 'text-slate-700');
            }
        }

        // Toggle Mobile Menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Select Database Product
        function selectDbProduct(productId, btnElement) {
            const prod = dbProducts.find(p => p.id === productId);
            if (prod) {
                selectedDbProduct = prod;
                currentProduct = prod.tipe;

                // Update styling of buttons
                document.querySelectorAll('#productSelectorContainer .product-btn').forEach(b => {
                    b.className = "product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-stone-200 bg-white p-4 text-center transition hover:bg-stone-50 active:scale-98";
                });
                if (btnElement) {
                    btnElement.className = "product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-brand-600 bg-emerald-50/60 p-4 text-center transition hover:bg-emerald-50 active:scale-98";
                }

                // Adjust range slider min/default
                const range = document.getElementById('dailyRange');
                const minVal = parseFloat(prod.harga_per_hari || prod.minimal_setor || 5000);
                if (minVal > 0) {
                    range.value = minVal;
                }

                updateCalculator();
            }
        }

        // Set Tabungan Product Type (fallback)
        function setProductType(type) {
            currentProduct = type;
            selectedDbProduct = null;
            const btnBebas = document.getElementById('btnTabunganBebas');
            const btnSembako = document.getElementById('btnPaketSembako');

            if (btnBebas && btnSembako) {
                if (type === 'bebas') {
                    btnBebas.className = "product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-brand-600 bg-emerald-50/60 p-4 text-center transition hover:bg-emerald-50 active:scale-98";
                    btnSembako.className = "product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-stone-200 bg-white p-4 text-center transition hover:bg-stone-50 active:scale-98";
                } else {
                    btnSembako.className = "product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-brand-600 bg-emerald-50/60 p-4 text-center transition hover:bg-emerald-50 active:scale-98";
                    btnBebas.className = "product-btn flex flex-col items-center justify-center rounded-2xl border-2 border-stone-200 bg-white p-4 text-center transition hover:bg-stone-50 active:scale-98";
                }
            }
            updateCalculator();
        }

        // Set Duration
        function setDuration(days, btnElement) {
            currentDurationDays = days;
            document.querySelectorAll('.dur-btn').forEach(b => {
                b.className = "dur-btn rounded-xl border border-stone-300 py-2.5 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-stone-50";
            });
            btnElement.className = "dur-btn active-dur rounded-xl border-2 border-brand-600 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-600 shadow-sm";
            updateCalculator();
        }

        // Calculator Logic & Formatting
        function updateCalculator() {
            const range = document.getElementById('dailyRange');
            const dailyVal = parseInt(range.value);
            
            // Format Daily Range display
            document.getElementById('dailyAmountText').innerText = 'Rp ' + dailyVal.toLocaleString('id-ID');

            // Calculate total
            const total = dailyVal * currentDurationDays;
            document.getElementById('calcTotalAmount').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('calcSubText').innerText = `Terkumpul dalam ${currentDurationDays} hari penyetoran`;

            // Bonus text logic
            const bonusText = document.getElementById('bonusText');
            if (selectedDbProduct) {
                if (selectedDbProduct.isi_paket && Array.isArray(selectedDbProduct.isi_paket) && selectedDbProduct.isi_paket.length > 0) {
                    const isiStr = selectedDbProduct.isi_paket.map(i => `${i.nama} (${i.jumlah})`).join(', ');
                    bonusText.innerText = `Bonus Produk (${selectedDbProduct.nama}): ${isiStr}`;
                } else if (selectedDbProduct.tipe === 'paket') {
                    bonusText.innerText = `Paket ${selectedDbProduct.nama}: Dapatkan manfaat paket sembako / barang harian pilihan cair menjelang Lebaran!`;
                } else {
                    bonusText.innerText = `Tabungan Bebas (${selectedDbProduct.nama}): Setor kapan saja, saldo dapat ditarik sewaktu-waktu tanpa penalti.`;
                }
            } else if (currentProduct === 'sembako') {
                if (dailyVal >= 50000) {
                    bonusText.innerText = "Paket Sembako Super Mewah: Beras 25kg, Minyak 5L, Daging Sapi 2kg, Biskuit, Sirup, & Bahan Pokok Premium Lengkap!";
                } else if (dailyVal >= 20000) {
                    bonusText.innerText = "Paket Sembako Lengkap: Beras 10kg, Minyak Goreng 2L, Gula 2kg, Sirup 2 Botol, & Biskuit Lebaran.";
                } else {
                    bonusText.innerText = "Paket Sembako Hemat: Beras 5kg, Minyak 1L, Gula 1kg, & Sirup Hari Raya.";
                }
            } else {
                bonusText.innerText = "Tabungan Bebas Harian dapat ditarik kapan saja sesuai kebutuhan Anda tanpa biaya penalti.";
            }
        }

        // Switch Role Tabs
        function switchRole(role) {
            // Hide all content
            document.querySelectorAll('.role-content').forEach(el => el.classList.add('hidden'));
            
            // Reset buttons styling
            document.querySelectorAll('.role-tab').forEach(el => {
                el.className = "role-tab flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition text-slate-600 hover:text-slate-900";
            });

            // Active button styling
            const activeTab = document.getElementById(`tab-${role}`);
            activeTab.className = "role-tab flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition bg-white text-slate-900 shadow-sm";

            // Show selected role content
            document.getElementById(`role-${role}`).classList.remove('hidden');
        }

        // Toggle FAQ Accordion
        function toggleFaq(index) {
            const ans = document.getElementById(`faq-answer-${index}`);
            const icon = document.getElementById(`faq-icon-${index}`);

            if (ans.classList.contains('hidden')) {
                ans.classList.remove('hidden');
                icon.innerText = '−';
                icon.classList.add('rotate-180');
            } else {
                ans.classList.add('hidden');
                icon.innerText = '+';
                icon.classList.remove('rotate-180');
            }
        }

        // Initial setup
        window.onload = function() {
            if (dbProducts.length > 0) {
                const firstBtn = document.querySelector('#productSelectorContainer .product-btn');
                if (firstBtn) {
                    selectDbProduct(dbProducts[0].id, firstBtn);
                }
            } else {
                updateCalculator();
            }
        };
    </script>
</body>
</html> 