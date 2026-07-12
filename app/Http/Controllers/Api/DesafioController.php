<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desafio;
use App\Models\UsuarioDesafio;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesafioController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'listar'   => $this->listar($request),
            'detalle'  => $this->detalle($request),
            'aceptar'  => $this->aceptar($request),
            'sugerido' => $this->sugerido($request),
            default    => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function detalle(Request $request)
    {
        $id = (int) $request->input('id');
        $d  = Desafio::with(['categoria', 'insignia'])->find($id);
        if (!$d) return $this->json(false, 'Desafío no encontrado');

        $progreso = Auth::check()
            ? UsuarioDesafio::where('usuario_id', Auth::id())->where('desafio_id', $id)->first()
            : null;

        return $this->json(true, 'OK', ['desafio' => $this->formato($d, $progreso)]);
    }

    private function listar(Request $request)
    {
        $categoria = (int) $request->input('categoria_id', 0);
        $dificultad = trim((string) $request->input('dificultad', ''));

        $q = Desafio::with(['categoria', 'insignia'])->where('activo', true);
        if ($categoria) $q->where('categoria_id', $categoria);
        if (in_array($dificultad, ['facil', 'medio', 'dificil'])) $q->where('dificultad', $dificultad);

        $desafios = $q->orderBy('orden')->get();

        $progresos = [];
        if (Auth::check()) {
            $progresos = UsuarioDesafio::where('usuario_id', Auth::id())
                ->whereIn('desafio_id', $desafios->pluck('id'))
                ->get()->keyBy('desafio_id');
        }

        return $this->json(true, 'OK', [
            'desafios' => $desafios->map(fn ($d) => $this->formato($d, $progresos[$d->id] ?? null)),
        ]);
    }

    /** Devuelve un desafío aleatorio no completado, para la sección "¿Sin ideas?" */
    private function sugerido(Request $request)
    {
        $completados = [];
        if (Auth::check()) {
            $completados = UsuarioDesafio::where('usuario_id', Auth::id())
                ->where('completado', true)->pluck('desafio_id')->toArray();
        }

        $d = Desafio::with(['categoria', 'insignia'])->where('activo', true)
            ->whereNotIn('id', $completados)->inRandomOrder()->first();

        if (!$d) $d = Desafio::with(['categoria', 'insignia'])->where('activo', true)->inRandomOrder()->first();
        if (!$d) return $this->json(false, 'No hay desafíos disponibles');

        return $this->json(true, 'OK', ['desafio' => $this->formato($d)]);
    }

    private function aceptar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para aceptar un desafío');

        $id = (int) $request->input('desafio_id');
        $d  = Desafio::find($id);
        if (!$d || !$d->activo) return $this->json(false, 'Desafío no encontrado');

        $progreso = UsuarioDesafio::firstOrCreate(
            ['usuario_id' => Auth::id(), 'desafio_id' => $id],
            ['completado' => false]
        );

        if ($progreso->completado) {
            return $this->json(true, 'Ya completaste este desafío, pero puedes crear otra propuesta inspirada en él', ['desafio_id' => $id]);
        }

        return $this->json(true, 'Desafío aceptado. ¡Vamos a crear tu propuesta!', ['desafio_id' => $id]);
    }

    private function formato(Desafio $d, ?UsuarioDesafio $progreso = null): array
    {
        $estado = 'no_iniciado';
        if ($progreso) $estado = $progreso->completado ? 'completado' : 'en_progreso';

        return [
            'id'                     => $d->id,
            'titulo'                 => $d->titulo,
            'descripcion'            => $d->descripcion,
            'dificultad'             => $d->dificultad,
            'icono'                  => $d->icono,
            'categoria_id'           => $d->categoria_id,
            'categoria'              => $d->categoria->nombre ?? null,
            'categoria_icono'        => $d->categoria->icono ?? 'fas fa-tag',
            'categoria_color'        => $d->categoria->color ?? '#36c0a1',
            'xp_recompensa'          => $d->xp_recompensa,
            'reputacion_recompensa'  => $d->reputacion_recompensa,
            'insignia'               => $d->insignia ? [
                'nombre' => $d->insignia->nombre, 'icono' => $d->insignia->icono, 'color' => $d->insignia->color,
            ] : null,
            'estado'                 => $estado,
        ];
    }
}
