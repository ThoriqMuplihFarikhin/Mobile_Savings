<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use Illuminate\Database\QueryException;
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

    public $showForm = false;

    public $editId = null;

    public $nama = '';

    public $tipe = 'bebas';

    public $persen_komisi = '';

    public $minimal_setor = '';

    public $harga_per_hari = '';

    public $isi_paket = '';

    public $uang_tunai = '';

    public array $isiPaketItems = [];

    public $periode_mulai = '';

    public $periode_selesai = '';

    public $tanggal_boleh_cair = '';

    public $batas_toleransi = '';

    public $tampilKonfirmasiHapus = false;

    public $deleteId = null;

    public function mount(): void
    {
        $this->isiPaketItems = [['nama' => '', 'jumlah' => '']];
    }

    public function updatedTipe($value): void
    {
        if ($value === 'paket') {
            $this->persen_komisi = '';
        }
    }

    public function tambahItemPaket(): void
    {
        $this->isiPaketItems[] = ['nama' => '', 'jumlah' => ''];
    }

    public function hapusItemPaket($index): void
    {
        unset($this->isiPaketItems[$index]);
        $this->isiPaketItems = array_values($this->isiPaketItems);

        if (empty($this->isiPaketItems)) {
            $this->isiPaketItems = [['nama' => '', 'jumlah' => '']];
        }
    }

    public function render()
    {
        $produk = ProdukTabungan::latest()->paginate(10);

        return view('livewire.admin.manajemen-produk', compact('produk'));
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->resetForm();
    }

    public function resetForm()
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
    }

    public function save()
    {
        $this->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:bebas,paket',
            'persen_komisi' => 'required_if:tipe,bebas|nullable|numeric|min:0|max:100',
            'minimal_setor' => 'nullable|numeric|min:0',
            'harga_per_hari' => 'required_if:tipe,paket|nullable|numeric|min:0',
            'uang_tunai' => 'nullable|numeric|min:0',
            'tanggal_boleh_cair' => 'nullable|date',
            'batas_toleransi' => 'nullable|integer|min:0',
            'isiPaketItems.*.nama' => 'nullable|string|max:255',
            'isiPaketItems.*.jumlah' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'persen_komisi' => $this->tipe === 'paket' ? 0 : $this->persen_komisi,
            'minimal_setor' => $this->minimal_setor ?: null,
            'harga_per_hari' => $this->harga_per_hari ?: null,
            'tanggal_boleh_cair' => $this->tanggal_boleh_cair ?: null,
            'batas_toleransi_tunggakan_hari' => $this->batas_toleransi ?: null,
            'status' => 'aktif',
        ];

        if ($this->tipe === 'paket') {
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
            $data['periode_mulai'] = $this->periode_mulai ?: null;
            $data['periode_selesai'] = $this->periode_selesai ?: null;
        }

        if ($this->editId) {
            ProdukTabungan::find($this->editId)->update($data);
        } else {
            ProdukTabungan::create($data);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Produk berhasil disimpan!');
    }

    public function edit($id)
    {
        $produk = ProdukTabungan::find($id);
        $this->editId = $produk->id;
        $this->nama = $produk->nama;
        $this->tipe = $produk->tipe;
        $this->persen_komisi = $produk->persen_komisi;
        $this->minimal_setor = $produk->minimal_setor;
        $this->harga_per_hari = $produk->harga_per_hari;
        $this->isi_paket = $produk->isi_paket ? json_encode($produk->isi_paket) : '';

        $items = collect($produk->isi_paket ?? []);
        $uangTunai = $items->first(fn ($item) => strtolower(trim($item['nama'] ?? '')) === 'uang tunai');
        $this->uang_tunai = $uangTunai ? preg_replace('/\D/', '', $uangTunai['jumlah'] ?? '') : ($produk->uang_tunai ?? '');

        $barangItems = $items->reject(fn ($item) => strtolower(trim($item['nama'] ?? '')) === 'uang tunai')->values()->all();
        $this->isiPaketItems = ! empty($barangItems) ? $barangItems : [['nama' => '', 'jumlah' => '']];

        $this->periode_mulai = $produk->periode_mulai?->format('Y-m-d');
        $this->periode_selesai = $produk->periode_selesai?->format('Y-m-d');
        $this->tanggal_boleh_cair = $produk->tanggal_boleh_cair?->format('Y-m-d');
        $this->batas_toleransi = $produk->batas_toleransi_tunggakan_hari;
        $this->showForm = true;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->tampilKonfirmasiHapus = true;
    }

    public function delete()
    {
        try {
            ProdukTabungan::find($this->deleteId)?->delete();
            session()->flash('success', 'Produk berhasil dihapus!');
        } catch (QueryException $e) {
            session()->flash('error', 'Produk ini tidak bisa dihapus karena masih memiliki data transaksi/nasabah terkait. Nonaktifkan produk ini saja alih-alih menghapusnya.');
        }

        $this->tampilKonfirmasiHapus = false;
        $this->deleteId = null;
    }

    public function toggleStatus($id)
    {
        $produk = ProdukTabungan::find($id);
        if ($produk) {
            $produk->update(['status' => $produk->status === 'aktif' ? 'nonaktif' : 'aktif']);
        }
        session()->flash('success', 'Status produk berhasil diubah!');
    }
}
