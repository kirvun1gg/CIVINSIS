<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── LOGROS ────────────────────────────────────────────────
        DB::table('logros')->insert([
            // Propuestas
            ['clave'=>'primera_propuesta','nombre'=>'Primer Paso','descripcion'=>'Crea tu primera propuesta ciudadana','icono'=>'🌱','categoria'=>'propuestas','rareza'=>'comun','xp_recompensa'=>100,'reputacion_recompensa'=>10,'condicion'=>json_encode(['tipo'=>'propuestas_creadas','valor'=>1]),'orden'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'5_propuestas','nombre'=>'Voz Activa','descripcion'=>'Crea 5 propuestas ciudadanas','icono'=>'📢','categoria'=>'propuestas','rareza'=>'comun','xp_recompensa'=>250,'reputacion_recompensa'=>25,'condicion'=>json_encode(['tipo'=>'propuestas_creadas','valor'=>5]),'orden'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'20_propuestas','nombre'=>'Líder Comunitario','descripcion'=>'Crea 20 propuestas ciudadanas','icono'=>'🏛️','categoria'=>'propuestas','rareza'=>'raro','xp_recompensa'=>750,'reputacion_recompensa'=>75,'condicion'=>json_encode(['tipo'=>'propuestas_creadas','valor'=>20]),'orden'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'50_propuestas','nombre'=>'Arquitecto del Cambio','descripcion'=>'Crea 50 propuestas ciudadanas','icono'=>'⚡','categoria'=>'propuestas','rareza'=>'epico','xp_recompensa'=>2000,'reputacion_recompensa'=>200,'condicion'=>json_encode(['tipo'=>'propuestas_creadas','valor'=>50]),'orden'=>4,'created_at'=>now(),'updated_at'=>now()],
            // Votos recibidos
            ['clave'=>'primer_voto','nombre'=>'Aprobado','descripcion'=>'Recibe tu primer voto positivo','icono'=>'👍','categoria'=>'propuestas','rareza'=>'comun','xp_recompensa'=>50,'reputacion_recompensa'=>5,'condicion'=>json_encode(['tipo'=>'votos_recibidos','valor'=>1]),'orden'=>5,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'100_votos','nombre'=>'Trending','descripcion'=>'Acumula 100 votos en tus propuestas','icono'=>'🔥','categoria'=>'propuestas','rareza'=>'raro','xp_recompensa'=>500,'reputacion_recompensa'=>50,'condicion'=>json_encode(['tipo'=>'votos_recibidos','valor'=>100]),'orden'=>6,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'500_votos','nombre'=>'Leyenda Ciudadana','descripcion'=>'Acumula 500 votos en tus propuestas','icono'=>'🌟','categoria'=>'propuestas','rareza'=>'legendario','xp_recompensa'=>3000,'reputacion_recompensa'=>300,'condicion'=>json_encode(['tipo'=>'votos_recibidos','valor'=>500]),'orden'=>7,'created_at'=>now(),'updated_at'=>now()],
            // Comentarios
            ['clave'=>'primer_comentario','nombre'=>'Opinión Cuenta','descripcion'=>'Publica tu primer comentario','icono'=>'💬','categoria'=>'comunidad','rareza'=>'comun','xp_recompensa'=>30,'reputacion_recompensa'=>3,'condicion'=>json_encode(['tipo'=>'comentarios','valor'=>1]),'orden'=>8,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'50_comentarios','nombre'=>'Debate Activo','descripcion'=>'Publica 50 comentarios','icono'=>'🗣️','categoria'=>'comunidad','rareza'=>'raro','xp_recompensa'=>400,'reputacion_recompensa'=>40,'condicion'=>json_encode(['tipo'=>'comentarios','valor'=>50]),'orden'=>9,'created_at'=>now(),'updated_at'=>now()],
            // Rachas
            ['clave'=>'racha_3','nombre'=>'En Racha','descripcion'=>'Accede 3 días consecutivos','icono'=>'📅','categoria'=>'racha','rareza'=>'comun','xp_recompensa'=>75,'reputacion_recompensa'=>0,'condicion'=>json_encode(['tipo'=>'racha_dias','valor'=>3]),'orden'=>10,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'racha_7','nombre'=>'Semana Cívica','descripcion'=>'Accede 7 días consecutivos','icono'=>'🗓️','categoria'=>'racha','rareza'=>'raro','xp_recompensa'=>200,'reputacion_recompensa'=>20,'condicion'=>json_encode(['tipo'=>'racha_dias','valor'=>7]),'orden'=>11,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'racha_30','nombre'=>'Ciudadano del Mes','descripcion'=>'Accede 30 días consecutivos','icono'=>'🏆','categoria'=>'racha','rareza'=>'epico','xp_recompensa'=>1000,'reputacion_recompensa'=>100,'condicion'=>json_encode(['tipo'=>'racha_dias','valor'=>30]),'orden'=>12,'created_at'=>now(),'updated_at'=>now()],
            // Niveles
            ['clave'=>'nivel_5','nombre'=>'Comprometido','descripcion'=>'Alcanza el nivel 5','icono'=>'⭐','categoria'=>'nivel','rareza'=>'comun','xp_recompensa'=>150,'reputacion_recompensa'=>15,'condicion'=>json_encode(['tipo'=>'nivel','valor'=>5]),'orden'=>13,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'nivel_10','nombre'=>'Veterano','descripcion'=>'Alcanza el nivel 10','icono'=>'💫','categoria'=>'nivel','rareza'=>'raro','xp_recompensa'=>500,'reputacion_recompensa'=>50,'condicion'=>json_encode(['tipo'=>'nivel','valor'=>10]),'orden'=>14,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'nivel_25','nombre'=>'Élite Ciudadana','descripcion'=>'Alcanza el nivel 25','icono'=>'👑','categoria'=>'nivel','rareza'=>'legendario','xp_recompensa'=>2500,'reputacion_recompensa'=>250,'condicion'=>json_encode(['tipo'=>'nivel','valor'=>25]),'orden'=>15,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ── TÍTULOS ───────────────────────────────────────────────
        DB::table('titulos')->insert([
            ['clave'=>'ciudadano','nombre'=>'Ciudadano','color'=>'#8892a4','rareza'=>'comun','condicion_tipo'=>'nivel','condicion_valor'=>1,'xp_requerido'=>0,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'activista','nombre'=>'Activista','color'=>'#36c0a1','rareza'=>'comun','condicion_tipo'=>'nivel','condicion_valor'=>3,'xp_requerido'=>300,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'promotor','nombre'=>'Promotor Cívico','color'=>'#4a9eff','rareza'=>'comun','condicion_tipo'=>'nivel','condicion_valor'=>5,'xp_requerido'=>700,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'visionario','nombre'=>'Visionario','color'=>'#ef7e22','rareza'=>'raro','condicion_tipo'=>'nivel','condicion_valor'=>8,'xp_requerido'=>1500,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'lider','nombre'=>'Líder Comunitario','color'=>'#9b59b6','rareza'=>'raro','condicion_tipo'=>'nivel','condicion_valor'=>10,'xp_requerido'=>2500,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'guardian','nombre'=>'Guardián del Cambio','color'=>'#e74c3c','rareza'=>'epico','condicion_tipo'=>'nivel','condicion_valor'=>15,'xp_requerido'=>5000,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'arquitecto','nombre'=>'Arquitecto Social','color'=>'#f39c12','rareza'=>'epico','condicion_tipo'=>'nivel','condicion_valor'=>20,'xp_requerido'=>9000,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'leyenda','nombre'=>'Leyenda de CIVINSIS','color'=>'#ffe066','rareza'=>'legendario','condicion_tipo'=>'nivel','condicion_valor'=>25,'xp_requerido'=>15000,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'fundador','nombre'=>'Fundador','color'=>'#36c0a1','rareza'=>'legendario','condicion_tipo'=>'manual','condicion_valor'=>0,'xp_requerido'=>0,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ── INSIGNIAS ─────────────────────────────────────────────
        DB::table('insignias')->insert([
            ['clave'=>'pionero','nombre'=>'Pionero','descripcion'=>'Usuario fundador de CIVINSIS','icono'=>'🚀','color'=>'#36c0a1','categoria'=>'especial','rareza'=>'legendario','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'propulsor','nombre'=>'Propulsor','descripcion'=>'Creador activo de propuestas','icono'=>'💡','color'=>'#ffe066','categoria'=>'logro','rareza'=>'raro','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'debatidor','nombre'=>'Gran Debatidor','descripcion'=>'Participante activo en debates','icono'=>'⚔️','color'=>'#4a9eff','categoria'=>'logro','rareza'=>'raro','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'guardabosques','nombre'=>'Guardabosques','descripcion'=>'Reportó contenido dañino','icono'=>'🛡️','color'=>'#2ecc71','categoria'=>'especial','rareza'=>'epico','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'tendencia','nombre'=>'En Tendencia','descripcion'=>'Tuvo una propuesta trending','icono'=>'🔥','color'=>'#e74c3c','categoria'=>'logro','rareza'=>'epico','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'veterano','nombre'=>'Veterano','descripcion'=>'Más de un año en CIVINSIS','icono'=>'⏳','color'=>'#9b59b6','categoria'=>'especial','rareza'=>'raro','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'verificado','nombre'=>'Verificado','descripcion'=>'Identidad verificada','icono'=>'✅','color'=>'#36c0a1','categoria'=>'rol','rareza'=>'raro','created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'admin','nombre'=>'Administrador','descripcion'=>'Rol de administrador del sistema','icono'=>'👑','color'=>'#f39c12','categoria'=>'rol','rareza'=>'legendario','created_at'=>now(),'updated_at'=>now()],
        ]);

        // ── COSMÉTICOS ────────────────────────────────────────────
        DB::table('cosmeticos')->insert([
            // Marcos de avatar
            ['clave'=>'marco_basico','nombre'=>'Marco Básico','tipo'=>'marco_avatar','valor'=>'marco-basico','preview'=>'border: 3px solid #36c0a1;border-radius:50%','rareza'=>'comun','nivel_requerido'=>1,'xp_requerido'=>0,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'marco_dorado','nombre'=>'Marco Dorado','tipo'=>'marco_avatar','valor'=>'marco-dorado','preview'=>'border: 3px solid #ffe066;border-radius:50%;box-shadow:0 0 12px #ffe066','rareza'=>'raro','nivel_requerido'=>5,'xp_requerido'=>700,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'marco_epico','nombre'=>'Marco Épico','tipo'=>'marco_avatar','valor'=>'marco-epico','preview'=>'border: 3px solid transparent;border-radius:50%;background:linear-gradient(#0f1c19,#0f1c19) padding-box,linear-gradient(135deg,#36c0a1,#ef7e22,#9b59b6) border-box','rareza'=>'epico','nivel_requerido'=>10,'xp_requerido'=>2500,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'marco_legendario','nombre'=>'Marco Legendario','tipo'=>'marco_avatar','valor'=>'marco-legendario','preview'=>'border: 3px solid #ffe066;border-radius:50%;box-shadow:0 0 20px #ffe066,0 0 40px rgba(255,224,102,.4)','rareza'=>'legendario','nivel_requerido'=>20,'xp_requerido'=>9000,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'marco_hexagono','nombre'=>'Marco Hexágono','tipo'=>'marco_avatar','valor'=>'marco-hexagono','preview'=>'border: 3px solid #4a9eff;clip-path:polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%)','rareza'=>'raro','nivel_requerido'=>7,'xp_requerido'=>1200,'created_at'=>now(),'updated_at'=>now()],
            // Fondos de perfil
            ['clave'=>'fondo_oscuro','nombre'=>'Noche Salvadoreña','tipo'=>'fondo_perfil','valor'=>'fondo-oscuro','preview'=>'background:linear-gradient(135deg,#0f1c19,#1a3a30)','rareza'=>'comun','nivel_requerido'=>1,'xp_requerido'=>0,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'fondo_aurora','nombre'=>'Aurora Cívica','tipo'=>'fondo_perfil','valor'=>'fondo-aurora','preview'=>'background:linear-gradient(135deg,#0f1c19 0%,#1a3a30 40%,#0d2a20 100%)','rareza'=>'comun','nivel_requerido'=>3,'xp_requerido'=>300,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'fondo_fuego','nombre'=>'Fuego Ciudadano','tipo'=>'fondo_perfil','valor'=>'fondo-fuego','preview'=>'background:linear-gradient(135deg,#1c1000,#3a1a00,#1c0800)','rareza'=>'raro','nivel_requerido'=>8,'xp_requerido'=>1500,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'fondo_cosmo','nombre'=>'Cosmos CIVINSIS','tipo'=>'fondo_perfil','valor'=>'fondo-cosmo','preview'=>'background:linear-gradient(135deg,#0a0a1a,#1a0a3a,#0a1a2a)','rareza'=>'epico','nivel_requerido'=>15,'xp_requerido'=>5000,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'fondo_leyenda','nombre'=>'Dorado Legendario','tipo'=>'fondo_perfil','valor'=>'fondo-leyenda','preview'=>'background:linear-gradient(135deg,#1a1200,#3a2a00,#1a0a00)','rareza'=>'legendario','nivel_requerido'=>25,'xp_requerido'=>15000,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // ── MISIONES ──────────────────────────────────────────────
        DB::table('misiones')->insert([
            // Diarias
            ['clave'=>'diaria_comentar','nombre'=>'Voz del Día','descripcion'=>'Publica 1 comentario hoy','tipo'=>'diaria','accion'=>'comentar','cantidad'=>1,'xp_recompensa'=>25,'reputacion_recompensa'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'diaria_votar','nombre'=>'Voto Ciudadano','descripcion'=>'Vota en 3 propuestas hoy','tipo'=>'diaria','accion'=>'votar','cantidad'=>3,'xp_recompensa'=>20,'reputacion_recompensa'=>0,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'diaria_explorar','nombre'=>'Explorador Diario','descripcion'=>'Visita 5 propuestas hoy','tipo'=>'diaria','accion'=>'ver_propuesta','cantidad'=>5,'xp_recompensa'=>15,'reputacion_recompensa'=>0,'created_at'=>now(),'updated_at'=>now()],
            // Semanales
            ['clave'=>'semanal_propuesta','nombre'=>'Propuesta Semanal','descripcion'=>'Crea 1 propuesta esta semana','tipo'=>'semanal','accion'=>'crear_propuesta','cantidad'=>1,'xp_recompensa'=>150,'reputacion_recompensa'=>15,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'semanal_comentar','nombre'=>'Debate Semanal','descripcion'=>'Comenta en 10 propuestas esta semana','tipo'=>'semanal','accion'=>'comentar','cantidad'=>10,'xp_recompensa'=>100,'reputacion_recompensa'=>10,'created_at'=>now(),'updated_at'=>now()],
            ['clave'=>'semanal_votar','nombre'=>'Voto Activo','descripcion'=>'Vota en 20 propuestas esta semana','tipo'=>'semanal','accion'=>'votar','cantidad'=>20,'xp_recompensa'=>80,'reputacion_recompensa'=>5,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        DB::table('misiones')->truncate();
        DB::table('cosmeticos')->truncate();
        DB::table('insignias')->truncate();
        DB::table('titulos')->truncate();
        DB::table('logros')->truncate();
    }
};
