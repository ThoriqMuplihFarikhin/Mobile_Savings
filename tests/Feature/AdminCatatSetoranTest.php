<?php

use App\Actions\Setoran\CatatSetoranAction;
use App\Jobs\KirimNotifikasiWhatsApp;
use App\Livewire\Admin\InputSetoran as AdminInputSetoran;
use App\Livewire\Admin\KasKolektor;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function seedAdminSetoranP35(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Setoran P35',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Produk Setoran P35',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function isiFormAdminSetoranP35(array $data, array $options = []): Testable
{
    return Livewire::test(AdminInputSetoran::class)
        ->set('nasabahId', $data['nasabah']->id)
        ->set('produkId', $data['produk']->id)
        ->set('nominal', $options['nominal'] ?? 50000)
        ->set('catatan', $options['catatan'] ?? '');
}

it('mencatat setoran admin dengan saldo bertambah ditandai sudah disetor ke kantor', function () {
    $data = seedAdminSetoranP35();

    $this->actingAs($data['admin']);
    isiFormAdminSetoranP35($data)->call('submit')->assertHasNoErrors();

    $this->assertDatabaseCount('transaksi_setoran', 1);

    $transaksi = TransaksiSetoran::firstOrFail();
    $saldo = SaldoProduk::where('nasabah_id', $data['nasabah']->id)
        ->where('produk_id', $data['produk']->id)
        ->first();

    expect($saldo->saldo)->toBe('50000.00')
        ->and($transaksi->input_by)->toBe($data['admin']->id)
        ->and($transaksi->sudah_disetor_ke_kantor)->toBeTrue()
        ->and($transaksi->sumber_input)->toBe('real_time')
        ->and($transaksi->status)->toBe('tercatat');
});

it('tidak memasukkan setoran admin ke kas kolektor manapun', function () {
    $data = seedAdminSetoranP35();

    $this->actingAs($data['admin']);
    isiFormAdminSetoranP35($data)->call('submit')->assertHasNoErrors();

    expect(TransaksiSetoran::belumDisetor()->count())->toBe(0)
        ->and(TransaksiSetoran::teragregasiPerKolektor())->toBe([])
        ->and(collect(KasKolektor::kumpulkanKas())->sum('total_belum_disetor'))->toBe(0.0);
});

it('membatasi idempotency key sehingga setoran ganda hanya tercatat sekali', function () {
    $data = seedAdminSetoranP35();

    $payload = [
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produk']->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'sumber_input' => 'real_time',
        'input_by' => $data['admin']->id,
        'catatan' => null,
        'sudah_disetor_ke_kantor' => true,
        'idempotency_key' => 'klik-ganda-p35',
    ];

    app(CatatSetoranAction::class)->execute($payload);

    expect(fn () => app(CatatSetoranAction::class)->execute($payload))
        ->toThrow(DomainException::class, 'Setoran ini sudah diproses. Muat ulang halaman untuk setoran baru.');

    $this->assertDatabaseCount('transaksi_setoran', 1);
});

it('mencatat log aktivitas setor dengan input_by admin', function () {
    $data = seedAdminSetoranP35();

    $this->actingAs($data['admin']);
    isiFormAdminSetoranP35($data)->call('submit')->assertHasNoErrors();

    $log = LogAktivitas::where('aksi', 'setor')->firstOrFail();

    expect($log->entitas_terkait)->toBe('transaksi_setoran')
        ->and($log->user_id)->toBe($data['admin']->id)
        ->and($log->detail['input_by'])->toBe($data['admin']->id)
        ->and($log->detail['nominal'])->toBe(50000);
});

it('mengirim notifikasi ke nasabah atas setoran admin', function () {
    Queue::fake();

    $data = seedAdminSetoranP35();

    $this->actingAs($data['admin']);
    isiFormAdminSetoranP35($data)->call('submit')->assertHasNoErrors();

    $dikirim = LogNotifikasi::where('nasabah_id', $data['nasabah']->id)
        ->where('judul', 'Setoran Dicatat');

    Queue::assertPushed(KirimNotifikasiWhatsApp::class);

    expect((clone $dikirim)->where('channel', 'in_app')->count())->toBe(1)
        ->and((clone $dikirim)->where('channel', 'whatsapp')->count())->toBe(1)
        ->and((clone $dikirim)->where('channel', 'whatsapp')->value('status_kirim'))->toBe('antri');
});

it('hanya admin yang bisa mengakses halaman catat setoran', function () {
    $data = seedAdminSetoranP35();

    $this->actingAs($data['admin'])->get('/admin/setoran')->assertOk();
    $this->actingAs($data['kolektor'])->get('/admin/setoran')->assertForbidden();
    $this->actingAs($data['nasabah'])->get('/admin/setoran')->assertForbidden();
});

it('mengeluarkan setoran dengan input_by bukan kolektor dari scope belumDisetor', function () {
    $data = seedAdminSetoranP35();

    TransaksiSetoran::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produk']->id,
        'nominal' => 25000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $data['admin']->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    expect(TransaksiSetoran::belumDisetor()->count())->toBe(0);
});
