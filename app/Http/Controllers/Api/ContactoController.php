<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactoMensaje;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactoController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'enviar'       => $this->enviar($request),
            'listar'       => $this->listar($request),
            'marcar_leido' => $this->marcarLeido($request),
            'responder'    => $this->responder($request),
            'eliminar'     => $this->eliminar($request),
            default        => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function enviar(Request $request)
    {
        $nombre  = trim((string) $request->input('nombre'));
        $email   = trim((string) $request->input('email'));
        $asunto  = trim((string) $request->input('asunto'));
        $mensaje = trim((string) $request->input('mensaje'));

        if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '')
            return $this->json(false, 'Todos los campos son obligatorios');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, 'Email inválido');

        ContactoMensaje::create([
            'nombre'     => $nombre,
            'email'      => $email,
            'asunto'     => $asunto,
            'mensaje'    => $mensaje,
            'usuario_id' => Auth::id(),
        ]);

        return $this->json(true, '¡Mensaje enviado! Te responderemos pronto.');
    }

    private function adminOnly(): bool
    {
        $u = Auth::user();
        return $u && in_array($u->rol_nombre, ['admin', 'moderador']);
    }

    private function listar(Request $request)
    {
        if (!$this->adminOnly()) return $this->json(false, 'Sin permisos');
        $query = ContactoMensaje::orderByDesc('fecha_creacion');
        if ($request->filled('leido')) $query->where('leido', (int) $request->input('leido'));
        $msgs = $query->get()->map(function ($m) {
            $arr = $m->toArray();
            $arr['fecha_formateada'] = optional($m->fecha_creacion ?? $m->created_at)->format('d/m/Y H:i');
            return $arr;
        });
        return $this->json(true, 'OK', ['mensajes' => $msgs, 'total' => $msgs->count()]);
    }

    private function marcarLeido(Request $request)
    {
        if (!$this->adminOnly()) return $this->json(false, 'Sin permisos');
        ContactoMensaje::where('id', (int) $request->input('id'))->update(['leido' => true]);
        return $this->json(true, 'Marcado como leído');
    }

    private function responder(Request $request)
    {
        if (!$this->adminOnly()) return $this->json(false, 'Sin permisos');
        ContactoMensaje::where('id', (int) $request->input('id'))
            ->update(['respuesta' => (string) $request->input('respuesta'), 'leido' => true]);
        return $this->json(true, 'Respuesta guardada');
    }

    private function eliminar(Request $request)
    {
        if (!$this->adminOnly()) return $this->json(false, 'Sin permisos');
        ContactoMensaje::where('id', (int) $request->input('id'))->delete();
        return $this->json(true, 'Mensaje eliminado');
    }
}
