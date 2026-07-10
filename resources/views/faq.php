<?php
$activeNav = 'faq';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preguntas Frecuentes – CIVINSIS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php echo view('layouts.navbar')->render(); ?>

<!-- Hero FAQ -->
<section class="faq-hero">
  <div class="faq-hero-bg">
    <div class="faq-orb faq-orb1"></div>
    <div class="faq-orb faq-orb2"></div>
    <div class="faq-grid-decor"></div>
  </div>
  <div class="container faq-hero-content">
    <div class="faq-hero-badge reveal"><i class="fas fa-question-circle"></i> Centro de Ayuda</div>
    <h1 class="faq-hero-title reveal">Preguntas <span>Frecuentes</span></h1>
    <p class="faq-hero-desc reveal">Todo lo que necesitas saber sobre CIVINSIS, respondido de forma clara y rápida.</p>
    <div class="faq-search-wrap reveal">
      <div class="faq-search-box">
        <i class="fas fa-search faq-search-icon"></i>
        <input type="text" id="faqSearch" placeholder="Buscar pregunta..." autocomplete="off">
        <button class="faq-search-clear" id="faqSearchClear" style="display:none"><i class="fas fa-times"></i></button>
      </div>
    </div>
  </div>
  <div class="faq-hero-wave">
    <svg viewBox="0 0 1440 90" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,40 C360,90 1080,0 1440,50 L1440,90 L0,90 Z" fill="var(--bg)"/>
    </svg>
  </div>
</section>

