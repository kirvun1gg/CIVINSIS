<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ContactoController;
use App\Http\Controllers\Api\IaController;
use App\Http\Controllers\Api\GamificacionController;
use App\Http\Controllers\Api\DebateController;
use App\Http\Controllers\Api\DesafioController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\ActividadController;

/*
|--------------------------------------------------------------------------
| Rutas de CIVINSIS
|--------------------------------------------------------------------------
| Se conservan las URLs originales (terminadas en .php) para no romper el
| frontend existente, pero todo está respaldado por controladores Laravel.
*/

// ── Páginas ──────────────────────────────────────────────────
Route::get('/',               [PageController::class, 'index'])->name('home');
Route::get('/index.php',      [PageController::class, 'index']);
Route::get('/dashboard.php',  [PageController::class, 'dashboard']);
Route::get('/crear.php',      [PageController::class, 'crear']);
Route::get('/propuesta.php',  [PageController::class, 'propuesta']);
Route::get('/perfil.php',     [PageController::class, 'perfil']);
Route::get('/usuario.php',    [PageController::class, 'usuario']);
Route::get('/faq.php',        [PageController::class, 'faq']);
Route::get('/contacto.php',   [PageController::class, 'contacto']);
Route::get('/comunidad.php',  [PageController::class, 'comunidad']);
Route::get('/debates.php',    [PageController::class, 'debates']);
Route::get('/debate.php',     [PageController::class, 'debate']);
Route::get('/desafios.php',   [PageController::class, 'desafios']);
Route::get('/ranking.php',    [PageController::class, 'ranking']);
Route::get('/inicio.php',     [PageController::class, 'inicio']);
Route::get('/privacidad.php', [PageController::class, 'privacidad']);
Route::get('/terminos.php',   [PageController::class, 'terminos']);
Route::get('/admin.php',      [PageController::class, 'admin']);
Route::get('/auth.php',       [PageController::class, 'auth']);

// ── API (mismas rutas que el frontend ya usa) ────────────────
Route::match(['get', 'post'], '/php/auth.php',             [AuthController::class, 'handle']);
Route::match(['get', 'post'], '/auth-handler.php',         [AuthController::class, 'handle']);
Route::match(['get', 'post'], '/php/propuestas.php',       [ProposalController::class, 'handle']);
Route::match(['get', 'post'], '/php/admin_categorias.php', [CategoriaController::class, 'handle']);
Route::match(['get', 'post'], '/php/contacto.php',         [ContactoController::class, 'handle']);
Route::match(['get', 'post'], '/php/gamificacion.php', [GamificacionController::class, 'handle']);
Route::match(['get', 'post'], '/php/debates.php',      [DebateController::class, 'handle']);
Route::match(['get', 'post'], '/php/desafios.php',     [DesafioController::class, 'handle']);
Route::match(['get', 'post'], '/php/notificaciones.php', [NotificacionController::class, 'handle']);
Route::match(['get', 'post'], '/php/ranking.php',      [RankingController::class, 'handle']);
Route::match(['get', 'post'], '/php/actividad.php',    [ActividadController::class, 'handle']);
Route::match(['get', 'post'], '/php/ia.php',               [IaController::class, 'handle']);
