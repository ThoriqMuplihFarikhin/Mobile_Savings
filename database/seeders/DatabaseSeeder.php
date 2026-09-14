<?php

namespace Database\Seeders;

use App\Models\KolektorNasabah;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin Utama',
            'no_hp' => '081234567890',
            'pin_hash' => Hash::make('123456'),
            'role' => 'admin',
            'status_akun' => 'aktif',
        ]);
        $admin->assignRole('admin');

        // Create kolektor user
        $kolektor = User::create([
            'name' => 'Kolektor Satu',
            'no_hp' => '081234567891',
            'pin_hash' => Hash::make('123456'),
            'role' => 'kolektor',
            'status_akun' => 'aktif',
        ]);
        $kolektor->assignRole('kolektor');

        // Create nasabah user
        $nasabah = User::create([
            'name' => 'Nasabah Satu',
            'no_hp' => '081234567892',
            'pin_hash' => Hash::make('123456'),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
        ]);
        $nasabah->assignRole('nasabah');

        // Create nasabah profil
        $nasabah->nasabahProfil()->create([
            'nama' => 'Nasabah Satu',
            'alamat' => 'Jl. Contoh No. 1, Jakarta',
            'didaftarkan_oleh' => $admin->id,
            'status_pendaftaran' => 'aktif',
            'diverifikasi_oleh' => $admin->id,
            'tanggal_verifikasi' => now(),
        ]);

        // Assign nasabah to kolektor
        KolektorNasabah::create([
            'kolektor_id' => $kolektor->id,
            'nasabah_id' => $nasabah->id,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
        ]);
    }
}