<!-- Categorías de FAQ -->
<section class="section" style="background:var(--bg);padding-top:3rem">
  <div class="container">

    <!-- Tabs de categorías -->
    <div class="faq-tabs reveal" id="faqTabs">
      <button class="faq-tab active" data-tab="general"><i class="fas fa-star"></i> General</button>
      <button class="faq-tab" data-tab="cuenta"><i class="fas fa-user"></i> Cuenta</button>
      <button class="faq-tab" data-tab="propuestas"><i class="fas fa-lightbulb"></i> Propuestas</button>
      <button class="faq-tab" data-tab="comunidad"><i class="fas fa-users"></i> Comunidad</button>
      <button class="faq-tab" data-tab="gamificacion"><i class="fas fa-trophy"></i> Gamificación</button>
      <button class="faq-tab" data-tab="tecnico"><i class="fas fa-cog"></i> Técnico</button>
    </div>

    <div class="faq-layout">
      <!-- Lista de preguntas -->
      <div class="faq-main">

        <!-- GENERAL -->
        <div class="faq-category-group" data-cat="general">
          <div class="faq-cat-label"><i class="fas fa-star"></i> General</div>
          <div class="faq-list" id="faqList">

            <div class="faq-item reveal" data-keywords="civitas plataforma para quien juvenil que es">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es CIVINSIS y para quién está diseñado?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS es una plataforma de participación social juvenil donde cualquier persona puede publicar propuestas de mejora comunitaria, votar ideas y debatir con otros ciudadanos. Está diseñada especialmente para jóvenes que quieren ser agentes de cambio. Si tienes una idea, aquí es el lugar.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="gratis costo precio registro pago">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Es gratis usar CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>¡Sí, completamente gratis! No hay planes de pago, ni funciones premium ocultas. Solo necesitas un nombre y correo electrónico para crear tu cuenta y comenzar a participar. La participación ciudadana no debería tener costo.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="impacto real propuestas llegan autoridades cambio">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Las propuestas tienen impacto real?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>El objetivo de CIVINSIS es conectar a ciudadanos con quienes toman decisiones. Las propuestas más votadas ganan visibilidad y pueden ser presentadas ante instituciones, municipios y organizaciones. El poder de cambio real está en tu voz y en la de tu comunidad.</p>
                <div class="faq-tip">
                  <i class="fas fa-lightbulb"></i>
                  <span>Tip: Las propuestas bien documentadas con evidencia y soluciones concretas tienen más probabilidades de ser tomadas en serio.</span>
                </div>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="civi asistente ia inteligencia artificial chatbot robot">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es CIVI y cómo me ayuda?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVI es el asistente de inteligencia artificial de CIVINSIS. No solo responde preguntas sobre la plataforma, sino también sobre política, historia, ciencia, tecnología y cultura general. Su especialidad es ayudarte a redactar propuestas ciudadanas más convincentes y responder cualquier duda que tengas.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="moderacion automatica censurado contenido ia revision">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo funciona la moderación automática?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS usa inteligencia artificial para revisar automáticamente cada propuesta y comentario publicado. Si detecta contenido inapropiado como malas palabras, discurso de odio o spam, lo censura y envía una alerta al equipo de administración. El contenido no se borra — solo se censura hasta que un admin lo revise.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="salvador el salvador pais local regional">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿CIVINSIS es solo para El Salvador?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS nació en El Salvador con enfoque en la participación ciudadana juvenil salvadoreña, pero cualquier persona hispanohablante puede registrarse y participar. Las categorías y temas están orientados a la realidad local, aunque las ideas pueden inspirar a comunidades de cualquier país.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- CUENTA -->
        <div class="faq-category-group" data-cat="cuenta" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-user"></i> Cuenta</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="crear cuenta registro pasos como registrarse">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo me registro en CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Es muy sencillo: haz clic en "Registrarse" en la esquina superior derecha, completa tu nombre, apellido y correo electrónico, elige una contraseña segura y ¡listo! Todo el proceso toma menos de un minuto.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="foto perfil avatar cambiar imagen subir">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo subir una foto de perfil?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>¡Sí! Ve a tu perfil haciendo clic en tu nombre en la barra de navegación. Verás un ícono de cámara sobre tu avatar. Al hacer clic, podrás subir una imagen JPG, PNG o WebP. Tu foto aparecerá en tus propuestas, comentarios y en la barra de navegación.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="contrasena cambiar olvide actualizar seguridad">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo cambio mi contraseña?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Dirígete a tu perfil → pestaña "Contraseña" → sección "Cambiar contraseña". Necesitas ingresar tu contraseña actual y la nueva. Las contraseñas deben tener mínimo 8 caracteres.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="datos privacidad seguridad personal informacion">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Mis datos personales están seguros?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Absolutamente. Las contraseñas se almacenan encriptadas con bcrypt y nunca se guardan en texto plano. Tu correo electrónico es privado y solo se usa para autenticación. No compartimos tu información con terceros.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="eliminar cuenta borrar perfil baja">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo eliminar mi cuenta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Sí. Puedes solicitar la eliminación de tu cuenta contactando al equipo de administración desde la sección de Contacto. Ten en cuenta que esta acción es irreversible y elimina todas tus propuestas, comentarios y progreso de gamificación.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="titulo marco fondo cosmetico personalizar perfil gamificacion apariencia">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo personalizo mi perfil con cosméticos?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Ve a tu perfil → pestaña <strong>Gamificación</strong> → sección <strong>Cosméticos</strong>. Ahí puedes equipar marcos de avatar y fondos de perfil que hayas desbloqueado al subir de nivel. También puedes cambiar tu título desde la sección <strong>Títulos</strong>.</p>
                <div class="faq-tip"><i class="fas fa-palette"></i><span>Los cosméticos legendarios se desbloquean al alcanzar el nivel 20 o más.</span></div>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="dos cuentas multiples usuarios misma persona duplicada">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo tener más de una cuenta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>No está permitido crear múltiples cuentas para manipular votos o evadir suspensiones. Si detectamos cuentas duplicadas, ambas pueden ser suspendidas. Si tienes un problema con tu cuenta principal, contáctanos.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- PROPUESTAS -->
        <div class="faq-category-group" data-cat="propuestas" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-lightbulb"></i> Propuestas</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="cuantas propuestas crear limite publicar cantidad">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cuántas propuestas puedo crear?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>No hay límite. Puedes crear tantas propuestas como quieras, siempre que respeten las normas de la comunidad. Recuerda que las propuestas más detalladas y bien argumentadas reciben más apoyo de la comunidad.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="imagen foto portada subir propuesta tarjeta personalizar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo agrego imagen y personalizo mi propuesta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Al crear una propuesta encontrarás múltiples opciones de personalización:</p>
                <ul style="margin:.75rem 0;padding-left:1.5rem;line-height:2">
                  <li>📸 <strong>Imagen de portada</strong> — aparece en la tarjeta del listado</li>
                  <li>🎨 <strong>Diseño de tarjeta</strong> — Clásico, Oscuro, Gradiente, Minimalista, Neón, Glassmorphism y más</li>
                  <li>✏️ <strong>Editor enriquecido</strong> — dentro del foro puedes insertar imágenes, tablas, código y más</li>
                </ul>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="votar voto vez unica retirar quitar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo votar varias veces por la misma propuesta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>No. Cada usuario puede votar una sola vez por propuesta. Sin embargo, puedes retirar tu voto haciendo clic nuevamente en el botón de votar, lo que te permite cambiar de opinión.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="eliminar borrar propuesta propia autor">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo eliminar una propuesta que publiqué?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Sí. Solo tú (como autor) o un administrador pueden eliminar tus propuestas. Ve a la propuesta y verás los botones de "Editar" y "Eliminar" si eres el autor.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="editar modificar propuesta publicada cambiar actualizar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo editar una propuesta después de publicarla?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Sí. Ve a la propuesta y haz clic en el botón <strong>Editar</strong> (visible solo si eres el autor). Los cambios se guardan inmediatamente y el contador de votos se mantiene intacto.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="xp ganar puntos propuesta comentario voto gamificacion recompensa">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cuánto XP gano por participar?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>El XP se distribuye así:</p>
                <ul style="margin:.75rem 0;padding-left:1.5rem;line-height:2.2">
                  <li>🚀 <strong>Crear propuesta</strong> — 80 XP</li>
                  <li>💬 <strong>Comentar</strong> — 15 XP</li>
                  <li>👍 <strong>Votar</strong> — 5 XP</li>
                  <li>⭐ <strong>Recibir un voto</strong> — 10 XP</li>
                  <li>🔥 <strong>Racha diaria</strong> — 10 XP</li>
                  <li>✅ <strong>Completar misión diaria</strong> — 15 a 25 XP</li>
                  <li>🏆 <strong>Completar misión semanal</strong> — 80 a 150 XP</li>
                </ul>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="propuesta revision censurada estado moderacion bloqueada">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Por qué mi propuesta está en revisión?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>El sistema de moderación automática detectó posible contenido inapropiado. Un administrador la revisará manualmente. Si fue un error del sistema, la propuesta volverá a estar activa. Puedes contactarnos si crees que fue un error.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="categorias tipos propuesta temas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué categorías existen?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.5rem;margin-top:.5rem">
                  <?php foreach ($categorias as $cat): ?>
                  <span style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;padding:.4rem .7rem;background:var(--surface);border-radius:8px">
                    <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>"></i>
                    <?= htmlspecialchars($cat['nombre']) ?>
                  </span>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- COMUNIDAD -->
        <div class="faq-category-group" data-cat="comunidad" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-users"></i> Comunidad</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="comentar comentarios responder opinion debate">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo comento en una propuesta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Abre cualquier propuesta y desplázate hasta la sección de comentarios en la parte inferior. Necesitas estar registrado para comentar. Escribe tu opinión en el cuadro de texto y haz clic en "Publicar comentario".</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="reportar contenido inapropiado normas reglas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo reporto contenido inapropiado?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Puedes contactar a los administradores a través de la sección de Contacto. El equipo de moderación revisará el contenido y tomará las medidas necesarias. También puedes usar el sistema automático de moderación que detecta contenido inapropiado.</p>
                <a href="contacto.php?asunto=Reporte de contenido" class="btn btn-sm btn-outline" style="margin-top:.75rem"><i class="fas fa-flag"></i> Ir a Contacto</a>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="bloqueo ban cuenta suspendida sancion">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Por qué fue suspendida mi cuenta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Las cuentas son suspendidas cuando se violan las normas de la comunidad, como publicar spam, contenido ofensivo o información falsa. Si crees que fue un error, contáctanos y revisaremos tu caso.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="normas reglas comunidad comportamiento conducta">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cuáles son las normas de la comunidad?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>En CIVINSIS valoramos el debate respetuoso y constructivo. Las principales normas son:</p>
                <ul style="margin:.75rem 0;padding-left:1.5rem;line-height:2">
                  <li>No publicar contenido ofensivo, discriminatorio o violento</li>
                  <li>No hacer spam ni publicidad no autorizada</li>
                  <li>Respetar las opiniones diferentes a la tuya</li>
                  <li>Publicar propuestas con sustento real y verificable</li>
                  <li>No crear cuentas falsas ni manipular votos</li>
                </ul>
              </div>
            </div>

          </div>
        </div>

        <!-- GAMIFICACIÓN -->
        <div class="faq-category-group" data-cat="gamificacion" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-trophy"></i> Gamificación</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="gamificacion xp nivel puntos reputacion logros insignias titulos que es">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es el sistema de gamificación?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS premia tu participación ciudadana con un sistema completo de gamificación. Al crear propuestas, comentar y votar ganas <strong>XP</strong> que te permiten subir de nivel. También tienes <strong>reputación</strong> independiente, logros desbloqueables, insignias, títulos y cosméticos para personalizar tu perfil.</p>
                <div class="faq-tip"><i class="fas fa-trophy"></i><span>Crea tu primera propuesta y gana 80 XP de inmediato.</span></div>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="nivel subir xp experiencia como funciona niveles">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo subo de nivel?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Participando activamente en la plataforma. Cada acción te da XP y al acumular suficiente XP subes de nivel automáticamente. Hay 25 niveles en total, desde <strong>Ciudadano</strong> (nivel 1) hasta <strong>Leyenda de CIVINSIS</strong> (nivel 25). Puedes ver tu progreso en tu perfil → Gamificación.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="reputacion que es diferencia xp independiente">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es la reputación y en qué se diferencia del XP?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>El <strong>XP</strong> se gana con todas tus acciones y determina tu nivel. La <strong>reputación</strong> es independiente y refleja el reconocimiento de la comunidad — la ganas cuando otros votan tus propuestas. Puedes tener mucho XP pero poca reputación si participas poco en debates, y viceversa.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="racha dias consecutivos bonus login acceso streak">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es la racha de días?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>La racha cuenta los días consecutivos que inicias sesión en CIVINSIS. Cada día que entras ganas 10 XP de bonus. Si llegas a 7 días desbloqueas el logro <strong>"Semana Cívica"</strong> con 200 XP extra, y a 30 días el logro <strong>"Ciudadano del Mes"</strong> con 1000 XP. ¡No rompas la racha!</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="misiones diarias semanales completar recompensa objetivos tareas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo funcionan las misiones?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Las misiones son objetivos que se renuevan automáticamente. Hay misiones <strong>diarias</strong> (se reinician cada día) y <strong>semanales</strong> (se reinician cada lunes). Ejemplos:</p>
                <ul style="margin:.75rem 0;padding-left:1.5rem;line-height:2">
                  <li>📅 <strong>Diaria:</strong> Publica 1 comentario — 25 XP</li>
                  <li>📅 <strong>Diaria:</strong> Vota en 3 propuestas — 20 XP</li>
                  <li>🗓️ <strong>Semanal:</strong> Crea 1 propuesta — 150 XP</li>
                  <li>🗓️ <strong>Semanal:</strong> Comenta en 10 propuestas — 100 XP</li>
                </ul>
                <p>Ve a tu perfil → Gamificación → Misiones para ver tu progreso.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="logros desbloquear como obtener requisitos condiciones">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo desbloqueo logros?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Los logros se desbloquean automáticamente cuando cumples sus condiciones. Por ejemplo, crear tu primera propuesta desbloquea "Primer Paso", acumular 100 votos desbloquea "Trending", etc. Hay 15 logros con rarezas comun, raro, épico y legendario. Los logros dan XP y reputación extra.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="titulos equipar cambiar color nombre perfil">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo obtengo y equipo títulos?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Los títulos se desbloquean automáticamente al subir de nivel. Hay 9 títulos desde <strong>Ciudadano</strong> hasta <strong>Leyenda de CIVINSIS</strong>, cada uno con su propio color y rareza. Para equipar un título ve a tu perfil → Gamificación → Títulos y haz clic en el que quieras mostrar.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="ranking top usuarios posicion clasificacion tabla">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo funciona el ranking?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>El ranking muestra los usuarios más activos de CIVINSIS. Puedes verlo ordenado por <strong>XP total</strong>, <strong>reputación</strong> o <strong>nivel</strong>. Ve a tu perfil → Gamificación → Ranking para ver tu posición entre todos los ciudadanos de la plataforma.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="cosmeticos marcos fondos desbloquear nivel avatar perfil">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo desbloqueo cosméticos?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Los cosméticos se desbloquean automáticamente al subir de nivel. Hay marcos de avatar y fondos de perfil con diferentes rarezas. Por ejemplo, el marco dorado se desbloquea en el nivel 5 y el marco legendario en el nivel 20. Ve a tu perfil → Gamificación → Cosméticos para equiparlos.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- TÉCNICO -->
        <div class="faq-category-group" data-cat="tecnico" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-cog"></i> Técnico</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="navegador compatible funciona soporte chrome firefox">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿En qué navegadores funciona CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS funciona en todos los navegadores modernos: Chrome, Firefox, Safari, Edge y sus versiones móviles. Recomendamos mantener tu navegador actualizado para la mejor experiencia.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="movil celular app android ios aplicacion telefono">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Hay aplicación móvil de CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Por ahora CIVINSIS es una plataforma web optimizada para móviles. Puedes usarla desde cualquier navegador en tu celular sin instalar nada. En el futuro planeamos lanzar apps nativas para Android e iOS.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="imagen no carga error upload subir problema">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Por qué no puedo subir mi imagen?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Las imágenes deben cumplir con estos requisitos:</p>
                <ul style="margin:.75rem 0;padding-left:1.5rem;line-height:2">
                  <li>Formato: JPG, PNG, WebP o GIF</li>
                  <li>Tamaño máximo: 5MB para portada, 2MB para avatar</li>
                  <li>Recomendado: 16:9 para portadas</li>
                </ul>
                <p>Si sigues teniendo problemas, intenta comprimir la imagen en <a href="https://squoosh.app" target="_blank" style="color:var(--verde)">squoosh.app</a></p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="sesion cerrada expiro volver atras seguridad">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Por qué me pide que inicie sesión de nuevo?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Las sesiones expiran automáticamente por seguridad. Si cerraste sesión manualmente, el sistema asegura que no puedas regresar a páginas protegidas con el botón "Atrás". Solo inicia sesión nuevamente para continuar.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="lento carga lentitud rendimiento velocidad problema demora">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>La plataforma carga lento, ¿qué hago?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Prueba estos pasos:</p>
                <ul style="margin:.75rem 0;padding-left:1.5rem;line-height:2">
                  <li>Limpia la caché del navegador con Ctrl+Shift+R</li>
                  <li>Verifica tu conexión a internet</li>
                  <li>Prueba con otro navegador</li>
                  <li>Desactiva extensiones que puedan bloquear recursos</li>
                </ul>
                <p>Si el problema persiste, contáctanos indicando el navegador y sistema operativo que usas.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="error pagina no carga problema tecnico bug fallo">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué hago si veo un error en la página?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Toma una captura de pantalla y contáctanos indicando qué estabas haciendo cuando ocurrió el error. Nuestro equipo técnico lo revisará a la brevedad.</p>
                <a href="contacto.php?asunto=Problema técnico" class="btn btn-sm btn-outline" style="margin-top:.75rem"><i class="fas fa-bug"></i> Reportar error</a>
              </div>
            </div>

          </div>
        </div>

        <!-- No results -->
        <div class="faq-no-results" id="faqNoResults" style="display:none">
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <p>No encontramos preguntas para "<span id="searchTermDisplay"></span>"</p>
            <a href="contacto.php" class="btn btn-outline" style="margin-top:1rem"><i class="fas fa-envelope"></i> Pregúntanos directamente</a>
          </div>
        </div>

      </div>

      <!-- Sidebar de contacto -->
      <aside class="faq-sidebar">
        <div class="faq-sidebar-card">
          <div class="faq-sidebar-icon"><i class="fas fa-headset"></i></div>
          <h3>¿No encontraste tu respuesta?</h3>
          <p>Nuestro equipo está aquí para ayudarte. Escríbenos y te respondemos a la brevedad.</p>
          <a href="contacto.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.25rem">
            <i class="fas fa-envelope"></i> Contactar equipo
          </a>
          <a href="contacto.php" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:.5rem">
            <i class="fas fa-envelope"></i> Escribir al equipo
          </a>
        </div>

        <div class="faq-sidebar-card faq-sidebar-stats">
          <h3><i class="fas fa-chart-bar"></i> CIVINSIS en números</h3>
          <div class="faq-stat-row">
            <span class="faq-stat-num" id="faqStatProp">–</span>
            <span class="faq-stat-label">Propuestas publicadas</span>
          </div>
          <div class="faq-stat-row">
            <span class="faq-stat-num">8</span>
            <span class="faq-stat-label">Categorías activas</span>
          </div>
          <div class="faq-stat-row">
            <span class="faq-stat-num">25</span>
            <span class="faq-stat-label">Niveles de ciudadanía</span>
          </div>
          <div class="faq-stat-row">
            <span class="faq-stat-num">100%</span>
            <span class="faq-stat-label">Gratuito para siempre</span>
          </div>
        </div>

        <div class="faq-sidebar-card faq-sidebar-tip">
          <div style="font-size:1.75rem;margin-bottom:.75rem">🌿</div>
          <h3>¿Sabías que...?</h3>
          <p id="faqTipText">Cada voto en CIVINSIS representa una persona real que cree que el cambio es posible. ¡Tu voz cuenta!</p>
        </div>
      </aside>
    </div>

    <!-- CTA Final -->
    <?php if (!$usuarioLogueado): ?>
    <div class="faq-cta reveal">
      <div class="faq-cta-inner">
        <h2>¿Listo para hacer la diferencia?</h2>
        <p>Únete a la comunidad de jóvenes que ya están cambiando el mundo desde CIVINSIS.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
          <a href="auth.php?tab=registro" class="btn btn-primary btn-lg"><i class="fas fa-rocket"></i> Crear cuenta gratis</a>
          <a href="dashboard.php" class="btn btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.4)"><i class="fas fa-compass"></i> Explorar primero</a>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php echo view('layouts.footer')->render(); ?>

