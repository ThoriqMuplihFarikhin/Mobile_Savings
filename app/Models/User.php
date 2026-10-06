<?php

namespace App\Models;

use App\Support\KasKolektorHitung;
use App\Support\NomorHp;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string|null $no_hp
 * @property string $pin_hash
 * @property string $role
 * @property string $status_akun
 * @property string $mode_akses
 * @property int $percobaan_gagal
 * @property bool $harus_ganti_pin
 * @property Carbon|null $login_terkunci_hingga
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'no_hp', 'pin_hash', 'role', 'status_akun', 'mode_akses', 'percobaan_gagal', 'harus_ganti_pin', 'notifikasi_wa_aktif', 'foto_profil_path', 'banner_path', 'login_terkunci_hingga'])]
#[Hidden(['pin_hash', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->wasChanged('role') && $user->role) {
                $user->syncRoles([$user->role]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'percobaan_gagal' => 'integer',
            'harus_ganti_pin' => 'boolean',
            'notifikasi_wa_aktif' => 'boolean',
            'login_terkunci_hingga' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string|null, string>
     */
    protected function noHp(): Attribute
    {
        return Attribute::set(fn (?string $value) => $value === null ? null : NomorHp::normalize($value));
    }

    public function isOffline(): bool
    {
        return $this->mode_akses === 'offline';
    }

    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function nasabahProfil(): HasOne
    {
        return $this->hasOne(NasabahProfil::class);
    }

    public function saldoProduks(): HasMany
    {
        return $this->hasMany(SaldoProduk::class, 'nasabah_id');
    }

    public function transaksiSetorans(): HasMany
    {
        return $this->hasMany(TransaksiSetoran::class, 'nasabah_id');
    }

    public function transaksiPenarikans(): HasMany
    {
        return $this->hasMany(TransaksiPenarikan::class, 'nasabah_id');
    }

    public function kepesertaanPakets(): HasMany
    {
        return $this->hasMany(KepesertaanPaket::class, 'nasabah_id');
    }

    public function komplains(): HasMany
    {
        return $this->hasMany(Komplain::class, 'nasabah_id');
    }

    public function kolektorNasabahs(): HasMany
    {
        return $this->hasMany(KolektorNasabah::class, 'kolektor_id');
    }

    public function jadwalKunjungans(): HasMany
    {
        return $this->hasMany(JadwalKunjungan::class, 'kolektor_id');
    }

    public function setoranKolektorKantors(): HasMany
    {
        return $this->hasMany(SetoranKolektorKantor::class, 'kolektor_id');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class);
    }

    public function logNotifikasis(): HasMany
    {
        return $this->hasMany(LogNotifikasi::class, 'nasabah_id');
    }

    public function isLocked(): bool
    {
        return $this->status_akun === 'terkunci';
    }

    public function isNasabah(): bool
    {
        return $this->role === 'nasabah';
    }

    public function isKolektor(): bool
    {
        return $this->role === 'kolektor';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasUnsettledCash(): bool
    {
        if (! $this->isKolektor()) {
            return false;
        }

        // D13: kas net — setoran belum disetor dikurangi penarikan tunai
        // yang belum direkonsiliasi (sumber tunggal KasKolektorHitung).
        return bccomp(KasKolektorHitung::kasDiTangan((int) $this->id), '0', 2) > 0;
    }
}
