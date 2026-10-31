@props([
    'status' => null,
    'domain' => null,
    'teks' => null,
])

@php
    $nilai = (string)($status ?? $slot);
    $label = $teks ?? $nilai;

    $domainMap = [
        'pendaftaran' => [
            'pending_verifikasi' => ['chip' => 'wr', 'label' => 'Pending'],
            'aktif' => ['chip' => 'ok', 'label' => 'Aktif'],
            'ditolak' => ['chip' => 'er', 'label' => 'Ditolak'],
        ],
        'akun' => [
            'aktif' => ['chip' => 'ok', 'label' => 'Aktif'],
            'terkunci' => ['chip' => 'er', 'label' => 'Terkunci'],
        ],
        'produk' => [
            'aktif' => ['chip' => 'ok', 'label' => 'Aktif'],
            'nonaktif' => ['chip' => 'mu', 'label' => 'Nonaktif'],
        ],
        'mode_akses' => [
            'digital' => ['chip' => 'in', 'label' => 'Digital'],
            'offline' => ['chip' => 'mu', 'label' => 'Offline'],
        ],
        'setoran' => [
            'tercatat' => ['chip' => 'ok', 'label' => 'Tercatat'],
            'dikoreksi' => ['chip' => 'wr', 'label' => 'Dikoreksi'],
            'dibatalkan' => ['chip' => 'mu', 'label' => 'Dibatalkan'],
        ],
        'penarikan' => [
            'pending' => ['chip' => 'wr', 'label' => 'Pending'],
            'approved' => ['chip' => 'in', 'label' => 'Disetujui'],
            'selesai' => ['chip' => 'ok', 'label' => 'Selesai'],
            'ditolak' => ['chip' => 'er', 'label' => 'Ditolak'],
            'dibatalkan' => ['chip' => 'mu', 'label' => 'Dibatalkan'],
            'kedaluwarsa' => ['chip' => 'mu', 'label' => 'Kedaluwarsa'],
        ],
        'setor_kas' => [
            'pending' => ['chip' => 'wr', 'label' => 'Menunggu'],
            'cocok' => ['chip' => 'ok', 'label' => 'Cocok'],
            'lebih' => ['chip' => 'wr', 'label' => 'Lebih'],
            'kurang' => ['chip' => 'er', 'label' => 'Kurang'],
            'dibatalkan' => ['chip' => 'mu', 'label' => 'Dibatalkan'],
        ],
        'tinjauan_offline' => [
            'sesuai' => ['chip' => 'ok', 'label' => 'Sesuai'],
            'bermasalah' => ['chip' => 'er', 'label' => 'Bermasalah'],
            '' => ['chip' => 'wr', 'label' => 'Belum ditinjau'],
        ],
        'komplain' => [
            'baru' => ['chip' => 'wr', 'label' => 'Baru'],
            'diproses' => ['chip' => 'in', 'label' => 'Diproses'],
            'selesai' => ['chip' => 'ok', 'label' => 'Selesai'],
        ],
        'kas_kolektor' => [
            'aman' => ['chip' => 'ok', 'label' => 'Aman'],
            'lewat_batas' => ['chip' => 'er', 'label' => 'Lewat Batas'],
        ],
    ];

    if ($domain && isset($domainMap[$domain][$nilai])) {
        $tipe = $domainMap[$domain][$nilai]['chip'];
        $label = $teks ?? $domainMap[$domain][$nilai]['label'];
    } else {
        $TN = [
            'Aktif' => 'ok', 'aktif' => 'ok',
            'Pending' => 'wr', 'pending' => 'wr', 'pending_verifikasi' => 'wr',
            'Ditolak' => 'er', 'ditolak' => 'er',
            'Terkunci' => 'er', 'terkunci' => 'er',
            'Disetujui' => 'in', 'approved' => 'in',
            'Selesai' => 'ok', 'selesai' => 'ok',
            'Dibatalkan' => 'mu', 'dibatalkan' => 'mu',
            'Kedaluwarsa' => 'mu', 'kedaluwarsa' => 'mu',
            'Baru' => 'wr', 'baru' => 'wr',
            'Diproses' => 'in', 'diproses' => 'in',
            'Tercatat' => 'ok', 'tercatat' => 'ok',
            'Dikoreksi' => 'wr', 'dikoreksi' => 'wr',
            'Perlu Review' => 'wr', 'perlu_review' => 'wr',
            'Cocok' => 'ok', 'cocok' => 'ok',
            'Lebih' => 'wr', 'lebih' => 'wr',
            'Kurang' => 'er', 'kurang' => 'er',
            'Menunggu' => 'wr', 'menunggu' => 'wr',
            'Belum ditinjau' => 'wr', 'belum_ditinjau' => 'wr',
            'Sesuai' => 'ok', 'sesuai' => 'ok',
            'Bermasalah' => 'er', 'bermasalah' => 'er',
            'Aman' => 'ok', 'aman' => 'ok',
            'Lewat Batas' => 'er', 'lewat_batas' => 'er',
            'Diizinkan' => 'ok', 'diizinkan' => 'ok',
            'Diblokir' => 'er', 'diblokir' => 'er',
            'Nonaktif' => 'mu', 'nonaktif' => 'mu',
            'Digital' => 'in', 'digital' => 'in',
            'Offline' => 'mu', 'offline' => 'mu',
            'Boleh' => 'ok', 'boleh' => 'ok',
            'Sudah absen' => 'ok', 'sudah_absen' => 'ok',
            'Izin' => 'in', 'izin' => 'in',
            'Belum absen' => 'wr', 'belum_absen' => 'wr',
            'Tidak masuk' => 'er', 'tidak_masuk' => 'er',
            'Diterima' => 'ok', 'diterima' => 'ok',
            'Berhasil' => 'ok', 'berhasil' => 'ok',
            'Ditinjau' => 'wr', 'ditinjau' => 'wr',
        ];
        $tipe = $TN[$nilai] ?? 'mu';
    }

    $warna = match($tipe) {
        'ok' => 'bg-primary-soft text-on-primary-soft',
        'wr' => 'bg-warning-soft text-warning-deep',
        'er' => 'bg-danger-soft text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text',
        'in' => 'bg-info-soft text-info',
        default => 'bg-inset text-mute',
    };
@endphp

<span data-test="status-chip" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium leading-4 whitespace-nowrap ' . $warna]) }}>
    {{ $label }}
</span>