<div class="toast-container"></div>
<script src="js/app.js"></script>
<script>
// FAQ Tabs
const tabBtns = document.querySelectorAll('.faq-tab');
const groups  = document.querySelectorAll('.faq-category-group');
tabBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    tabBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const tab = btn.dataset.tab;
    groups.forEach(g => {
      g.style.display = g.dataset.cat === tab ? 'block' : 'none';
    });
    document.getElementById('faqSearch').value = '';
    document.getElementById('faqNoResults').style.display = 'none';
  });
});

// FAQ Search
const searchInput = document.getElementById('faqSearch');
const clearBtn    = document.getElementById('faqSearchClear');
searchInput.addEventListener('input', function() {
  const q = this.value.trim().toLowerCase();
  clearBtn.style.display = q ? 'flex' : 'none';
  if (!q) {
    tabBtns.forEach(b => b.classList.remove('active'));
    tabBtns[0].classList.add('active');
    groups.forEach((g,i) => g.style.display = i===0 ? 'block' : 'none');
    document.getElementById('faqNoResults').style.display = 'none';
    return;
  }
  let found = 0;
  groups.forEach(g => {
    g.style.display = 'block';
    const items = g.querySelectorAll('.faq-item');
    let groupHas = false;
    items.forEach(item => {
      const text = (item.textContent + (item.dataset.keywords||'')).toLowerCase();
      const match = text.includes(q);
      item.style.display = match ? 'block' : 'none';
      if (match) { groupHas = true; found++; }
    });
    g.style.display = groupHas ? 'block' : 'none';
  });
  tabBtns.forEach(b => b.classList.remove('active'));
  document.getElementById('faqNoResults').style.display = found === 0 ? 'block' : 'none';
  if (found === 0) document.getElementById('searchTermDisplay').textContent = q;
});
clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  searchInput.dispatchEvent(new Event('input'));
  clearBtn.style.display = 'none';
});

