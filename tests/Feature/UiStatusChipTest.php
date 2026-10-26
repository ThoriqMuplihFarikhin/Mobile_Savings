<?php

use App\Models\KepesertaanPaket;
use Illuminate\Support\Facades\Blade;

/**
 * DESIGN.md §2 "Pemetaan status -> chip".
 * Nilai enum diambil dari migrasi enum(...) pada nasabah_profil, users,
 * produk_tabungan, transaksi_setoran, transaksi_penarikan,
 * setoran_kolektor_kantor, izin_kolektor, kepesertaan_paket, komplain,
 * jadwal_kunjungan, log_notifikasi, dan log_handover_kolektor; domain
 * `pencairan` memakai hasil KepesertaanPaket::statusPencairan() (bukan enum).
 * [kelas chip, teks chip]; kelas null = "tanpa chip" pada tabel §2.
 */
$semuaDomain = [
    'pendaftaran' => [
        'pending_verifikasi' => ['bg-warning-soft', 'Menunggu verifikasi'],
        'aktif' => ['bg-primary-soft', 'Aktif'],
        'ditolak' => ['bg-danger-soft', 'Ditolak'],
    ],
    'akun' => [
        'aktif' => ['bg-primary-soft', 'Aktif'],
        'terkunci' => ['bg-danger-soft', 'Terkunci'],
    ],
    'produk' => [
        'aktif' => ['bg-primary-soft', 'Aktif'],
        'nonaktif' => ['bg-inset', 'Nonaktif'],
    ],
    'mode_akses' => [
        'digital' => [null, null],
        'offline' => ['bg-inset', 'Offline'],
    ],
    'setoran' => [
        'tercatat' => ['bg-inset', 'Tercatat'],
        'dikoreksi' => ['bg-warning-soft', 'Dikoreksi'],
        'dibatalkan' => ['bg-inset', 'Dibatalkan'],
    ],
    'penarikan' => [
        'pending' => ['bg-warning-soft', 'Menunggu'],
        'approved' => ['bg-info-soft', 'Disetujui'],
        'selesai' => ['bg-primary-soft', 'Selesai'],
        'ditolak' => ['bg-danger-soft', 'Ditolak'],
        'dibatalkan' => ['bg-inset', 'Dibatalkan'],
        'kedaluwarsa' => ['bg-inset', 'Kedaluwarsa'],
    ],
    'setor_kas' => [
        'pending' => ['bg-warning-soft', 'Menunggu'],
        'cocok' => ['bg-primary-soft', 'Cocok'],
        'lebih' => ['bg-info-soft', 'Lebih'],
        'kurang' => ['bg-danger-soft', 'Kurang'],
        'dibatalkan' => ['bg-inset', 'Dibatalkan'],
    ],
    'persetujuan' => [
        'pending' => ['bg-warning-soft', 'Menunggu'],
        'disetujui' => ['bg-primary-soft', 'Disetujui'],
        'ditolak' => ['bg-danger-soft', 'Ditolak'],
    ],
    'tinjauan_offline' => [
        'sesuai' => ['bg-primary-soft', 'Sesuai'],
        'bermasalah' => ['bg-danger-soft', 'Bermasalah'],
    ],
    'alert_paket' => [
        'normal' => [null, null],
        'peringatan' => ['bg-warning-soft', 'Peringatan'],
        'perlu_review' => ['bg-danger-soft', 'Perlu tinjauan'],
    ],
    'komplain' => [
        'baru' => ['bg-info-soft', 'Baru'],
        'diproses' => ['bg-warning-soft', 'Diproses'],
        'selesai' => ['bg-primary-soft', 'Selesai'],
    ],
    'kunjungan' => [
        'dikunjungi' => ['bg-primary-soft', 'Dikunjungi'],
        'dilewati' => ['bg-inset', 'Dilewati'],
        'tidak_ada' => ['bg-warning-soft', 'Tidak ada'],
    ],
    'serah_terima' => [
        'belum' => ['bg-warning-soft', 'Belum'],
        'sudah_diterima' => ['bg-primary-soft', 'Sudah diterima'],
    ],
    'notifikasi' => [
        'antri' => ['bg-inset', 'Antri'],
        'terkirim' => ['bg-primary-soft', 'Terkirim'],
        'gagal' => ['bg-danger-soft', 'Gagal'],
    ],
    'kas_handover' => [
        'lunas' => ['bg-primary-soft', 'Lunas'],
        'masih_tunggakan' => ['bg-danger-soft', 'Masih tunggakan'],
    ],
    'pencairan' => [
        'boleh' => ['bg-primary-soft', 'Sudah Bisa Dicairkan'],
        'belum_waktunya' => ['bg-inset', 'Belum waktunya'],
        'tanggal_kosong' => ['bg-warning-soft', KepesertaanPaket::ALASAN_TANGGAL_KOSONG],
        'ada_tunggakan' => ['bg-warning-soft', KepesertaanPaket::ALASAN_TUNGGAKAN],
        'belum_komitmen' => ['bg-warning-soft', KepesertaanPaket::ALASAN_BELUM_KOMITMEN],
        'sudah_diserahkan' => ['bg-inset', KepesertaanPaket::ALASAN_SUDAH_DISERAHKAN],
    ],
];

