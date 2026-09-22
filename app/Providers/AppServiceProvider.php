<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use App\Models\Categoria;
use App\Models\Titulo;
use App\Services\Translation\DeepLProvider;
use App\Services\Translation\TranslationProviderInterface;
use App\Services\TranslationService;
use App\Support\CatalogoTraducido;

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
            $tituloEquipado = ($user && $user->titulo_equipado)
                ? Titulo::where('clave', $user->titulo_equipado)->first()
                : null;
            if ($tituloEquipado) {
                // Mutación en memoria (no se guarda): así todas las vistas que
                // ya leen $usuarioTitulo->nombre (sidebar, etc.) reciben el
                // texto traducido sin tener que tocar cada plantilla.
                $tituloEquipado->nombre = CatalogoTraducido::campo(
                    'titulos', $tituloEquipado->clave, 'nombre', $tituloEquipado->nombre
                );
            }

            // OJO: no se muta $categoria->nombre aquí. Varias vistas (dashboard,
            // debates, desafios, faq, crear, propuesta) ya llaman ellas mismas a
            // $cat->translated('nombre'); si además se tradujera aquí, la segunda
            // llamada comparar-ía el hash del texto YA traducido contra el hash
            // cacheado del original, fallaría siempre y forzaría una llamada viva
            // a DeepL en cada request (fue exactamente el bug que causó cargas de
            // 6+ segundos en esas páginas). Cada vista es responsable de traducir
            // por su cuenta; footer.php hace lo mismo por su lado.
            $categorias = Categoria::orderBy('nombre')->get();
            $locale = App::getLocale();
            if ($locale !== 'es' && $categorias->isNotEmpty()) {
                app(TranslationService::class)->warmMany($categorias, ['nombre'], $locale);
            }

            $view->with([
                'usuarioLogueado' => (bool) $user,
                'usuarioId'       => $user->id ?? null,
                'usuarioNombre'   => $user->nombre ?? '',
                'usuarioEmail'    => $user->email ?? '',
                'usuarioRol'      => $user->rol_nombre ?? 'invitado',
                'usuarioAvatar'   => $user->avatar ?? null,
                'usuarioTema'     => $user->tema_perfil ?? 'verde',
                'usuarioNivel'    => $user->nivel ?? 1,
                'usuarioInsigniaEmoji' => $user->insignia ?? null,
                'usuarioTitulo'   => $tituloEquipado,
                'categorias'      => $categorias,
            ]);
        });
    }
}
