/**
 * CIVINSIS — Tema dark/light para la página de verificación de Google.
 * Independiente de auth.js: ese script asume pestañas login/registro,
 * paneles con transición flip y partículas que esta página no tiene.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const html = document.documentElement;
  const themeBtn = document.getElementById('themeBtn');
  const themeIcon = document.getElementById('themeIcon');
  if (!themeBtn || !themeIcon) return;

  function applyTheme(mode) {
    if (mode === 'light') {
      html.classList.add('light-mode');
      themeIcon.className = 'fas fa-sun';
    } else {
      html.classList.remove('light-mode');
      themeIcon.className = 'fas fa-moon';
    }
  }

  const saved = localStorage.getItem('civitas_theme');
  const sys = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
  applyTheme(saved || sys);

  themeBtn.addEventListener('click', () => {
    const isLight = html.classList.toggle('light-mode');
    themeIcon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    localStorage.setItem('civitas_theme', isLight ? 'light' : 'dark');
  });
});
