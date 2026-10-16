@props([
    'domain' => null,
    'status' => null,
    'teks' => null,
])

@php
    /**
     * Satu sumber pemetaan status -> chip (DESIGN.md §2). Satu domain, satu
     * pemetaan; warna hanya diambil dari token yang ada di dokumen.
     * Chip: success = primary-soft/on-primary-soft (nilai identik dengan
     * success-soft/success-deep), warning/info = token baru, danger memakai pasangan
     * gelap tersendiri karena --color-danger-soft lama harus tetap terang untuk admin.
     * Properti opsional `teks` menimpa label pemetaan untuk nilai yang dihitung di
     * view pemakai (mis. "15 Hari Lagi" di kartu paket beranda); warna tetap §2.
     */
    $pemetaan = [
        'pendaftaran' => [
            'pending_verifikasi' => ['chip' => 'warning', 'label' => 'Menunggu verifikasi'],
            'aktif' => ['chip' => 'success', 'label' => 'Aktif'],
            'ditolak' => ['chip' => 'danger', 'label' => 'Ditolak'],
        ],
        'akun' => [
            'aktif' => ['chip' => 'success', 'label' => 'Aktif'],
            'terkunci' => ['chip' => 'danger', 'label' => 'Terkunci'],
        ],
        'produk' => [
            'aktif' => ['chip' => 'success', 'label' => 'Aktif'],
            'nonaktif' => ['chip' => 'neutral', 'label' => 'Nonaktif'],
        ],
        'mode_akses' => [
            'digital' => ['chip' => null, 'label' => 'Digital'],
            'offline' => ['chip' => 'neutral', 'label' => 'Offline', 'ikon' => 'signal-slash'],
        ],
        'setoran' => [
            'tercatat' => ['chip' => 'neutral', 'label' => 'Tercatat', 'ikon' => 'check'],
            'dikoreksi' => ['chip' => 'warning', 'label' => 'Dikoreksi'],
            'dibatalkan' => ['chip' => 'neutral', 'label' => 'Dibatalkan'],
        ],
        'penarikan' => [
            'pending' => ['chip' => 'warning', 'label' => 'Menunggu'],
            'approved' => ['chip' => 'info', 'label' => 'Disetujui'],
            'selesai' => ['chip' => 'success', 'label' => 'Selesai'],
            'ditolak' => ['chip' => 'danger', 'label' => 'Ditolak'],
            'dibatalkan' => ['chip' => 'neutral', 'label' => 'Dibatalkan'],
            'kedaluwarsa' => ['chip' => 'neutral', 'label' => 'Kedaluwarsa'],
        ],
        'setor_kas' => [
            'pending' => ['chip' => 'warning', 'label' => 'Menunggu'],
            'cocok' => ['chip' => 'success', 'label' => 'Cocok'],
            'lebih' => ['chip' => 'info', 'label' => 'Lebih'],
            'kurang' => ['chip' => 'danger', 'label' => 'Kurang'],
            'dibatalkan' => ['chip' => 'neutral', 'label' => 'Dibatalkan'],
        ],
        'persetujuan' => [
            'pending' => ['chip' => 'warning', 'label' => 'Menunggu'],
            'disetujui' => ['chip' => 'success', 'label' => 'Disetujui'],
            'ditolak' => ['chip' => 'danger', 'label' => 'Ditolak'],
        ],
        'tinjauan_offline' => [
            'sesuai' => ['chip' => 'success', 'label' => 'Sesuai'],
            'bermasalah' => ['chip' => 'danger', 'label' => 'Bermasalah'],
            // NULL di database = belum ditinjau.
            '' => ['chip' => 'warning', 'label' => 'Belum ditinjau'],
        ],
        'alert_paket' => [
            'normal' => ['chip' => null, 'label' => 'Normal'],
            'peringatan' => ['chip' => 'warning', 'label' => 'Peringatan'],
            'perlu_review' => ['chip' => 'danger', 'label' => 'Perlu tinjauan'],
        ],
        'komplain' => [
            'baru' => ['chip' => 'info', 'label' => 'Baru'],
            'diproses' => ['chip' => 'warning', 'label' => 'Diproses'],
            'selesai' => ['chip' => 'success', 'label' => 'Selesai'],
        ],
        'kunjungan' => [
            'dikunjungi' => ['chip' => 'success', 'label' => 'Dikunjungi'],
            'dilewati' => ['chip' => 'neutral', 'label' => 'Dilewati'],
            'tidak_ada' => ['chip' => 'warning', 'label' => 'Tidak ada'],
        ],
        'serah_terima' => [
            'belum' => ['chip' => 'warning', 'label' => 'Belum'],
            'sudah_diterima' => ['chip' => 'success', 'label' => 'Sudah diterima'],
        ],
        'notifikasi' => [
            'antri' => ['chip' => 'neutral', 'label' => 'Antri'],
            'terkirim' => ['chip' => 'success', 'label' => 'Terkirim'],
            'gagal' => ['chip' => 'danger', 'label' => 'Gagal'],
        ],
        'kas_handover' => [
            'lunas' => ['chip' => 'success', 'label' => 'Lunas'],
            'masih_tunggakan' => ['chip' => 'danger', 'label' => 'Masih tunggakan'],
        ],
        // Status pencairan paket (D17): bukan enum database melainkan hasil
        // KepesertaanPaket::statusPencairan(). Warna dipilih dari tabel §2;
        // teksnya bisa berupa angka yang dihitung ("15 Hari Lagi"), jadi view
        // boleh mengirim `teks` sendiri.
        'pencairan' => [
            'boleh' => ['chip' => 'success', 'label' => 'Sudah Bisa Dicairkan'],
            'belum_waktunya' => ['chip' => 'neutral', 'label' => 'Belum waktunya'],
            'tanggal_kosong' => ['chip' => 'warning', 'label' => \App\Models\KepesertaanPaket::ALASAN_TANGGAL_KOSONG],
            'ada_tunggakan' => ['chip' => 'warning', 'label' => \App\Models\KepesertaanPaket::ALASAN_TUNGGAKAN],
            'belum_komitmen' => ['chip' => 'warning', 'label' => \App\Models\KepesertaanPaket::ALASAN_BELUM_KOMITMEN],
            'sudah_diserahkan' => ['chip' => 'neutral', 'label' => \App\Models\KepesertaanPaket::ALASAN_SUDAH_DISERAHKAN],
        ],
    ];

    $kunci = $status === null ? '' : (is_scalar($status) ? (string) $status : null);

    $aturan = is_string($domain) && $kunci !== null ? ($pemetaan[$domain][$kunci] ?? null) : null;

    // `teks` opsional: teks yang dihitung di view pemakai tetap memakai warna
    // dari pemetaan §2 (DESIGN: status tidak dibedakan hanya oleh warna).
    $teksDinamis = is_string($teks) && $teks !== '' ? $teks : null;

    if ($aturan !== null) {
        $chip = $aturan['chip'];
        $teks = $teksDinamis ?? $aturan['label'];
        $ikon = $aturan['ikon'] ?? null;
    } elseif ($kunci !== null && $kunci !== '') {
        // Status tak dikenal: chip netral dengan teks aslinya, tanpa error.
        $chip = 'neutral';
        $teks = $teksDinamis ?? $kunci;
        $ikon = null;
    } else {
        // Tidak ada status yang bisa ditampilkan.
        $chip = null;
        $teks = null;
        $ikon = null;
    }

    $kelasChip = $chip === null ? null : match ($chip) {
        'success' => 'bg-primary-soft text-on-primary-soft',
        'warning' => 'bg-warning-soft text-warning-deep',
        'danger' => 'bg-danger-soft text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text',
        'info' => 'bg-info-soft text-info',
        default => 'bg-inset text-body',
    };
@endphp

@if ($kelasChip !== null)
    <span data-test="status-chip" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium leading-4 '.$kelasChip]) }}>
        @if ($ikon === 'check')
            <flux:icon.check class="size-3.5" />
        @elseif ($ikon === 'signal-slash')
            <flux:icon.signal-slash class="size-3.5" />
        @endif
        {{ $teks }}
    </span>
@endif
