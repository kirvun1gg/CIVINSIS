<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Categoria;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Comparte con TODAS las vistas las variables que el frontend espera,
        // reemplazando al antiguo session_helper.php / getCategorias().
        View::composer('*', function ($view) {
            $user = Auth::user();

            $view->with([
                'usuarioLogueado' => (bool) $user,
                'usuarioId'       => $user->id ?? null,
                'usuarioNombre'   => $user->nombre ?? '',
                'usuarioEmail'    => $user->email ?? '',
                'usuarioRol'      => $user->rol_nombre ?? 'invitado',
                'usuarioAvatar'   => $user->avatar ?? null,
                'usuarioTema'     => $user->tema_perfil ?? 'verde',
                'categorias'      => Categoria::orderBy('nombre')->get(),
            ]);
        });
    }
}
