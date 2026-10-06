<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\NomorHp;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NormalisasiNomorHp extends Command
{
    protected $signature = 'users:normalisasi-hp {--dry-run : Laporkan perubahan tanpa menulis ke database}';

    protected $description = 'Menormalisasi nomor HP ke format kanonik 08xxxxxxxxxx dan melaporkan duplikat sebelum digabung manual';

    public function handle(): int
    {
        /** @var Collection<int, User> $users */
        $users = User::whereNotNull('no_hp')->orderBy('id')->get(['id', 'name', 'no_hp']);

        $duplikat = $users
            ->groupBy(fn (User $user) => NomorHp::normalize($user->no_hp))
            ->filter(fn (Collection $anggota) => $anggota->count() > 1);

        if ($duplikat->isNotEmpty()) {
            $this->warn('Duplikat ditemukan setelah normalisasi:');

            foreach ($duplikat as $target => $anggota) {
                $daftar = $anggota
                    ->map(fn (User $user) => "[id {$user->id}] {$user->no_hp}")
                    ->implode(', ');
                $this->line("- {$target}: {$daftar}");
            }

            $this->warn('Tidak ada perubahan yang dijalankan. Selesaikan duplikat secara manual lalu jalankan ulang.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            if (! NomorHp::valid(NomorHp::normalize($user->no_hp))) {
                $this->warn("Nomor tidak valid setelah normalisasi: [id {$user->id}] {$user->no_hp}");
            }
        }

        $perubahan = $users->filter(
            fn (User $user) => NomorHp::normalize($user->no_hp) !== $user->no_hp
        );

        if ($perubahan->isEmpty()) {
            $this->info('Tidak ada nomor HP yang perlu dinormalisasi.');

            return self::SUCCESS;
        }

        foreach ($perubahan as $user) {
            $this->line("{$user->no_hp} -> ".NomorHp::normalize($user->no_hp)." (id {$user->id})");
        }

        if ($this->option('dry-run')) {
            $this->info("Dry-run: {$perubahan->count()} nomor tidak diubah.");

            return self::SUCCESS;
        }

        foreach ($perubahan as $user) {
            $user->update(['no_hp' => NomorHp::normalize($user->no_hp)]);
        }

        $this->info("{$perubahan->count()} nomor berhasil dinormalisasi.");

        return self::SUCCESS;
    }
}
