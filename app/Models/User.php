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
        'perfil_publico', 'ultimo_acceso',
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
