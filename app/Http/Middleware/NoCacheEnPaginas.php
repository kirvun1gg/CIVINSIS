<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Evita que el navegador guarde las páginas HTML en su caché "atrás/adelante"
 * (bfcache). Sin esto, al cerrar sesión y pulsar "atrás", el navegador podría
 * restaurar una copia congelada de la página con el usuario aún visible.
 */
class NoCacheEnPaginas
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo afecta respuestas HTML (no assets, no JSON de la API)
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'text/html')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
