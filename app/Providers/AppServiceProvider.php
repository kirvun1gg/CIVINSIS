<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Categoria;
use App\Services\Translation\DeepLProvider;
use App\Services\Translation\TranslationProviderInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Deja la puerta abierta a cambiar de proveedor de traducción sin
        // tocar TranslationService ni el resto de la app.
        $this->app->bind(TranslationProviderInterface::class, DeepLProvider::class);
    }

    public function boot()
    {
        // Comparte con TODAS las vistas las variables que el frontend espera,
        // reemplazando al antiguo session_helper.php / getCategorias().
        View::composer('*', function ($view) {
            $user = auth_user();

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
