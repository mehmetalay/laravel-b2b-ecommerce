<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Route::is('admin.*')) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user->status == 0 || $user->block_entry == 1) {
                Auth::guard('web')->logout();

                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login.form')->with(['swal-warning' => 'Hesabınız engellendi veya geçici olarak devre dışı bırakıldı.']);
            }
        } else if (Auth::guard('subdealer')->check()) {
            $subDealer = Auth::guard('subdealer')->user();

            if ($subDealer->status == 0) {
                Auth::guard('subdealer')->logout();

                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login.form')->with(['swal-warning' => 'Hesabınız pasif duruma getirildiği için oturumunuz sonlandırıldı.']);
            }
        }

        return $next($request);
    }
}
