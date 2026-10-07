<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Offline - Tabungan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            padding-bottom: calc(24px + env(safe-area-inset-bottom));
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            color: #171717;
        }
        main {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border: 1px solid #ebebeb;
            border-radius: 24px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
        }
        .ikon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            border-radius: 20px;
            background: #f7d4d6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1 { font-size: 20px; font-weight: 700; letter-spacing: -0.02em; }
        p { margin-top: 8px; font-size: 14px; line-height: 1.6; color: #52525b; }
        button {
            margin-top: 24px;
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 999px;
            background: #3730a3;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        button:active { opacity: 0.85; }
    </style>
</head>
<body>
    <main>
        <div class="ikon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#c50000" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0119 12.55M5 12.55a10.94 10.94 0 015.17-2.39M10.71 5.05A16 16 0 0122.58 9M1.42 9a15.91 15.91 0 014.7-2.88M8.53 16.11a6 6 0 016.95 0M12 20h.01" />
            </svg>
        </div>
        <h1>Koneksi Terputus</h1>
        <p>Anda sedang offline atau jaringan sedang bermasalah. Periksa koneksi internet Anda lalu coba lagi.</p>
        <button type="button" onclick="location.reload()">Coba Lagi</button>
    </main>
</body>
</html>
