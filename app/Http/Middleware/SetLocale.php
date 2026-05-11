<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session()->has('language')) {
            App::setLocale(session()->get('language'));
        } else {
            App::setLocale('tr');
        }

        return $next($request);
    }
}
