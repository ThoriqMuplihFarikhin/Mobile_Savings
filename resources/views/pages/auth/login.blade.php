@php
    $labelPortal = \App\Support\PortalLogin::labelPortal($portal);
    $aksenPortal = \App\Support\PortalLogin::aksen($portal);
    $portalLain = array_values(array_filter(\App\Support\PortalLogin::semua(), fn (string $p) => $p !== $portal));

    /**
     * DESIGN.md §1: warna identitas portal hanya untuk chip/logo/manifest.
     * Warna aksi (tombol, tautan, fokus) tetap hijau di portal mobile dan
     * tinta di portal admin.
     */
    $portalAdmin = $portal === \App\Support\PortalLogin::ADMIN;

    $kelasTombol = $portalAdmin
        ? 'h-9 rounded-md bg-portal-admin text-sm hover:bg-ink'
        : 'h-12 rounded-full bg-primary text-base hover:bg-primary-hover';

    /** DESIGN.md §6: admin radius 6px / tinggi 40px, mobile radius 12px / 48px. */
    $ukuranInput = $portalAdmin ? 'h-10 rounded-md' : 'h-12 rounded-xl';

    $kelasFokus = $portalAdmin
        ? 'focus:border-ink focus:ring-ink/10'
        : 'focus:border-primary focus:ring-primary/20';

    $kelasTautan = $portalAdmin ? 'text-info' : 'text-primary';
@endphp

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
            <div class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                {{ __('Akun terkunci karena terlalu banyak percobaan gagal. Hubungi admin.') }}
            </div>
        @endif

        @if (session('idle_expired'))
            <div class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                {{ __('Sesi berakhir karena tidak aktif. Silakan masuk kembali.') }}
            </div>
        @endif

        @error('no_hp')
            <div class="flex items-center gap-2.5 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm text-danger-deep">
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
                @unless ($portal === 'admin')
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember" class="size-4 rounded border-hairline-strong accent-primary focus:ring-primary/20" {{ old('remember') ? 'checked' : '' }} />
                        <label for="remember" class="text-sm text-body">Ingat saya</label>
                    </div>
                @endunless
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
