<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AkunBukaKunci extends Command
{
    protected $signature = 'akun:buka-kunci {no_hp : Nomor HP akun yang akan dibuka kuncinya}';

    protected $description = 'Buka kunci akun darurat: mereset status_akun, percobaan_gagal, dan login_terkunci_hingga';

    public function handle(): int
    {
        $user = User::where('no_hp', $this->argument('no_hp'))->first();

        if (! $user) {
            $this->error('Akun tidak ditemukan.');

            return self::FAILURE;
        }

        $user->update([
            'status_akun' => 'aktif',
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        $this->info("Akun {$user->no_hp} berhasil dibuka kuncinya.");

        return self::SUCCESS;
    }
}
