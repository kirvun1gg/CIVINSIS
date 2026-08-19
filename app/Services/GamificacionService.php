<?php
namespace App\Services;

use App\Models\User;
use App\Models\Logro;
use App\Models\Titulo;
use App\Models\Cosmetico;
use App\Models\Insignia;
use App\Models\Mision;
use Illuminate\Support\Facades\DB;

class GamificacionService
{
    const NIVELES = [
        1=>0,2=>100,3=>250,4=>450,5=>700,6=>1000,7=>1350,8=>1750,9=>2200,10=>2700,
        11=>3300,12=>4000,13=>4800,14=>5700,15=>6700,16=>7800,17=>9000,18=>10300,
        19=>11700,20=>13200,21=>15000,22=>17000,23=>19500,24=>22500,25=>26000,
    ];

    const XP_ACCIONES = [
        'crear_propuesta'=>80,'comentar'=>15,'votar'=>5,'recibir_voto'=>10,
        'racha_diaria'=>10,'logro_desbloqueado'=>0,
        'crear_debate'=>30,'responder_debate'=>12,'recibir_voto_respuesta'=>4,
    ];

    public function otorgarXP(User $user, string $accion, ?int $refId=null, ?int $xpCustom=null): array
    {
        $xp = $xpCustom ?? (self::XP_ACCIONES[$accion] ?? 0);
        if ($xp <= 0) return ['xp'=>0,'subio_nivel'=>false,'nivel_nuevo'=>$user->nivel];

        $nivelAntes = $user->nivel;

        DB::table('xp_historial')->insert([
            'usuario_id'=>$user->id,'accion'=>$accion,'xp'=>$xp,
            'descripcion'=>$this->descAccion($accion),'referencia_id'=>$refId,
            'created_at'=>now(),'updated_at'=>now(),
        ]);

        $user->xp_total += $xp;
        $nuevoNivel      = $this->calcularNivel($user->xp_total);
        $user->nivel     = $nuevoNivel;
        $user->save();

        $subio = $nuevoNivel > $nivelAntes;
        // Se comprueba siempre: hay cosmeticos que dependen de XP,
        // reputacion o participacion, no solo del nivel.
        $this->desbloquearTitulos($user);
        $this->desbloquearCosmeticos($user);
        $this->actualizarMisiones($user, $accion);
        $this->verificarLogros($user);

        return ['xp'=>$xp,'xp_total'=>$user->xp_total,'nivel'=>$user->nivel,
                'subio_nivel'=>$subio,'nivel_nuevo'=>$nuevoNivel,'nivel_antes'=>$nivelAntes];
    }

    public function otorgarReputacion(User $user, string $razon, int $puntos, ?int $deUid=null, ?int $refId=null): void
    {
        if (!$puntos) return;
        DB::table('reputacion_historial')->insert([
            'usuario_id'=>$user->id,'de_usuario_id'=>$deUid,'puntos'=>$puntos,
            'razon'=>$razon,'referencia_id'=>$refId,'created_at'=>now(),'updated_at'=>now(),
        ]);
        $user->increment('reputacion', $puntos);
    }

    public function calcularNivel(int $xpTotal): int
    {
        $nivel = 1;
        foreach (self::NIVELES as $n=>$req) { if ($xpTotal>=$req) $nivel=$n; }
        return $nivel;
    }

    public function porcentajeNivel(User $user): int
    {
        $cur = self::NIVELES[$user->nivel] ?? 0;
        $sig = self::NIVELES[min($user->nivel+1,25)] ?? 26000;
        if ($sig<=$cur) return 100;
        return (int)(($user->xp_total-$cur)/($sig-$cur)*100);
    }

    public function xpParaSiguienteNivel(int $nivel): int { return self::NIVELES[min($nivel+1,25)] ?? 26000; }
    public function xpNivelActual(int $nivel): int { return self::NIVELES[$nivel] ?? 0; }

