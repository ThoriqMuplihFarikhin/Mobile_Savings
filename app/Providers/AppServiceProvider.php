<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAccountIsActive;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Middleware\RoleMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->guardDatabaseProduksi();
        $this->configureDefaults();
        $this->configureLivewire();
    }

    /**
     * SQLite tidak mendukung lockForUpdate; kunci baris dipakai semua aksi saldo.
     */
    protected function guardDatabaseProduksi(): void
    {
        if (app()->isProduction() && config('database.default') === 'sqlite') {
            throw new RuntimeException('Produksi wajib MySQL: SQLite tidak mendukung lockForUpdate.');
        }
    }

    /**
     * Jalankan middleware active & role terhadap route halaman asal
     * setiap request /livewire/update (bukan hanya saat muat halaman).
     */
    protected function configureLivewire(): void
    {
        Livewire::addPersistentMiddleware([
            EnsureAccountIsActive::class,
            RoleMiddleware::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
