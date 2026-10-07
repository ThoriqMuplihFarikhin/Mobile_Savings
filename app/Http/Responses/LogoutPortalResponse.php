<?php

namespace App\Http\Responses;

use App\Support\PortalLogin;
use Laravel\Fortify\Contracts\LogoutResponse;

class LogoutPortalResponse implements LogoutResponse
{
    /**
     * Logout kembali ke halaman login portal yang sama (D19, §6.3).
     * Cookie portal dibersihkan setelah dibaca; tanpa cookie -> portal nasabah.
     */
    public function toResponse($request)
    {
        $portal = $request->cookie(PortalLogin::COOKIE);

        if (! is_string($portal) || ! in_array($portal, PortalLogin::semua(), true)) {
            $portal = PortalLogin::NASABAH;
        }

        return redirect()
            ->route(PortalLogin::routeName($portal))
            ->withCookie(cookie()->forget(PortalLogin::COOKIE));
    }
}
