<?php

namespace App\Console\Commands;

use App\Models\AbsensiKolektor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PindahkanBerkasAbsensiLama extends Command
{
    protected $signature = 'absensi:pindahkan-berkas-lama {--dry-run : Hanya melaporkan tanpa mengubah apa pun}';

    protected $description = 'Memindahkan berkas absensi (selfie & tanda tangan) lama dari disk public ke disk private';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $dipindah = 0;
        $hilang = 0;
        $dilewati = 0;

        AbsensiKolektor::query()
            ->where(function ($query) {
                $query->whereNotNull('foto_selfie_path')
                    ->where('foto_selfie_path', 'not like', 'absensi/%')
                    ->orWhereNotNull('tanda_tangan_path')
                    ->where('tanda_tangan_path', 'not like', 'absensi/%');
            })
            ->chunkById(100, function ($rows) use (&$dipindah, &$hilang, &$dilewati, $dryRun) {
                foreach ($rows as $row) {
                    $update = [];
                    $pathLama = [];

                    foreach (['foto_selfie_path' => 'selfie', 'tanda_tangan_path' => 'tanda-tangan'] as $kolom => $prefix) {
                        $lama = $row->{$kolom};

                        if ($lama === null || str_starts_with($lama, 'absensi/')) {
                            if ($lama !== null) {
                                $dilewati++;
                            }

                            continue;
                        }

                        if (! Storage::disk('public')->exists($lama)) {
                            $hilang++;

                            continue;
                        }

                        $tujuan = 'absensi/'.$prefix.'-'.Str::uuid().'.'.$this->ekstensi($lama, $prefix);

                        if (! $dryRun) {
                            Storage::disk('local')->put($tujuan, Storage::disk('public')->get($lama));
                            $update[$kolom] = $tujuan;
                            $pathLama[] = $lama;
                        }

                        $dipindah++;
                    }

                    if ($update !== [] && ! $dryRun) {
                        $row->update($update);

                        Storage::disk('public')->delete($pathLama);
                    }
                }
            });

        $diarsipkan = $dryRun ? 0 : $this->arsipkanBerkasYatim();

        $this->info("Dipindah: {$dipindah}, hilang: {$hilang}, diarsipkan: {$diarsipkan}, dilewati: {$dilewati}.");

        if ($dryRun) {
            $this->info('Mode dry-run: tidak ada perubahan yang dilakukan.');
        }

        return self::SUCCESS;
    }

    /**
     * Pindahkan berkas di disk public yang tidak lagi dirujuk baris absensi mana pun
     * ke folder arsip privat agar tidak lagi terekspos lewat /storage.
     */
    private function arsipkanBerkasYatim(): int
    {
        $dirujuk = AbsensiKolektor::query()
            ->get(['foto_selfie_path', 'tanda_tangan_path'])
            ->flatMap(fn (AbsensiKolektor $row) => [$row->foto_selfie_path, $row->tanda_tangan_path])
            ->filter()
            ->flip();

        $diarsipkan = 0;

        foreach (['selfie', 'tanda_tangan'] as $folder) {
            foreach (Storage::disk('public')->allFiles($folder) as $file) {
                if (isset($dirujuk[$file])) {
                    continue;
                }

                Storage::disk('local')->put('absensi/arsip/'.basename($file), Storage::disk('public')->get($file));
                Storage::disk('public')->delete($file);
                $diarsipkan++;
            }
        }

        return $diarsipkan;
    }

    private function ekstensi(string $path, string $prefix): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === '') {
            return $prefix === 'selfie' ? 'jpg' : 'png';
        }

        return preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
    }
}
