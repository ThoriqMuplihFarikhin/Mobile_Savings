<?php

namespace App\Livewire\Nasabah;

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class AjukanPenarikan extends Component
{
    use AuthorizesRole;

    /**
     * Batas percobaan PIN mengikuti pola IkutiPaketAction (5 kali / 15 menit).
     */
    private const MAX_PERCOBAAN = 5;

    private const RATELIMIT_DECAY_DETIK = 900;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public $produkId = '';

    public $nominal = '';

    public $lokasi_pengambilan = 'kantor';

    public $produkList = [];

    public $saldoList = [];

    public $selectedProduk = null;

    public $persenKomisi = 0;

    public $nominalKomisi = 0;

    public $nominalDiterima = 0;

    public string $nominalMinimal = '10000';

    public bool $tampilKonfirmasi = false;

    public string $pin = '';

    public ?string $pesanError = null;

    public bool $tampilSukses = false;

    public string $suksesProduk = '';

    public string $suksesNominalDiminta = '0.00';

    public string $suksesKomisi = '0.00';

    public string $suksesDiterima = '0.00';

    public string $suksesLokasi = 'kantor';

    public function mount()
    {
        $this->nominalMinimal = (string) AdminSetting::get('penarikan_minimal', '10000');
        $this->loadProduk();
        $this->loadSaldo();
    }

    public function loadProduk()
    {
        $this->produkList = ProdukTabungan::where(function ($query) {
            $query->where('status', 'aktif')
                ->orWhereIn('id', function ($sub) {
                    $sub->select('produk_id')
                        ->from('saldo_produk')
                        ->where('nasabah_id', Auth::id())
                        ->where('saldo', '>', 0);
                });
        })->get();
    }

    public function loadSaldo()
    {
        $this->saldoList = SaldoProduk::where('nasabah_id', Auth::id())
            ->with('produk')
            ->get();
    }

    public function updatedProdukId()
    {
        if ($this->produkId) {
            $this->selectedProduk = ProdukTabungan::find($this->produkId);
            $this->persenKomisi = $this->selectedProduk->persen_komisi ?? 0;
            $this->hitungKomisi();
        } else {
            $this->selectedProduk = null;
            $this->persenKomisi = 0;
            $this->nominalKomisi = 0;
            $this->nominalDiterima = 0;
        }
    }

    public function updatedNominal()
    {
        $this->hitungKomisi();
    }

    public function hitungKomisi()
    {
        $teks = trim((string) $this->nominal);

        if (! is_numeric($teks) || preg_match('/^\d{1,13}(\.\d{1,2})?$/', $teks) !== 1 || (float) $teks <= 0) {
            $this->nominalKomisi = '0.00';
            $this->nominalDiterima = $teks;

            return;
        }

        $terbulatkan = bcadd($teks, '0', 2);

        if ($this->persenKomisi <= 0) {
            $this->nominalKomisi = '0.00';
            $this->nominalDiterima = $terbulatkan;

            return;
        }

        $hasil = AjukanPenarikanAction::hitungKomisi($terbulatkan, $this->persenKomisi);
        $this->nominalKomisi = $hasil['komisi'];
        $this->nominalDiterima = $hasil['diterima'];
    }

    /**
     * Langkah 1 (DESIGN.md §8): validasi formulir lalu buka sheet konfirmasi.
     * Eksekusi pengajuan pindah ke konfirmasi() setelah PIN diverifikasi.
     */
    public function submit(): void
    {
        $this->validate([
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|decimal:0,2|gt:0',
            'lokasi_pengambilan' => 'required|in:rumah_kolektor,kantor',
        ]);

        $this->pin = '';
        $this->pesanError = null;
        $this->tampilKonfirmasi = true;
    }

    public function tutupKonfirmasi(): void
    {
        $this->tampilKonfirmasi = false;
        $this->pin = '';
        $this->pesanError = null;
    }

    /**
     * Langkah 2: verifikasi PIN ber-rate-limit lalu eksekusi pengajuan.
     * Pesan kesalahan PIN mengikuti pola domain IkutiPaketAction; kesalahan
     * eksekusi tampil di sheet (pesanError). Sukses membuka layar sukses §7.
     */
    public function konfirmasi(AjukanPenarikanAction $action): void
    {
        $this->validate([
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|decimal:0,2|gt:0',
            'lokasi_pengambilan' => 'required|in:rumah_kolektor,kantor',
            'pin' => 'required|string',
        ]);

        $nasabah = Auth::user();
        if (! $nasabah instanceof User) {
            abort(403);
        }

        $key = 'ajukan-penarikan:nasabah:'.$nasabah->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_PERCOBAAN)) {
            $this->pesanError = 'Terlalu banyak percobaan PIN. Coba lagi nanti atau hubungi admin.';

            return;
        }

        if ($nasabah->harus_ganti_pin) {
            $this->pesanError = 'Anda belum mengganti PIN awal. Silakan ganti PIN terlebih dahulu di halaman Pengaturan.';

            return;
        }

        if (! Hash::check($this->pin, $nasabah->pin_hash)) {
            RateLimiter::hit($key, self::RATELIMIT_DECAY_DETIK);

            $sisa = self::MAX_PERCOBAAN - RateLimiter::attempts($key);

            $this->pesanError = "PIN salah. Sisa percobaan: {$sisa}.";

            return;
        }

        RateLimiter::clear($key);

        $produk = ProdukTabungan::whereKey($this->produkId)->firstOrFail();

        try {
            $action->execute(
                $nasabah,
                $produk,
                (string) $this->nominal,
                'online',
                $this->lokasi_pengambilan
            );

            $this->suksesProduk = $produk->nama;
            $this->suksesNominalDiminta = number_format((float) $this->nominal, 2, '.', '');
            $this->suksesKomisi = (string) $this->nominalKomisi;
            $this->suksesDiterima = (string) $this->nominalDiterima;
            $this->suksesLokasi = $this->lokasi_pengambilan;

            $this->reset(['produkId', 'nominal', 'lokasi_pengambilan']);
            $this->selectedProduk = null;
            $this->tutupKonfirmasi();
            $this->tampilSukses = true;
        } catch (\Exception $e) {
            $this->pesanError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.nasabah.ajukan-penarikan');
    }
}
