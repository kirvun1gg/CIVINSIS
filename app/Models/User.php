<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'apellido', 'email', 'password', 'rol_id', 'activo',
        'avatar', 'bio', 'color_perfil', 'color_banner', 'banner_imagen',
        'tema_perfil', 'marco_avatar', 'insignia', 'frase', 'ubicacion',
        'sitio_web', 'social_twitter', 'social_instagram', 'social_github',
        'perfil_publico', 'ultimo_acceso', 'idioma',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'activo'         => 'boolean',
        'perfil_publico' => 'boolean',
        'ultimo_acceso'  => 'datetime',
    ];

    // Relaciones
    public function rol()         { return $this->belongsTo(Role::class, 'rol_id'); }
    public function propuestas()  { return $this->hasMany(Proposal::class, 'usuario_id'); }
    public function votos()       { return $this->hasMany(Voto::class, 'usuario_id'); }
    public function comentarios() { return $this->hasMany(Comentario::class, 'usuario_id'); }

    // Helpers
    /**
     * Clase CSS del marco equipado.
     * En 'marco_equipado' se guarda la CLAVE del cosmético (p. ej. "marco_dorado"),
     * pero el CSS usa el campo VALOR (p. ej. "marco-dorado"). Aquí se traduce.
     */
    public function getMarcoClaseAttribute(): ?string
    {
        if (empty($this->marco_equipado)) return null;

        static $cache = [];
        $clave = $this->marco_equipado;
        if (!array_key_exists($clave, $cache)) {
            $cache[$clave] = \App\Models\Cosmetico::where('clave', $clave)->value('valor');
        }
        // Si el cosmético ya no existe, se convierte la clave por si acaso
        return $cache[$clave] ?: str_replace('_', '-', $clave);
    }

    /** Clase CSS del efecto equipado (mismo caso que el marco). */
    public function getEfectoClaseAttribute(): ?string
    {
        if (empty($this->efecto_equipado)) return null;

        static $cacheE = [];
        $clave = $this->efecto_equipado;
        if (!array_key_exists($clave, $cacheE)) {
            $cacheE[$clave] = \App\Models\Cosmetico::where('clave', $clave)->value('valor');
        }
        return $cacheE[$clave] ?: str_replace('_', '-', $clave);
    }

    /** Clase CSS del fondo de perfil equipado (mismo caso que el marco). */
    public function getFondoClaseAttribute(): ?string
    {
        if (empty($this->fondo_equipado)) return null;

        static $cacheF = [];
        $clave = $this->fondo_equipado;
        if (!array_key_exists($clave, $cacheF)) {
            $cacheF[$clave] = \App\Models\Cosmetico::where('clave', $clave)->value('valor');
        }
        return $cacheF[$clave] ?: str_replace('_', '-', $clave);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    public function getRolNombreAttribute(): string
    {
        return $this->rol->nombre ?? 'usuario';
    }

    public function esAdmin(): bool
    {
        return in_array($this->rol_nombre, ['admin', 'moderador']);
    }

    public function getInicialesAttribute(): string
    {
        return strtoupper(mb_substr($this->nombre, 0, 1));
    }
}
