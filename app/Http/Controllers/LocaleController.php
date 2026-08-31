<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function cambiar(Request $request, string $locale)
    {
        $soportados = array_keys(config('locales.supported', []));
        if (!in_array($locale, $soportados, true)) {
            return redirect()->back();
        }

        session(['locale' => $locale]);

        $usuario = Auth::user();
        if ($usuario) {
            $usuario->idioma = $locale;
            $usuario->save();
        }

        return redirect()->back()->cookie(Cookie::forever('locale', $locale));
    }
}
