<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isLocked()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('locked', true);
        }

        if ($request->user() && $request->user()->harus_ganti_pin) {
            $excludedRoutes = ['security.edit', 'logout', 'login'];
            $currentRouteName = $request->route()?->getName();

            if (! in_array($currentRouteName, $excludedRoutes, true)) {
                return redirect()->route('security.edit')->with('force_pin_change', true);
            }
        }

        return $next($request);
    }
}
