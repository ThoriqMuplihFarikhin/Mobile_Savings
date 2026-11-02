<?php

use App\Livewire\Kolektor\Notifikasi;
use App\Models\LogNotifikasi;
use App\Models\User;
use Livewire\Livewire;

function seedNotifikasiKolektor(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    return compact('kolektor', 'nasabah');
}

it('halaman notifikasi kolektor hanya dapat diakses oleh kolektor', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah] = seedNotifikasiKolektor();

    $this->get(route('kolektor.notifikasi.index'))->assertRedirect(route('login'));

    $this->actingAs($nasabah)->get(route('kolektor.notifikasi.index'))->assertForbidden();

    $this->actingAs($kolektor)->get(route('kolektor.notifikasi.index'))->assertOk();
});

it('menampilkan notifikasi kolektor dan dapat menandai dibaca', function () {
    ['kolektor' => $kolektor] = seedNotifikasiKolektor();

    $notif1 = LogNotifikasi::create([
        'nasabah_id' => $kolektor->id,
        'judul' => 'Penarikan menunggu verifikasi',
        'pesan' => 'Ada penarikan offline baru di rumah nasabah.',
        'jenis_notifikasi' => 'penarikan',
        'channel' => 'in_app',
        'status_kirim' => 'terkirim',
        'is_read' => false,
        'waktu_kirim' => now(),
    ]);

    $notif2 = LogNotifikasi::create([
        'nasabah_id' => $kolektor->id,
        'judul' => 'Setoran kantor diterima',
        'pesan' => 'Admin telah memverifikasi setoran kantor Anda.',
        'jenis_notifikasi' => 'setoran',
        'channel' => 'in_app',
        'status_kirim' => 'terkirim',
        'is_read' => false,
        'waktu_kirim' => now(),
    ]);

    $component = Livewire::actingAs($kolektor)->test(Notifikasi::class);

    $component->assertOk()
        ->assertSee('Penarikan menunggu verifikasi')
        ->assertSee('Setoran kantor diterima')
        ->assertSee('2 pesan belum dibaca');

    // Tandai 1 dibaca
    $component->call('markAsRead', $notif1->id);
    expect($notif1->fresh()->is_read)->toBeTrue();
    expect($notif2->fresh()->is_read)->toBeFalse();

    // Tandai semua dibaca
    $component->call('markAllRead');
    expect($notif2->fresh()->is_read)->toBeTrue();
});

it('beranda kolektor menampilkan badge notifikasi belum dibaca', function () {
    ['kolektor' => $kolektor] = seedNotifikasiKolektor();

    LogNotifikasi::create([
        'nasabah_id' => $kolektor->id,
        'judul' => 'Jadwal diperbarui',
        'pesan' => 'Admin telah memperbarui rute hari ini.',
        'jenis_notifikasi' => 'jadwal',
        'channel' => 'in_app',
        'status_kirim' => 'terkirim',
        'is_read' => false,
        'waktu_kirim' => now(),
    ]);

    $response = $this->actingAs($kolektor)->get(route('dashboard'));

    $response->assertOk();
    expect($response->viewData('unreadNotifikasiCount'))->toBe(1);

    $html = $response->getContent();
    expect($html)
        ->toContain(route('kolektor.notifikasi.index'))
        ->toContain('aria-label="Notifikasi"');
});

it('layar notifikasi kolektor memakai token desain tanpa gradien', function () {
    ['kolektor' => $kolektor] = seedNotifikasiKolektor();

    $html = Livewire::actingAs($kolektor)->test(Notifikasi::class)->html();

    expect(array_filter([
        'bg-gradient',
        'blur-',
        'font-mono',
        'text-[9px]',
        'text-[10px]',
        'text-[11px]',
        'font-bold',
        'font-extrabold',
        'font-black',
        'rounded-3xl',
        'uppercase tracking',
        'shadow-2xl',
    ], fn ($pola) => str_contains($html, $pola)))->toBe([]);
});
