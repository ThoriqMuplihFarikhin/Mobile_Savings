<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('no_hp', $request->no_hp)->first();

            if (! $user) {
                Hash::check($request->password, self::DUMMY_HASH);

                return null;
            }

            if ($user->isLocked()) {
                return null;
            }

            if ($user->login_terkunci_hingga?->isFuture()) {
                return null;
            }

            if (! Hash::check($request->password, $user->pin_hash)) {
                $user->increment('percobaan_gagal');

                if ($user->percobaan_gagal >= 5) {
                    $user->update([
                        'login_terkunci_hingga' => now()->addMinutes(15),
                        'percobaan_gagal' => 0,
                    ]);
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
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
