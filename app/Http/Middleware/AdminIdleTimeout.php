<?php

namespace App\Http\Middleware;

use App\Helpers\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * F2 / D26 — sesi admin berakhir bila tidak aktif melewati batas
 * (config auth.admin_idle_minutes / env ADMIN_IDLE_MINUTES, default 30 menit).
 *
 * Berjalan pada seluruh rute ber-`auth` sehingga mencakup rute prefix admin
 * maupun rute bersama yang dibuka admin, dan didaftarkan sebagai persistent
 * middleware Livewire agar permintaan update juga ikut diperiksa.
 */
class AdminIdleTimeout
{
    /**
     * Kunci sesi penyimpanan stempel aktivitas terakhir admin.
     */
    public const SESSION_KEY = 'admin_last_activity';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isAdmin()) {
            return $next($request);
        }

        $batasMenit = (int) config('auth.admin_idle_minutes', 30);

        if ($batasMenit <= 0) {
            return $next($request);
        }

        $session = $request->session();
        $sekarang = now()->getTimestamp();
        $terakhir = $session->get(self::SESSION_KEY);

        if (is_numeric($terakhir) && ($sekarang - (int) $terakhir) > $batasMenit * 60) {
            ActivityLogger::log('sesi_admin_kedaluwarsa', 'users', $user->id, [
                'idle_menit' => (int) floor(($sekarang - (int) $terakhir) / 60),
                'batas_menit' => $batasMenit,
            ], $user->id);

            auth()->logout();

            $session->invalidate();
            $session->regenerateToken();

            return redirect()->route('login.admin')->with('idle_expired', true);
        }

        $session->put(self::SESSION_KEY, $sekarang);

        return $next($request);
    }
}
