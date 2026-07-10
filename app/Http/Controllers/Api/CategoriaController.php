<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Proposal;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        // listar es público; el resto requiere admin/moderador
        $accion = $request->input('accion', '');

        if ($accion === 'listar') return $this->listar();

        $u = Auth::user();
        if (!$u || !in_array($u->rol_nombre, ['admin', 'moderador'])) return $this->json(false, 'Sin permisos');

        return match ($accion) {
            'crear'    => $this->crear($request),
            'editar'   => $this->editar($request),
            'eliminar' => $this->eliminar($request),
            default    => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function listar()
    {
        $cats = Categoria::orderBy('nombre')->get();
        return $this->json(true, 'OK', ['categorias' => $cats]);
    }

    private function crear(Request $request)
    {
        $nombre = trim((string) $request->input('nombre'));
        if ($nombre === '') return $this->json(false, 'Nombre requerido');
        $cat = Categoria::create([
            'nombre'      => $nombre,
            'icono'       => $request->input('icono', 'fas fa-tag'),
            'color'       => $request->input('color', '#36c0a1'),
            'descripcion' => $request->input('descripcion', ''),
            'efecto'      => $request->input('efecto', 'default'),
        ]);
        return $this->json(true, 'Categoría creada', ['id' => $cat->id]);
    }

    private function editar(Request $request)
    {
        $id = (int) $request->input('id');
        $nombre = trim((string) $request->input('nombre'));
        if (!$id || $nombre === '') return $this->json(false, 'Datos inválidos');
        Categoria::where('id', $id)->update([
            'nombre'      => $nombre,
            'icono'       => $request->input('icono', 'fas fa-tag'),
            'color'       => $request->input('color', '#36c0a1'),
            'descripcion' => $request->input('descripcion', ''),
            'efecto'      => $request->input('efecto', 'default'),
        ]);
        return $this->json(true, 'Categoría actualizada');
    }

    private function eliminar(Request $request)
    {
        if (Auth::user()->rol_nombre !== 'admin')
            return $this->json(false, 'Solo administradores pueden eliminar categorías');
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, 'ID inválido');
        $count = Proposal::where('categoria_id', $id)->count();
        if ($count > 0) return $this->json(false, "No puedes eliminar: tiene $count propuestas asociadas");
        Categoria::where('id', $id)->delete();
        return $this->json(true, 'Categoría eliminada');
    }
}