    public function verificarLogros(User $user): array
    {
        $nuevos = [];
        $ya = DB::table('usuario_logros')->where('usuario_id',$user->id)->pluck('logro_id')->toArray();
        foreach (Logro::where('activo',true)->get() as $logro) {
            if (in_array($logro->id,$ya)) continue;
            $cond = json_decode($logro->condicion, true);
            if ($this->cumpleCondicion($user,$cond)) {
                DB::table('usuario_logros')->insertOrIgnore([
                    'usuario_id'=>$user->id,'logro_id'=>$logro->id,'desbloqueado_at'=>now(),
                ]);
                if ($logro->xp_recompensa>0) $this->otorgarXP($user,'logro_desbloqueado',null,$logro->xp_recompensa);
                if ($logro->reputacion_recompensa>0) $this->otorgarReputacion($user,'Logro: '.$logro->nombre,$logro->reputacion_recompensa);
                $nuevos[] = $logro;
            }
        }
        return $nuevos;
    }

    private function cumpleCondicion(User $user, array $c): bool
    {
        return match($c['tipo']) {
            'propuestas_creadas'=>$user->propuestas()->count()>=$c['valor'],
            'votos_recibidos'=>$user->propuestas()->sum('votos')>=$c['valor'],
            'comentarios'=>$user->comentarios()->count()>=$c['valor'],
            'racha_dias'=>$user->racha_dias>=$c['valor'],
            'nivel'=>$user->nivel>=$c['valor'],
            default=>false,
        };
    }

    private function desbloquearTitulos(User $user): void
    {
        foreach (Titulo::where('condicion_tipo','nivel')->where('condicion_valor','<=',$user->nivel)->where('activo',true)->get() as $t) {
            DB::table('usuario_titulos')->insertOrIgnore(['usuario_id'=>$user->id,'titulo_id'=>$t->id,'equipado'=>false,'desbloqueado_at'=>now()]);
        }
        if (!$user->titulo_equipado) {
            $t = Titulo::where('condicion_tipo','nivel')->where('condicion_valor','<=',$user->nivel)->where('activo',true)->orderByDesc('condicion_valor')->first();
            if ($t) { $user->titulo_equipado=$t->clave; $user->save(); }
        }
    }

    /**
     * Desbloquea los cosméticos que el usuario ya se ha ganado.
     * No todo depende del nivel: hay condiciones por XP, reputación
     * y por participación real (propuestas, comentarios, debates, votos).
     */
    private function desbloquearCosmeticos(User $user): void
    {
        $progreso = $this->progresoCondiciones($user);

        foreach (Cosmetico::where('activo', true)->get() as $c) {
            if ($this->cumpleCondicionCosmetico($c, $user, $progreso)) {
                DB::table('usuario_cosmeticos')->insertOrIgnore([
                    'usuario_id'=>$user->id, 'cosmetico_id'=>$c->id,
                    'equipado'=>false, 'desbloqueado_at'=>now(),
                ]);
            }
        }
    }

