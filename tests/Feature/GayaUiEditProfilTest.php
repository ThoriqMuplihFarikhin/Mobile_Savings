<?php

use App\Models\User;

/**
 * Migrasi halaman edit profil (pages/settings/⚡profile, dipakai nasabah +
 * kolektor) ke DESIGN.md:
 *
 * - §1/§5: tanpa gradien — banner kosong memakai latar datar `primary-deep`.
 * - §2: warna memakai token (ink/body/mute/subtle/hairline-strong/inset/
 *   danger/primary), bukan hex mentah atau varian `dark:` zinc.
 * - §7 Form: input mobile 48px radius 12px, label 14/500, fokus primary,
 *   pesan error 14px berikon + border danger.
 * - §4/§10: tombol utama satu per layar (hijau pil 48px), target sentuh
 *   ≥44px, ikon dekoratif `aria-hidden`.
 * - §9: teks antarmuka berbahasa Indonesia (judul tab, toast sukses).
 */
function sumberEditProfil(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/resources/views/pages/settings/⚡profile.blade.php');
}

it('sumber halaman edit profil bebas gradien, hex mentah, dan zinc gelap', function () {
    $sumber = sumberEditProfil();

    expect($sumber)
        ->not->toContain('linear-gradient', false)
        ->not->toContain('bg-gradient', false)
        ->not->toContain('[#', false)
        ->not->toContain('dark:bg-zinc', false)
        ->not->toContain('dark:border-zinc', false)
        ->not->toContain('dark:text-zinc', false)
        ->not->toContain('backdrop-blur', false);
});

it('sumber halaman edit profil memakai token FORM dan tombol utama hijau', function () {
    $sumber = sumberEditProfil();

    expect($sumber)
        ->toContain('bg-primary-deep', false)
        ->toContain('bg-primary-soft', false)
        ->toContain('border-hairline-strong', false)
        ->toContain('border-danger', false)
        ->toContain('h-12 w-full rounded-xl', false)
        ->toContain('rounded-full bg-primary', false)
        ->toContain('focus:ring-primary/20', false)
        ->toContain('text-sm text-danger', false)
        ->toContain('dark:text-dark-danger-text', false)
        ->toContain('text-sm font-medium text-body', false)
        ->toContain('shadow-[0_1px_2px_rgb(0_0_0/0.06)]', false);
});

it('judul tab dan toast sukses halaman edit profil berbahasa Indonesia', function () {
    $sumber = sumberEditProfil();

    expect($sumber)
        ->toContain("Title('Edit profil')", false)
        ->toContain('Perubahan profil berhasil disimpan!', false)
        ->not->toContain('Profile updated', false);
});

it('layar edit profil nasabah memakai token yang terender, tanpa gradien', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Nasabah sejak')
        ->assertDontSee('Bergabung sejak')
        ->assertDontSee('linear-gradient', false)
        ->assertSee('bg-primary-deep', false)
        ->assertSee('border-hairline-strong', false)
        ->assertSee('rounded-full bg-primary', false);
});

it('layar edit profil kolektor memakai label bergabung sejak', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Bergabung sejak')
        ->assertDontSee('Nasabah sejak');
});
