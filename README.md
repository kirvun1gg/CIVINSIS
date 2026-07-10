# 🏛️ CIVINSIS — Plataforma de Participación Ciudadana (Laravel)

Plataforma web donde la juventud salvadoreña publica, vota y comenta propuestas
para mejorar su comunidad. Migrada de PHP plano a **Laravel 8** con base de datos,
controladores, modelos Eloquent y un asistente de IA.

---

## 🚀 Puesta en marcha (3 pasos)

El proyecto viene configurado para **SQLite**, así que **no necesitas instalar MySQL**
ni configurar nada para probarlo.

```bash
# 1. Instalar dependencias (si no traes la carpeta vendor/)
composer install

# 2. Crear la base de datos y datos de ejemplo
php artisan migrate:fresh --seed

# 3. Levantar el servidor
php artisan serve
```

Luego abre 👉 **http://127.0.0.1:8000**

> Si clonas el proyecto limpio y falta el `.env`, copia `.env.example` a `.env`
> y ejecuta `php artisan key:generate`. El `.env` incluido ya funciona tal cual.

### 👤 Usuarios de prueba

| Rol     | Correo                  | Contraseña |
|---------|-------------------------|------------|
| Admin   | `admin@civinsis.test`   | `password` |
| Usuario | `fercho@civinsis.test`  | `password` |

---

## 🗄️ ¿Quieres usar MySQL / XAMPP?

Edita `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=civinsis_db
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base `civinsis_db` en phpMyAdmin y vuelve a correr `php artisan migrate:fresh --seed`.

---

## 🤖 Asistente de IA "CIVI" (Groq)

El botón flotante 🤖 (abajo a la derecha) abre el asistente en todas las páginas.

Funciona **sin configurar nada** gracias a respuestas de respaldo, pero para activar
la IA real (gratis):

1. Crea una API key gratuita en 👉 https://console.groq.com/keys
2. Pégala en `.env`:

```env
GROQ_API_KEY=tu_api_key_aqui
GROQ_MODEL=llama-3.3-70b-versatile
```

CIVI puede dar ideas de propuestas, mejorar tu redacción y explicar la plataforma.

---

## ✨ Funcionalidades implementadas

1. **Migración completa a Laravel** — rutas, controladores (`app/Http/Controllers/Api`),
   modelos Eloquent (`app/Models`), migraciones y seeders. Se conservan las URLs
   originales (`php/propuestas.php`, etc.) para no romper el frontend.

2. **Personalización ampliada (perfil + tarjetas):**
   - **Perfil:** temas de color, color de acento, color de banner, marco del avatar
     (círculo, cuadrado, hexágono, estrella), insignia (emoji), frase/lema, ubicación
     y redes sociales (Twitter, Instagram, GitHub).
   - **Tarjetas:** 12 diseños (incluye **aurora**, **cyber**, **pastel**), color de
     acento personalizado, marcar como **destacada** y activar/desactivar el efecto temático.

3. **Foto de perfil en todas partes** — navbar, menú móvil, tarjetas de propuesta
   (avatar del autor) y comentarios.

4. **Asistente de IA funcional** — integración con **Groq** (`llama-3.3-70b`) + respaldo
   offline. Widget de chat disponible en todo el sitio.

5. **Efectos hover por categoría** — cada categoría suelta partículas temáticas al pasar
   el cursor sobre la tarjeta:
   - 🌿 Medio Ambiente → hojas y flores
   - 📚 Educación → libros y birretes
   - ❤️ Salud → corazones (con latido)
   - ⚡ Tecnología → chispas y barrido de escáner
   - 🎭 Cultura → notas y máscaras
   - ⚽ Deporte → balones y fuego
   - 🛡️ Seguridad → escudos y candados
   - 🚧 Infraestructura → herramientas y ladrillos

---

## 📁 Estructura

```
app/Http/Controllers/
  PageController.php          → renderiza las páginas
  Api/AuthController.php       → login, registro, perfil, avatar, admin
  Api/ProposalController.php   → propuestas, votos, comentarios
  Api/CategoriaController.php  → categorías
  Api/ContactoController.php   → mensajes de contacto
  Api/IaController.php         → asistente CIVI (Groq)
app/Models/                    → User, Role, Categoria, Proposal, Voto, Comentario...
app/Providers/AppServiceProvider.php → comparte usuario/categorías con las vistas
database/migrations/           → esquema completo
database/seeders/              → roles, 8 categorías, usuarios y propuestas demo
resources/views/               → vistas (layouts/navbar.php, footer.php compartidos)
public/css/civinsis-extra.css  → efectos, diseños, personalización, IA
public/js/civinsis-extra.js    → partículas por categoría + widget IA
public/js/app.js               → lógica principal
routes/web.php                 → todas las rutas
```

---

## 🔧 Notas técnicas

- **Laravel 8.83** sobre **PHP 8.1+**.
- La tabla de usuarios se llama `usuarios` (mapeada en el modelo `User`).
- Los endpoints `php/*` están exentos de CSRF (`app/Http/Middleware/VerifyCsrfToken.php`)
  para mantener compatibilidad con el frontend original.
- El asistente de IA nunca queda inservible: si no hay API key o falla la red,
  responde con mensajes de respaldo locales.

Hecho con 💚🧡 para la juventud.
