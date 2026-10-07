<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRoles;
use App\Livewire\Concerns\ValidatesKolektorNasabah;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class SerahTerimaPaket extends Component
{
    use AuthorizesRoles;
    use ValidatesKolektorNasabah;
    use WithFileUploads;

    /** @var string */
    public $filter = 'siap';

    /** @var string */
    public $diterimaOleh = '';

    /** @var string */
    public $tanggalSerahTerima = '';

    /** @var UploadedFile|TemporaryUploadedFile|null */
    public $buktiFoto = null;

    /** @var int|null */
    public $konfirmasiId = null;

    /**
     * @return array<int, string>
     */
    protected function requiredRoles(): array
    {
        return ['admin', 'kolektor'];
    }

    public function mount(): void
    {
        $this->tanggalSerahTerima = now()->toDateString();
    }

    public function pilihFilter(string $filter): void
    {
        if (in_array($filter, ['siap', 'belum', 'sudah'], true)) {
            $this->filter = $filter;
            $this->konfirmasiId = null;
        }
    }

    public function bukaKonfirmasi(int $kepesertaanId): void
    {
        $this->konfirmasiId = $kepesertaanId;
        $this->diterimaOleh = '';
        $this->buktiFoto = null;
    }

    public function batalKonfirmasi(): void
    {
        $this->konfirmasiId = null;
    }

    /**
     * Konfirmasi penyerahan paket ke nasabah: status berubah menjadi sudah_diterima,
     * disertai foto bukti pada disk private, log aktivitas, dan notifikasi nasabah.
     */
    public function konfirmasi(int $kepesertaanId): void
    {
        $this->validate([
            'diterimaOleh' => 'required|string|max:255',
            'tanggalSerahTerima' => 'required|date',
            'buktiFoto' => 'required|image|max:2048',
        ]);

        $foto = $this->buktiFoto;

        if ($foto === null) {
            session()->flash('error', 'Foto bukti serah terima wajib diunggah.');

            return;
        }

        $path = (string) $foto->store('serah-terima', 'local');

        try {
            $kepesertaan = DB::transaction(function () use ($kepesertaanId, $path): KepesertaanPaket {
                $item = KepesertaanPaket::whereKey($kepesertaanId)->lockForUpdate()->first();

                if (! $item instanceof KepesertaanPaket) {
                    throw new DomainException('Kepesertaan tidak ditemukan.');
                }

                if ($item->status_serah_terima === 'sudah_diterima') {
                    throw new DomainException('Kepesertaan ini sudah pernah diserahkan.');
                }

                if ($item->metode_pengambilan === null) {
                    throw new DomainException('Nasabah belum memilih metode pengambilan.');
                }

                if (auth()->user()?->role === 'kolektor') {
                    if ($item->metode_pengambilan !== 'diantar_kolektor') {
                        throw new DomainException('Serah terima dengan metode ini hanya dikonfirmasi admin.');
                    }

                    if (! $this->isNasabahBinaan((int) $item->nasabah_id)) {
                        throw new DomainException('Hanya kolektor penanggung jawab nasabah ini yang dapat mengonfirmasi.');
                    }
                }

                $status = $item->statusPencairan();

                if (! $status['boleh']) {
                    throw new DomainException((string) $status['alasan']);
                }

                $item->update([
                    'status_serah_terima' => 'sudah_diterima',
                    'diterima_oleh' => $this->diterimaOleh,
                    'tanggal_serah_terima' => $this->tanggalSerahTerima,
                    'bukti_foto_url' => $path,
                ]);

                $item->refresh();

                return $item;
            });
        } catch (DomainException $e) {
            Storage::disk('local')->delete($path);
            session()->flash('error', $e->getMessage());

            return;
        }

        try {
            $tanggal = Carbon::parse($this->tanggalSerahTerima);

            ActivityLogger::log('serah_terima_paket', 'kepesertaan_paket', $kepesertaan->id, [
                'nasabah_id' => $kepesertaan->nasabah_id,
                'produk_id' => $kepesertaan->produk_id,
                'metode_pengambilan' => $kepesertaan->metode_pengambilan,
                'diterima_oleh' => $kepesertaan->diterima_oleh,
                'tanggal_serah_terima' => $tanggal->toDateString(),
                'bukti_foto' => $kepesertaan->bukti_foto_url,
            ]);
            ActivityLogger::notify(
                (int) $kepesertaan->nasabah_id,
                'Serah Terima Paket',
                'Paket tabungan Anda sudah diserahkan oleh '.$kepesertaan->diterima_oleh.' pada '.$tanggal->translatedFormat('d M Y').'.',
                'both'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $this->konfirmasiId = null;
        $this->diterimaOleh = '';
        $this->buktiFoto = null;
        $this->tanggalSerahTerima = now()->toDateString();

        session()->flash('success', 'Serah terima paket berhasil dikonfirmasi.');
    }

    public function render(): View
    {
        $query = KepesertaanPaket::query()->with(['produk', 'nasabah']);

        if (auth()->user()?->role === 'kolektor') {
            $query->whereIn('nasabah_id', KolektorNasabah::where('kolektor_id', Auth::id())
                ->where('status', 'aktif')
                ->select('nasabah_id'));
        }

        if ($this->filter === 'sudah') {
            $query->where('status_serah_terima', 'sudah_diterima');
        } else {
            $query->where('status_serah_terima', 'belum');

            if ($this->filter === 'siap') {
                $query->whereNotNull('metode_pengambilan');
            }
        }

        $kepesertaan = $query->orderByDesc('tanggal_mulai_ikut')->get();

        if ($this->filter === 'siap') {
            $kepesertaan = $kepesertaan
                ->filter(fn (KepesertaanPaket $item): bool => $item->statusPencairan()['boleh'])
                ->values();
        } else {
            foreach ($kepesertaan as $item) {
                $item->hitungUlangKepesertaan(false);
            }
        }

        return view('livewire.admin.serah-terima-paket', [
            'kepesertaan' => $kepesertaan,
            'jumlahSiap' => $this->jumlahSiap(),
        ]);
    }

    private function jumlahSiap(): int
    {
        $query = KepesertaanPaket::query()->with('produk')
            ->where('status_serah_terima', 'belum')
            ->whereNotNull('metode_pengambilan');

        if (auth()->user()?->role === 'kolektor') {
            $query->whereIn('nasabah_id', KolektorNasabah::where('kolektor_id', Auth::id())
                ->where('status', 'aktif')
                ->select('nasabah_id'));
        }

        return $query->get()
            ->filter(fn (KepesertaanPaket $item): bool => $item->statusPencairan()['boleh'])
            ->count();
    }
}
