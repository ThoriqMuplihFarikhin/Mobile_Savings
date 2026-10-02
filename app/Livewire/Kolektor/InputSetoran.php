<?php

namespace App\Livewire\Kolektor;

use App\Actions\Tabungan\HitungTunggakanAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Livewire\Concerns\ValidatesKolektorNasabah;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class InputSetoran extends Component
{
    use AuthorizesRole;
    use ValidatesKolektorNasabah;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $nasabahId = '';

    public $produkId = '';

    public $nominal = '';

    public $tanggal_transaksi = '';

    public $sumber_input = 'real_time';

    public $catatan = '';

    public string $idempotencyKey = '';

    public $nasabahList = [];

    public $produkList = [];

    public $selectedNasabah = null;

    public $showSuccess = false;

    public $searchNasabah = '';

    public $showPickerNasabah = true;

    public $tunggakanInfo = null;

    public function mount()
    {
        $this->idempotencyKey = (string) Str::uuid();
        $this->tanggal_transaksi = now()->format('Y-m-d');
        $this->loadNasabah();
        $this->loadProduk();
    }

    public function loadNasabah()
    {
        $kolektorId = Auth::id();

        $query = NasabahProfil::where('status_pendaftaran', 'aktif')
            ->whereIn('user_id', function ($q) use ($kolektorId) {
                $q->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->with('user');

        if ($this->searchNasabah) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$this->searchNasabah.'%'));
        }

        $this->nasabahList = $query->get();
    }

    public function loadProduk()
    {
        $this->produkList = ProdukTabungan::where('status', 'aktif')->get();
    }

    public function updatedSearchNasabah()
    {
        $this->loadNasabah();
    }

    public function pilihNasabah($nasabahId)
    {
        if (! $this->isNasabahBinaan((int) $nasabahId)) {
            session()->flash('error', 'Nasabah tidak valid.');

            return;
        }

        $this->nasabahId = $nasabahId;
        $this->updatedNasabahId();
        $this->showPickerNasabah = false;
        $this->searchNasabah = '';

        $this->loadNasabah();

        $produkAktif = SaldoProduk::where('nasabah_id', $nasabahId)
            ->whereHas('produk', fn ($q) => $q->where('status', 'aktif'))
            ->pluck('produk_id');
        if ($produkAktif->count() === 1) {
            $this->produkId = $produkAktif->first();
            $this->hitungTunggakan();
        }
    }

    public function updatedNasabahId()
    {
        if ($this->nasabahId && ! $this->isNasabahBinaan((int) $this->nasabahId)) {
            $this->nasabahId = '';
            $this->selectedNasabah = null;
            session()->flash('error', 'Nasabah tidak valid.');

            return;
        }

        if ($this->nasabahId) {
            $this->selectedNasabah = NasabahProfil::where('user_id', $this->nasabahId)
                ->with(['user', 'user.saldoProduks.produk'])
                ->first();
        } else {
            $this->selectedNasabah = null;
        }

        $this->hitungTunggakan();
    }

    public function updatedProdukId()
    {
        $this->hitungTunggakan();
    }

    public function tambahNominal($jumlah)
    {
        $this->nominal = ($this->nominal ?: 0) + $jumlah;
    }

    public function hitungTunggakan()
    {
        $this->tunggakanInfo = null;

        if (! $this->nasabahId || ! $this->produkId) {
            return;
        }

        if (! $this->isNasabahBinaan((int) $this->nasabahId)) {
            return;
        }

        $this->tunggakanInfo = app(HitungTunggakanAction::class)
            ->execute((int) $this->nasabahId, (int) $this->produkId, simpan: false);
    }

    public function submit()
    {
        $produk = ProdukTabungan::find($this->produkId);
        $minimalSetor = max(1000, (int) ($produk->minimal_setor ?? 0));

        $this->validate([
            'nasabahId' => 'required|exists:users,id',
            'produkId' => ['required', Rule::exists('produk_tabungan', 'id')->where('status', 'aktif')],
            'nominal' => ['required', 'numeric', 'min:'.$minimalSetor, 'max:1000000000'],
            'tanggal_transaksi' => [
                'required',
                'date',
                $this->sumber_input === 'real_time'
                    ? 'date_equals:'.now()->toDateString()
                    : 'before_or_equal:today',
            ],
            'sumber_input' => 'required|in:real_time,susulan',
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $isTanggungJawab = KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('nasabah_id', $this->nasabahId)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            session()->flash('error', 'Nasabah ini bukan tanggung jawab Anda.');

            return;
        }

        $cacheKey = 'setoran-submit:'.Auth::id().':'.$this->idempotencyKey;
        if (! Cache::add($cacheKey, true, now()->addMinutes(10))) {
            session()->flash('error', 'Setoran ini sudah diproses. Muat ulang halaman untuk setoran baru.');

            return;
        }

        try {
            $transaksi = DB::transaction(function () use ($produk) {
                $saldo = SaldoProduk::firstOrCreate(
                    ['nasabah_id' => $this->nasabahId, 'produk_id' => $this->produkId],
                    ['saldo' => 0]
                );
                $saldo = SaldoProduk::whereKey($saldo->id)->lockForUpdate()->first();

                $transaksi = TransaksiSetoran::create([
                    'nasabah_id' => $this->nasabahId,
                    'produk_id' => $this->produkId,
                    'nominal' => $this->nominal,
                    'tanggal_transaksi' => $this->tanggal_transaksi,
                    'tanggal_input_sistem' => now(),
                    'input_by' => Auth::id(),
                    'sumber_input' => $this->sumber_input,
                    'status' => 'tercatat',
                    'catatan' => $this->catatan ?: null,
                ]);

                $saldo->increment('saldo', $this->nominal);

                if ($produk && $produk->tipe === 'paket') {
                    app(HitungTunggakanAction::class)
                        ->execute((int) $this->nasabahId, (int) $this->produkId, simpan: true);
                }

                return $transaksi;
            });
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            report($e);
            session()->flash('error', 'Gagal mencatat setoran. Silakan coba lagi.');

            return;
        }

        try {
            ActivityLogger::log('setor', 'transaksi_setoran', $transaksi->id, [
                'nasabah_id' => $this->nasabahId,
                'nominal' => $this->nominal,
                'produk_id' => $this->produkId,
            ]);

            ActivityLogger::notify(
                $this->nasabahId,
                'Setoran Dicatat',
                'Setoran Rp '.number_format($this->nominal, 0, ',', '.').' ke produk '.($produk->nama ?? '-').' telah dicatat.',
                'both'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $this->idempotencyKey = (string) Str::uuid();
        $this->showSuccess = true;
        $this->reset(['nasabahId', 'produkId', 'nominal', 'catatan']);
        $this->selectedNasabah = null;
        $this->tunggakanInfo = null;

        session()->flash('success', 'Setoran berhasil dicatat!');

        $this->dispatch('setoranCreated');
    }

    public function resetNominal()
    {
        $this->nominal = '';
    }

    public function setNominalTunggakan()
    {
        if ($this->tunggakanInfo && isset($this->tunggakanInfo['tunggakan'])) {
            $this->nominal = (int) $this->tunggakanInfo['tunggakan'];
        }
    }

    public function setTanggalToday()
    {
        $this->tanggal_transaksi = now()->format('Y-m-d');
    }

    public function setTanggalYesterday()
    {
        $this->tanggal_transaksi = now()->subDay()->format('Y-m-d');
    }

    public function render()
    {
        $riwayatHariIni = TransaksiSetoran::where('input_by', Auth::id())
            ->whereDate('tanggal_input_sistem', today())
            ->with(['nasabah', 'produk'])
            ->latest('id')
            ->take(5)
            ->get();

        $totalBelumDisetor = TransaksiSetoran::belumDisetor()
            ->where('input_by', Auth::id())
            ->sum('nominal');

        return view('livewire.kolektor.input-setoran', compact('riwayatHariIni', 'totalBelumDisetor'));
    }
}
