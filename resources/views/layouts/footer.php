<?php
// includes/footer.php - Footer universal
// $categorias lo inyecta el View Composer global en TODAS las vistas
// (app/Providers/AppServiceProvider.php::boot()). El valor por defecto de
// abajo nunca se usa en producción - solo evita que el IDE marque la
// variable como indefinida y sirve de red de seguridad.
$categorias = $categorias ?? collect();
$categorias_footer = is_array($categorias) ? $categorias : $categorias->all();
?>
<footer class="footer">
  <div class="footer-main">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-brand-name">CIVINSIS</div>
          <p class="footer-brand-desc"><?= __('civinsis.footer.descripcion') ?></p>
          <div class="social-links">
            <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-link" title="TikTok"><i class="fab fa-tiktok"></i></a>
          </div>
        </div>
        <div>
          <h4 class="footer-col-title"><?= __('civinsis.footer.plataforma') ?></h4>
          <div class="footer-links">
            <a href="dashboard.php" class="footer-link"><?= __('civinsis.footer.explorar_propuestas') ?></a>
            <a href="crear.php" class="footer-link"><?= __('civinsis.footer.crear_propuesta') ?></a>
            <a href="debates.php" class="footer-link"><?= __('civinsis.footer.debates') ?></a>
            <a href="desafios.php" class="footer-link"><?= __('civinsis.footer.desafios') ?></a>
            <a href="ranking.php" class="footer-link"><?= __('civinsis.footer.ranking') ?></a>
            <a href="tendencias.php" class="footer-link"><?= __('civinsis.footer.tendencias') ?></a>
            <a href="index.php#top-votadas" class="footer-link"><?= __('civinsis.footer.mas_votadas') ?></a>
            <a href="index.php#como-funciona" class="footer-link"><?= __('civinsis.footer.como_funciona') ?></a>
            <a href="faq.php" class="footer-link"><?= __('civinsis.footer.faq') ?></a>
            <a href="contacto.php" class="footer-link"><?= __('civinsis.footer.contacto') ?></a>
          </div>
        </div>
        <div>
          <h4 class="footer-col-title"><?= __('civinsis.footer.categorias') ?></h4>
          <div class="footer-links">
            <?php foreach (array_slice($categorias_footer, 0, 6) as $cat): ?>
              <a href="dashboard.php?cat=<?= $cat['id'] ?>" class="footer-link">
                <i class="<?= $cat['icono'] ?>" style="color:<?= $cat['color'] ?>;margin-right:.4rem"></i>
                <?= htmlspecialchars($cat['nombre']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <h4 class="footer-col-title"><?= __('civinsis.footer.legal_info') ?></h4>
          <div class="footer-links">
            <a href="terminos.php" class="footer-link"><?= __('civinsis.footer.terminos_uso') ?></a>
            <a href="privacidad.php" class="footer-link"><?= __('civinsis.footer.politica_privacidad') ?></a>
            <a href="comunidad.php" class="footer-link"><?= __('civinsis.footer.guia_comunidad') ?></a>
            <a href="faq.php" class="footer-link"><?= __('civinsis.footer.preguntas_frecuentes') ?></a>
            <a href="contacto.php" class="footer-link"><?= __('civinsis.footer.contacto') ?></a>
            <a href="contacto.php?asunto=Reporte" class="footer-link"><?= __('civinsis.footer.reportar_problema') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> CIVINSIS. <?= __('civinsis.footer.derechos_reservados', ['corazon' => '<span style="color:var(--naranja-400)">♥</span>']) ?></span>
      <div class="footer-bottom-links">
        <a href="terminos.php"><?= __('civinsis.footer.terminos') ?></a>
        <a href="privacidad.php"><?= __('civinsis.footer.privacidad') ?></a>
        <a href="comunidad.php"><?= __('civinsis.footer.comunidad') ?></a>
      </div>
    </div>
  </div>
</footer>

<?php
// Textos fijos de la interfaz que public/js/app.js y public/js/debates.js
// necesitan en tiempo de ejecución (secciones de progreso, votación, aviso
// de traducción). Vienen de resources/lang/<idioma>/civinsis.php::js/aspectos
// — nunca se traducen vía DeepL porque son iguales en TODAS las propuestas/debates.
$civiI18n = __('civinsis.js');
$civiI18n['aspectos'] = __('civinsis.aspectos');
?>
<script>
window.CIVI_I18N = <?= str_replace('</', '<\/', json_encode($civiI18n, JSON_UNESCAPED_UNICODE)) ?>;
</script>

<!-- CIVINSIS · Extras (efectos por categoría, personalización y asistente IA) -->
<link rel="stylesheet" href="css/civinsis-extra.css">
<link rel="stylesheet" href="css/cosmeticos.css">
  <link rel="stylesheet" href="css/marcos-gsap.css">
  <link rel="stylesheet" href="css/fondos.css">
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
  <script src="js/fondos.js" defer></script>
  <link rel="stylesheet" href="css/efectos.css">
  <script src="js/efectos-gsap.js" defer></script>
  <script src="js/efectos-eventos.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/MotionPathPlugin.min.js" defer></script>
  <script src="js/marcos-gsap.js" defer></script>
  <script src="js/marcos-descubrimiento.js" defer></script>
<link rel="stylesheet" href="css/civinsis-polish.css">
<script src="js/civinsis-extra.js" defer></script>
<script src="js/civinsis-polish.js" defer></script>
