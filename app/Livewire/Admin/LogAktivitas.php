<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\LogAktivitas as LogAktivitasModel;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class LogAktivitas extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $search = '';

    public string $aksiFilter = '';

    /**
     * @var array<int, string|null>
     */
    private static array $userNameCache = [];

    public function render(): View
    {
        $query = LogAktivitasModel::with('user');

        if ($this->search) {
            $searchTerm = "%{$this->search}%";
            $query->where(function ($q) use ($searchTerm) {
                $q->where('aksi', 'like', $searchTerm)
                    ->orWhere('entitas_terkait', 'like', $searchTerm)
                    ->orWhere('detail', 'like', $searchTerm)
                    ->orWhere('ip_address', 'like', $searchTerm)
                    ->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($this->aksiFilter) {
            $aksiMap = [
                'setor' => ['setor', 'koreksi_setoran', 'batal_setoran'],
                'tarik' => ['tarik', 'selesai_penarikan_offline', 'kas_berkurang_penarikan_tunai', 'batalkan_penarikan'],
                'verifikasi' => ['verifikasi', 'verifikasi_nasabah', 'verifikasi_penarikan_offline'],
                'approve_penarikan' => ['approve_penarikan', 'approve_penarikan_pertama'],
                'komplain' => ['komplain', 'buat_komplain', 'selesai_komplain'],
                'rekon' => ['rekon', 'batal_setoran_kantor', 'tolak_setoran_kantor'],
            ];

            if (isset($aksiMap[$this->aksiFilter])) {
                $query->whereIn('aksi', $aksiMap[$this->aksiFilter]);
            } else {
                $query->where('aksi', $this->aksiFilter);
            }
        }

        $logs = $query->latest('timestamp')->latest('id')->paginate(20);

        return view('livewire.admin.log-aktivitas', compact('logs'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAksiFilter(): void
    {
        $this->resetPage();
    }

    public function formatAksi(string $aksi): string
    {
        return match ($aksi) {
            'setor', 'koreksi_setoran', 'batal_setoran' => 'Setor',
            'tarik', 'selesai_penarikan_offline', 'kas_berkurang_penarikan_tunai', 'batalkan_penarikan' => 'Tarik',
            'verifikasi', 'verifikasi_nasabah', 'verifikasi_penarikan_offline' => 'Verifikasi',
            'approve_penarikan', 'approve_penarikan_pertama' => 'Approve Penarikan',
            'komplain', 'buat_komplain', 'selesai_komplain' => 'Komplain',
            'rekon', 'batal_setoran_kantor', 'tolak_setoran_kantor' => 'Rekonsiliasi',
            default => str(str_replace('_', ' ', $aksi))->title()->toString(),
        };
    }

    public function formatKeterangan(LogAktivitasModel $log): string
    {
        $detail = $log->detail;

        if (is_string($detail) && ! empty($detail)) {
            return $detail;
        }

        if (is_array($detail)) {
            if (isset($detail['keterangan']) && is_string($detail['keterangan']) && ! empty($detail['keterangan'])) {
                return $detail['keterangan'];
            }
            if (isset($detail['pesan']) && is_string($detail['pesan']) && ! empty($detail['pesan'])) {
                return $detail['pesan'];
            }
            if (isset($detail['catatan']) && is_string($detail['catatan']) && ! empty($detail['catatan'])) {
                return $detail['catatan'];
            }

            if ($log->aksi === 'setor') {
                $nominal = isset($detail['nominal']) ? 'Rp '.number_format((float) $detail['nominal'], 0, ',', '.') : '';
                $target = '';
                if (isset($detail['nasabah_nama'])) {
                    $target = ' untuk '.$detail['nasabah_nama'];
                } elseif (isset($detail['nasabah_id'])) {
                    $nama = $this->getUserName((int) $detail['nasabah_id']);
                    if ($nama) {
                        $target = ' untuk '.$nama;
                    }
                }

                return 'Setoran '.$nominal.$target;
            }

            if (in_array($log->aksi, ['approve_penarikan', 'approve_penarikan_pertama'], true)) {
                $nominal = isset($detail['nominal']) ? 'Rp '.number_format((float) $detail['nominal'], 0, ',', '.') : '';
                $target = '';
                if (isset($detail['nasabah_nama'])) {
                    $target = ' '.$detail['nasabah_nama'];
                } elseif (isset($detail['nasabah_id'])) {
                    $nama = $this->getUserName((int) $detail['nasabah_id']);
                    if ($nama) {
                        $target = ' '.$nama;
                    }
                }

                return 'Menyetujui penarikan '.$nominal.$target;
            }

            if (in_array($log->aksi, ['tarik', 'selesai_penarikan_offline', 'kas_berkurang_penarikan_tunai'], true)) {
                $nominal = isset($detail['nominal']) ? 'Rp '.number_format((float) $detail['nominal'], 0, ',', '.') : '';
                $target = '';
                if (isset($detail['nasabah_nama'])) {
                    $target = ' '.$detail['nasabah_nama'];
                } elseif (isset($detail['nasabah_id'])) {
                    $nama = $this->getUserName((int) $detail['nasabah_id']);
                    if ($nama) {
                        $target = ' '.$nama;
                    }
                }

                return ($log->aksi === 'selesai_penarikan_offline' ? 'Penarikan offline ' : 'Penarikan ').$nominal.$target;
            }

            if ($log->aksi === 'rekon') {
                $kolektor = '';
                if (isset($detail['kolektor_nama'])) {
                    $kolektor = ' '.$detail['kolektor_nama'];
                } elseif (isset($detail['kolektor_id'])) {
                    $nama = $this->getUserName((int) $detail['kolektor_id']);
                    if ($nama) {
                        $kolektor = ' '.$nama;
                    }
                }
                $diterima = isset($detail['diterima']) ? 'Rp '.number_format((float) $detail['diterima'], 0, ',', '.') : '';

                return 'Setoran kas'.$kolektor.' diterima '.$diterima;
            }

            if ($log->aksi === 'verifikasi_nasabah') {
                $target = '';
                if (isset($detail['nama'])) {
                    $target = ' '.$detail['nama'];
                } elseif (isset($detail['nasabah_id'])) {
                    $nama = $this->getUserName((int) $detail['nasabah_id']);
                    if ($nama) {
                        $target = ' '.$nama;
                    }
                }

                return 'Menyetujui pendaftaran'.$target;
            }

            if ($log->aksi === 'selesai_komplain') {
                $target = '';
                if (isset($detail['nasabah_nama'])) {
                    $target = ' '.$detail['nasabah_nama'];
                }

                return 'Memproses komplain'.$target;
            }

            $parts = [];
            foreach ($detail as $key => $val) {
                if (is_scalar($val)) {
                    $parts[] = "{$key}: {$val}";
                }
            }
            if (! empty($parts)) {
                return implode(', ', $parts);
            }
        }

        return $log->entitas_terkait ? "Aktivitas pada {$log->entitas_terkait}" : '-';
    }

    private function getUserName(int $id): ?string
    {
        if (! array_key_exists($id, self::$userNameCache)) {
            self::$userNameCache[$id] = User::whereKey($id)->value('name');
        }

        return self::$userNameCache[$id];
    }
}
