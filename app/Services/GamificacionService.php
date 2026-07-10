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
        if ($subio) {
            $this->desbloquearTitulos($user);
            $this->desbloquearCosmeticos($user);
        }
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

    private function desbloquearCosmeticos(User $user): void
    {
        foreach (Cosmetico::where('nivel_requerido','<=',$user->nivel)->where('xp_requerido','<=',$user->xp_total)->where('activo',true)->get() as $c) {
            DB::table('usuario_cosmeticos')->insertOrIgnore(['usuario_id'=>$user->id,'cosmetico_id'=>$c->id,'equipado'=>false,'desbloqueado_at'=>now()]);
        }
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
        $titulo=Titulo::where('clave',$user->titulo_equipado)->first();
        $logros=DB::table('usuario_logros')->join('logros','logros.id','=','usuario_logros.logro_id')->where('usuario_logros.usuario_id',$user->id)->select('logros.*','usuario_logros.desbloqueado_at')->orderByDesc('usuario_logros.desbloqueado_at')->get();
        $insignias=DB::table('usuario_insignias')->join('insignias','insignias.id','=','usuario_insignias.insignia_id')->where('usuario_insignias.usuario_id',$user->id)->select('insignias.*','usuario_insignias.equipada','usuario_insignias.desbloqueado_at')->get();
        $titulos=DB::table('usuario_titulos')->join('titulos','titulos.id','=','usuario_titulos.titulo_id')->where('usuario_titulos.usuario_id',$user->id)->select('titulos.*','usuario_titulos.equipado','usuario_titulos.desbloqueado_at')->get();
        $cosmeticos=DB::table('usuario_cosmeticos')->join('cosmeticos','cosmeticos.id','=','usuario_cosmeticos.cosmetico_id')->where('usuario_cosmeticos.usuario_id',$user->id)->select('cosmeticos.*','usuario_cosmeticos.equipado','usuario_cosmeticos.desbloqueado_at')->get();
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
            'logros'=>$logros,'insignias'=>$insignias,'titulos'=>$titulos,'cosmeticos'=>$cosmeticos,
            'misiones'=>$misiones,'total_logros'=>count($logros),'total_insignias'=>count($insignias),
        ];
    }

    public function desbloquearTitulosPublic(User $user): void { $this->desbloquearTitulos($user); }
    public function desbloquearCosmeticosPublic(User $user): void { $this->desbloquearCosmeticos($user); }

    private function descAccion(string $a): string {
        return match($a) {
            'crear_propuesta'=>'Creaste una propuesta','comentar'=>'Participaste en debate',
            'votar'=>'Votaste en propuesta','recibir_voto'=>'Tu propuesta recibió un voto',
            'racha_diaria'=>'Bonus racha diaria','logro_desbloqueado'=>'Recompensa por logro',
            default=>ucfirst(str_replace('_',' ',$a)),
        };
    }
}
