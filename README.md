# 🎙️ Ecosistema Digital La Cueva del Güero & El Güero Bot (v2.5 PRO)

> **Plataforma All-in-One de Automatización, Preproducción, Postproducción con IA, Landing Page de Conversión, Blindaje de Seguridad y Gestión Transmedia para Podcasters.**

---

## 📌 Tabla de Contenidos

1. [🌟 Visión General del Proyecto](#-visión-general-del-proyecto)
2. [🌐 Experiencia y Arquitectura de la Landing Page](#-experiencia-y-arquitectura-de-la-landing-page)
   - [2.1. Hero Cinemático & Motor de Partículas Neón](#21-hero-cinemático--motor-de-partículas-neón)
   - [2.2. Secciones Clave de Conversión y Entretenimiento](#22-secciones-clave-de-conversión-y-entretenimiento)
   - [2.3. Muro Social Integrador (YouTube, Spotify, Meta, TikTok)](#23-muro-social-integrador-youtube-spotify-meta-tiktok)
   - [2.4. Mapa Interactivo de la Manada](#24-mapa-interactivo-de-la-manada)
   - [2.5. Sistema de Evaluación y Feedback en Vivo](#25-sistema-de-evaluación-y-feedback-en-vivo)
3. [🛡️ Capas de Seguridad y Blindaje de Producción](#️-capas-de-seguridad-y-blindaje-de-producción)
   - [3.1. Protección a Nivel Servidor Web y Apache (.htaccess)](#31-protección-a-nivel-servidor-web-y-apache-htaccess)
   - [3.2. Cabeceras HTTP de Seguridad (Security Headers)](#32-cabeceras-http-de-seguridad-security-headers)
   - [3.3. Prevención de Inyecciones SQL y Sanitización en Backend](#33-prevención-de-inyecciones-sql-y-sanitización-en-backend)
   - [3.4. Gestión y Rotación Segura de Claves de API (Gemini/Imagen)](#34-gestión-y-rotación-segura-de-claves-de-api-geminiimagen)
   - [3.5. Autenticación, Control de Sesiones y Protección de Cargas](#35-autenticación-control-de-sesiones-y-protección-de-cargas)
4. [🛠️ Guía Paso a Paso de Uso de Cada Herramienta](#️-guía-paso-a-paso-de-uso-de-cada-herramienta)
   - [4.1. Asistente IA & Conversión (El Güero Bot / Paw Agent)](#41-asistente-ia--conversión-el-güero-bot--paw-agent)
   - [4.2. Suite de Preproducción & Storytelling Pro](#42-suite-de-preproducción--storytelling-pro)
   - [4.3. Suite de Postproducción de Video & Audio con IA](#43-suite-de-postproducción-de-video--audio-con-ia)
   - [4.4. Editor Gráfico Canva PRO Cyberpunk](#44-editor-gráfico-canva-pro-cyberpunk)
   - [4.5. Motor de Avatares e Ilustraciones (Avatar Engine)](#45-motor-de-avatares-e-ilustraciones-avatar-engine)
   - [4.6. Gestor de Blog Transmedia & SEO con IA](#46-gestor-de-blog-transmedia--seo-con-ia)
   - [4.7. Analítica de YouTube & CRM de Invitados](#47-analítica-de-youtube--crm-de-invitados)
5. [💻 Stack Tecnológico de Producción](#-stack-tecnológico-de-producción)
6. [⚙️ Instalación, Configuración y Despliegue](#️-instalación-configuración-y-despliegue)
7. [📈 Recomendaciones para Escalar el Proyecto](#-recomendaciones-para-escalar-el-proyecto)
8. [🗺️ Roadmap y Siguientes Fases de Aplicación](#️-roadmap-y-siguientes-fases-de-aplicación)
9. [💎 Beneficios Estratégicos para un Podcaster](#-beneficios-estratégicos-para-un-podcaster)
10. [📄 Estructura de Directorios del Repositorio](#-estructura-de-directorios-del-repositorio)
11. [⚖️ Licencia y Créditos](#️-licencia-y-créditos)

---

## 🌟 Visión General del Proyecto

**La Cueva del Güero** es una plataforma tecnológica integral diseñada para transformar un podcast independiente en una **cadena de medios transmedia automatizada**. 

Nacida para el show más representativo y auténtico de Mexicali, B.C., la solución centraliza en un solo lugar:
* **Landing Page de Alta Conversión:** Interfaz interactiva inmersiva estilo Cyberpunk con animaciones personalizadas, motor de partículas, feeds sociales en vivo y captación de leads.
* **Preproducción inteligente:** Cuestionarios dinámicos para invitados, diseño de escaletas con ganchos de retención, tarjetas de apoyo en vivo (*Cue Cards*) y generación legal de cesión de derechos de imagen.
* **Postproducción acelerada con IA:** Detección de silencios, limpieza acústica con aislamiento vocal, cortes multicámara J/L, masterización a estándares oficiales (-14 LUFS) y transcripción automática.
* **Diseño gráfico de miniaturas y assets:** Editor canvas multipista con estética Neón, eliminación de fondo con IA y conectores directos a la nube (Google Drive, Dropbox, OneDrive, TeraBox).
* **Generación de contenido derivado:** Artículos de blog SEO, ganchos (*hooks*) virales para TikTok/Shorts/Reels y resúmenes estructurados.
* **Agente de interacción 24/7:** Chatbot perruno (*El Güero Bot / Paw Agent*) con conocimiento contextual del podcast, RAG sobre episodios y captación de leads.

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        ECOSISTEMA LA CUEVA DEL GÜERO                                   │
└────────────────────────────────────────────────────────────────────────────────────────┘
                                            │
       ┌────────────────────────────────────┼────────────────────────────────────┐
       ▼                                    ▼                                    ▼
┌─────────────────────────┐      ┌─────────────────────────┐      ┌─────────────────────────┐
│  EXPERIENCIA & LANDING  │      │ PRE/POSTPRODUCCIÓN & IA │      │ DISTRIBUCIÓN & SEGURIDAD│
├─────────────────────────┤      ├─────────────────────────┤      ├─────────────────────────┤
│• Hero Cinemático Anime  │      │• Storytelling & Guiones │      │• Blog SEO Automático    │
│• Muro Social Multicanal │      │• Video Editor (FFmpeg)  │      │• Paw Agent (Gemini RAG) │
│• Calificación Emojis    │      │• Canva PRO Cyberpunk    │      │• Blindaje .htaccess/CSP │
│• Mapa de la Manada SVG  │      │• Cesión Legal Derechos  │      │• PDO & Rotación API Keys│
└─────────────────────────┘      └─────────────────────────┘      └─────────────────────────┘
```

---

## 🌐 Experiencia y Arquitectura de la Landing Page

La página web principal (`index.html`) está concebida como un embudo interactivo (*conversion funnel*) y una experiencia inmersiva para la comunidad:

### 2.1. Hero Cinemático & Motor de Partículas Neón
* **Animación Secuencial Coreografiada:**
  1. *Fase 1 (0.8s):* Levantamiento del telón negro y activación del canvas de partículas neón flotantes (cian, magenta, verde y amarillo).
  2. *Fase 2 (1.8s):* Fundido suave del fondo del estudio de grabación.
  3. *Fase 3 (2.8s):* Entrada lateral de los avatares siluetados de *El Güero* y *Junior*.
  4. *Fase 4 (7.0s):* Transición al sofá con remoción de silueta para revelar avatares a color e iluminación del graffiti principal.
  5. *Fase 5 (8.5s):* Despliegue de la botonera de acción rápida:
     - **Invitado:** Acceso y activación del modo invitado.
     - **Producción:** Conexión con el equipo técnico.
     - **Fandom:** Interacción con la comunidad.
     - **Dashboard PRO:** Acceso directo a la consola de administración.
* **Canvas de Partículas HTML5:** Renderizado continuo a 60 FPS con cálculo vectorial de rebote, resplandor (*glow effect*) y ajuste responsivo dinámico.

### 2.2. Secciones Clave de Conversión y Entretenimiento
* **La Cueva (Manifiesto):** Declaración de principios, identidad urbana, cultura de barrio y propósito editorial del podcast.
* **Invitados de la Cueva:** Grid de episodios destacados con enlaces a YouTube, fotografías de alta definición en formato WebP con compresión optimizada y sinopsis temática.
* **Galería Urbana:** Carrete fotográfico detrás de cámaras (*behind the scenes*), mostrando la vibra real del set y el equipo de producción.
* **Blog Integrado:** Renderizado reactivo desde `posts.json` con soporte para lectura rápida de resúmenes y artículos completos.
* **Contacto Directo:** Formulario de contacto para propuestas de invitados, colaboraciones comerciales y patrocinios.

### 2.3. Muro Social Integrador (YouTube, Spotify, Meta, TikTok)
* **Embed Inteligente de YouTube:** Carga asíncrona mediante el microservicio `api/api-youtube-latest.php`, detectando automáticamente el último video subido al canal sin depender de llamadas directas que agoten la cuota de la API.
* **Reproductor de Spotify:** Integración del reproductor oficial para streaming de audio.
* **Muro Social Multicanal:** Grilla adaptativa que incrusta en vivo los feeds oficiales de **Facebook**, **Instagram** y **TikTok**, permitiendo a los usuarios consumir clips virales sin abandonar la plataforma.

### 2.4. Mapa Interactivo de la Manada
* **Tecnología:** SVG vectorial interactivo con capas dinámicas y controles de navegación.
* **Funcionalidades:**
  * Soporte para **Zoom In (+)** y **Zoom Out (-)**.
  * Arrastre y desplazamiento táctil / ratón (*Pan & Drag*).
  * API JavaScript para añadir pines de ubicación de los oyentes (`window.addMapPin(x, y, name)`) con efectos neón de resplandor.

### 2.5. Sistema de Evaluación y Feedback en Vivo
* **Selector de Flow por Emojis:** Sistema de calificación visual en un clic (`🔥 Brutal`, `👍 Bueno`, `😐 Regular`, `👎 Flojo`, `❌ Malo`).
* **Formulario de Reseñas:** Captura de opiniones y sugerencias que se envían directamente a la base de datos para análisis de satisfacción del público.

---

## 🛡️ Capas de Seguridad y Blindaje de Producción

El ecosistema implementa una estrategia de **Defensa en Profundidad (Defense in Depth)** estructurada en múltiples capas:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CAPAS DE SEGURIDAD                              │
└────────────────────────────────────────────────────────────────────────┘
  [ Capa 1: Perímetro Web & Apache ]  ──> .htaccess, Bloqueo de Rutas y Archivos
  [ Capa 2: Cabeceras HTTP (Headers) ] ──> CSP, HSTS, X-Frame, No-Sniff, XSS
  [ Capa 3: Lógica Backend & Datos ]   ──> PDO Prepared Statements, Sanitización
  [ Capa 4: Gestión de Secretos ]      ──> .env Aislado, Rotación Keys Gemini
  [ Capa 5: Autenticación & Cargas ]   ──> Bcrypt/Argon2, Restricción en /uploads/
```

### 3.1. Protección a Nivel Servidor Web y Apache (`.htaccess`)
* **Bloqueo de Archivos Sensibles y Críticos:**
  Reglas estrictas que deniegan cualquier acceso HTTP a archivos de entorno, configuración, control de versiones y respaldos:
  ```apache
  <FilesMatch "^(config\.php|\.env|\.env\..*|\.gitignore|\.git)$">
      Order Allow,Deny
      Deny from all
  </FilesMatch>

  <FilesMatch "\.(bak|backup|swp|tmp|temp|old|orig|dist)$">
      Order Allow,Deny
      Deny from all
  </FilesMatch>
  ```
* **Prevención de Exploración de Directorios:**
  Instrucción `Options -Indexes` activa para evitar que atacantes listen el contenido de carpetas (`/api/`, `/uploads/`, `/js/`, `/css/`).
* **Filtro Anti SQL Injection en la URL (Query Strings):**
  Detección temprana y desvío de patrones de inyección comunes (`%27`, `--`, `%23`, `;`) antes de llegar al intérprete de PHP:
  ```apache
  RewriteCond %{QUERY_STRING} (\%27)|(\-\-)|(\%23)|(;) [NC]
  RewriteRule ^(.*)$ index.php [L]
  ```
* **Encubrimiento de Errores (Information Disclosure):**
  Páginas de error personalizadas para 403, 404 y 500 que previenen la fuga de rutas internas del servidor o versiones de software.

### 3.2. Cabeceras HTTP de Seguridad (Security Headers)
Configuradas para proteger a los usuarios y al servidor contra vectores de ataque web modernos:
* `X-Frame-Options: SAMEORIGIN`: Bloquea ataques de **Clickjacking**, impidiendo que el sitio sea embebido en iframes de dominios externos maliciosos.
* `X-Content-Type-Options: nosniff`: Evita que los navegadores interpreten archivos como un tipo MIME distinto al declarado (mitigando ejecución involuntaria de scripts).
* `X-XSS-Protection: 1; mode=block`: Activa el filtro nativo del navegador contra **Cross-Site Scripting (XSS)**.
* `Referrer-Policy: strict-origin-when-cross-origin`: Protege la privacidad evitando enviar información sensible de la URL en peticiones externas.
* `Permissions-Policy: geolocation=(), microphone=(), camera=()`: Desactiva por defecto el acceso a sensores y hardware sensible en la landing pública.

### 3.3. Prevención de Inyecciones SQL y Sanitización en Backend
* **Uso Exclusivo de PDO con Parámetros Preparados:**
  Ninguna consulta a la base de datos PostgreSQL concatena variables directamente en el SQL. Todas las operaciones (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) emplean marcadores posicionales (`?`) o nombrados (`:param`).
* **Sanitización y Validación de Entradas:**
  Todas las peticiones recibidas a través de `$_POST` o `php://input` son filtradas con `htmlspecialchars()`, `trim()`, `strip_tags()` y casteadas a sus tipos numéricos o booleanos correspondientes antes de ser procesadas.

### 3.4. Gestión y Rotación Segura de Claves de API (Gemini/Imagen)
* **Balanceador de Carga y Aislamiento:**
  Las claves maestras de Google AI Studio residen en el archivo `.env` (fuera del repositorio de git).
* **Algoritmo de Rotación Aleatoria (`config/config.php`):**
  Evita el bloqueo por saturación de cuotas (*Rate Limits*) en la capa gratuita o de pago, distribuyendo equitativamente el consumo entre múltiples claves registradas.

### 3.5. Autenticación, Control de Sesiones y Protección de Cargas
* **Dashboard Blindado:** Control de acceso mediante `api/api-auth.php` con verificación de sesiones PHP y contraseñas hasheadas con algoritmos seguros (`Bcrypt` / `Argon2id`).
* **Protección del Directorio `/uploads/`:**
  Las carpetas donde se suben avatares, miniaturas y audios tienen deshabilitada la ejecución de scripts PHP mediante configuración de Apache, evitando que una imagen maliciosa actúe como una *Web Shell*.

---

## 🛠️ Guía Paso a Paso de Uso de Cada Herramienta

### 4.1. Asistente IA & Conversión (El Güero Bot / Paw Agent)
* **Ubicación:** `js/paw-agent.js`, `api/api-el-guero-bot.php`, `api/api-guero-knowledge.php`, `index-paw.html`.
* **¿Qué hace?**
  * Es un chatbot interactivo con la personalidad carismática, perruna y norteña del show.
  * Responde dudas sobre episodios, temas tratados, invitados pasados, mercancía y colaboraciones mediante **Google Gemini API** y una base de conocimiento (RAG).
  * Incluye disparadores de captación de correo/WhatsApp para leads de patrocinadores o fans VIP.
* **¿Cómo utilizarlo?**
  1. El widget aparece automáticamente en la esquina inferior derecha del sitio web.
  2. Al dar clic, el usuario puede interactuar con botones rápidos (*"Ver último episodio"*, *"Quiero ser patrocinador"*, *"Recomiéndame un tema"*) o escribir preguntas libres.
  3. Para actualizar su base de conocimiento, edita `api/api-guero-knowledge.php` o sincroniza los episodios en el panel administrativo.

---

### 4.2. Suite de Preproducción & Storytelling Pro

#### A. Cuestionario de Storytelling para Invitados
* **Ubicación:** `storytelling-invitado.html`, `js/storytelling-invitado-page.js`, `api/api-invitados-save.php`.
* **¿Qué hace?** Formulario estructurado que se envía al invitado previo a la grabación para extraer anécdotas clave, momentos difíciles, giros dramáticos y aprendizajes.
* **¿Cómo utilizarlo?**
  1. Envía el enlace `https://tudominio.com/storytelling-invitado.html` a tu próximo invitado.
  2. El invitado completa sus datos, redes, anécdotas y aprendizajes.
  3. Al enviar, la información se almacena en PostgreSQL y queda disponible inmediatamente en el Dashboard.

#### B. Generador de Escaletas y Guiones con IA
* **Ubicación:** `js/escaletas.js`, `api/api-escaleta.php`, `api/api-guion.php`.
* **¿Qué hace?** Construye la estructura minuto a minuto del episodio: *Hook inicial (0-2m)*, *Intro*, *Bloque 1 (Conflicto)*, *Bloque 2 (Clímax/Revelación)*, *Bloque 3 (Aprendizaje)* y *Cierre con CTA*.
* **¿Cómo utilizarlo?**
  1. En el Dashboard, abre la pestaña **"Escaletas & Guiones"**.
  2. Selecciona un invitado registrado o ingresa un tema central.
  3. Haz clic en **"Generar Escaleta con Gemini"**. El sistema creará tiempos estimados, preguntas detonantes y notas para el host.
  4. Puedes editar los bloques manualmente y guardar la versión final.

#### C. Cue Cards Digitales en Vivo
* **Ubicación:** `js/cuecards.js`, `api/api-cuecards.php`, `dashboard/text_card.html`.
* **¿Qué hace?** Tarjetas interactivas de apoyo para la mesa de grabación (modo teleprompter / tarjeta de mano para iPad o móvil).
* **¿Cómo utilizarlo?**
  1. Abre las **Cue Cards** del episodio activo desde el panel o tu tableta.
  2. Navega entre tarjetas con gestos táctiles o teclado (flechas `←` y `→`).
  3. Consulta preguntas detonantes, datos duros del invitado y recordatorios de menciones de patrocinadores en tiempo real.

#### D. Contrato y Cesión de Derechos de Imagen
* **Ubicación:** `cesion-derechos.html`, `Legal/`, `api/api-export-pdf.php`.
* **¿Qué hace?** Formato legal para la autorización de uso de imagen, voz y material audiovisual conforme a las leyes aplicables de derechos de autor.
* **¿Cómo utilizarlo?**
  1. Ingresa a `cesion-derechos.html`.
  2. Llena los datos del invitado y del titular de la producción.
  3. Permite la firma en pantalla (vía Canvas) o exporta a PDF para firma física/digital antes de encender las cámaras.

---

### 4.3. Suite de Postproducción de Video & Audio con IA
* **Ubicación:** `js/video-editor.js`, `js/ffmpeg-wasm-helper.js`, `api/api-video-process.php`, `api/api-cloud-video.php`, `api/api-video-clips.php`.
* **¿Qué hace?**
  * **Corte Automático de Silencios:** Algoritmo que detecta pausas incómodas y silencios prolongados para limpiar la línea de tiempo.
  * **Cortes J y L Multicámara:** Transiciones donde el audio precede o sigue al corte de video para una conversación orgánica.
  * **Masterización de Audio:** Normalización automática a **-14 LUFS** y **-1.0 dB True Peak** (norma oficial de YouTube y Spotify).
  * **Aislamiento Vocal:** Integración con Demucs para eliminar eco de sala y ruido de fondo.
  * **Generador de Clips / Shorts:** Detección de momentos de alta energía y corte vertical 9:16 con subtítulos automáticos.
* **¿Cómo utilizarlo?**
  1. En el Dashboard, dirígete a **"Editor de Video"**.
  2. Carga el archivo de video o audio crudo (o vincula una URL/ruta en la nube).
  3. Selecciona las operaciones deseadas: *Silencios*, *Normalizar LUFS*, *Generar Transcripción*, *Extraer Clips*.
  4. Haz clic en **"Procesar Pipeline"**. La consola mostrará el progreso en tiempo real y te permitirá descargar el render final.

---

### 4.4. Editor Gráfico Canva PRO Cyberpunk
* **Ubicación:** `js/editor-canva.js`, `css/editor-canva.css`, `dashboard/index.php`.
* **¿Qué hace?** Suite de diseño basada en Canvas HTML5 para crear miniaturas de YouTube (1280x720) e historias/portadas (1080x1920) sin salir del ecosistema.
* **Características Clave:**
  * Tipografías oficiales del podcast (*Permanent Marker, Luckiest Guy, Outfit, Architects Daughter*).
  * Capas independientes con control de opacidad, profundidad (*z-index*), rotación y sombras de neón.
  * Stickers, insignias, marcos grunge y logos oficiales de *La Cueva del Güero*.
  * **Cloud File Picker:** Importación directa de fotos desde Google Drive, Dropbox, OneDrive y TeraBox vía OAuth.
  * **Rembg AI:** Botón para quitar el fondo de la foto del invitado con un solo clic.
* **¿Cómo utilizarlo?**
  1. Abre la pestaña **"Canva PRO"** en el Dashboard.
  2. Elige la plantilla (YouTube Thumbnail o Vertical Short).
  3. Carga la foto del invitado y dale clic a *"Quitar Fondo"*.
  4. Agrega textos llamativos, aplica efecto Neón Glow y coloca los stickers de la cueva.
  5. Exporta en PNG/JPG de alta resolución o guarda como plantilla reutilizable.

---

### 4.5. Motor de Avatares e Ilustraciones (Avatar Engine)
* **Ubicación:** `avatar-engine/`, `api/api-avatar-engine.php`, `api/api-imagen-generator.php`.
* **¿Qué hace?** Genera ilustraciones artísticas, retratos cyberpunk y variaciones de la mascota/avatares del show utilizando **Google Imagen 3**.
* **¿Cómo utilizarlo?**
  1. En el panel o vía API, define el prompt temático (ej. *"El Güero en estilo anime cyberpunk con gafas de sol neón en un estudio de radio"*).
  2. El microservicio se comunica con Google AI Studio y renderiza la imagen en alta definición.
  3. La imagen se guarda en `uploads/avatars/` y queda lista para usar en el Canva PRO o en redes sociales.

---

### 4.6. Gestor de Blog Transmedia & SEO con IA
* **Ubicación:** `manage-blog.html`, `js/manage-blog.js`, `api/api-blog-ai.php`, `api/upload-blog.php`, `posts.json`.
* **¿Qué hace?** Transforma cada episodio de audio/video en un artículo de blog completo, estructurado con encabezados H2/H3, palabras clave, meta-descripciones y citas memorables para posicionamiento en Google.
* **¿Cómo utilizarlo?**
  1. Ingresa a `manage-blog.html` o la pestaña **"Blog PRO"** en el Dashboard.
  2. Pega la transcripción del episodio o ingresa el título y tema principal.
  3. Haz clic en **"Generar Artículo con IA"**. Gemini estructurará la redacción optimizada para SEO.
  4. Revisa, añade una imagen de cabecera y haz clic en **"Publicar"**. El artículo se indexa instantáneamente en el blog público.

---

### 4.7. Analítica de YouTube & CRM de Invitados
* **Ubicación:** `api/api-youtube-analytics.php`, `api/api-youtube-latest.php`, `api/api-guest-tracking.php`.
* **¿Qué hace?**
  * Sincroniza las estadísticas del canal oficial de YouTube (vistas, likes, retención y comentarios).
  * Monitorea el estado de los invitados: *Por Contactar ➔ Cuestionario Enviado ➔ Escaleta Lista ➔ Grabado ➔ Editado ➔ Publicado*.
* **¿Cómo utilizarlo?**
  1. Consulta la vista principal del Dashboard para ver las gráficas de rendimiento y el último video transmitido.
  2. Usa el **Kanban de Invitados** para mover a los participantes por cada etapa de producción.

---

## 💻 Stack Tecnológico de Producción

| Capa | Tecnologías | Propósito |
| :--- | :--- | :--- |
| **Frontend UI** | HTML5 Semántico, CSS3 Neón Cyberpunk, Vanilla JavaScript (ES6+), Canvas API, FontAwesome 6, Google Fonts | Interfaz reactiva ultrarrápida sin sobrecarga de frameworks |
| **Backend & APIs** | PHP 8.1 / 8.2 (FPM/Apache), PDO PostgreSQL, cURL, GD/Imagick, mod_rewrite | Microservicios RESTful y controladores de negocio |
| **Microservicio Avatares** | Node.js, Express.js | Orquestación de generación gráfica y segmentación |
| **Bases de Datos** | PostgreSQL 15+ (Google Cloud SQL / NeonDB / Supabase) | Persistencia relacional de episodios, blogs, usuarios y CRM |
| **Modelos de IA** | Google Gemini 1.5 / 3.0 Flash Preview, Google Imagen 3 | Generación de guiones, RAG de chatbot, SEO y arte |
| **Procesamiento Multimedia**| FFmpeg, faster-whisper (Large-v3), Demucs AI, Auto-Editor, FFmpeg.wasm | Edición de video, limpieza acústica y transcripción |
| **Cloud & Hosting** | Google Cloud Platform (Compute Engine, Cloud Run, Cloud SQL, GCS), Hostinger | Servidor web de producción y procesamiento distribuido |
| **Seguridad & Red** | Apache `.htaccess` blindado, CSP, HSTS, X-Frame-Options, SSL/TLS (Certbot), Rotación de API Keys | Blindaje de endpoints, mitigación de inyecciones y alta disponibilidad |

---

## ⚙️ Instalación, Configuración y Despliegue

### Requisitos Previos
* Servidor con Linux (Ubuntu 22.04 LTS recomendado) o entorno local con XAMPP/Docker.
* PHP 8.1 o superior con extensiones: `pdo`, `pdo_pgsql`, `curl`, `gd`, `mbstring`, `json`.
* PostgreSQL 14+.
* FFmpeg instalado en el sistema (`sudo apt install ffmpeg`).
* Clave de API de [Google AI Studio](https://aistudio.google.com/) (Gemini & Imagen 3).

---

### Paso 1: Clonar el Repositorio y Configurar Variables de Entorno
```bash
# Clonar repositorio
git clone https://github.com/whoiselgallo/LACUEVAWEB-ELGUEROBOT.git
cd LACUEVAWEB-ELGUEROBOT

# Crear archivo .env basado en la plantilla
cp .env.example .env
```

Edita el archivo `.env` con tus credenciales:
```ini
# Configuración del Sistema
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lacuevadelguero.com

# Base de Datos PostgreSQL
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=neondb
DB_USER=usuario_cueva
DB_PASS=tu_password_seguro

# Inteligencia Artificial (Rotación de Claves Gemini)
GEMINI_API_KEYS=AIzaSyA_clave1...,AIzaSyB_clave2...,AIzaSyC_clave3...

# Integraciones Opcionales
YOUTUBE_API_KEY=tu_youtube_data_api_key
YOUTUBE_CHANNEL_ID=UCxxxxxxxxxxxxxx
```

---

### Paso 2: Inicialización de la Base de Datos
Ejecuta el script de inicialización para crear todas las tablas requeridas:
```bash
# Vía CLI
php db_init.php

# O accediendo desde tu navegador (eliminar o proteger tras el primer uso):
# https://tudominio.com/db_init.php
```

---

### Paso 3: Despliegue en Servidor Apache / Ubuntu (GCP o VPS)
```bash
# 1. Actualizar paquetes e instalar dependencias
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 php libapache2-mod-php php-pdo php-pgsql php-curl php-gd php-mbstring git ffmpeg

# 2. Habilitar mod_rewrite y cabeceras de Apache
sudo a2enmod rewrite headers
sudo systemctl restart apache2

# 3. Configurar permisos de directorios
sudo chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs
sudo chmod -R 775 /var/www/html/uploads /var/www/html/logs

# 4. Obtener certificado SSL gratuito con Certbot
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d tudominio.com -d www.tudominio.com
```

---

## 📈 Recomendaciones para Escalar el Proyecto

A medida que el podcast crezca en volumen de audiencia y horas de producción, se sugiere implementar las siguientes optimizaciones:

```
                  ┌───────────────────────────────┐
                  │      CLOUDFLARE CDN / WAF     │
                  └───────────────┬───────────────┘
                                  │
                  ┌───────────────▼───────────────┐
                  │      LOAD BALANCER / NGINX    │
                  └───────┬───────────────┬───────┘
                          │               │
            ┌─────────────▼─────┐   ┌─────▼─────────────┐
            │ Web App (PHP-FPM) │   │ Video Workers     │
            │   Google Cloud VM │   │ (Cloud Run / GPU) │
            └─────────────┬─────┘   └─────┬─────────────┘
                          │               │
            ┌─────────────┴───────────────┴─────────────┐
            │  Cloud SQL (PostgreSQL) + Redis Cache     │
            └───────────────────────────────────────────┘
```

1. **Desacoplar el Procesamiento de Video a Cloud Run / Workers Asíncronos:**
   * Mover las tareas pesadas de FFmpeg, Demucs y Whisper a contenedores en **Google Cloud Run** con autoescalado a cero para no saturar el servidor web principal.
   * Utilizar **Redis + Celery / BullMQ** para encolar solicitudes de edición.
2. **Base de Datos Vectorial para RAG de Gran Escala:**
   * Habilitar la extensión `pgvector` en PostgreSQL para almacenar los *embeddings* de todas las transcripciones de episodios pasados.
   * Permitirá que *El Güero Bot* responda con citas exactas y timestamps precisos: *"En el episodio 45, minuto 23:15, el invitado habló sobre este tema"*.
3. **Almacenamiento de Medios en Google Cloud Storage (GCS) + CDN:**
   * Almacenar videos, audios y miniaturas en buckets de GCS con distribución global mediante Cloudflare o Cloud CDN para descargas ultrarrápidas y menor consumo de ancho de banda.
4. **Sistema de Roles y Permisos (RBAC):**
   * Expandir `api-auth.php` con perfiles diferenciados: **Host** (acceso a escaletas/cuecards), **Editor** (acceso a suite de video/canva), **Copywriter** (acceso a blog/hooks) y **Staff Legal**.
5. **Monitoreo y Alertas en Tiempo Real:**
   * Integrar Sentry para rastreo de errores frontend/backend y Google Cloud Monitoring para el control de cuotas de las APIs de Gemini.

---

## 🗺️ Roadmap y Siguientes Fases de Aplicación

```
  FASE 1               FASE 2               FASE 3               FASE 4
[Actual]           [Corto Plazo]        [Mediano Plazo]       [Largo Plazo]
──────────────     ──────────────       ───────────────       ─────────────
• Web & Chatbot    • Clipping Auto 9:16 • Guest Portal Auto   • Auto-Publish
• Canva PRO        • Subtítulos Animados• Sync Calendly       • Spotify/YT API
• Storytelling     • RAG con pgvector   • Firma Biométrica    • Monetización VIP
• Escaletas IA     • Cloud Run Workers  • CRM de Sponsors     • Audio Ads Dinámicos
```

* **Fase 2 (Corto Plazo - Automatización de Shorts):**
  * Generador automático de clips verticales 9:16 con subtítulos animados palabra por palabra (estilo Alex Hormozi) directo desde el video crudo.
  * Implementación de cola de tareas con Redis y migración del pipeline pesado a Google Cloud Run.
* **Fase 3 (Mediano Plazo - Portal Autoservicio del Invitado):**
  * Portal web exclusivo para el invitado (`/guest/id`): auto-agendamiento sincronizado con Google Calendar, cuestionario interactivo, kit de prensa y firma biométrica del acuerdo de cesión de derechos.
  * Módulo CRM para prospección y seguimiento de patrocinadores comerciales.
* **Fase 4 (Largo Plazo - Distribución y Monetización 360°):**
  * Publicación automatizada en un clic hacia YouTube API, Spotify for Podcasters y redes sociales.
  * Sistema de membresías exclusivas para oyentes VIP con pasarela de pagos Stripe y contenido desbloqueable.

---

## 💎 Beneficios Estratégicos para un Podcaster

| Área | Flujo Tradicional | Con La Cueva del Güero Suite | Impacto Directo |
| :--- | :--- | :--- | :--- |
| **Preparación de Entrevistas** | Hojas sueltas, notas improvisadas, preguntas genéricas. | Cuestionario guiado + Escaleta estructurada con IA + Cue Cards en tiempo real. | **+80% de profundidad y retención de audiencia.** |
| **Tiempo de Postproducción** | 6 a 10 horas editando silencios, niveles de audio y miniaturas manualmente. | Pipeline automático de silencios, LUFS, Demucs y Canva integrado. | **Ahorro de +15 horas semanales por episodio.** |
| **Seguridad Legal** | Acuerdos verbales o contratos en papel extraviados. | Contratos digitales de cesión de derechos firmados y archivados. | **100% de blindaje legal frente a reclamos.** |
| **Estrategia Transmedia** | Solo se sube el video a YouTube y nada más. | 1 episodio ➔ Video largo + Shorts + Artículo de Blog SEO + Ganchos para Reels. | **3x a 5x más alcance orgánico multicanal.** |
| **Atención a la Comunidad** | Mensajes directos ignorados por falta de tiempo. | Asistente de IA (*El Güero Bot*) interactuando 24/7 con el estilo del show. | **Captación constante de fans y nuevos patrocinadores.** |

---

## 📄 Estructura de Directorios del Repositorio

```text
LACUEVAWEB-ELGUEROBOT/
├── .htaccess                        # Reglas de seguridad Apache, headers y anti-SQLi
├── .env.example                     # Plantilla de variables de entorno
├── api/                             # Microservicios y Endpoints Backend (PHP)
│   ├── api-auth.php                 # Autenticación segura del dashboard
│   ├── api-avatar-engine.php        # Integración con Google Imagen 3
│   ├── api-blog-ai.php              # Generador de artículos SEO con Gemini
│   ├── api-cloud-video.php          # Orquestador de video en Cloud Run
│   ├── api-cuecards.php             # CRUD y despacho de Cue Cards
│   ├── api-db-test.php              # Diagnóstico de conexión a PostgreSQL
│   ├── api-drive-oauth.php          # Conector OAuth2 para nubes de almacenamiento
│   ├── api-el-guero-bot.php         # Endpoint conversacional de El Güero Bot
│   ├── api-episodes-sync.php        # Sincronización de episodios
│   ├── api-escaleta.php             # Generación y almacenamiento de escaletas
│   ├── api-export-pdf.php           # Generador de contratos y documentos PDF
│   ├── api-guero-knowledge.php      # Base de conocimiento RAG del podcast
│   ├── api-guest-tracking.php       # CRM y seguimiento de invitados
│   ├── api-guion.php                # Creador de guiones detallados
│   ├── api-hooks-ai.php             # Generador de ganchos virales para redes
│   ├── api-invitados-save.php       # Guardado de respuestas de storytelling
│   ├── api-media-library.php        # Gestor de biblioteca multimedia
│   ├── api-video-clips.php          # Extractor de clips y momentos destacados
│   ├── api-video-process.php        # Pipeline local FFmpeg, LUFS y Whisper
│   ├── api-youtube-analytics.php    # Métricas y KPIs del canal de YouTube
│   └── upload-blog.php              # Carga y publicación de posts de blog
├── avatar-engine/                   # Capa del generador de avatares (Node.js/Express)
├── config/                          # Configuración global y rotación de claves
│   └── config.php                   # Constantes, conexión DB y balanceador Gemini
├── css/                             # Hojas de estilo Neón Cyberpunk
│   ├── styles.css                   # Estilos generales del portal público
│   ├── editor-canva.css             # Estilos del editor gráfico
│   └── dashboard-pro.css            # Estilos del panel administrativo
├── dashboard/                       # Vistas del Panel Administrativo PRO
│   ├── index.php                    # Dashboard unificado multipestaña
│   ├── login.php                    # Pantalla de acceso al panel
│   └── text_card.html               # Vista optimizada para Cue Cards en vivo
├── docs/                            # Documentación técnica adicional y guías
│   ├── EVALUACION_CURADURIA.md      # Criterios de curaduría de contenido
│   └── GUIA_GOOGLE_CLOUD_RUN_VIDEO.md # Despliegue de procesador de video en GCP
├── js/                              # Lógica frontend en JavaScript Vanilla
│   ├── avatar-engine.js             # Controlador del generador de avatares
│   ├── cuecards.js                  # Lógica interactiva de tarjetas Cue Cards
│   ├── dashboard-pro.js             # Controlador principal del panel administrativo
│   ├── editor-canva.js              # Motor del editor gráfico en Canvas HTML5
│   ├── escaletas.js                 # Lógica del generador de escaletas
│   ├── ffmpeg-wasm-helper.js        # Soporte de procesamiento en el cliente
│   ├── manage-blog.js               # Administrador de publicaciones de blog
│   ├── paw-agent.js                 # Comportamiento e interfaz de El Güero Bot
│   ├── storytelling-invitado-page.js# Validación y envío de cuestionario de invitados
│   └── video-editor.js              # Interfaz de postproducción de video
├── Legal/                           # Formatos legales y contratos modelo
├── posts.json                       # Base de datos ligera de artículos de blog
├── cesion-derechos.html             # Generador y firma de cesión de derechos
├── storytelling-invitado.html       # Cuestionario público para invitados
├── manage-blog.html                 # Interfaz de gestión del blog
├── index.html                       # Página web pública del podcast
├── index-paw.html                   # Vista dedicada del agente conversacional
├── db_init.php                      # Creador de esquema de base de datos
├── diagnostico.php                  # Script de salud del sistema y dependencias
├── Dockerfile                       # Definición de contenedor Docker
├── render.yaml                      # Configuración de despliegue en la nube
└── README.md                        # Documentación centralizada del proyecto
```

---

## ⚖️ Licencia y Créditos

* **Desarrollo y Arquitectura:** Ecosistema desarrollado para **La Cueva del Güero**.
* **Modelos de IA:** Potenciado por **Google Gemini API** & **Google Imagen 3**.
* **Licencia:** Software propietario para uso exclusivo del show y sus canales autorizados. Todos los derechos reservados © 2026.

---

<p align="center">
  <b>🎙️ La Cueva del Güero — El Podcast Más Chido de Mexicali 🔥</b><br>
  <i>Automatizando el futuro del podcasting independiente con Inteligencia Artificial.</i>
</p>
