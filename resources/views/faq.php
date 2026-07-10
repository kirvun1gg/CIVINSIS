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
      <button class="faq-tab" data-tab="tecnico"><i class="fas fa-cog"></i> Técnico</button>
    </div>

    <div class="faq-layout">
      <!-- Lista de preguntas -->
      <div class="faq-main">

        <!-- GENERAL -->
        <div class="faq-category-group" data-cat="general">
          <div class="faq-cat-label"><i class="fas fa-star"></i> General</div>
          <div class="faq-list" id="faqList">

            <div class="faq-item reveal" data-keywords="civitas plataforma para quién juvenil">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es CIVINSIS y para quién está diseñado?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS es una plataforma de participación social juvenil donde cualquier persona puede publicar propuestas de mejora comunitaria, votar ideas y debatir con otros ciudadanos. Está diseñada especialmente para jóvenes que quieren ser agentes de cambio. Si tienes una idea, aquí es el lugar.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="gratis costo precio registro">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Es gratis usar CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>¡Sí, completamente gratis! No hay planes de pago, ni funciones premium ocultas. Solo necesitas un nombre y correo electrónico para crear tu cuenta y comenzar a participar. La participación ciudadana no debería tener costo.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="impacto real propuestas llegan autoridades">
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

            <div class="faq-item reveal" data-keywords="auris asistente chatbot robot">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Qué es AURIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>AURIS es el asistente virtual de CIVINSIS. Puede ayudarte a redactar propuestas, explicarte cómo funciona la plataforma, responder tus dudas y orientarte para que tu idea tenga el mayor impacto posible.</p>
                <p style="margin-top:.75rem">Lo encontrarás en el botón flotante <span style="background:var(--verde-alpha);color:var(--verde);padding:.15rem .5rem;border-radius:4px;font-weight:600"><i class="fas fa-robot"></i></span> en la esquina inferior izquierda.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- CUENTA -->
        <div class="faq-category-group" data-cat="cuenta" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-user"></i> Cuenta</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="crear cuenta registro pasos">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo me registro en CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Es muy sencillo: haz clic en "Registrarse" en la esquina superior derecha, completa tu nombre, apellido y correo electrónico, elige una contraseña segura y ¡listo! Todo el proceso toma menos de un minuto.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="foto perfil avatar cambiar imagen">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo subir una foto de perfil?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>¡Sí! Ve a tu perfil haciendo clic en tu nombre en la barra de navegación. Verás un ícono de cámara sobre tu avatar. Al hacer clic, podrás subir una imagen JPG, PNG o WebP. Tu foto aparecerá en tus propuestas, comentarios y en la barra de navegación.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="contraseña cambiar olvidé actualizar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo cambio mi contraseña?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Dirígete a tu perfil → pestaña "Seguridad" → sección "Cambiar contraseña". Necesitas ingresar tu contraseña actual y la nueva. Las contraseñas deben tener mínimo 8 caracteres.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="datos privacidad seguridad personal">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Mis datos personales están seguros?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Absolutamente. Las contraseñas se almacenan encriptadas con bcrypt y nunca se guardan en texto plano. Tu correo electrónico es privado y solo se usa para autenticación. No compartimos tu información con terceros.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- PROPUESTAS -->
        <div class="faq-category-group" data-cat="propuestas" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-lightbulb"></i> Propuestas</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="cuántas propuestas crear límite publicar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cuántas propuestas puedo crear?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>No hay límite. Puedes crear tantas propuestas como quieras, siempre que respeten las normas de la comunidad. Recuerda que las propuestas más detalladas y bien argumentadas reciben más apoyo de la comunidad.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="imagen foto portada subir propuesta tarjeta">
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

            <div class="faq-item reveal" data-keywords="votar voto vez única retirar">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo votar varias veces por la misma propuesta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>No. Cada usuario puede votar una sola vez por propuesta. Sin embargo, puedes retirar tu voto haciendo clic nuevamente en el botón de votar, lo que te permite cambiar de opinión.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="eliminar borrar propuesta propia">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Puedo eliminar una propuesta que publiqué?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Sí. Solo tú (como autor) o un administrador pueden eliminar tus propuestas. Ve a la propuesta y verás los botones de "Editar" y "Eliminar" si eres el autor.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="categorías tipos propuesta">
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

            <div class="faq-item reveal" data-keywords="comentar comentarios responder opinión">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo comento en una propuesta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Abre cualquier propuesta y desplázate hasta la sección de comentarios en la parte inferior. Necesitas estar registrado para comentar. Escribe tu opinión en el cuadro de texto y haz clic en "Publicar comentario".</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="reportar contenido inapropiado normas">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Cómo reporto contenido inapropiado?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Puedes contactar a los administradores a través de la sección de Contacto o escribirle directamente a AURIS explicando el problema. El equipo de moderación revisará el contenido y tomará las medidas necesarias.</p>
                <a href="contacto.php?asunto=Reporte de contenido" class="btn btn-sm btn-outline" style="margin-top:.75rem"><i class="fas fa-flag"></i> Ir a Contacto</a>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="bloqueo ban cuenta suspendida">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Por qué fue suspendida mi cuenta?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Las cuentas son suspendidas cuando se violan las normas de la comunidad, como publicar spam, contenido ofensivo o información falsa. Si crees que fue un error, contáctanos y revisaremos tu caso.</p>
              </div>
            </div>

          </div>
        </div>

        <!-- TÉCNICO -->
        <div class="faq-category-group" data-cat="tecnico" style="display:none">
          <div class="faq-cat-label"><i class="fas fa-cog"></i> Técnico</div>
          <div class="faq-list">

            <div class="faq-item reveal" data-keywords="navegador compatible funciona soporte">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿En qué navegadores funciona CIVINSIS?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>CIVINSIS funciona en todos los navegadores modernos: Chrome, Firefox, Safari, Edge y sus versiones móviles. Recomendamos mantener tu navegador actualizado para la mejor experiencia.</p>
              </div>
            </div>

            <div class="faq-item reveal" data-keywords="imagen no carga error upload">
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

            <div class="faq-item reveal" data-keywords="sesión cerrada expiró volver atrás">
              <button class="faq-question" onclick="toggleFaq(this)">
                <span>¿Por qué me pide que inicie sesión de nuevo?</span>
                <i class="fas fa-chevron-down faq-icon"></i>
              </button>
              <div class="faq-answer">
                <p>Las sesiones expiran automáticamente por seguridad. Si cerraste sesión manualmente, el sistema asegura que no puedas regresar a páginas protegidas con el botón "Atrás". Solo inicia sesión nuevamente para continuar.</p>
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
          <button onclick="Auris.togglePanel()" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:.5rem">
            <i class="fas fa-robot"></i> Preguntar a AURIS
          </button>
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
    // Restaurar
    tabBtns.forEach(b => b.classList.remove('active'));
    tabBtns[0].classList.add('active');
    groups.forEach((g,i) => g.style.display = i===0 ? 'block' : 'none');
    document.getElementById('faqNoResults').style.display = 'none';
    return;
  }
  // Buscar en todos los grupos
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
  "AURIS puede ayudarte a mejorar el texto de tu propuesta para que sea más persuasiva.",
  "Puedes personalizar tu tarjeta de foro con 8 estilos visuales distintos.",
  "Los comentarios constructivos aumentan la visibilidad de las propuestas en el ranking.",
];
let tipIdx = 0;
setInterval(() => {
  tipIdx = (tipIdx + 1) % tips.length;
  const el = document.getElementById('faqTipText');
  el.style.opacity = 0;
  setTimeout(() => { el.textContent = tips[tipIdx]; el.style.opacity = 1; el.style.transition = 'opacity .5s'; }, 300);
}, 5000);
</script>
</body>
</html>
