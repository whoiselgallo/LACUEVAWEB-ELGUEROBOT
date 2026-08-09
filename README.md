# 🐾 La Cueva del Güero & El Güero Bot
> Plataforma Digital de Podcasting, Inteligencia Artificial, Curaduría de Invitados y Generación de Contenido Multimedia.

[![Render Status](https://img.shields.io/badge/Render-Deployed-brightgreen)](https://lacuevaweb-elguerobot-oilt.onrender.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/Neon.tech-PostgreSQL-00FFFF)](https://neon.tech)
[![AI Engine](https://img.shields.io/badge/Dify.ai-Llama_3.3_70B-FF00FF)](https://dify.ai)

---

## 📖 Descripción General

**La Cueva del Güero** es una solución web integral diseñada para potenciar la producción de podcasting, la automatización de guiones y la captación de audiencia. Se compone de dos entornos principales:

1. **Sitio Web Público de Alto Impacto Visual (Mobile-First):**
   * Estética neón glassmorphism, responsive en cualquier resolución.
   * **El Güero Bot:** Asistente conversacional inteligente de la huella neón con personalidad norteña.
   * **Formulario de Storytelling:** Onboarding público para prospectos a invitados (`/storytelling-invitado.html`).
   * **Blog Oficial:** Lectura de artículos y publicaciones descargables.

2. **Portal Privado de Producción (Dashboard PRO):**
   * **Autenticación Corporativa:** Dominios restringidos `@lacuevadelguero.com` y `@tsolutionsipidd.com`.
   * **Curaduría en 3 Niveles (🟢 Alto, 🟡 Medio, 🔴 Bajo):** Evaluación automatizada de historias para decidir el formato de entrevista o canalización a Shorts/Reels.
   * **Generador de Hooks:** Creación de ganchos magnéticos para TikTok, Reels y Shorts.
   * **Editor Canva PRO:** Recorte de fondo (transparente), corrección de color y creación de posters en PNG, JPEG y WEBP.
   * **Avatar Engine:** Creación de avatares estilo Comic Neón (requiere 3 fotos + consentimiento legal en PDF), humanoide aislado con fondo transparente, selector de ropa (*deportivo 👟, casual 👕, formal 👔*) e importador oculto de avatares pre-existentes.

---

## 🛠️ Tecnologías Utilizadas

* **Frontend:** HTML5, Vanilla CSS3 (Mobile-First, `clamp()`, neumorfismo neón), JavaScript ES6+, Lienzo HTML5 Canvas, FontAwesome 6, PDF.js.
* **Backend:** PHP 8.2 (RESTful JSON APIs), PDO SSL connection.
* **Base de Datos:** Neon.tech (PostgreSQL Serverless).
* **Inteligencia Artificial:** Dify.ai REST API, GroqCloud (`llama-3.3-70b-versatile`).
* **Despliegue & DevOps:** Render Cloud Blueprint (`render.yaml`), Docker.

---

## 🔑 Variables de Entorno (Render Dashboard)

| Variable | Descripción | Valor por Defecto |
| :--- | :--- | :--- |
| `DB_HOST` | Host PostgreSQL de Neon.tech | `ep-winter-queen-af6tc66y-pooler.c-2.us-west-2.aws.neon.tech` |
| `DB_PORT` | Puerto PostgreSQL | `5432` |
| `DB_NAME` | Nombre de Base de Datos | `neondb` |
| `DB_USER` | Usuario de BD | `neondb_owner` |
| `DB_PASS` | Contraseña de BD | `npg_eOUvM7qXj0SZ` |
| `DIFY_API_KEY` | Clave API de Dify.ai | `app-GXS1gJ5xnclVP3rrCm9QpEsI` |
| `ADMIN_USER` | Usuario maestro de setup | `admin` |
| `ADMIN_PASS` | Clave maestra de setup | `eldesmadredelGuero1` |

---

## 🚀 Instalación y Despliegue Local (XAMPP / Servidor Local)

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/whoiselgallo/LACUEVAWEB-ELGUEROBOT.git
   ```
2. Mover la carpeta a `htdocs` en XAMPP o configurar tu Host Virtual.
3. Habilitar la extensión `pdo_pgsql` en tu `php.ini`.
4. Abrir en el navegador la URL para inicializar tablas:
   ```text
   http://localhost/LACUEVAWEB+ELGUEROBOT/db_init.php
   ```
5. Acceder al Dashboard PRO:
   ```text
   http://localhost/LACUEVAWEB+ELGUEROBOT/dashboard/login.php
   ```
   * Usar usuario maestro `admin` y clave `eldesmadredelGuero1` para activar el registro de tu correo corporativo (`@lacuevadelguero.com`).

---

## 📜 Licencia y Derechos

© 2026 **La Cueva del Güero**. Todos los derechos reservados.
Desarrollado con arquitectura de IA por Antigravity / Google DeepMind Agentic Coding.
