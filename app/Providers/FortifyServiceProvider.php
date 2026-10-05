<?php

namespace App\Providers;

use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Support\NomorHp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Hash palsu untuk menyamakan waktu respons ketika nomor tidak terdaftar.
     */
    private const DUMMY_HASH = '$2y$10$Y0Xe2V.kSpRMChxXQefPC.5TNb3vBMnrpPFsTZf4B.hGcE3Z1mfRa';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    private function configureActions(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $password = is_string($request->password) ? $request->password : '';

            if (preg_match('/^\d{6}$/', $password) !== 1) {
                Hash::check($password, self::DUMMY_HASH);

                return null;
            }

            $user = User::where('no_hp', NomorHp::normalize((string) $request->no_hp))->first();

            if (! $user) {
                Hash::check($password, self::DUMMY_HASH);

                return null;
            }

            if ($user->isLocked()) {
                return null;
            }

            $kunciHingga = $user->login_terkunci_hingga;

            if ($kunciHingga !== null && $kunciHingga->isFuture()) {
                if (Hash::check($password, $user->pin_hash)) {
                    throw ValidationException::withMessages([
                        Fortify::username() => 'Akun terkunci sementara hingga '.$kunciHingga->format('H:i').'. Coba lagi setelah masa kunci berakhir.',
                    ]);
                }

                return null;
            }

            if (! Hash::check($password, $user->pin_hash)) {
                $user->increment('percobaan_gagal');

                ActivityLogger::log('login_gagal', 'users', $user->id, [
                    'percobaan_gagal' => $user->percobaan_gagal,
                ], $user->id);

                if ($user->percobaan_gagal >= 5) {
                    $kunciKe = (int) Cache::get("login-lock-count:{$user->id}", 0) + 1;
                    Cache::put("login-lock-count:{$user->id}", $kunciKe, now()->addDay());

                    $durasiMenit = match ($kunciKe) {
                        1 => 15,
                        2 => 60,
                        default => 360,
                    };

                    $user->update([
                        'login_terkunci_hingga' => now()->addMinutes($durasiMenit),
                        'percobaan_gagal' => 0,
                    ]);

                    ActivityLogger::log('akun_terkunci_otomatis', 'users', $user->id, [
                        'kunci_ke' => $kunciKe,
                        'durasi_menit' => $durasiMenit,
                    ], $user->id);
                }

                return null;
            }

            $user->update(['percobaan_gagal' => 0, 'login_terkunci_hingga' => null]);

            return $user;
        });
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages.auth.login'));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $identity = NomorHp::normalize((string) $request->input(Fortify::username()));
            $throttleKey = Str::transliterate(Str::lower($identity).'|'.$request->ip());

            return [
                Limit::perMinute(5)->by($throttleKey),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });
    }
}
