<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<script>
  // Halaman auth memakai data-scheme="light" (selalu terang, mengikuti
  // perilaku lama); layar lain mengikuti preferensi pengguna.
  if (document.documentElement.getAttribute('data-scheme') !== 'light' &&
      (localStorage.getItem('td-theme') === 'dark' ||
       (!('td-theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches))) {
    document.documentElement.classList.add('dark');
  }
</script>

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}{{ auth()->check() ? ' · '.\App\Support\PortalLogin::labelPortal(auth()->user()->role) : '' }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@php
    $portalManifest = $portal ?? (auth()->check() ? auth()->user()->role : null);
    $portalManifest = is_string($portalManifest) && in_array($portalManifest, \App\Support\PortalLogin::semua(), true) ? $portalManifest : null;
@endphp
<link rel="manifest" href="{{ $portalManifest ? '/manifest-'.$portalManifest.'.webmanifest' : '/manifest.webmanifest' }}">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/service-worker.js');
        });
    }
</script>

<script>
    // Tombol Back Android di shell Capacitor (P8 butir 11.2): mundur di riwayat
    // browser; tanpa riwayat (halaman awal) keluar dari aplikasi.
    if (window.Capacitor && typeof window.Capacitor.isPluginAvailable === 'function'
        && window.Capacitor.isPluginAvailable('App')) {
        window.Capacitor.Plugins.App.addListener('backButton', function (data) {
            if (data.canGoBack) {
                window.history.back();
            } else {
                window.Capacitor.Plugins.App.exitApp();
            }
        });
    }
</script>

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
