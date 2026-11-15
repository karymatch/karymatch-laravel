<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Pemakaian di rute: RoleMiddleware::class.':admin'
     * Kalau akun yang sedang masuk perannya berbeda, sesinya ditutup dan
     * pengguna diarahkan ke halaman masuk (bukan "akses ditolak" yang membingungkan).
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role !== $role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Halaman itu khusus '.$role.'. Anda sudah keluar, silakan masuk dengan akun yang sesuai.');
        }

        return $next($request);
    }
}
