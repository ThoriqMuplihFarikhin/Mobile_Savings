<?php

use App\Models\User;
use App\Support\PortalLogin;

it('landing menampilkan tiga tombol masuk menuju tiap portal', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Masuk Nasabah')
        ->assertSee('Masuk Kolektor')
        ->assertSee('Masuk Admin')
        ->assertSee(route('login.kolektor'), false)
        ->assertSee(route('login.admin'), false)
        ->assertDontSee('Masuk Sekarang');
});

it('halaman login menampilkan judul tab dan label portal sesuai peran', function () {
    foreach (PortalLogin::semua() as $portal) {
        $label = PortalLogin::labelPortal($portal);

        $this->get(route(PortalLogin::routeName($portal)))
            ->assertOk()
            ->assertSee('Login '.$label)
            ->assertSee('data-test="label-portal"', false)
            ->assertSee('>Portal '.$label.'</span>', false);
    }
});

it('menyediakan tautan ke portal lain di bawah form login', function () {
    $this->get(route('login.admin'))
        ->assertOk()
        ->assertSee('data-test="tautan-portal-lain"', false)
        ->assertSee(route('login.kolektor'), false)
        ->assertDontSee(route('login.admin'), false);
});

it('head memuat manifest sesuai portal halaman dan peran pengguna', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('/manifest-nasabah.webmanifest', false);

    $this->get(route('login.kolektor'))
        ->assertOk()
        ->assertSee('/manifest-kolektor.webmanifest', false);

    $this->get(route('login.admin'))
        ->assertOk()
        ->assertSee('/manifest-admin.webmanifest', false);

    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('/manifest-kolektor.webmanifest', false);
});

it('manifest per portal tersedia dengan identitas berbeda', function () {
    $nama = [];
    $tema = [];

    foreach (PortalLogin::semua() as $portal) {
        $path = public_path('manifest-'.$portal.'.webmanifest');

        expect($path)->toBeFile();

        $isi = json_decode((string) file_get_contents($path), true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE)
            ->and($isi['start_url'] ?? null)->toBe(route(PortalLogin::routeName($portal), absolute: false))
            ->and($isi['theme_color'] ?? null)->not->toBeNull()
            ->and($isi['name'] ?? null)->not->toBeNull()
            ->and($isi['short_name'] ?? null)->not->toBeNull()
            ->and(collect($isi['icons'] ?? [])->pluck('sizes'))->toContain('192x192', '512x512');

        $nama[] = $isi['name'];
        $tema[] = $isi['theme_color'];
    }

    expect(array_unique($nama))->toHaveCount(3)
        ->and(array_unique($tema))->toHaveCount(3);
});

it('judul tab dan chip peran di header memuat peran pengguna', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('· Admin')
        ->assertSee('data-test="chip-peran"', false)
        ->assertSee('>Admin</span>', false);

    $this->actingAs($admin)
        ->get(route('admin.log.index'))
        ->assertOk()
        ->assertSee('data-test="chip-peran"', false);

    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('· Kolektor')
        ->assertSee('>Kolektor</span>', false);

    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('· Nasabah')
        ->assertSee('>Nasabah</span>', false);
});
