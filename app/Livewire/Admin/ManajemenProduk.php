<?php

namespace App\Livewire\Admin;

use App\Models\ProdukTabungan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManajemenProduk extends Component
{
    use WithPagination;

    public $showForm = false;

    public $editId = null;

    public $nama = '';

    public $tipe = 'bebas';

    public $persen_komisi = '';

    public $minimal_setor = '';

    public $harga_per_hari = '';

    public $isi_paket = '';

    public $periode_mulai = '';

    public $periode_selesai = '';

    public $tanggal_boleh_cair = '';

    public $batas_toleransi = '';

    public $confirmDelete = false;

    public $deleteId = null;

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
            'persen_komisi' => 'required|numeric|min:0|max:100',
            'minimal_setor' => 'nullable|numeric|min:0',
            'harga_per_hari' => 'required_if:tipe,paket|nullable|numeric|min:0',
            'tanggal_boleh_cair' => 'nullable|date',
            'batas_toleransi' => 'nullable|integer|min:0',
        ]);

        $data = [
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'persen_komisi' => $this->persen_komisi,
            'minimal_setor' => $this->minimal_setor ?: null,
            'harga_per_hari' => $this->harga_per_hari ?: null,
            'tanggal_boleh_cair' => $this->tanggal_boleh_cair ?: null,
            'batas_toleransi_tunggakan_hari' => $this->batas_toleransi ?: null,
            'status' => 'aktif',
        ];

        if ($this->tipe === 'paket') {
            $data['isi_paket'] = $this->isi_paket ? json_decode($this->isi_paket, true) : null;
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
        $this->periode_mulai = $produk->periode_mulai?->format('Y-m-d');
        $this->periode_selesai = $produk->periode_selesai?->format('Y-m-d');
        $this->tanggal_boleh_cair = $produk->tanggal_boleh_cair?->format('Y-m-d');
        $this->batas_toleransi = $produk->batas_toleransi_tunggakan_hari;
        $this->showForm = true;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function delete()
    {
        ProdukTabungan::find($this->deleteId)?->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Produk berhasil dihapus!');
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