    /** Cuenta valores distintos registrados de un tipo de exploracion. */
    private function exploracion(User $user, string $tipo): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('usuario_exploracion')) return 0;
        return DB::table('usuario_exploracion')
            ->where('usuario_id', $user->id)->where('tipo', $tipo)->count();
    }

    /** Registra un comportamiento de exploracion (idempotente). */
    public function registrarExploracion(User $user, string $tipo, string $valor): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('usuario_exploracion')) return;
        DB::table('usuario_exploracion')->insertOrIgnore([
            'usuario_id' => $user->id, 'tipo' => $tipo, 'valor' => $valor,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Texto legible del requisito de un cosmético. */
    public function textoRequisito(Cosmetico $c): string
    {
        if (($c->oculto ?? false)) return 'Requisito oculto';
        $v = (int) ($c->condicion_valor ?? 0);
        return match ($c->condicion_tipo ?? 'nivel') {
            'xp'          => "{$v} XP acumulados",
            'reputacion'  => "{$v} de reputación",
            'propuestas'  => $v === 1 ? 'Crea tu primera propuesta' : "Crea {$v} propuestas",
            'comentarios' => "Escribe {$v} comentarios",
            'debates'     => "Participa en {$v} debates",
            'votos'       => "Recibe {$v} votos en tus propuestas",
            'votos_emitidos' => "Vota en {$v} propuestas",
            'secciones'   => "Explora {$v} secciones de CIVINSIS",
            'categorias'  => "Consulta propuestas de {$v} categorías",
            'dias_activos' => "Participa en {$v} días distintos",
            'coleccion'   => "Reúne {$v} cosméticos",
            'secreto', 'secreto_vortice', 'secreto_legado' => 'Requisito oculto',
            default       => 'Nivel ' . max($v, (int) $c->nivel_requerido),
        };
    }

    /** Contadores de participación usados por las condiciones. */
    public function progresoCondiciones(User $user): array
    {
        return [
            'nivel'       => (int) $user->nivel,
            'xp'          => (int) $user->xp_total,
            'reputacion'  => (int) $user->reputacion,
            'propuestas'  => DB::table('propuestas')->where('usuario_id', $user->id)->count(),
            'comentarios' => DB::table('comentarios')->where('usuario_id', $user->id)->count(),
            'debates'     => DB::table('debate_respuestas')->where('usuario_id', $user->id)->count()
                           + DB::table('debates')->where('usuario_id', $user->id)->count(),
            'votos'       => (int) DB::table('propuestas')->where('usuario_id', $user->id)->sum('votos'),

            // ── Desbloqueos que empujan a explorar CIVINSIS ──
            'votos_emitidos' => DB::table('votos')->where('usuario_id', $user->id)->count(),
            'secciones'   => $this->exploracion($user, 'seccion'),
            'categorias'  => $this->exploracion($user, 'categoria'),
            'dias_activos' => $this->exploracion($user, 'dia_activo'),
            'coleccion'   => DB::table('usuario_cosmeticos')
                                ->where('usuario_id', $user->id)->count(),
            'secreto'     => 0,
        ];
    }

    /** ¿El usuario cumple la condición de este cosmético? */
    public function cumpleCondicionCosmetico(Cosmetico $c, User $user, array $progreso): bool
    {
        // Los administradores tienen todo desbloqueado: necesitan poder
        // revisar y probar cada cosmetico desde el panel.
        if (method_exists($user, 'esAdmin') && $user->esAdmin()) return true;

        // Requisitos base (compatibilidad con el sistema anterior)
        if (($c->nivel_requerido ?? 1) > $user->nivel) return false;
        if (($c->xp_requerido ?? 0) > $user->xp_total) return false;

        $tipo  = $c->condicion_tipo ?? 'nivel';
        $valor = (int) ($c->condicion_valor ?? 0);

        // Los secretos se ganan con una combinación poco evidente:
        // participar en las tres formas posibles.
        // ── Secretos: el usuario los descubre usando la plataforma ──
        if ($tipo === 'secreto') {
            return $progreso['propuestas'] >= 1
                && $progreso['comentarios'] >= 10
                && $progreso['debates'] >= 3;
        }
        // Vortice: participar de las TRES formas el mismo dia
        if ($tipo === 'secreto_vortice') {
            $hoy = now()->toDateString();
            $p = DB::table('propuestas')->where('usuario_id', $user->id)
                    ->whereDate('created_at', $hoy)->exists();
            $c = DB::table('comentarios')->where('usuario_id', $user->id)
                    ->whereDate('created_at', $hoy)->exists();
            $d = DB::table('debate_respuestas')->where('usuario_id', $user->id)
                    ->whereDate('created_at', $hoy)->exists();
            return $p && $c && $d;
        }
        // Legado Vivo: huella real en la comunidad
        if ($tipo === 'secreto_legado') {
            return $progreso['reputacion'] >= 200
                && $progreso['votos'] >= 40
                && $progreso['dias_activos'] >= 20;
        }

        return ($progreso[$tipo] ?? 0) >= $valor;
    }

    public function actualizarMisiones(User $user, string $accion): void
    {
        $hoy=$now=now()->toDateString();
        $sem=now()->startOfWeek()->toDateString();
        foreach (Mision::where('accion',$accion)->where('activo',true)->get() as $m) {
            $periodo=$m->tipo==='diaria'?$hoy:$sem;
            $p=DB::table('usuario_misiones')->where('usuario_id',$user->id)->where('mision_id',$m->id)->where('periodo',$periodo)->first();
            if ($p&&$p->completada) continue;
            if ($p) {
                $n=$p->progreso+1; $c=$n>=$m->cantidad;
                DB::table('usuario_misiones')->where('id',$p->id)->update(['progreso'=>$n,'completada'=>$c,'completada_at'=>$c?now():null]);
                if ($c) { $this->otorgarXP($user,'logro_desbloqueado',null,$m->xp_recompensa); if ($m->reputacion_recompensa>0) $this->otorgarReputacion($user,'Misión: '.$m->nombre,$m->reputacion_recompensa); }
            } else {
                $c=1>=$m->cantidad;
                DB::table('usuario_misiones')->insertOrIgnore(['usuario_id'=>$user->id,'mision_id'=>$m->id,'progreso'=>1,'completada'=>$c,'completada_at'=>$c?now():null,'periodo'=>$periodo]);
            }
        }
    }

    public function actualizarRacha(User $user): void
    {
        $hoy=now()->toDateString(); $ayer=now()->subDay()->toDateString();
        if ($user->ultima_racha===$hoy) return;
        $user->racha_dias = $user->ultima_racha===$ayer ? $user->racha_dias+1 : 1;
        $user->ultima_racha=$hoy; $user->save();
        $this->otorgarXP($user,'racha_diaria');
        $this->verificarLogros($user);
    }

    public function equiparItem(User $user, string $tipo, string $clave): bool
    {
        switch($tipo) {
            case 'titulo':
                $t=Titulo::where('clave',$clave)->first();
                if (!$t||!DB::table('usuario_titulos')->where('usuario_id',$user->id)->where('titulo_id',$t->id)->exists()) return false;
                DB::table('usuario_titulos')->where('usuario_id',$user->id)->update(['equipado'=>false]);
                DB::table('usuario_titulos')->where('usuario_id',$user->id)->where('titulo_id',$t->id)->update(['equipado'=>true]);
                $user->titulo_equipado=$clave; $user->save(); return true;
            case 'marco':
                $c=Cosmetico::where('clave',$clave)->where('tipo','marco_avatar')->first();
                if (!$c||!DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->where('cosmetico_id',$c->id)->exists()) return false;
                DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->whereIn('cosmetico_id',Cosmetico::where('tipo','marco_avatar')->pluck('id'))->update(['equipado'=>false]);
                DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->where('cosmetico_id',$c->id)->update(['equipado'=>true]);
                $user->marco_equipado=$clave; $user->save(); return true;
            case 'efecto':
                $c=Cosmetico::where('clave',$clave)->where('tipo','efecto_avatar')->first();
                if (!$c||!DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->where('cosmetico_id',$c->id)->exists()) return false;
                DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->whereIn('cosmetico_id',Cosmetico::where('tipo','efecto_avatar')->pluck('id'))->update(['equipado'=>false]);
                DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->where('cosmetico_id',$c->id)->update(['equipado'=>true]);
                $user->efecto_equipado=$clave; $user->save(); return true;
            case 'fondo':
                $c=Cosmetico::where('clave',$clave)->where('tipo','fondo_perfil')->first();
                if (!$c||!DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->where('cosmetico_id',$c->id)->exists()) return false;
                DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->whereIn('cosmetico_id',Cosmetico::where('tipo','fondo_perfil')->pluck('id'))->update(['equipado'=>false]);
                DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->where('cosmetico_id',$c->id)->update(['equipado'=>true]);
                $user->fondo_equipado=$clave; $user->save(); return true;
        }
        return false;
    }

    public function perfilCompleto(User $user): array
    {
        // Reevaluar desbloqueos al abrir el perfil: hay condiciones que no
        // dependen del nivel (propuestas, debates, reputación…) y ademas
        // asi aparecen los cosmeticos anadidos despues del registro.
        $this->desbloquearCosmeticos($user);
        $this->desbloquearTitulos($user);

        $titulo=Titulo::where('clave',$user->titulo_equipado)->first();
        $logros=DB::table('usuario_logros')->join('logros','logros.id','=','usuario_logros.logro_id')->where('usuario_logros.usuario_id',$user->id)->select('logros.*','usuario_logros.desbloqueado_at')->orderByDesc('usuario_logros.desbloqueado_at')->get();
        $insignias=DB::table('usuario_insignias')->join('insignias','insignias.id','=','usuario_insignias.insignia_id')->where('usuario_insignias.usuario_id',$user->id)->select('insignias.*','usuario_insignias.equipada','usuario_insignias.desbloqueado_at')->get();
        $titulos=DB::table('usuario_titulos')->join('titulos','titulos.id','=','usuario_titulos.titulo_id')->where('usuario_titulos.usuario_id',$user->id)->select('titulos.*','usuario_titulos.equipado','usuario_titulos.desbloqueado_at')->get();
        // Catálogo COMPLETO: los bloqueados también se muestran (apagados)
        $desbloq=DB::table('usuario_cosmeticos')->where('usuario_id',$user->id)->pluck('equipado','cosmetico_id');
        $prog=$this->progresoCondiciones($user);
        $cosmeticos=Cosmetico::where('activo',true)->orderBy('tipo')->orderBy('orden')->get()->map(function($c) use($desbloq,$prog,$user){
            $tiene=$desbloq->has($c->id);
            $a=$c->toArray();
            $a['desbloqueado']=$tiene;
            $a['equipado']=$tiene ? (bool)$desbloq[$c->id] : false;
            $a['requisito']=$this->textoRequisito($c);
            $a['progreso_actual']=$prog[$c->condicion_tipo ?? 'nivel'] ?? 0;
            // Un cosmético oculto no revela su diseño hasta conseguirlo
            $a['misterioso']=(bool)($c->oculto ?? false) && !$tiene;
            if ($a['misterioso']) {
                $a['nombre'] = '???';
                // la pista da una direccion sin revelar la condicion exacta
                $a['descripcion'] = $c->pista ?: 'Aún no descubierto.';
                $a['valor'] = '';
                $a['requisito'] = 'Desconocido';
            }
            return $a;
        });
        $hoy=now()->toDateString(); $sem=now()->startOfWeek()->toDateString();
        $misiones=Mision::where('activo',true)->get()->map(function($m) use($user,$hoy,$sem) {
            $p=DB::table('usuario_misiones')->where('usuario_id',$user->id)->where('mision_id',$m->id)->where('periodo',$m->tipo==='diaria'?$hoy:$sem)->first();
            return ['id'=>$m->id,'nombre'=>$m->nombre,'descripcion'=>$m->descripcion,'tipo'=>$m->tipo,'cantidad'=>$m->cantidad,'xp'=>$m->xp_recompensa,'progreso'=>$p->progreso??0,'completada'=>(bool)($p->completada??false)];
        });
        return [
            'nivel'=>$user->nivel,'xp'=>$user->xp_total,
            'xp_nivel_actual'=>$this->xpNivelActual($user->nivel),
            'xp_siguiente_nivel'=>$this->xpParaSiguienteNivel($user->nivel),
            'porcentaje_nivel'=>$this->porcentajeNivel($user),
            'reputacion'=>$user->reputacion,'racha_dias'=>$user->racha_dias,
            'titulo'=>$titulo?['nombre'=>$titulo->nombre,'color'=>$titulo->color,'rareza'=>$titulo->rareza]:null,
            'marco_equipado'=>$user->marco_equipado,'fondo_equipado'=>$user->fondo_equipado,
            'marco_clase'=>$user->marco_clase,'fondo_clase'=>$user->fondo_clase,
            'efecto_equipado'=>$user->efecto_equipado,'efecto_clase'=>$user->efecto_clase,
            // evento que dispara el efecto equipado (configurable en la BD)
            'efecto_evento'=>$user->efecto_equipado
                ? DB::table('cosmeticos')->where('clave',$user->efecto_equipado)->value('evento')
                : null,
            'logros'=>$logros,'insignias'=>$insignias,'titulos'=>$titulos,'cosmeticos'=>$cosmeticos,
            'misiones'=>$misiones,'total_logros'=>count($logros),'total_insignias'=>count($insignias),
        ];
    }

    public function desbloquearTitulosPublic(User $user): void { $this->desbloquearTitulos($user); }
    public function desbloquearCosmeticosPublic(User $user): void { $this->desbloquearCosmeticos($user); }

    private function descAccion(string $a): string {
        return match($a) {
            'crear_propuesta'=>'Creaste una propuesta','comentar'=>'Comentaste una propuesta',
            'votar'=>'Votaste en propuesta','recibir_voto'=>'Tu propuesta recibió un voto',
            'racha_diaria'=>'Bonus racha diaria','logro_desbloqueado'=>'Recompensa por logro',
            'crear_debate'=>'Iniciaste un debate','responder_debate'=>'Participaste en un debate',
            'recibir_voto_respuesta'=>'Tu respuesta recibió un voto',
            'completar_desafio'=>'Completaste un desafío',
            default=>ucfirst(str_replace('_',' ',$a)),
        };
    }
}
