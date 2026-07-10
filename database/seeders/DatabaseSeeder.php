<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Categoria;
use App\Models\User;
use App\Models\Proposal;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ───────────────────────────────────────────
        $roles = [
            ['nombre' => 'admin',     'descripcion' => 'Administrador total'],
            ['nombre' => 'moderador', 'descripcion' => 'Modera contenido'],
            ['nombre' => 'usuario',   'descripcion' => 'Usuario estándar'],
        ];
        foreach ($roles as $r) Role::firstOrCreate(['nombre' => $r['nombre']], $r);

        $rolAdmin   = Role::where('nombre', 'admin')->first()->id;
        $rolUsuario = Role::where('nombre', 'usuario')->first()->id;

        // ── Categorías (con su efecto hover temático) ───────
        $categorias = [
            ['nombre' => 'Medio Ambiente', 'icono' => 'fas fa-leaf',          'color' => '#22c55e', 'efecto' => 'medio-ambiente',  'descripcion' => 'Sostenibilidad, naturaleza y ecología'],
            ['nombre' => 'Educación',      'icono' => 'fas fa-graduation-cap', 'color' => '#3b82f6', 'efecto' => 'educacion',       'descripcion' => 'Aprendizaje y formación'],
            ['nombre' => 'Salud',          'icono' => 'fas fa-heart-pulse',    'color' => '#ef4444', 'efecto' => 'salud',           'descripcion' => 'Bienestar y salud pública'],
            ['nombre' => 'Tecnología',     'icono' => 'fas fa-microchip',      'color' => '#06b6d4', 'efecto' => 'tecnologia',      'descripcion' => 'Innovación y tecnología'],
            ['nombre' => 'Cultura',        'icono' => 'fas fa-masks-theater',  'color' => '#a855f7', 'efecto' => 'cultura',         'descripcion' => 'Arte, música y tradiciones'],
            ['nombre' => 'Deporte',        'icono' => 'fas fa-futbol',         'color' => '#f97316', 'efecto' => 'deporte',         'descripcion' => 'Deporte y vida activa'],
            ['nombre' => 'Seguridad',      'icono' => 'fas fa-shield-halved',  'color' => '#eab308', 'efecto' => 'seguridad',       'descripcion' => 'Seguridad ciudadana'],
            ['nombre' => 'Infraestructura','icono' => 'fas fa-road',           'color' => '#64748b', 'efecto' => 'infraestructura', 'descripcion' => 'Obras y espacios públicos'],
        ];
        foreach ($categorias as $c) Categoria::firstOrCreate(['nombre' => $c['nombre']], $c);

        // ── Usuario administrador ───────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@civinsis.test'],
            [
                'nombre'       => 'Admin',
                'apellido'     => 'CIVINSIS',
                'password'     => Hash::make('password'),
                'rol_id'       => $rolAdmin,
                'bio'          => 'Administrador de la plataforma CIVINSIS.',
                'tema_perfil'  => 'naranja',
                'color_perfil' => '#ef7e22',
                'frase'        => 'Construyendo comunidad 🚀',
                'insignia'     => '👑',
                'ubicacion'    => 'El Salvador',
            ]
        );

        // ── Usuarios de ejemplo ─────────────────────────────
        $demo = User::firstOrCreate(
            ['email' => 'fercho@civinsis.test'],
            [
                'nombre'       => 'Fercho',
                'apellido'     => 'Demo',
                'password'     => Hash::make('password'),
                'rol_id'       => $rolUsuario,
                'bio'          => 'Estudiante apasionado por la participación ciudadana.',
                'tema_perfil'  => 'azul',
                'color_perfil' => '#3b82f6',
                'frase'        => 'Tu voz transforma el mundo.',
                'insignia'     => '🌱',
                'ubicacion'    => 'Santa Tecla',
            ]
        );

        // ── Propuestas de ejemplo ───────────────────────────
        if (Proposal::count() === 0) {
            $cats = Categoria::pluck('id', 'efecto');
            $ejemplos = [
                ['Más áreas verdes en el parque central', 'Proponemos sembrar 200 árboles nativos y crear zonas de sombra.', 'medio-ambiente', 'gradient', 42, 310],
                ['Tutorías gratuitas de matemáticas',     'Crear un programa de tutorías entre estudiantes para reforzar materias.', 'educacion', 'ocean', 28, 180],
                ['Jornadas de salud mental juvenil',      'Talleres mensuales gratuitos sobre manejo del estrés y bienestar.', 'salud', 'sunset', 35, 220],
                ['Internet libre en espacios públicos',   'Instalar puntos WiFi gratuitos en plazas y bibliotecas.', 'tecnologia', 'cyber', 51, 400],
                ['Festival de música local',              'Un escenario abierto para artistas jóvenes de la comunidad.', 'cultura', 'aurora', 19, 140],
                ['Cancha multiusos para el barrio',       'Renovar la cancha y agregar iluminación para uso nocturno.', 'deporte', 'neon', 23, 160],
            ];
            foreach ($ejemplos as $e) {
                Proposal::create([
                    'titulo'       => $e[0],
                    'descripcion'  => $e[1],
                    'contenido'    => '<p>' . $e[1] . ' Esta es una propuesta de ejemplo generada automáticamente.</p>',
                    'categoria_id' => $cats[$e[2]] ?? Categoria::first()->id,
                    'usuario_id'   => $demo->id,
                    'diseno'       => $e[3],
                    'votos'        => $e[4],
                    'vistas'       => $e[5],
                    'estado'       => 'activa',
                ]);
            }
        }
    }
}
