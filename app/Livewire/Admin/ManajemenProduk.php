<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManajemenProduk extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public bool $showForm = false;

    public ?int $editId = null;

    public string $nama = '';

    public string $tipe = 'bebas';

    public string $persen_komisi = '';

    public string $minimal_setor = '';

    public string $harga_per_hari = '';

    public string $isi_paket = '';

    public string $uang_tunai = '';

    /** @var array<int|string, mixed> */
    public array $isiPaketItems = [];

    public ?string $periode_mulai = '';

    public ?string $periode_selesai = '';

    public ?string $tanggal_boleh_cair = '';

    public string $batas_toleransi = '';

    public bool $tampilKonfirmasiHapus = false;

    public ?int $deleteId = null;

    public int $jumlahPesertaEdit = 0;

    public function mount(): void
    {
        $this->isiPaketItems = [['nama' => '', 'jumlah' => '']];
    }

    public function updatedTipe(string $value): void
    {
        if ($value === 'paket') {
            $this->persen_komisi = '';
        }
    }

    public function tambahItemPaket(): void
    {
        $this->isiPaketItems[] = ['nama' => '', 'jumlah' => ''];
    }

    public function hapusItemPaket(int|string $index): void
    {
        unset($this->isiPaketItems[$index]);
        $this->isiPaketItems = array_values($this->isiPaketItems);

        if (empty($this->isiPaketItems)) {
            $this->isiPaketItems = [['nama' => '', 'jumlah' => '']];
        }
    }

    public function render(): View
    {
        $produk = ProdukTabungan::latest()->paginate(10);

        return view('livewire.admin.manajemen-produk', compact('produk'));
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->nama = '';
        $this->tipe = 'bebas';
        $this->persen_komisi = '';
        $this->minimal_setor = '';
        $this->harga_per_hari = '';
        $this->isi_paket = '';
        $this->uang_tunai = '';
        $this->isiPaketItems = [['nama' => '', 'jumlah' => '']];
        $this->periode_mulai = '';
        $this->periode_selesai = '';
        $this->tanggal_boleh_cair = '';
        $this->batas_toleransi = '';
        $this->jumlahPesertaEdit = 0;
    }

    public function save(): void
    {
        $paket = $this->tipe === 'paket';

        $this->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:bebas,paket',
            'persen_komisi' => 'required_if:tipe,bebas|nullable|numeric|min:0|max:100',
            'minimal_setor' => 'nullable|numeric|min:0',
            'harga_per_hari' => $paket ? 'required|numeric|min:1' : 'nullable',
            'uang_tunai' => 'nullable|numeric|min:0',
            'periode_mulai' => $paket ? 'required|date' : 'nullable',
            'periode_selesai' => $paket ? 'required|date|after_or_equal:periode_mulai' : 'nullable',
            'tanggal_boleh_cair' => $paket ? 'required|date|after_or_equal:periode_selesai' : 'nullable',
            'batas_toleransi' => 'nullable|integer|min:0',
            'isiPaketItems.*.nama' => 'nullable|string|max:255',
            'isiPaketItems.*.jumlah' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'persen_komisi' => $paket ? 0 : $this->persen_komisi,
            'minimal_setor' => $this->minimal_setor ?: null,
            'harga_per_hari' => $paket ? ($this->harga_per_hari ?: null) : null,
            'periode_mulai' => $paket ? ($this->periode_mulai ?: null) : null,
            'periode_selesai' => $paket ? ($this->periode_selesai ?: null) : null,
            'tanggal_boleh_cair' => $paket ? ($this->tanggal_boleh_cair ?: null) : null,
            'batas_toleransi_tunggakan_hari' => $this->batas_toleransi ?: null,
        ];

        if ($paket) {
            $items = collect($this->isiPaketItems)
                ->filter(fn ($item) => trim($item['nama'] ?? '') !== '')
                ->map(fn ($item) => [
                    'nama' => trim($item['nama']),
                    'jumlah' => trim($item['jumlah'] ?? ''),
                ])
                ->values()
                ->all();

            if ($this->uang_tunai) {
                $items[] = [
                    'nama' => 'Uang Tunai',
                    'jumlah' => 'Rp '.number_format((float) $this->uang_tunai, 0, ',', '.'),
                ];
            }

            $data['isi_paket'] = ! empty($items) ? $items : null;
            $data['uang_tunai'] = $this->uang_tunai ?: null;
        }

        if ($this->editId) {
            $produk = ProdukTabungan::find($this->editId);
            if (! $produk) {
                session()->flash('error', 'Produk tidak ditemukan.');

                return;
            }
            $produk->update($data);
        } else {
            ProdukTabungan::create($data + ['status' => 'aktif']);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Produk berhasil disimpan!');
    }

    public function edit(int $id): void
    {
        $produk = ProdukTabungan::find($id);
        if (! $produk) {
            session()->flash('error', 'Produk tidak ditemukan.');

            return;
        }

        $this->editId = $produk->id;
        $this->nama = $produk->nama;
        $this->tipe = $produk->tipe;
        $this->persen_komisi = (string) $produk->persen_komisi;
        $this->minimal_setor = (string) $produk->minimal_setor;
        $this->harga_per_hari = (string) $produk->harga_per_hari;
        $this->isi_paket = $produk->isi_paket ? (string) json_encode($produk->isi_paket) : '';

        $items = collect($produk->isi_paket ?? []);
        $uangTunai = $items->first(fn ($item) => strtolower(trim($item['nama'] ?? '')) === 'uang tunai');
        $this->uang_tunai = $uangTunai ? (string) preg_replace('/\D/', '', (string) ($uangTunai['jumlah'] ?? '')) : (string) ($produk->uang_tunai ?? '');

        $barangItems = $items->reject(fn ($item) => strtolower(trim($item['nama'] ?? '')) === 'uang tunai')->values()->all();
        $this->isiPaketItems = ! empty($barangItems) ? $barangItems : [['nama' => '', 'jumlah' => '']];

        $this->periode_mulai = $produk->periode_mulai ? Carbon::parse($produk->periode_mulai)->format('Y-m-d') : null;
        $this->periode_selesai = $produk->periode_selesai ? Carbon::parse($produk->periode_selesai)->format('Y-m-d') : null;
        $this->tanggal_boleh_cair = $produk->tanggal_boleh_cair ? Carbon::parse($produk->tanggal_boleh_cair)->format('Y-m-d') : null;
        $this->batas_toleransi = (string) ($produk->batas_toleransi_tunggakan_hari ?? '');
        $this->jumlahPesertaEdit = $produk->kepesertaanPakets()->count();
        $this->showForm = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->tampilKonfirmasiHapus = true;
    }

    public function delete(): void
    {
        $produk = ProdukTabungan::find($this->deleteId);

        if (! $produk) {
            session()->flash('error', 'Produk tidak ditemukan.');
        } else {
            try {
                $produk->delete();
                session()->flash('success', 'Produk berhasil dihapus!');
            } catch (QueryException $e) {
                session()->flash('error', 'Produk ini tidak bisa dihapus karena masih memiliki data transaksi/nasabah terkait. Nonaktifkan produk ini saja alih-alih menghapusnya.');
            }
        }

        $this->tampilKonfirmasiHapus = false;
        $this->deleteId = null;
    }

    public function toggleStatus(int $id): void
    {
        $produk = ProdukTabungan::find($id);
        if ($produk) {
            $produk->update(['status' => $produk->status === 'aktif' ? 'nonaktif' : 'aktif']);
        }
        session()->flash('success', 'Status produk berhasil diubah!');
    }
}
