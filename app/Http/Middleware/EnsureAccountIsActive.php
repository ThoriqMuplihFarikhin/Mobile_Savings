<?php

namespace App\Http\Middleware;

use App\Support\PortalLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isLocked()) {
            $role = $request->user()->role;

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route(PortalLogin::routeName($role))
                ->with('locked', true);
        }

        if ($request->user() && $request->user()->harus_ganti_pin) {
            $excludedRoutes = ['security.edit', 'admin.settings.security', 'logout', 'login'];
            $currentRouteName = $request->route()?->getName();

            if (! in_array($currentRouteName, $excludedRoutes, true)) {
                $targetRoute = $request->user()->isAdmin() ? 'admin.settings.security' : 'security.edit';

                return redirect()->route($targetRoute)->with('force_pin_change', true);
            }
        }

        return $next($request);
    }
}
