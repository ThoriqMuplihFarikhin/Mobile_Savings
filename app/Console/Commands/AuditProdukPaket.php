<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

#[Signature('produk:audit-paket')]
#[Description('Audit produk paket: tanggal_boleh_cair atau periode_* yang kosong')]
class AuditProdukPaket extends Command
{
    public function handle(): int
    {
        $temuan = DB::table('produk_tabungan')
            ->where('tipe', 'paket')
            ->where(function (Builder $query) {
                $query->whereNull('tanggal_boleh_cair')
                    ->orWhereNull('periode_mulai')
                    ->orWhereNull('periode_selesai');
            })
            ->get();

        if ($temuan->isEmpty()) {
            $this->info('Semua produk paket memiliki tanggal pencairan dan periode lengkap.');

            return self::SUCCESS;
        }

        $this->error('Produk paket dengan data belum lengkap: '.$temuan->count());

        $baris = [];
        foreach ($temuan as $row) {
            $baris[] = [
                'id' => $row->id,
                'nama' => $row->nama,
                'status' => $row->status,
                'periode_mulai' => $row->periode_mulai ?? '(kosong)',
                'periode_selesai' => $row->periode_selesai ?? '(kosong)',
                'tanggal_boleh_cair' => $row->tanggal_boleh_cair ?? '(kosong)',
            ];
        }

        $this->table(
            ['id', 'nama', 'status', 'periode_mulai', 'periode_selesai', 'tanggal_boleh_cair'],
            $baris,
        );

        $this->warn('Perbaiki lewat halaman Manajemen Produk. Command ini tidak mengubah data.');

        return self::FAILURE;
    }
}
