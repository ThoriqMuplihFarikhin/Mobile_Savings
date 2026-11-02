<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Struk Setoran {{ 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { box-sizing: border-box; }
        :root {
            --primary: #15803d;
            --primary-hover: #166534;
            --primary-soft: #dcfce7;
            --primary-soft-ink: #14532d;
            --amber-soft: #fef3c7;
            --amber-soft-ink: #78350f;
            --ink: #171717;
            --mute: #737373;
            --hairline: #e5e5e5;
            --surface: #ffffff;
            --backdrop: rgba(15, 23, 18, 0.6);
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: flex-end; /* Mobile: bottom sheet */
            justify-content: center;
            background: var(--backdrop);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--ink);
            padding: 0;
        }
        @media (min-width: 640px) {
            body {
                align-items: center; /* Tablet / Desktop: centered modal */
                padding: 24px;
            }
        }

        /* Backdrop hitbox for dismiss */
        .backdrop-hitbox {
            position: fixed;
            inset: 0;
            z-index: 1;
            cursor: pointer;
        }

        /* Sheet / Pop-up Dialog */
        .struk-popup {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border-radius: 28px 28px 0 0;
            padding: 16px 20px 24px;
            box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 92vh;
            overflow-y: auto;
        }
        @media (min-width: 640px) {
            .struk-popup {
                border-radius: 24px;
                padding: 24px;
                box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.3);
                animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            }
        }
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes popIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .drag-handle {
            width: 36px;
            height: 4px;
            background: #d4d4d4;
            border-radius: 999px;
            margin: 0 auto 12px;
        }
        @media (min-width: 640px) {
            .drag-handle { display: none; }
        }

        .popup-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--hairline);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
        }
        .brand-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .brand-icon svg {
            width: 16px;
            height: 16px;
        }
        .btn-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            border: none;
            background: #f5f5f5;
            color: var(--mute);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-close:hover {
            background: #e5e5e5;
            color: var(--ink);
        }
        .btn-close svg {
            width: 16px;
            height: 16px;
        }

        /* Receipt ticket slip card */
        .slip-card {
            background: #f3f7f1;
            border: 1px solid #e1eadd;
            border-radius: 20px;
            padding: 18px 16px;
            margin-top: 14px;
            position: relative;
        }

        .slip-badge {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-soft-ink);
            box-shadow: 0 0 0 4px rgba(220, 252, 231, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .slip-badge svg {
            width: 24px;
            height: 24px;
        }

        .judul-wrapper {
            text-align: center;
            margin-top: 10px;
        }
        h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--ink);
        }
        .nomor-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ffffff;
            border: 1px solid #e5e5e5;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            color: var(--mute);
            margin-top: 6px;
        }
        .nomor-pill strong {
            color: var(--ink);
        }

        .nominal-wrapper {
            text-align: center;
            margin: 14px 0 10px;
        }
        .nominal-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--mute);
            margin-bottom: 2px;
        }
        .nominal {
            font-variant-numeric: tabular-nums;
            font-size: 28px;
            font-weight: 600;
            color: var(--primary);
            letter-spacing: -0.02em;
        }
        .status-container {
            margin-top: 6px;
            text-align: center;
        }
        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 12px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-soft-ink);
            border: 1px solid #bbf7d0;
        }
        .status .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--primary);
        }
        .status.dikoreksi {
            background: var(--amber-soft);
            color: var(--amber-soft-ink);
            border: 1px solid #fde68a;
        }
        .status.dikoreksi .status-dot {
            background: #d97706;
        }
        .status-koreksi {
            margin: 6px 0 0;
            text-align: center;
            font-size: 12px;
            color: var(--mute);
        }
        .status-koreksi .line-through {
            text-decoration: line-through;
            font-weight: 600;
            color: var(--ink);
        }

        /* Perforation Tear Notch */
        .perforation-wrap {
            position: relative;
            margin: 16px -16px;
            display: flex;
            align-items: center;
        }
        .perforation-notch-left {
            width: 16px;
            height: 16px;
            margin-left: -8px;
            border-radius: 0 999px 999px 0;
            background: var(--surface);
            border: 1px solid #e1eadd;
            border-left: none;
        }
        .perforation-line {
            flex: 1;
            border-top: 1px dashed #c4c4c4;
        }
        .perforation-notch-right {
            width: 16px;
            height: 16px;
            margin-right: -8px;
            border-radius: 999px 0 0 999px;
            background: var(--surface);
            border: 1px solid #e1eadd;
            border-right: none;
        }

        dl { margin: 0; }
        dl > div {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 5px 0;
            font-size: 13px;
        }
        dt { color: var(--mute); }
        dd {
            margin: 0;
            font-weight: 600;
            text-align: right;
            color: var(--ink);
            font-variant-numeric: tabular-nums;
        }

        .slip-footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #e1eadd;
            text-align: center;
        }
        .slip-footer p {
            margin: 0;
            font-size: 12px;
            color: var(--mute);
            font-weight: 500;
        }
        .slip-footer .subtext {
            margin-top: 3px;
            font-size: 11px;
            color: #858585;
            font-weight: normal;
        }

        .aksi {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .aksi button {
            flex: 1;
            height: 44px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: 1px solid var(--hairline);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.15s ease;
        }
        .aksi button svg {
            width: 15px;
            height: 15px;
        }
        .aksi .btn-cetak {
            background: #fff;
            color: var(--ink);
            border-color: #858585;
        }
        .aksi .btn-cetak:hover {
            background: #f5f5f5;
        }
        .aksi .btn-bagikan {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 1px 2px rgba(21, 128, 61, 0.2);
        }
        .aksi .btn-bagikan:hover {
            background: var(--primary-hover);
        }

        .aksi-kembali {
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 40px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            color: var(--mute);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .aksi-kembali:hover {
            color: var(--ink);
            background: #f5f5f5;
        }

        .toast {
            display: none;
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #171717;
            color: #fff;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 100;
            pointer-events: none;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                align-items: flex-start !important;
            }
            .backdrop-hitbox,
            .drag-handle,
            .btn-close,
            .popup-header,
            .aksi,
            .aksi-kembali,
            .toast {
                display: none !important;
            }
            .struk-popup {
                border: 0 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 0 !important;
                animation: none !important;
            }
            .slip-card {
                background: #fff !important;
                border: 1px solid #171717 !important;
                border-radius: 0 !important;
                padding: 16px !important;
                margin: 0 !important;
            }
            .slip-badge {
                display: none !important;
            }
            .perforation-notch-left,
            .perforation-notch-right {
                display: none !important;
            }
            .perforation-line {
                border-top: 1px dashed #171717 !important;
            }
            .nominal {
                color: #000 !important;
            }
            .status {
                border: 1px solid #000 !important;
                background: transparent !important;
                color: #000 !important;
            }
            .status .status-dot {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    {{-- Clicking backdrop area dismisses the pop up --}}
    <div class="backdrop-hitbox" onclick="tutupPopUp()" aria-hidden="true"></div>

    <main class="struk-popup" role="dialog" aria-modal="true" aria-labelledby="judul-struk">
        <div class="drag-handle" aria-hidden="true"></div>

        <div class="popup-header">
            <div class="brand">
                <span class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </span>
                <span>Tabungan Digital</span>
            </div>
            <button type="button" class="btn-close" onclick="tutupPopUp()" aria-label="Tutup" title="Tutup struk">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="slip-card">
            <div class="slip-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <div class="judul-wrapper">
                <h1 id="judul-struk">Struk Setoran</h1>
                <div class="nomor-pill">
                    <span>No.</span>
                    <strong>{{ 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT) }}</strong>
                </div>
            </div>

            <div class="nominal-wrapper">
                <div class="nominal-label">Total Setoran</div>
                <div class="nominal">Rp {{ number_format((float) $setoran->nominal, 0, ',', '.') }}</div>
                <div class="status-container">
                    <span class="status {{ $setoran->status === 'dikoreksi' ? 'dikoreksi' : '' }}">
                        <span class="status-dot"></span>
                        <span>{{ $setoran->status === 'dikoreksi' ? 'Dikoreksi' : 'Diterima' }}</span>
                    </span>
                </div>
                @if ($setoran->status === 'dikoreksi' && $setoran->nominal_asli !== null)
                    <p class="status-koreksi">
                        Dikoreksi dari <span class="line-through">Rp {{ number_format((float) $setoran->nominal_asli, 0, ',', '.') }}</span>
                    </p>
                @endif
            </div>

            <div class="perforation-wrap" aria-hidden="true">
                <div class="perforation-notch-left"></div>
                <div class="perforation-line"></div>
                <div class="perforation-notch-right"></div>
            </div>

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
            </dl>

            <div class="slip-footer">
                <p>Terima kasih sudah menabung.</p>
                <div class="subtext">Bukti transaksi sah yang tercatat di sistem.</div>
            </div>
        </div>

        <div class="aksi">
            <button type="button" class="btn-cetak" onclick="window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Cetak</span>
            </button>
            <button type="button" class="btn-bagikan" onclick="bagikanStruk()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="5" r="3"></circle>
                    <circle cx="6" cy="12" r="3"></circle>
                    <circle cx="18" cy="19" r="3"></circle>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                </svg>
                <span>Bagikan</span>
            </button>
        </div>
        <button type="button" class="aksi-kembali" onclick="tutupPopUp()">Tutup Struk</button>
    </main>

    <div id="toast" class="toast">Struk disalin ke clipboard</div>

    <script>
        function tutupPopUp() {
            if (window.history.length > 1 && document.referrer) {
                window.history.back();
            } else {
                window.location.href = "{{ route('dashboard') }}";
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupPopUp();
            }
        });

        function bagikanStruk() {
            const teks = 'Struk Setoran No. {{ "SET-".str_pad((string) $setoran->id, 6, "0", STR_PAD_LEFT) }}\nNasabah: {{ $setoran->nasabah?->name ?? "-" }}\nNominal: Rp {{ number_format((float) $setoran->nominal, 0, ",", ".") }}\nTanggal: {{ \Illuminate\Support\Carbon::parse($setoran->tanggal_transaksi)->translatedFormat("d M Y") }}';
            if (navigator.share) {
                navigator.share({
                    title: 'Struk Setoran Tabungan Digital',
                    text: teks,
                    url: window.location.href
                }).catch(() => {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(teks).then(() => {
                    tampilkanToast();
                });
            } else {
                tampilkanToast();
            }
        }

        function tampilkanToast() {
            const toast = document.getElementById('toast');
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2500);
        }
    </script>
</body>
</html>
