<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $soportados = array_keys(config('locales.supported', []));
        $porDefecto = config('locales.default', 'es');

        $locale = $request->query('lang');

        if ($locale && in_array($locale, $soportados, true)) {
            session(['locale' => $locale]);
        } else {
            $usuario = Auth::user();
            $locale = $usuario->idioma
                ?? session('locale')
                ?? $request->cookie('locale')
                ?? $porDefecto;
        }

        if (!in_array($locale, $soportados, true)) {
            $locale = $porDefecto;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
