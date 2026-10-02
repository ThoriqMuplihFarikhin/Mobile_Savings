<?php

namespace App\Actions\Kolektor;

use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class TugaskanNasabahAction
{
    /**
     * Menugaskan satu nasabah ke satu kolektor secara eksklusif (D4).
     *
     * Idempoten bila sudah aktif pada kolektor yang sama; melempar DomainException
     * bila nasabah sudah dipegang kolektor lain atau identitas tidak valid.
     */
    public function execute(int $kolektorId, int $nasabahId): void
    {
        DB::transaction(function () use ($kolektorId, $nasabahId) {
            $nasabah = User::whereKey($nasabahId)->lockForUpdate()->first();

            if (! $nasabah || $nasabah->role !== 'nasabah') {
                throw new DomainException('Penugasan hanya untuk akun berperan nasabah.');
            }

            $kolektor = User::find($kolektorId);

            if (! $kolektor || $kolektor->role !== 'kolektor' || $kolektor->status_akun !== 'aktif') {
                throw new DomainException('Kolektor tujuan tidak valid atau tidak aktif.');
            }

            $profil = NasabahProfil::where('user_id', $nasabahId)->first();

            if (! $profil || $profil->status_pendaftaran !== 'aktif') {
                throw new DomainException('Nasabah harus berstatus aktif sebelum ditugaskan.');
            }

            $existing = KolektorNasabah::where('nasabah_id', $nasabahId)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ((int) $existing->kolektor_id === $kolektorId) {
                    return;
                }

                throw new DomainException('Nasabah masih dipegang kolektor lain. Gunakan Handover/Pindahkan.');
            }

            KolektorNasabah::create([
                'kolektor_id' => $kolektorId,
                'nasabah_id' => $nasabahId,
                'tanggal_mulai_ditangani' => now()->toDateString(),
                'status' => 'aktif',
                'aktif_unik' => 1,
            ]);
        });
    }
}
