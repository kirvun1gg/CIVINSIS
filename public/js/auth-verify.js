/**
 * CIVINSIS — Tema dark/light + mostrar/ocultar contraseña para las páginas
 * de auth de un solo panel (verificación de Google, forgot/reset password).
 * Independiente de auth.js: ese script asume pestañas login/registro,
 * paneles con transición flip y partículas que estas páginas no tienen.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const html = document.documentElement;
  const themeBtn = document.getElementById('themeBtn');
  const themeIcon = document.getElementById('themeIcon');

  function applyTheme(mode) {
    if (mode === 'light') {
      html.classList.add('light-mode');
      if (themeIcon) themeIcon.className = 'fas fa-sun';
    } else {
      html.classList.remove('light-mode');
      if (themeIcon) themeIcon.className = 'fas fa-moon';
    }
  }

  if (themeBtn && themeIcon) {
    const saved = localStorage.getItem('civitas_theme');
    const sys = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    applyTheme(saved || sys);

    themeBtn.addEventListener('click', () => {
      const isLight = html.classList.toggle('light-mode');
      themeIcon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
      localStorage.setItem('civitas_theme', isLight ? 'light' : 'dark');
    });
  }

  // Mostrar / ocultar contraseña (páginas como reset-password, con 2 campos)
  document.querySelectorAll('.eye-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.getAttribute('data-for'));
      const icon  = btn.querySelector('i');
      if (!input) return;
      if (input.type === 'password') {
        input.type = 'text';
        if (icon) icon.className = 'fas fa-eye-slash';
      } else {
        input.type = 'password';
        if (icon) icon.className = 'fas fa-eye';
      }
    });
  });
});
