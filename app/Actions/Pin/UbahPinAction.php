<?php

namespace App\Actions\Pin;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class UbahPinAction
{
    private const MAX_PERCOBAAN = 5;

    private const RATELIMIT_DECAY_DETIK = 900;

    /**
     * PIN yang dilarang selain pola berulang/berurutan.
     */
    private const PIN_DILARANG = ['123123', '123321', '112233'];

    /**
     * Ganti PIN pengguna: verifikasi PIN lama, tolak PIN sama/lemah,
     * reset penanda wajib ganti PIN, cabut sesi lain, dan catat log.
     *
     * @throws ValidationException bila rate limit terlampaui, PIN lama salah,
     *                             PIN baru sama dengan PIN lama, atau PIN lemah.
     */
    public function execute(User $user, string $pinLama, string $pinBaru): void
    {
        $key = 'ubah-pin:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_PERCOBAAN)) {
            throw ValidationException::withMessages([
                'current_pin' => 'Terlalu banyak percobaan ganti PIN. Coba lagi dalam beberapa menit.',
            ]);
        }

        if (! Hash::check($pinLama, $user->pin_hash)) {
            RateLimiter::hit($key, self::RATELIMIT_DECAY_DETIK);

            throw ValidationException::withMessages([
                'current_pin' => 'PIN saat ini tidak sesuai.',
            ]);
        }

        if ($pinBaru === $pinLama) {
            throw ValidationException::withMessages([
                'pin' => 'PIN baru tidak boleh sama dengan PIN saat ini.',
            ]);
        }

        if ($this->pinLemah($pinBaru)) {
            throw ValidationException::withMessages([
                'pin' => 'PIN terlalu mudah ditebak. Pilih kombinasi lain.',
            ]);
        }

        RateLimiter::clear($key);

        $user->update([
            'pin_hash' => Hash::make($pinBaru),
            'harus_ganti_pin' => false,
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        ActivityLogger::log('ubah_pin', 'users', $user->id);
    }

    private function pinLemah(string $pin): bool
    {
        if (preg_match('/^(\d)\1{5}$/', $pin) === 1) {
            return true;
        }

        $naik = true;
        $turun = true;

        for ($i = 1; $i < 6; $i++) {
            $selisih = ord($pin[$i]) - ord($pin[$i - 1]);

            if ($selisih !== 1) {
                $naik = false;
            }

            if ($selisih !== -1) {
                $turun = false;
            }
        }

        return $naik || $turun || in_array($pin, self::PIN_DILARANG, true);
    }
}
