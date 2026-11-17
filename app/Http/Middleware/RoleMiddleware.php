<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request based on user roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            // Save intended URL for redirect back after login
            return redirect()->guest(route('login'))->with('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? 'customer');

        // Admin has full access to admin and driver dashboard
        if ($userRole === 'admin') {
            return $next($request);
        }

        $normalizedRoles = array_map('strtolower', $roles);

        // Driver / courier equivalence
        if (in_array('driver', $normalizedRoles) && in_array($userRole, ['driver', 'courier'])) {
            return $next($request);
        }

        if (in_array($userRole, $normalizedRoles)) {
            return $next($request);
        }

        // Unauthorized redirect based on user's actual role
        if ($userRole === 'driver' || $userRole === 'courier') {
            return redirect()->route('driver.dashboard')->with('error', 'Akses ditolak: Akun kurir driver hanya dapat mengakses dashboard kurir.');
        }

        return redirect()->route('home')->with('error', 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman tersebut.');
    }
}
