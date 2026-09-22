<?php

namespace App\Support;

/**
 * Traduce un campo de texto de un ítem de catálogo estático (cosméticos,
 * títulos, misiones, logros, insignias) sembrado directo en la BD en
 * español, sin columna de idioma. Cada ítem tiene una 'clave' única y su
 * traducción vive en resources/lang/{locale}/civinsis.php bajo
 * "<tabla>.<clave>.<campo>". Si falta la clave (ítem nuevo sin traducir
 * todavía), cae de vuelta al valor original guardado en la BD.
 */
class CatalogoTraducido
{
    public static function campo(string $tabla, ?string $clave, string $campo, ?string $original): string
    {
        $original = $original ?? '';
        if (!$clave) return $original;

        $llave = "civinsis.{$tabla}.{$clave}.{$campo}";
        $valor = __($llave);

        // __() devuelve la propia clave tal cual cuando no encuentra traducción.
        return $valor === $llave ? $original : $valor;
    }
}
