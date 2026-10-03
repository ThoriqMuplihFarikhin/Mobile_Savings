<?php

namespace App\Livewire\Nasabah;

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class AjukanPenarikan extends Component
{
    use AuthorizesRole;

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

    public function submit(AjukanPenarikanAction $action)
    {
        $this->validate([
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|decimal:0,2|gt:0',
            'lokasi_pengambilan' => 'required|in:rumah_kolektor,kantor',
        ]);

        $nasabah = Auth::user();
        $produk = ProdukTabungan::findOrFail($this->produkId);

        try {
            $action->execute(
                $nasabah,
                $produk,
                (string) $this->nominal,
                'online',
                $this->lokasi_pengambilan
            );

            $this->reset(['produkId', 'nominal', 'lokasi_pengambilan']);
            $this->selectedProduk = null;

            session()->flash('success', 'Pengajuan penarikan berhasil dikirim! Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.nasabah.ajukan-penarikan');
    }
}
