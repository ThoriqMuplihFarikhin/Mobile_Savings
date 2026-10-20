@php
    $labelPortal = \App\Support\PortalLogin::labelPortal($portal);
    $aksenPortal = \App\Support\PortalLogin::aksen($portal);
    $portalLain = array_values(array_filter(\App\Support\PortalLogin::semua(), fn (string $p) => $p !== $portal));

    $portalAdmin = $portal === \App\Support\PortalLogin::ADMIN;

    $kelasTombol = $portalAdmin
        ? 'h-9 rounded-md bg-portal-admin text-sm hover:bg-ink'
        : 'h-12 rounded-full bg-primary text-base hover:bg-primary-hover';

    $ukuranInput = $portalAdmin ? 'h-10 rounded-md' : 'h-12 rounded-xl';

    $kelasFokus = $portalAdmin
        ? 'focus:border-ink focus:ring-ink/10'
        : 'focus:border-primary focus:ring-primary/20';

    $kelasTautan = $portalAdmin ? 'text-info' : 'text-primary';

    $nomorWa = \App\Models\AdminSetting::get('nomor_wa_bantuan') ?: config('services.admin_wa');
    $waLink = $nomorWa ? 'https://wa.me/'.preg_replace('/\D/', '', (string) $nomorWa) : null;
@endphp

@if ($portalAdmin)
    {{-- Portal Admin: Pertahankan tampilan ringkas padat eksisting (IMPLEMENTASI_PLAN.md butir 6) --}}
    <x-layouts::auth :title="'Login '.$labelPortal.' - Tabungan Digital'" :portal="$portal">
        <div class="flex flex-col gap-6">
            <div class="flex w-full flex-col items-center text-center">
                <a href="{{ route('home') }}" class="mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl text-white transition hover:opacity-90" style="background: {{ $aksenPortal }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </a>
                <h1 class="text-2xl font-semibold tracking-tight text-ink">Tabungan Digital</h1>
                <p class="mt-1 text-xs text-mute">Sistem Kolektor Keliling v1.0</p>
                <span data-test="label-portal" class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white" style="background: {{ $aksenPortal }}">Portal {{ $labelPortal }}</span>
            </div>

            @if (session('locked'))
                <div role="alert" class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                    {{ __('Akun terkunci karena terlalu banyak percobaan gagal. Hubungi admin.') }}
                </div>
            @endif

            @if (session('idle_expired'))
                <div role="alert" class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                    {{ __('Sesi berakhir karena tidak aktif. Silakan masuk kembali.') }}
                </div>
            @endif

            @error('no_hp')
                <div role="alert" class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ $message }}
                </div>
            @enderror

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf

                <input type="hidden" name="portal" value="{{ $portal }}">

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-ink" for="no_hp">Nomor HP</label>
                    <input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp') }}" required autofocus autocomplete="tel" placeholder="08xxxxxxxxxx"
                        class="w-full border border-hairline-strong bg-white px-3 text-base tabular-nums text-ink placeholder:text-subtle {{ $ukuranInput }} {{ $kelasFokus }} focus:outline-none" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-ink" for="pin">PIN 6 Digit</label>
                    <input id="pin" name="password" type="password" required autocomplete="current-password" placeholder="••••••" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                        class="w-full border border-hairline-strong bg-white px-3 text-center text-xl font-semibold tracking-[0.25em] tabular-nums text-ink placeholder:text-subtle {{ $ukuranInput }} {{ $kelasFokus }} focus:outline-none" />
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="ms-auto text-xs {{ $kelasTautan }} hover:underline">Kembali ke Beranda</a>
                </div>

                <button type="submit" data-test="login-button"
                    class="flex w-full items-center justify-center font-medium transition {{ $kelasTombol }}">
                    Masuk
                </button>
            </form>

            <div class="text-center text-xs text-mute">
                <p>Hubungi admin jika lupa PIN atau akun terkunci</p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs">
                <span class="text-mute">Portal lain:</span>
                @foreach ($portalLain as $p)
                    <a href="{{ route(\App\Support\PortalLogin::routeName($p)) }}" data-test="tautan-portal-lain"
                        class="{{ $kelasTautan }} hover:underline">Masuk {{ \App\Support\PortalLogin::labelPortal($p) }}</a>
                @endforeach
            </div>
        </div>
    </x-layouts::auth>
