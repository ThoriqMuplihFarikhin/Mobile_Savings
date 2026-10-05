<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Setoran {{ 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f4f5;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: #18181b;
            padding: 24px;
        }
        .struk {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            padding: 28px 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }
        h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.02em;
        }
        .nomor {
            margin: 4px 0 0;
            text-align: center;
            font-family: ui-monospace, monospace;
            font-size: 13px;
            color: #52525b;
        }
        .garis {
            border: 0;
            border-top: 1px dashed #d4d4d8;
            margin: 18px 0;
        }
        dl { margin: 0; }
        dl > div {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 5px 0;
            font-size: 13px;
        }
        dt { color: #71717a; }
        dd {
            margin: 0;
            font-weight: 600;
            text-align: right;
        }
        .nominal {
            font-family: ui-monospace, monospace;
            font-size: 16px;
            font-weight: 800;
        }
        .status {
            margin: 16px 0 0;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f4f4f5;
        }
        .status-koreksi {
            margin: 8px 0 0;
            text-align: center;
            font-size: 12px;
            color: #b45309;
        }
        .aksi {
            display: flex;
            gap: 8px;
            margin-top: 20px;
        }
        .aksi button,
        .aksi a {
            flex: 1;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #e4e4e7;
        }
        .aksi button { background: #18181b; color: #fff; border-color: #18181b; }
        .aksi a { background: #fff; color: #18181b; }
        @media print {
            body { background: #fff; padding: 0; }
            .struk { border: 0; box-shadow: none; max-width: 100%; }
            .aksi { display: none; }
        }
    </style>
</head>
<body>
    <main class="struk">
        <h1>Struk Setoran</h1>
        <p class="nomor">No. {{ 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT) }}</p>

        <hr class="garis">

        <dl>
            <div>
                <dt>Tanggal</dt>
                <dd>{{ \Illuminate\Support\Carbon::parse($setoran->tanggal_transaksi)->translatedFormat('d M Y') }}</dd>
            </div>
            <div>
                <dt>Nasabah</dt>
                <dd>{{ $setoran->nasabah?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt>Produk</dt>
                <dd>{{ $setoran->produk?->nama ?? '-' }}</dd>
            </div>
            <div>
                <dt>Kolektor</dt>
                <dd>{{ $setoran->inputBy?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt>Nominal</dt>
                <dd class="nominal">Rp {{ number_format((float) $setoran->nominal, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <hr class="garis">

        <p class="status">Status: <strong>{{ ucfirst($setoran->status) }}</strong></p>

        @if ($setoran->status === 'dikoreksi' && $setoran->nominal_asli !== null)
            <p class="status-koreksi">
                Dikoreksi dari Rp {{ number_format((float) $setoran->nominal_asli, 0, ',', '.') }}
            </p>
        @endif

        <div class="aksi">
            <button type="button" onclick="window.print()">Cetak</button>
            <a href="{{ url()->previous() }}">Kembali</a>
        </div>
    </main>
</body>
</html>