$kasusStatus = [];
foreach ($semuaDomain as $domain => $status) {
    foreach ($status as $nilai => [$kelas, $teks]) {
        $kasusStatus[$domain.' · '.$nilai] = [$domain, $nilai, $kelas, $teks];
    }
}

it('memetakan tiap nilai enum ke chip dari tabel DESIGN.md §2', function (string $domain, string $nilai, ?string $kelas, ?string $teks) {
    $html = Blade::render('<x-ui.status-chip :domain="$domain" :status="$status" />', [
        'domain' => $domain,
        'status' => $nilai,
    ]);

    if ($kelas === null) {
        expect(trim($html))->toBe('');

        return;
    }

    expect($html)
        ->toContain('data-test="status-chip"')
        ->toContain($kelas)
        ->toContain($teks)
        ->toContain('text-xs');
})->with($kasusStatus);

it('menampilkan chip peringatan untuk tinjauan penarikan offline yang belum ditinjau', function () {
    $html = Blade::render('<x-ui.status-chip domain="tinjauan_offline" :status="null" />');

    expect($html)
        ->toContain('bg-warning-soft')
        ->toContain('Belum ditinjau');
});

it('menampilkan chip netral dengan teks asli untuk status yang tidak dikenal', function () {
    $html = Blade::render('<x-ui.status-chip domain="penarikan" status="status-baru" />');

    expect($html)
        ->toContain('data-test="status-chip"')
        ->toContain('bg-inset')
        ->toContain('status-baru');
});

it('tidak menghasilkan chip dan tidak melempar error saat status kosong', function () {
    $html = Blade::render('<x-ui.status-chip domain="komplain" :status="null" />');

    expect(trim($html))->toBe('', 'Status kosong seharusnya tidak menghasilkan chip');
});

it('memakai teks dinamis dari view dengan warna tetap dari pemetaan', function () {
    $html = Blade::render(
        '<x-ui.status-chip domain="pencairan" status="belum_waktunya" teks="15 Hari Lagi" />'
    );

    expect($html)
        ->toContain('data-test="status-chip"')
        ->toContain('bg-inset')
        ->toContain('15 Hari Lagi')
        ->not->toContain('Belum waktunya');
});

it('memprioritaskan teks dinamis di atas label status yang tidak dikenal', function () {
    $html = Blade::render(
        '<x-ui.status-chip domain="pencairan" status="status-baru" teks="15 Hari Lagi" />'
    );

    expect($html)
        ->toContain('bg-inset')
        ->toContain('15 Hari Lagi')
        ->not->toContain('status-baru');
});
