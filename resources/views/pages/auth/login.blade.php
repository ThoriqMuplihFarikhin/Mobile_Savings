<x-layouts::auth :title="__('Login - Tabungan Digital')">
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col items-center text-center">
            <a href="{{ route('home') }}" class="mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#171717] text-white transition hover:opacity-90">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </a>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Tabungan Digital</h1>
            <p class="mt-1 font-mono text-xs text-[#888888]">Sistem Kolektor Keliling v1.0</p>
        </div>

        @if (session('locked'))
            <div class="flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                {{ __('Akun terkunci karena terlalu banyak percobaan gagal. Hubungi admin.') }}
            </div>
        @endif

        @error('no_hp')
            <div class="flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <input type="hidden" name="portal" value="{{ $portal }}">

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-[#171717]">Nomor HP</label>
                <input name="no_hp" type="tel" value="{{ old('no_hp') }}" required autofocus autocomplete="tel" placeholder="08xxxxxxxxxx"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 font-mono text-sm text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-[#171717]">PIN 6 Digit</label>
                <input name="password" type="password" required autocomplete="current-password" placeholder="••••••" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 font-mono text-center text-base tracking-widest text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 rounded border-[#ebebeb] text-[#171717] focus:ring-[#171717]/10" {{ old('remember') ? 'checked' : '' }} />
                    <label for="remember" class="text-sm text-[#4d4d4d]">Ingat saya</label>
                </div>
                <a href="{{ route('home') }}" class="text-xs text-[#0070f3] hover:underline">Kembali ke Beranda</a>
            </div>

            <button type="submit" data-test="login-button"
                class="w-full rounded-full bg-[#171717] px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90">
                Masuk
            </button>
        </form>

        <div class="text-center text-xs text-[#888888]">
            <p>Hubungi admin jika lupa PIN atau akun terkunci</p>
        </div>
    </div>
</x-layouts::auth>