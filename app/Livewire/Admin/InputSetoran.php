<?php

namespace App\Livewire\Admin;

use App\Actions\Setoran\CatatSetoranAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class InputSetoran extends Component
{
    use AuthorizesRole;

    /** @var string */
    public $nasabahId = '';

    /** @var string */
    public $produkId = '';

    /** @var string */
    public $nominal = '';

    /** @var string */
    public $tanggal_transaksi = '';

    /** @var string */
    public $catatan = '';

    /** @var string */
    public $idempotencyKey = '';

    /** @var string */
    public $searchNasabah = '';

    /** @var bool */
    public $showSuccess = false;

    /** @var array<int, array{id: int, nama: string, alamat: string}> */
    public array $nasabahList = [];

    /** @var array<int, array{id: int, nama: string, minimal_setor: int}> */
    public array $produkList = [];

    /** @var array{id: int, nama: string, alamat: string}|null */
    public ?array $selectedNasabah = null;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public function mount(): void
    {
        $this->idempotencyKey = (string) Str::uuid();
        $this->tanggal_transaksi = now()->toDateString();
        $this->loadNasabah();
        $this->loadProduk();
    }

    public function loadNasabah(): void
    {
        $query = NasabahProfil::where('status_pendaftaran', 'aktif')->with('user');

        if ($this->searchNasabah !== '') {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$this->searchNasabah.'%'));
        }

        /** @var array<int, array{id: int, nama: string, alamat: string}> $daftar */
        $daftar = [];
        foreach ($query->get() as $profil) {
            $daftar[] = [
                'id' => (int) $profil->user_id,
                'nama' => (string) ($profil->user->name ?? '-'),
                'alamat' => (string) ($profil->alamat ?? '-'),
            ];
        }

        $this->nasabahList = $daftar;
    }

    public function loadProduk(): void
    {
        /** @var array<int, array{id: int, nama: string, minimal_setor: int}> $daftar */
        $daftar = [];
        foreach (ProdukTabungan::where('status', 'aktif')->get() as $produk) {
            $daftar[] = [
                'id' => (int) $produk->id,
                'nama' => (string) $produk->nama,
                'minimal_setor' => (int) ($produk->minimal_setor ?? 0),
            ];
        }

        $this->produkList = $daftar;
    }

    public function updatedSearchNasabah(): void
    {
        $this->loadNasabah();
    }

    public function pilihNasabah(int|string $id): void
    {
        $nasabahId = (int) $id;

        $dipilih = collect($this->nasabahList)->firstWhere('id', $nasabahId);

        if (! is_array($dipilih)) {
            session()->flash('error', 'Nasabah tidak valid.');

            return;
        }

        $this->nasabahId = (string) $nasabahId;
        $this->selectedNasabah = $dipilih;
        $this->searchNasabah = '';
        $this->showSuccess = false;
        $this->loadNasabah();
    }

    public function updatedNasabahId(): void
    {
        $dipilih = collect($this->nasabahList)->firstWhere('id', (int) $this->nasabahId);
        $this->selectedNasabah = is_array($dipilih) ? $dipilih : null;
    }

    public function gantiNasabah(): void
    {
        $this->nasabahId = '';
        $this->selectedNasabah = null;
        $this->searchNasabah = '';
        $this->loadNasabah();
    }

    public function submit(): void
    {
        $produk = collect($this->produkList)->firstWhere('id', (int) $this->produkId);
        $minimalSetor = max(1000, (int) ($produk['minimal_setor'] ?? 0));

        $this->validate([
            'nasabahId' => ['required', Rule::exists('nasabah_profil', 'user_id')->where('status_pendaftaran', 'aktif')],
            'produkId' => ['required', Rule::exists('produk_tabungan', 'id')->where('status', 'aktif')],
            'nominal' => ['required', 'numeric', 'min:'.$minimalSetor, 'max:1000000000'],
            'tanggal_transaksi' => ['required', 'date', 'date_equals:'.now()->toDateString()],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            app(CatatSetoranAction::class)->execute([
                'nasabah_id' => (int) $this->nasabahId,
                'produk_id' => (int) $this->produkId,
                'nominal' => $this->nominal,
                'tanggal_transaksi' => (string) $this->tanggal_transaksi,
                'sumber_input' => 'real_time',
                'input_by' => (int) Auth::id(),
                'catatan' => $this->catatan ?: null,
                'sudah_disetor_ke_kantor' => true,
                'idempotency_key' => (string) $this->idempotencyKey,
            ]);
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->idempotencyKey = (string) Str::uuid();
        $this->showSuccess = true;
        $this->reset(['nasabahId', 'produkId', 'nominal', 'catatan']);
        $this->selectedNasabah = null;
        $this->searchNasabah = '';
        $this->loadNasabah();

        session()->flash('success', 'Setoran berhasil dicatat.');

        $this->dispatch('setoranCreated');
    }

    public function render(): View
    {
        $riwayatHariIni = TransaksiSetoran::where('input_by', Auth::id())
            ->whereDate('tanggal_input_sistem', today())
            ->with(['nasabah', 'produk'])
            ->latest('id')
            ->take(10)
            ->get();

        $totalHariIni = (float) TransaksiSetoran::where('input_by', Auth::id())
            ->whereDate('tanggal_input_sistem', today())
            ->masihAktif()
            ->sum('nominal');

        return view('livewire.admin.input-setoran', compact('riwayatHariIni', 'totalHariIni'));
    }
}