// Load stats
(async () => {
  try {
    const r = await fetch('php/propuestas.php?accion=listar&pagina=1');
    const d = await r.json();
    if (d.total !== undefined) document.getElementById('faqStatProp').textContent = d.total;
  } catch(e) {}
})();

// Rotating tips
const tips = [
  "Cada voto en CIVINSIS representa una persona real que cree que el cambio es posible. ¡Tu voz cuenta!",
  "Las propuestas con imágenes y formato reciben en promedio 3x más votos.",
  "CIVI puede ayudarte a mejorar el texto de tu propuesta para que sea más persuasiva.",
  "Puedes personalizar tu tarjeta de foro con 8 estilos visuales distintos.",
  "Los comentarios constructivos aumentan la visibilidad de las propuestas en el ranking.",
  "Crea una propuesta y gana 80 XP de inmediato. ¡Sube de nivel participando!",
  "Inicia sesión cada día para mantener tu racha y ganar XP extra.",
  "Completa misiones diarias y semanales para acelerar tu progreso en CIVINSIS.",
  "Los logros legendarios dan hasta 3000 XP de recompensa.",
  "Tu reputación es independiente del XP — se gana con el apoyo de la comunidad.",
];
let tipIdx = 0;
setInterval(() => {
  tipIdx = (tipIdx + 1) % tips.length;
  const el = document.getElementById('faqTipText');
  el.style.opacity = 0;
  setTimeout(() => { el.textContent = tips[tipIdx]; el.style.opacity = 1; el.style.transition = 'opacity .5s'; }, 300);
}, 5000);

// Toggle FAQ
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}
</script>
</body>
</html>