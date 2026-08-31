<?php

if (!function_exists('civinsis_iniciales')) {
    function civinsis_iniciales(?string $nombre, bool $logueado = true): string
    {
        if (!$logueado) {
            return 'U';
        }

        return strtoupper(substr($nombre ?? '', 0, 1));
    }
}

if (!function_exists('auth_user')) {
    /**
     * Auth::user() está tipado por el contrato genérico Authenticatable, así
     * que el IDE no puede ver los métodos/relaciones propios de App\Models\User
     * (esAdmin(), propuestas(), etc.) y los marca como indefinidos aunque
     * existan y funcionen. Este helper declara el tipo de retorno real de la
     * app (config/auth.php usa App\Models\User como modelo), lo que resuelve
     * el aviso del IDE sin cambiar el comportamiento en tiempo de ejecución.
     */
    function auth_user(): ?\App\Models\User
    {
        return \Illuminate\Support\Facades\Auth::user();
    }
}