@else
    {{-- Portal Nasabah & Kolektor: Desain Baru 2 Langkah Interaktif (IMPLEMENTASI_PLAN.md) --}}
    <x-layouts::auth.portal :title="'Login '.$labelPortal.' - Tabungan Digital'" :portal="$portal">
        <!-- Pemilih Portal Tab (Tautan Route, Bukan JS Tab) -->
        <nav aria-label="Pilihan portal" class="grid grid-cols-2 p-1 rounded-xl bg-surface-inset border border-hairline mb-6">
            @if ($portal === \App\Support\PortalLogin::NASABAH)
                <span class="flex items-center justify-center py-2 px-3 rounded-lg bg-surface text-ink font-semibold text-xs sm:text-sm border border-hairline shadow-none select-none">Saya Nasabah</span>
                <a href="{{ route('login.kolektor') }}"
                   data-test="tautan-portal-lain"
                   class="flex items-center justify-center py-2 px-3 rounded-lg text-mute hover:text-ink font-medium text-xs sm:text-sm transition">Saya Kolektor</a>
            @else
                <a href="{{ route('login') }}"
                   data-test="tautan-portal-lain"
                   class="flex items-center justify-center py-2 px-3 rounded-lg text-mute hover:text-ink font-medium text-xs sm:text-sm transition">Saya Nasabah</a>
                <span class="flex items-center justify-center py-2 px-3 rounded-lg bg-surface text-ink font-semibold text-xs sm:text-sm border border-hairline shadow-none select-none">Saya Kolektor</span>
            @endif
        </nav>

        <!-- Pesan Status & Galat dari Server -->
        @if (session('locked'))
            <div role="alert" class="mb-5 flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span>{{ __('Akun terkunci karena terlalu banyak percobaan gagal. Hubungi admin.') }}</span>
            </div>
        @endif

        @if (session('idle_expired'))
            <div role="alert" class="mb-5 flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span>{{ __('Sesi berakhir karena tidak aktif. Silakan masuk kembali.') }}</span>
            </div>
        @endif

        @error('no_hp')
            <div role="alert" class="mb-5 flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <!-- Fallback Tanpa JavaScript (NoScript) -->
        <noscript>
            <div class="mb-4 rounded-xl border border-warning bg-warning-soft p-4 text-xs text-warning-deep">
                JavaScript tidak aktif di peramban Anda. Menggunakan formulir masuk standar langsung.
            </div>
            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="portal" value="{{ $portal }}">

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-ink" for="noscript_no_hp">Nomor HP</label>
                    <input id="noscript_no_hp" name="no_hp" type="tel" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx"
                           class="w-full h-12 border border-hairline-strong bg-white px-3 text-base tabular-nums rounded-xl text-ink" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-ink" for="noscript_pin">PIN 6 Digit</label>
                    <input id="noscript_pin" name="password" type="password" required maxlength="6" inputmode="numeric" placeholder="••••••"
                           class="w-full h-12 border border-hairline-strong bg-white px-3 text-center text-xl tracking-[0.25em] tabular-nums rounded-xl text-ink" />
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="noscript_remember" class="size-4 rounded accent-primary" {{ old('remember') ? 'checked' : '' }} />
                    <label for="noscript_remember" class="text-sm text-body">Ingat saya di HP ini</label>
                </div>

                <button type="submit" data-test="login-button"
                        class="flex h-12 w-full items-center justify-center rounded-full bg-primary font-medium text-white">
                    Masuk
                </button>
            </form>
        </noscript>

        <!-- Formulir Autentikasi 2 Langkah dengan Alpine.js -->
        <div x-data="loginFlow({
                oldHp: '{{ old('no_hp') }}',
                hasServerError: {{ $errors->any() ? 'true' : 'false' }},
                isLocked: {{ session('locked') ? 'true' : 'false' }}
             })"
             x-cloak
             class="flex flex-col gap-5">

            <form method="POST" action="{{ route('login.store') }}" x-ref="loginForm" class="flex flex-col gap-5">
                @csrf
                <input type="hidden" name="portal" value="{{ $portal }}">
                {{-- Input nomor HP asli yang dikirim ke server (dinormalkan via NomorHp::normalize) --}}
                <input type="hidden" name="no_hp" :value="rawHp">

                {{-- Input PIN tersembunyi namun aktif di DOM (dukungan password manager, autofill, keyboard fisik) --}}
                <input type="password"
                       name="password"
                       x-ref="pinInput"
                       id="password"
                       x-model="pin"
                       @input="onPinDirectInput($event)"
                       inputmode="numeric"
                       maxlength="6"
                       autocomplete="current-password"
                       class="sr-only"
                       tabindex="-1"
                       aria-hidden="true">

                <!-- LANGKAH 1: Input Nomor HP -->
                <div x-show="step === 1" class="flex flex-col gap-4 transition-all">
                    <div class="text-left">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-ink">Masuk Akun</h1>
                        <p class="text-xs sm:text-sm text-mute mt-1">Masukkan nomor HP yang terdaftar pada tabungan Anda.</p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-ink" for="no_hp_tampilan">Nomor HP</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-sm font-semibold text-mute select-none pointer-events-none">+62</span>
                            <input id="no_hp_tampilan"
                                   type="tel"
                                   x-ref="hpInput"
                                   x-model="displayHp"
                                   @input="onHpInput($event)"
                                   @keydown.enter.prevent="if (isHpValid) goToStep2()"
                                   autofocus
                                   autocomplete="tel"
                                   placeholder="812-3456-7890"
                                   class="w-full pl-13 pr-3 h-12 rounded-xl border border-hairline-strong bg-white text-base tabular-nums text-ink placeholder:text-subtle focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" />
                        </div>
                        <p class="text-[11px] text-mute">Mendukung format 08... atau 8...</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember" value="1"
                               class="size-4 rounded border-hairline-strong accent-primary focus:ring-primary/20"
                               {{ old('remember') ? 'checked' : '' }} />
                        <label for="remember" class="text-sm text-body cursor-pointer select-none">Ingat saya di HP ini</label>
                    </div>

                    <button type="button"
                            @click="goToStep2()"
                            :disabled="!isHpValid"
                            class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-semibold text-white transition hover:bg-primary-hover disabled:opacity-40 disabled:cursor-not-allowed shadow-none">
                        Lanjut
                    </button>
                </div>

                <!-- LANGKAH 2: Masukkan PIN 6 Digit -->
                <div x-show="step === 2" class="flex flex-col gap-5 transition-all">
                    <!-- Kartu Ringkas Nomor HP (Disamarkan) -->
                    <div class="flex items-center justify-between rounded-xl border border-hairline bg-surface-inset p-3 sm:p-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-surface text-mute border border-hairline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-[11px] font-medium text-mute">Nomor HP</span>
                                <span class="block text-sm font-semibold tabular-nums text-ink" x-text="maskedHp"></span>
                            </div>
                        </div>
                        <button type="button"
                                @click="goToStep1()"
                                class="text-xs font-semibold text-primary hover:underline px-2.5 py-1.5 rounded-lg hover:bg-surface transition">
                            Ganti
                        </button>
                    </div>

                    <div class="text-center">
                        <h2 class="text-lg sm:text-xl font-bold tracking-tight text-ink">Masukkan 6 Digit PIN</h2>
                        <p class="text-xs text-mute mt-0.5">Ketik 6 angka PIN keamanan akun Anda.</p>
                    </div>

                    <!-- Indikator Dots & Keypad PIN -->
                    <x-auth.pin-pad :portal="$portal" :locked="session('locked')" />

                    <!-- Tombol Masuk Cadangan / Submit Primer (Memenuhi data-test="login-button") -->
                    <button type="submit"
                            data-test="login-button"
                            :disabled="isLocked || pin.length < 6 || isSubmitting"
                            class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-semibold text-white transition hover:bg-primary-hover disabled:opacity-40 disabled:cursor-not-allowed shadow-none">
                        <span x-show="!isSubmitting">Masuk</span>
                        <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memeriksa...</span>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Tautan Bantuan Admin & WhatsApp -->
            <div class="text-center text-xs text-mute mt-1">
                <p>Hubungi admin jika lupa PIN atau akun terkunci</p>
                @if ($waLink)
                    <div class="mt-2.5">
                        <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 font-semibold text-primary hover:underline px-3 py-1.5 rounded-full border border-hairline bg-surface hover:bg-surface-inset transition">
                            <svg class="h-4 w-4 fill-[#25D366]" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z" />
                            </svg>
                            <span>Bantuan WhatsApp Admin</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer Tautan Beranda & Admin -->
            <div class="mt-2 flex flex-wrap items-center justify-between border-t border-hairline pt-4 text-xs">
                <a href="{{ route('home') }}" class="text-mute hover:text-ink hover:underline">
                    ← Kembali ke Beranda
                </a>
                <a href="{{ route('login.admin') }}" data-test="tautan-portal-lain" class="text-mute hover:text-ink hover:underline">
                    Masuk Admin
                </a>
            </div>
        </div>

        <!-- Alpine Component Script -->
        <script>
            function loginFlow(config) {
                return {
                    step: config.hasServerError ? 2 : 1,
                    rawHp: '',
                    displayHp: '',
                    pin: '',
                    showPin: false,
                    hasError: config.hasServerError,
                    isSubmitting: false,
                    isLocked: config.isLocked,
                    peekTimer: null,

                    init() {
                        if (config.oldHp) {
                            this.setHp(config.oldHp);
                        }

                        if (this.hasError) {
                            // Bergetar sejenak saat ada galat dari server
                            setTimeout(() => {
                                this.hasError = false;
                            }, 1200);
                        }

                        // Sync focus saat pindah ke step 2
                        this.$watch('step', (val) => {
                            if (val === 2) {
                                this.$nextTick(() => {
                                    if (this.$refs.pinInput) {
                                        this.$refs.pinInput.focus();
                                    }
                                });
                            }
                        });
                    },

                    setHp(val) {
                        // Bersihkan dari spasi/karakter non-digit
                        let digits = String(val).replace(/\D/g, '');
                        if (digits.startsWith('62')) {
                            digits = digits.substring(2);
                        } else if (digits.startsWith('0')) {
                            digits = digits.substring(1);
                        }

                        this.displayHp = this.formatDisplay(digits);
                        this.rawHp = digits ? '0' + digits : '';
                    },

                    formatDisplay(digits) {
                        if (!digits) return '';
                        // Format blok: 812 3456 7890
                        if (digits.length <= 3) return digits;
                        if (digits.length <= 7) return digits.slice(0, 3) + ' ' + digits.slice(3);
                        return digits.slice(0, 3) + ' ' + digits.slice(3, 7) + ' ' + digits.slice(7, 12);
                    },

                    onHpInput(e) {
                        const val = e.target.value;
                        let digits = val.replace(/\D/g, '');
                        if (digits.startsWith('62')) {
                            digits = digits.substring(2);
                        } else if (digits.startsWith('0')) {
                            digits = digits.substring(1);
                        }
                        this.displayHp = this.formatDisplay(digits);
                        this.rawHp = digits ? '0' + digits : '';
                    },

                    get isHpValid() {
                        return this.rawHp.length >= 10;
                    },

                    get maskedHp() {
                        if (!this.rawHp) return '+62 •••• ••••';
                        const num = this.rawHp.startsWith('0') ? this.rawHp.substring(1) : this.rawHp;
                        const awal = num.slice(0, 3);
                        const akhir = num.slice(-4);
                        return '+62 ' + awal + ' •••• ' + akhir;
                    },

                    goToStep2() {
                        if (!this.isHpValid) return;
                        this.step = 2;
                        this.pin = '';
                        this.hasError = false;
                    },

                    goToStep1() {
                        this.step = 1;
                        this.pin = '';
                        this.hasError = false;
                        this.$nextTick(() => {
                            if (this.$refs.hpInput) {
                                this.$refs.hpInput.focus();
                            }
                        });
                    },

                    appendDigit(d) {
                        if (this.isLocked || this.isSubmitting) return;
                        if (this.pin.length >= 6) return;

                        this.pin += String(d);
                        this.hasError = false;

                        if (this.pin.length === 6) {
                            this.submitForm();
                        }
                    },

                    deleteDigit() {
                        if (this.isLocked || this.isSubmitting) return;
                        if (this.pin.length > 0) {
                            this.pin = this.pin.slice(0, -1);
                            this.hasError = false;
                        }
                    },

                    peekPin() {
                        if (this.isLocked || this.pin.length === 0) return;
                        this.showPin = true;
                        if (this.peekTimer) clearTimeout(this.peekTimer);
                        this.peekTimer = setTimeout(() => {
                            this.showPin = false;
                        }, 1200);
                    },

                    onPinDirectInput(e) {
                        const val = String(e.target.value).replace(/\D/g, '').slice(0, 6);
                        this.pin = val;
                        if (this.pin.length === 6 && !this.isSubmitting && !this.isLocked) {
                            this.submitForm();
                        }
                    },

                    submitForm() {
                        if (this.isLocked || this.isSubmitting) return;
                        this.isSubmitting = true;
                        this.$nextTick(() => {
                            if (this.$refs.loginForm) {
                                this.$refs.loginForm.requestSubmit();
                            }
                        });
                    }
                };
            }
        </script>
    </x-layouts::auth.portal>
@endif
