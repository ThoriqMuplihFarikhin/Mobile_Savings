<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Mutasi {{ $nasabah->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f5f5f5;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: #171717;
            padding: 24px;
        }
        .lembar {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            border-bottom: 2px solid #171717;
            padding-bottom: 12px;
        }
        h1 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 0.02em; }
        .nasabah { margin: 4px 0 0; font-size: 14px; color: #404040; }
        .lencana {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 10px;
            border-radius: 999px;
            background: #e5e5e5;
            font-size: 12px;
            font-weight: 600;
            color: #404040;
        }
        .tanggal-cetak {
            font-size: 12px;
            color: #525252;
            text-align: right;
        }
        .ringkasan {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 16px 0;
        }
        .kartu {
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 12px;
        }
        .kartu p { margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: #737373; }
        .kartu .angka { margin-top: 6px; font-variant-numeric: tabular-nums; font-size: 17px; font-weight: 600; color: #171717; }
        h2 { margin: 20px 0 8px; font-size: 15px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 8px 10px; border-bottom: 1px solid #e5e5e5; text-align: left; }
        th { font-family: ui-monospace, monospace; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #525252; background: #fafafa; }
        td.num { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
        .masuk { color: #171717; }
        .keluar { color: #171717; }
        .kosong { padding: 16px; text-align: center; color: #737373; font-size: 13px; }
        .aksi { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
        .aksi form { margin: 0; }
        .aksi button, .aksi a {
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 999px;
            border: 1px solid #e5e5e5;
            background: #fff;
            color: #171717;
            cursor: pointer;
            text-decoration: none;
        }
        .aksi button { background: #171717; border-color: #171717; color: #fff; }
        .tanda-tangan { display: flex; gap: 16px; margin-top: 26px; }
        .tanda-tangan > div { flex: 1; text-align: center; }
        .tanda-tangan .label { margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: #525252; }
        .tanda-tangan .garis { display: block; height: 54px; border-bottom: 1px dashed #a3a3a3; }
        .tanda-tangan .nama { margin: 6px 0 0; font-size: 13px; font-weight: 600; color: #171717; }
        @media print {
            body { background: #fff; padding: 0; }
            .lembar { border: none; box-shadow: none; padding: 0; max-width: none; border-radius: 0; }
            .aksi { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="lembar">
        <header>
            <div>
                <h1>Rekap Mutasi</h1>
                <p class="nasabah">
                    {{ $nasabah->name }}
                    @if($nasabah->no_hp !== null) &middot; {{ $nasabah->no_hp }} @endif
                    @if($nasabah->isOffline())<span class="lencana">Mode Offline</span>@endif
                </p>
            </div>
            <div class="tanggal-cetak">
                Dicetak {{ now()->format('d-m-Y H:i') }}<br>
                Untuk buku tabungan fisik
            </div>
        </header>

        <div class="ringkasan">
            <div class="kartu">
                <p>Total Setoran</p>
                <div class="angka">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</div>
            </div>
            <div class="kartu">
                <p>Total Penarikan</p>
                <div class="angka">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</div>
            </div>
            <div class="kartu">
                <p>Saldo Sekarang</p>
                <div class="angka">Rp {{ number_format($saldoSekarang, 0, ',', '.') }}</div>
            </div>
        </div>

        @if($saldoPerProduk->isNotEmpty())
            <h2>Saldo per Produk</h2>
            <table>
                <thead>
                    <tr><th>Produk</th><th class="num">Saldo</th></tr>
                </thead>
                <tbody>
                    @foreach($saldoPerProduk as $baris)
                        <tr>
                            <td>{{ $baris->produk->nama ?? '-' }}</td>
                            <td class="num">Rp {{ number_format((float) $baris->saldo, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h2>Kronologi Mutasi</h2>
        @if($kronologi->isEmpty())
            <div class="kosong">Belum ada mutasi tercatat.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Produk</th>
                        <th class="num">Nominal</th>
                        <th class="num">Saldo Berjalan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kronologi as $baris)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($baris['tanggal'])->format('d-m-Y') }}</td>
                            <td>{{ $baris['tipe'] }}</td>
                            <td>{{ $baris['produk'] }}</td>
                            <td class="num {{ $baris['arah'] > 0 ? 'masuk' : 'keluar' }}">
                                {{ $baris['arah'] > 0 ? '+' : '-' }} Rp {{ number_format($baris['nominal'], 0, ',', '.') }}
                            </td>
                            <td class="num">Rp {{ number_format($baris['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="tanda-tangan">
            <div>
                <p class="label">Tanda Tangan Nasabah</p>
                <span class="garis"></span>
                <p class="nama">{{ $nasabah->name }}</p>
            </div>
            <div>
                <p class="label">Tanggal Cetak</p>
                <span class="garis"></span>
                <p class="nama">{{ $tanggalCetak }}</p>
            </div>
            <div>
                <p class="label">Nama Kolektor</p>
                <span class="garis"></span>
                <p class="nama">{{ $namaKolektor }}</p>
            </div>
        </div>

        <div class="aksi">
            <a href="{{ route('dashboard') }}">Kembali</a>
            <form method="POST" action="{{ route('rekap.nasabah.cetak', $nasabah) }}">
                @csrf
                <button type="submit">Cetak Rekap</button>
            </form>
        </div>
    </div>
    @if (session('siapCetak'))
        <script>window.print();</script>
    @endif
</body>
</html>
