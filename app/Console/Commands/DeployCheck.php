<?php

namespace App\Console\Commands;

use App\Models\AdminSetting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Throwable;

/**
 * F3 / D28 — pemeriksaan kesiapan produksi sebelum deploy.
 *
 * Seluruh butir dibaca dari config() (aman saat config di-cache) dan dari
 * public_path(), sehingga pemeriksaan identik di lokal, CI, dan produksi.
 */
#[Signature('deploy:check')]
#[Description('Pemeriksaan kesiapan produksi: env, artefak build, penjadwal, dan akses berkas')]
class DeployCheck extends Command
{
    /**
     * Perintah penjadwal yang wajib terdaftar agar tugas harian berjalan.
     *
     * @var array<int, string>
     */
    private const PENJADWAL_WAJIB = [
        'penarikan:kedaluwarsakan',
        'jadwal:generate',
        'paket:hitung-ulang',
    ];

    /**
     * Hasil pemeriksaan lulus/gagal.
     *
     * @var array<int, array{kode: string, nilai: string, lulus: bool, alasan: string}>
     */
    private array $hasil = [];

    /**
     * Keterangan informatif yang tidak memengaruhi kelulusan.
     *
     * @var array<int, array{kode: string, nilai: string}>
     */
    private array $info = [];

    public function handle(): int
    {
        $this->line('Pemeriksaan kesiapan produksi (deploy:check)');
        $this->line(str_repeat('-', 44));

        $this->periksaKonfigurasi();
        $this->periksaArtefak();
        $this->periksaPenjadwal();
        $this->periksaAksesBerkas();
        $this->laporkanSetelan();

        foreach ($this->hasil as $hasil) {
            $this->line(sprintf(
                '[%s] %s=%s%s',
                $hasil['lulus'] ? 'LULUS' : 'GAGAL',
                $hasil['kode'],
                $hasil['nilai'],
                $hasil['lulus'] ? '' : ' ('.$hasil['alasan'].')'
            ));
        }

        foreach ($this->info as $baris) {
            $this->line(sprintf('[INFO] %s=%s', $baris['kode'], $baris['nilai']));
        }

        $lulus = count(array_filter($this->hasil, fn (array $h): bool => $h['lulus']));
        $gagal = count($this->hasil) - $lulus;

        $this->line(sprintf('Ringkasan: %d lulus, %d gagal, %d info', $lulus, $gagal, count($this->info)));

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function periksaKonfigurasi(): void
    {
        $env = $this->teks('app.env');
        $this->catat('APP_ENV', $env, $env === 'production', 'harus production');

        $this->catat('APP_DEBUG', $this->teks('app.debug'), config('app.debug') === false, 'harus false');

        $url = $this->teks('app.url');
        $this->catat('APP_URL', $url, str_starts_with($url, 'https://'), 'harus berawalan https://');

        $this->catat(
            'SESSION_SECURE_COOKIE',
            $this->teks('session.secure'),
            config('session.secure') === true,
            'harus true agar cookie hanya lewat HTTPS'
        );

        $db = $this->teks('database.default');
        $this->catat('DB_DRIVER', $db, $db === 'mysql', 'harus mysql (SQLite tidak mendukung lockForUpdate)');

        $cache = config('cache.default');
        $this->catat(
            'CACHE_STORE',
            $this->teks('cache.default'),
            ! in_array($cache, ['array', 'sync'], true),
            'array/sync hanya untuk pengujian'
        );

        $queue = config('queue.default');
        $this->catat(
            'QUEUE_CONNECTION',
            $this->teks('queue.default'),
            ! in_array($queue, ['array', 'sync'], true),
            'sync hanya untuk pengujian'
        );

        $kunci = $this->teks('app.key', '');
        $this->catat('APP_KEY', $kunci === '' ? '(kosong)' : 'terisi', $kunci !== '', 'APP_KEY harus terisi');
    }

    private function periksaArtefak(): void
    {
        $hot = file_exists(public_path('hot'));
        $this->catat('PUBLIC_HOT', $hot ? 'ada' : 'tidak ada', ! $hot, 'hapus public/hot sebelum deploy');

        $font = file_exists(public_path('fonts-manifest.dev.json'));
        $this->catat(
            'FONTS_MANIFEST_DEV',
            $font ? 'ada' : 'tidak ada',
            ! $font,
            'hapus berkas dev ini sebelum deploy'
        );

        $manifest = file_exists(public_path('build/manifest.json'));
        $this->catat('BUILD_MANIFEST', $manifest ? 'ada' : 'tidak ada', $manifest, 'jalankan npm run build');
    }

    private function periksaPenjadwal(): void
    {
        /** @var Schedule $penjadwal */
        $penjadwal = $this->laravel->make(Schedule::class);

        $terdaftar = '';

        foreach ($penjadwal->events() as $event) {
            if (is_string($event->command)) {
                $terdaftar .= ' '.$event->command;
            }
        }

        $kurang = array_values(array_filter(
            self::PENJADWAL_WAJIB,
            fn (string $perintah): bool => ! str_contains($terdaftar, $perintah)
        ));

        $this->catat(
            'SCHEDULE',
            count($penjadwal->events()).' perintah',
            $kurang === [],
            'belum terdaftar: '.implode(', ', $kurang)
        );
    }

    private function periksaAksesBerkas(): void
    {
        $storage = storage_path();
        $bootstrap = base_path('bootstrap/cache');
        $dapat = is_writable($storage) && is_writable($bootstrap);

        $this->catat(
            'STORAGE_WRITABLE',
            $dapat ? 'ya' : 'tidak',
            $dapat,
            'storage dan bootstrap/cache harus dapat ditulis'
        );
    }

    /**
     * Setelan aplikasi dilaporkan sebagai info: nilainya penting dibaca
     * sebelum deploy, tetapi tidak menentukan lulus/gagal.
     */
    private function laporkanSetelan(): void
    {
        try {
            $kasMinus = AdminSetting::get('izinkan_kas_minus', 'false');
            $batas = AdminSetting::get('offline_penarikan_batas_langsung', '0');
        } catch (Throwable) {
            $kasMinus = null;
            $batas = null;
        }

        $this->info[] = [
            'kode' => 'izinkan_kas_minus',
            'nilai' => $kasMinus ?? '(tidak dapat dibaca)',
        ];
        $this->info[] = [
            'kode' => 'offline_penarikan_batas_langsung',
            'nilai' => $batas ?? '(tidak dapat dibaca)',
        ];
    }

    private function catat(string $kode, string $nilai, bool $lulus, string $alasan = ''): void
    {
        $this->hasil[] = compact('kode', 'nilai', 'lulus', 'alasan');
    }

    private function teks(string $kunci, string $default = '(kosong)'): string
    {
        $nilai = config($kunci);

        if (is_string($nilai)) {
            return $nilai;
        }

        if (is_bool($nilai)) {
            return $nilai ? 'true' : 'false';
        }

        if (is_int($nilai) || is_float($nilai)) {
            return (string) $nilai;
        }

        return $default;
    }
}
