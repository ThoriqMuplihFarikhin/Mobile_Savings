<?php

use App\Models\User;

/**
 * @return array{0: array<string, mixed>}
 */
function p65Manifest(): array
{
    $path = public_path('manifest.webmanifest');

    expect($path)->toBeFile();

    $manifest = json_decode((string) file_get_contents($path), true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE);

    return [$manifest];
}

it('manifest webmanifest tersedia dengan struktur pwa lengkap', function () {
    [$manifest] = p65Manifest();

    expect($manifest['name'] ?? null)->not->toBeNull()
        ->and($manifest['short_name'] ?? null)->not->toBeNull()
        ->and($manifest['display'])->toBe('standalone')
        ->and($manifest['start_url'])->toBe('/')
        ->and($manifest['theme_color'] ?? null)->not->toBeNull()
        ->and(collect($manifest['icons'] ?? [])->pluck('sizes'))->toContain('192x192', '512x512');
});

it('ikon pwa tersedia dalam png beresolusi 192 dan 512 piksel', function () {
    foreach ([192, 512] as $px) {
        $path = public_path("icon-{$px}.png");

        expect($path)->toBeFile();

        $info = getimagesize($path);

        expect($info)->not->toBeFalse()
            ->and($info[0])->toBe($px)
            ->and($info[1])->toBe($px)
            ->and($info['mime'])->toBe('image/png');
    }
});

it('service worker hanya menyasar aset statis dan tidak menyentuh halaman', function () {
    $path = public_path('service-worker.js');

    expect($path)->toBeFile();

    $sw = (string) file_get_contents($path);

    expect($sw)->toContain('/build/')
        ->and($sw)->toContain('manifest.webmanifest')
        ->and($sw)->toContain("method !== 'GET'")
        ->and($sw)->toContain('caches.open')
        ->and($sw)->not->toContain('navigate')
        ->and($sw)->not->toContain('/livewire')
        ->and($sw)->not->toContain('addAll');
});

it('partial head memuat tautan manifest dan pendaftaran service worker', function () {
    $head = (string) file_get_contents(resource_path('views/partials/head.blade.php'));

    expect($head)->toContain('/manifest.webmanifest')
        ->and($head)->toContain('serviceWorker.register')
        ->and($head)->toContain('/service-worker.js');
});

it('halaman terautentikasi memuat pwa manifest dan pendaftaran service worker', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('/manifest.webmanifest', false)
        ->assertSee('serviceWorker.register', false);
});
