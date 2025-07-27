<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = $request->user();

    // Jika belum login, arahkan ke halaman login
    if (!Auth::check()) {
        return redirect()->route('login'); // atau sesuaikan route login kamu
    }

    // Jika user tidak punya role
    if (!$user->role) {
        abort(403, 'ACCESS DENIED');
    }

    // Cek apakah role user diizinkan
    foreach ($roles as $role) {
        if ($user->role->role_name === $role) {
            return $next($request); // akses diizinkan
        }
    }

    // Role tidak sesuai
    abort(403, 'ACCESS DENIED');
}
}
