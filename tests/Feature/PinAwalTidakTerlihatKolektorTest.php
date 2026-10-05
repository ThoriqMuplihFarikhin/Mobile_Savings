<?php

use App\Jobs\KirimPinAwalWhatsApp;
use App\Livewire\Admin\RegistrasiNasabah;
use App\Livewire\Admin\VerifikasiNasabah;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\User;
use App\Support\Pin;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function daftarNasabahP41(User $kolektor): Testable
{
    return Livewire::actingAs($kolektor)
        ->test(DaftarNasabah::class)
        ->set('nama', 'Nasabah P41')
        ->set('noHp', '081234567890')
        ->set('alamat', 'Jl. P41 No. 1')
        ->set('tanggalLahir', '1990-01-01')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors();
}

function konfigurasiWaP41(): void
{
    config([
        'services.whatsapp.url' => 'https://wa.example.test/send',
        'services.whatsapp.token' => 'token-tes-p41',
    ]);
}

it('pendaftaran oleh kolektor tidak menampilkan pin awal di flash maupun halaman', function () {
    $kolektor = User::factory()->kolektor()->create();

    $halaman = daftarNasabahP41($kolektor);
    $profil = NasabahProfil::firstOrFail();

    $halaman->assertSee('mengganti PIN')
        ->assertDontSee('PIN awal:');

    expect($halaman->html())->not->toMatch('/PIN\s*(?:awal)?:\s*\d{6}/')
        ->and($profil->user->harus_ganti_pin)->toBeTrue()
        ->and($profil->user->pin_hash)->not->toBe('');
});

it('pin acak untuk pendaftaran lolos uji pin lemah', function () {
    foreach (range(1, 30) as $i) {
        $pin = Pin::acak();

        expect($pin)->toMatch('/^\d{6}$/')
            ->and(Pin::lemah($pin))->toBeFalse();
    }
});

it('verifikasi admin membuat pin baru dan mengirimkannya lewat job whatsapp terenkripsi', function () {
    Queue::fake();
    konfigurasiWaP41();

    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();
    daftarNasabahP41($kolektor);
    $profil = NasabahProfil::firstOrFail();
    $user = $profil->user;

    $this->actingAs($admin);

    $halaman = Livewire::test(VerifikasiNasabah::class)
        ->call('approve', $profil->id);

    $halaman->assertSee('berhasil diverifikasi')
        ->assertDontSee('PIN awal:');

    Queue::assertPushed(KirimPinAwalWhatsApp::class, function (KirimPinAwalWhatsApp $job) use ($user) {
        return $job instanceof ShouldBeEncrypted
            && preg_match('/^\d{6}$/', $job->pin) === 1
            && ! Pin::lemah($job->pin)
            && Hash::check($job->pin, $user->fresh()->pin_hash);
    });

    expect($user->fresh()->harus_ganti_pin)->toBeTrue()
        ->and($halaman->html())->not->toMatch('/PIN\s*(?:awal)?:\s*\d{6}/');

    $log = LogNotifikasi::latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->channel)->toBe('whatsapp')
        ->and($log->status_kirim)->toBe('antri')
        ->and($log->pesan)->not->toMatch('/(?<!\d)\d{6}(?!\d)/')
        ->and($log->pesan)->toContain('PIN awal dikirim');
});

it('payload job pin awal tersimpan terenkripsi tanpa pin di database', function () {
    $pin = '482917';
    $log = LogNotifikasi::create([
        'nasabah_id' => User::factory()->nasabah()->create()->id,
        'judul' => 'PIN Awal',
        'pesan' => 'PIN awal dikirim ke nomor Anda.',
        'jenis_notifikasi' => 'pin_awal',
        'channel' => 'whatsapp',
        'status_kirim' => 'antri',
        'is_read' => false,
        'waktu_kirim' => now(),
    ]);

    config(['queue.default' => 'database']);
    KirimPinAwalWhatsApp::dispatch($log->id, '081234567890', $pin);

    $payload = (string) DB::table('jobs')->value('payload');
    $data = json_decode($payload, true);
    $command = (string) ($data['data']['command'] ?? '');

    expect($payload)->not->toBe('')
        ->and($command)->not->toBe('')
        ->and($payload)->not->toContain($pin)
        ->and($command)->not->toContain($pin)
        ->and(Crypt::decryptString($command))->toContain($pin);
});

it('registrasi langsung oleh admin mengirim job pin awal tanpa menampilkan pin di flash', function () {
    Queue::fake();
    konfigurasiWaP41();

    $admin = User::factory()->admin()->create();

    $halaman = Livewire::actingAs($admin)
        ->test(RegistrasiNasabah::class)
        ->set('nama', 'Nasabah Admin')
        ->set('noHp', '081234567891')
        ->set('alamat', 'Jl. Admin No. 1')
        ->set('tanggalLahir', '1995-05-05')
        ->set('jenisKelamin', 'perempuan')
        ->call('submit')
        ->assertHasNoErrors();

    $user = User::where('no_hp', '081234567891')->firstOrFail();

    Queue::assertPushed(KirimPinAwalWhatsApp::class, function (KirimPinAwalWhatsApp $job) use ($user) {
        return $job instanceof ShouldBeEncrypted
            && Hash::check($job->pin, $user->pin_hash);
    });

    $halaman->assertDontSee('PIN awal:')
        ->assertSee('dikirim');

    expect($halaman->html())->not->toMatch('/PIN\s*(?:awal)?:\s*\d{6}/')
        ->and($user->harus_ganti_pin)->toBeTrue();
});

it('fallback menampilkan pin ke admin saat whatsapp belum terkonfigurasi', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $halaman = Livewire::actingAs($admin)
        ->test(RegistrasiNasabah::class)
        ->set('nama', 'Nasabah Fallback')
        ->set('noHp', '081234567892')
        ->set('alamat', 'Jl. Fallback No. 1')
        ->set('tanggalLahir', '1995-05-05')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors();

    $user = User::where('no_hp', '081234567892')->firstOrFail();
    $html = $halaman->html();

    Queue::assertNothingPushed();

    expect($html)->toMatch('/PIN awal: (\d{6})/');

    $pin = (string) preg_replace('/.*PIN awal: (\d{6}).*/s', '$1', $html);

    expect(Pin::lemah($pin))->toBeFalse()
        ->and(Hash::check($pin, $user->pin_hash))->toBeTrue()
        ->and($user->harus_ganti_pin)->toBeTrue()
        ->and(LogNotifikasi::count())->toBe(0);
});

it('fallback menampilkan pin ke admin saat notifikasi wa nasabah dimatikan', function () {
    Queue::fake();
    konfigurasiWaP41();

    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();
    daftarNasabahP41($kolektor);
    $profil = NasabahProfil::firstOrFail();
    $profil->user->update(['notifikasi_wa_aktif' => false]);

    $this->actingAs($admin);

    $halaman = Livewire::test(VerifikasiNasabah::class)
        ->call('approve', $profil->id);

    $user = $profil->user->fresh();
    $html = $halaman->html();

    Queue::assertNothingPushed();

    expect($html)->toMatch('/PIN awal: (\d{6})/');

    $pin = (string) preg_replace('/.*PIN awal: (\d{6}).*/s', '$1', $html);

    expect(Pin::lemah($pin))->toBeFalse()
        ->and(Hash::check($pin, $user->pin_hash))->toBeTrue()
        ->and($user->harus_ganti_pin)->toBeTrue()
        ->and(LogNotifikasi::count())->toBe(0);
});
