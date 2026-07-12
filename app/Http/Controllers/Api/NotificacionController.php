<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'listar'            => $this->listar($request),
            'marcar_leida'      => $this->marcarLeida($request),
            'marcar_todas'      => $this->marcarTodas($request),
            default             => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function listar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');

        $limit = min(50, max(1, (int) $request->input('limit', 20)));

        $notificaciones = Notificacion::where('usuario_id', Auth::id())
            ->orderByDesc('created_at')->limit($limit)->get()
            ->map(fn ($n) => [
                'id'       => $n->id,
                'tipo'     => $n->tipo,
                'icono'    => $n->icono,
                'color'    => $n->color,
                'mensaje'  => $n->mensaje,
                'enlace'   => $n->enlace,
                'leida'    => $n->leida,
                'fecha'    => optional($n->created_at)->diffForHumans(),
                'created_at' => optional($n->created_at)->toDateTimeString(),
            ]);

        $noLeidas = Notificacion::where('usuario_id', Auth::id())->where('leida', false)->count();

        return $this->json(true, 'OK', [
            'notificaciones' => $notificaciones,
            'no_leidas'      => $noLeidas,
        ]);
    }

    private function marcarLeida(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');

        $id = (int) $request->input('id');
        $n  = Notificacion::where('id', $id)->where('usuario_id', Auth::id())->first();
        if (!$n) return $this->json(false, 'Notificación no encontrada');

        $n->leida = true;
        $n->save();

        return $this->json(true, 'OK');
    }

    private function marcarTodas(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');

        Notificacion::where('usuario_id', Auth::id())->where('leida', false)->update(['leida' => true]);

        return $this->json(true, 'Todas las notificaciones marcadas como leídas');
    }
}
