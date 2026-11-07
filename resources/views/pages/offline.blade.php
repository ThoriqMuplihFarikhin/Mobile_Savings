<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Koneksi Terputus - KawalDuit</title>
    <style>
        :root {
            --canvas: #f3f7f1;
            --surface: #ffffff;
            --hairline: #e1eadd;
            --hairline-strong: #858585;
            --ink: #171717;
            --mute: #525252;
            --primary: #15803d;
            --primary-hover: #166534;
            --inset: #f5f5f5;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --canvas: #0a0a0a;
                --surface: #171717;
                --hairline: #2e2e2e;
                --hairline-strong: #808080;
                --ink: #fafafa;
                --mute: #a3a3a3;
                --inset: #262626;
            }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            padding-bottom: calc(20px + env(safe-area-inset-bottom));
            font-family: "Instrument Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--canvas);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }
        .container {
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .brand-badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 15px;
        }
        .brand-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--ink);
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 1px 2px rgba(20, 83, 45, 0.06);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: var(--inset);
            color: var(--mute);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-circle svg {
            width: 40px;
            height: 40px;
        }
        h1 {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--ink);
        }
        p {
            margin-top: 8px;
            font-size: 13px;
            line-height: 1.5;
            color: var(--mute);
        }
        .actions {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-primary {
            width: 100%;
            min-height: 42px;
            border: 0;
            border-radius: 999px;
            background: var(--primary);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .btn-primary:active {
            background: var(--primary-hover);
            transform: scale(0.98);
        }
        .btn-secondary {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--hairline-strong);
            border-radius: 999px;
            background: transparent;
            color: var(--ink);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .btn-secondary:active {
            background: var(--inset);
            transform: scale(0.98);
        }
        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: var(--mute);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <div class="brand-badge">KD</div>
            <span class="brand-title">KawalDuit</span>
        </div>

        <main class="card">
            <div class="icon-circle" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.5 10.672a6 6 0 0 0-.5 2.328m4.242 0a6 6 0 0 0-.586-1.572M6.343 6.343A10.965 10.965 0 0 0 3 12c2.164 4.092 6.386 7 11.5 7 1.846 0 3.585-.378 5.157-1.057M9.878 4.121A11.968 11.968 0 0 1 14.5 4c5.114 0 9.336 2.908 11.5 7a10.975 10.975 0 0 1-2.613 3.657" />
                </svg>
            </div>
            <h1>Koneksi Terputus</h1>
            <p>Anda sedang offline atau jaringan sedang bermasalah. Setoran tetap bisa dicatat dan data akan disinkronkan saat sinyal kembali.</p>

            <div class="actions">
                <button type="button" class="btn-primary" onclick="window.location.reload()">
                    Coba Lagi
                </button>
                <button type="button" class="btn-secondary" onclick="window.history.length > 1 ? window.history.back() : (window.location.href='/')">
                    Lanjut kerja offline
                </button>
            </div>
        </main>

        <p class="footer">KawalDuit · Layanan Tabungan Harian</p>
    </div>
</body>
</html>
