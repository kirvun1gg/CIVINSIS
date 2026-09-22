<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Proposal;
use App\Services\TranslationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CategoriaController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        // listar es público; el resto requiere admin/moderador
        $accion = $request->input('accion', '');

        if ($accion === 'listar') return $this->listar();

        $u = auth_user();
        if (!$u || !in_array($u->rol_nombre, ['admin', 'moderador'])) return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        return match ($accion) {
            'crear'    => $this->crear($request),
            'editar'   => $this->editar($request),
            'eliminar' => $this->eliminar($request),
            default    => $this->json(false, __('civinsis.toast.comunes.accion_no_reconocida')),
        };
    }

    private function listar()
    {
        $cats = Categoria::orderBy('nombre')->get();

        // 'nombre'/'descripcion' se dejan tal cual (son el valor real que se
        // edita en el panel); se agregan versiones traducidas aparte para
        // que las pantallas de solo-lectura (tabla del panel) puedan
        // mostrarlas sin arriesgar sobrescribir el original en español al
        // guardar una edición. warmMany() precarga todo en un solo lote en
        // vez de una llamada a DeepL por categoría.
        $locale = App::getLocale();
        if ($locale !== 'es') {
            app(TranslationService::class)->warmMany($cats, ['nombre', 'descripcion'], $locale);
        }
        $cats->each(function ($c) {
            $c->nombre_traducido = $c->translated('nombre');
            $c->descripcion_traducido = $c->translated('descripcion');
        });

        return $this->json(true, 'OK', ['categorias' => $cats]);
    }

    private function crear(Request $request)
    {
        $nombre = trim((string) $request->input('nombre'));
        if ($nombre === '') return $this->json(false, __('civinsis.toast.categoria.nombre_requerido'));
        $cat = Categoria::create([
            'nombre'      => $nombre,
            'icono'       => $request->input('icono', 'fas fa-tag'),
            'color'       => $request->input('color', '#36c0a1'),
            'descripcion' => $request->input('descripcion', ''),
            'efecto'      => $request->input('efecto', 'default'),
        ]);
        return $this->json(true, __('civinsis.toast.admin.categoria_creada'), ['id' => $cat->id]);
    }

    private function editar(Request $request)
    {
        $id = (int) $request->input('id');
        $nombre = trim((string) $request->input('nombre'));
        if (!$id || $nombre === '') return $this->json(false, __('civinsis.toast.comunes.datos_invalidos'));
        Categoria::where('id', $id)->update([
            'nombre'      => $nombre,
            'icono'       => $request->input('icono', 'fas fa-tag'),
            'color'       => $request->input('color', '#36c0a1'),
            'descripcion' => $request->input('descripcion', ''),
            'efecto'      => $request->input('efecto', 'default'),
        ]);
        return $this->json(true, __('civinsis.toast.admin.categoria_actualizada'));
    }

    private function eliminar(Request $request)
    {
        if (auth_user()->rol_nombre !== 'admin')
            return $this->json(false, __('civinsis.toast.categoria.solo_admin_eliminar'));
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, __('civinsis.toast.comunes.id_invalido'));
        $count = Proposal::where('categoria_id', $id)->count();
        if ($count > 0) return $this->json(false, __('civinsis.toast.categoria.tiene_propuestas_asociadas', ['count' => $count]));
        Categoria::where('id', $id)->delete();
        return $this->json(true, __('civinsis.toast.admin.categoria_eliminada'));
    }
}
