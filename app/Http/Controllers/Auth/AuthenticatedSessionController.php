<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * route('login') es a donde apunta el middleware "guest"/"auth" de
     * Laravel (y cualquier redirect()->route('login') del framework), pero
     * la página de login real de CIVINSIS es la pantalla split-screen de
     * auth.php — no la plantilla por defecto de Breeze. Sin esto, cualquier
     * redirección automática (reset de contraseña, sesión expirada, etc.)
     * mandaba al usuario a esa plantilla vieja sin ningún estilo.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.auth');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
