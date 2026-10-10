# 📋 CUEVA BOT KANBAN BOARD
**Proyecto: La Cueva del Güero (Podcast)**

Este tablero Kanban representa el estado del desarrollo, corrección de errores, rediseño y sincronización con el panel TSolution. Las tareas están organizadas por columnas y se pueden marcar con `[x]` para completarse.

---

## 📥 Acumulación de Pendientes (Backlog)
*Tareas planificadas a futuro para siguientes fases.*
- [ ] Conexión del inicio de sesión (SSO) unificado con la Landing de TSolutions.
- [ ] Módulo de pagos y suscripción recurrente con Stripe para Club VIP de miembros.

---

## 📌 Listo (Ready / To Do)
*Tareas preparadas para optimización continua.*
- [ ] Pruebas de carga y estrés concurrentes en el pipeline de video distribuido.
- [ ] Integración directa con la API de publicación automática de Spotify for Podcasters.

---

## ⚡ En Progreso (In Progress)
*Acciones actualmente en monitoreo activo.*
- [ ] Monitoreo de cuotas y rendimiento del balanceador de claves de Google Gemini API en producción.

---

## 🔍 En Resumen (Under Review / Verification)
*Tareas completadas que están siendo verificadas en producción.*
- [x] **Tracking de Invitados en Vivo:** Portal interactivo (`/tracking/index.html`) con barra de progreso de 5 fases, generador/recuperador de códigos y botón instantáneo para copiar enlace y compartir por WhatsApp.
- [x] **API de Gestión de Tracking:** Microservicio (`/api/api-guest-tracking.php`) con acciones `get_status` y `recover_code`.
- [x] **Prompt Maestro de Hooks:** Probado en `/api/api-hooks-ai.php` con neuro-marketing y fórmula de retención.
- [x] **Extractor de Clips Virales con IA:** Implementado en `/api/api-video-clips.php` para recortar automáticamente momentos cumbre y shorts 9:16.
- [x] **Gemini 100% Nativo:** Guión, escaleta y cue cards migrados con éxito desacoplados de Dify.
- [x] **Descargables PDF PRO:** Implementado `/api/api-export-pdf.php` con formatos broadcast, tarjetas de set y parrilla ejecutiva.
- [x] **El Güero Bot:** Restaurado con personalidad norteña urbana, captación de leads en `/api/api-el-guero-bot.php` y enlaces inteligentes de tracking.
- [x] **Generador de Avatares e Ilustraciones Neón:** Integración con Google Imagen 3 en `/api/api-avatar-engine.php` y `/api/api-imagen-generator.php`.
- [x] **Transcripción & Pipeline de Video:** Orquestación en `/api/api-video-process.php`, `/api/api-cloud-video.php` y editor basado en texto en `/api/api-text-based-editor.php`.

---

## 🛠️ Modificado (Modified)
*Componentes modificados estructuralmente durante el desarrollo.*
- [x] **tracking/index.html:** Creada interfaz de seguimiento con modal de recuperación y copiado directo de enlace.
- [x] **api-guest-tracking.php:** Creado endpoint con soporte para consultas dinámicas y generación de tokens de invitado.
- [x] **paw-agent.js:** Integrada detección de palabras clave de tracking y acción dedicada en los dedos de la pata (*toes*).
- [x] **api-hooks-ai.php:** Implementado nuevo Prompt Maestro de neuro-marketing y copywriting persuasivo.
- [x] **api-guion.php:** Migrado a Gemini nativo con formato cinematográfico broadcast de 6 bloques y timecodes.
- [x] **api-escaleta.php:** Migrado a Gemini con generación de escaleta técnica, guión base y preguntas de cabina.
- [x] **api-cuecards.php:** Migrado a Gemini con tarjetas A5 de alto contraste para set de grabación.
- [x] **api-export-pdf.php:** Creado motor de impresión y guardado como PDF profesional.
- [x] **dashboard-pro.js:** Integrado botón de exportación PDF y modal de impresión directa.
- [x] **config.php:** Modificado como núcleo dinámico con balanceador de claves Gemini y conexión a PostgreSQL.

---

## 🎨 Rediseñado (Redesigned)
*Mejoras aplicadas al diseño visual, tipografía y tokens.*
- [x] **Portal de Tracking:** Estética Neón Cyberpunk con timeline interactivo de fases completadas y en curso.
- [x] **Módulo de Impresión / PDF:** Estilos aplicados para lectura en cabina (A5 apaisado) y formato guión de cine.
- [x] **Hero Animado de la Landing:** Secuencia con motor de partículas HTML5 y avatares con caminata y sentada dinámica.

---

## 🐛 Corrección de Errores (Bug Fixes)
*Errores críticos y fallas de dependencias solucionados.*
- [x] **Alerta de Tracking Bloqueante:** Eliminado el placeholder `alert('Tracking en vivo próximamente')` y sustituido por el portal funcional.
- [x] **Navegación Móvil:** Corregido el auto-cierre de los enlaces dentro del dropdown en `js/scripts.js`.
- [x] **Reactivación El Güero Bot:** Restaurado el conector de IA Gemini en `api/api-el-guero-bot.php` y sincronizado con `js/paw-agent.js`.
- [x] **Doble salida JSON:** Reparado el bug de doble impresión JSON en `api-invitados-save.php`.
- [x] **Cue Cards 400 Bad Request:** Flexibilizada la validación en `api-cuecards.php`.

---

## ✅ Hecho (Done)
*Hitos completados y validados.*
- [x] Sistema de Tracking y CRM de Invitados 100% operativo.
- [x] Generador de Clips Virales y Hooks para Redes activo con Gemini.
- [x] Suite completa de Preproducción (Storytelling, Escaletas, Cue Cards y Contratos Legales).
- [x] Suite de Postproducción y Masterización (-14 LUFS, Demucs, FFmpeg).
- [x] Editor gráfico Canva PRO Cyberpunk con remoción de fondo y Cloud Picker.
- [x] Documentación centralizada en `README.md` con Landing Page y Capas de Seguridad.

---

## 📌 MESA DE TRABAJO: TASKS Y ASIGNACIONES OFICIALES

Esta sección desglosa las implementaciones pendientes y las mejoras recomendadas derivadas de la evaluación del plan de acción, estructuradas por áreas operativas, responsables canónicos y sub-acciones específicas.

### 📋 BLOQUE A: IMPLEMENTACIONES PENDIENTES

#### 1. Pruebas de Carga y Estrés Concurrentes en el Pipeline de Video
* **Área:** `Optimización` / `Producción`
* **Responsables:** **Tú (Antigravity)** & **El Gallo**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 1.1:** Configurar entorno de simulación de render concurrente (3-5 videos simultáneos).
    - [ ] *Sub-acción 1.1.1:* Crear script de benchmarking con FFmpeg y procesamiento de audio Demucs.
    - [ ] *Sub-acción 1.1.2:* Medir consumo de RAM, CPU y tiempos de procesamiento por minuto de video.
  - [ ] **Acción 1.2:** Probar orquestación serverless en Google Cloud Run / Worker dedicado.
    - [ ] *Sub-acción 1.2.1:* Validar timeout de ejecución y escalado automático de instancias.
    - [ ] *Sub-acción 1.2.2:* Validar descarga y persistencia segura de artefactos en almacenamiento en la nube.

#### 2. Integración Directa con la API de Publicación de Spotify for Podcasters
* **Área:** `Actualización` / `Producción`
* **Responsables:** **Tú (Antigravity)**, **El Gallo** & **El Junior**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 2.1:** Configurar flujo de autorización y credenciales OAuth con Spotify for Podcasters API.
    - [ ] *Sub-acción 2.1.1:* Registrar aplicación en Spotify Developer Dashboard y guardar secrets en variables de entorno seguras.
    - [ ] *Sub-acción 2.1.2:* Implementar mecanismo de renovación automática de tokens (`refresh_token`).
  - [ ] **Acción 2.2:** Desarrollar microservicio de publicación y sincronización de metadatos.
    - [ ] *Sub-acción 2.2.1:* Crear endpoint `/api/api-spotify-publish.php` con metadatos del episodio (título, descripción IA, audio -14 LUFS y carátula).
    - [ ] *Sub-acción 2.2.2:* Vincular el disparador automático con el cierre de la Fase 5 ("Publicado") en el CRM de Tracking.

#### 3. Monitoreo de Cuotas y Rendimiento del Balanceador de Claves Gemini API
* **Área:** `Optimización` / `Actualización`
* **Responsables:** **Tú (Antigravity)**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 3.1:** Implementar telemetría y métricas de consumo por llave API en producción.
    - [ ] *Sub-acción 3.1.1:* Registrar latencia, tokens consumidos y eventos de excepción (429/ResourceExhausted) en archivo rotativo de log.
    - [ ] *Sub-acción 3.1.2:* Habilitar alerta visual en el Dashboard PRO cuando una llave entra en período de enfriamiento (*cooldown*).
  - [ ] **Acción 3.2:** Optimización de prompts para reducción de consumo de tokens y coste.
    - [ ] *Sub-acción 3.2.1:* Compactar contextos en generación de guiones largos manteniendo el formato cinematográfico broadcast.

#### 4. Módulo de Pagos y Suscripción Recurrente con Stripe para Club VIP
* **Área:** `Finanzas` / `Actualización`
* **Responsables:** **La Mary**, **Tú (Antigravity)** & **El Junior**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 4.1:** Definición comercial y jurídica de planes de membresía VIP.
    - [ ] *Sub-acción 4.1.1:* Estructurar niveles de membresía (Mensual / Anual) y catálogo de beneficios (preventas, contenido sin censura, menciones) (**La Mary** & **El Junior**).
    - [ ] *Sub-acción 4.1.2:* Redactar términos y condiciones de suscripción recurrente (**La Mary**).
  - [ ] **Acción 4.2:** Integración técnica de Checkout y Webhooks de Stripe.
    - [ ] *Sub-acción 4.2.1:* Crear endpoints `/api/api-stripe-checkout.php` y `/api/api-stripe-webhook.php` (**Tú**).
    - [ ] *Sub-acción 4.2.2:* Sincronizar permisos VIP en la base de datos de usuarios (**Tú**).

#### 5. Conexión del Inicio de Sesión Unificado (SSO) con Landing TSolutions
* **Área:** `Actualización` / `Desarrollo e Infraestructura`
* **Responsables:** **Tú (Antigravity)** & **El Junior**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 5.1:** Diseñar protocolo de autenticación federada mediante JWT compartido.
    - [ ] *Sub-acción 5.1.1:* Definir llave simétrica de firmado y políticas de expiración e intercambio de tokens.
    - [ ] *Sub-acción 5.1.2:* Sincronizar perfiles de usuario, roles de staff y accesos entre ambos dominios.

---

### 💡 BLOQUE B: MEJORAS RECOMENDADAS (DEL PLAN DE ACCIÓN)

#### 6. Automatización de Plantillas de Subtítulos Animados y Shorts 9:16
* **Área:** `Edición` / `Diseño` / `Producción`
* **Responsables:** **El Gallo**, **Tú (Antigravity)** & **El Güero**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 6.1:** Crear biblioteca de presets gráficos para shorts verticales.
    - [ ] *Sub-acción 6.1.1:* Diseñar estilos de subtítulos dinámicos de alto contraste en colores Neón Cyan (`#00FFFF`) y Magenta (`#FF00FF`).
    - [ ] *Sub-acción 6.1.2:* Incorporar badge/sticker de "El Güero" con micro-animación en esquina superior (**El Güero** & **El Gallo**).
  - [ ] **Acción 6.2:** Curaduría algorítmica de momentos clímax con el Extractor de Hooks.
    - [ ] *Sub-acción 6.2.1:* Validar efectividad de ganchos de neuro-marketing en los primeros 3 segundos antes del render final (**El Gallo**).

#### 7. Retos Interactivos de Cabina y Dinámicas de Set en Vivo
* **Área:** `Grabación` / `Pre-producción`
* **Responsables:** **El Junior**, **El Gallo** & **El Güero**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 7.1:** Integrar dinámicas interactivas en las Cue Cards de set.
    - [ ] *Sub-acción 7.1.1:* Incorporar sección "La Pregunta del Güero" y retos espontáneos en las tarjetas digitales de tablet (**El Gallo**).
    - [ ] *Sub-acción 7.1.2:* Realizar ensayo de ritmo y modulación en cabina previo al inicio de grabación con el invitado (**El Junior**).

#### 8. Políticas de Respaldo y Snapshot Periódico de la Base de Datos
* **Área:** `Optimización` / `Infraestructura`
* **Responsables:** **Tú (Antigravity)** & **La Mary**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 8.1:** Automatizar respaldo diario y verificación de integridad de datos.
    - [ ] *Sub-acción 8.1.1:* Programar cronjob nocturno para dump comprimido de la base de datos de invitados y tracking.
    - [ ] *Sub-acción 8.1.2:* Documentar protocolo de contingencia y restauración rápida ante desastres (DRP) validado financieramente (**La Mary**).

#### 9. Gestión y Cierre de Patrocinios Comerciales en Guión Broadcast
* **Área:** `Pre-producción` / `Producción` / `Finanzas`
* **Responsables:** **La Mary**, **El Junior** & **El Gallo**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 9.1:** Estructuración de menciones y bloques comerciales en cabina.
    - [ ] *Sub-acción 9.1.1:* Negociar acuerdos comerciales y validar menciones de marca del Bloque 4 (**La Mary**).
    - [ ] *Sub-acción 9.1.2:* Integrar copys comerciales en la plantilla de `/api/api-guion.php` (**El Gallo** & **Tú**).
    - [ ] *Sub-acción 9.1.3:* Locución orgánica del mensaje comercial en vivo sin romper el ritmo de la entrevista (**El Junior**).

#### 10. Banco de Assets Visuales y Miniaturas Neón con Google Imagen 3
* **Área:** `Diseño`
* **Responsables:** **El Gallo**, **El Güero** & **Tú (Antigravity)**
* **Acciones & Sub-acciones:**
  - [ ] **Acción 10.1:** Generar y catalogar banco de recursos visuales de "La Cueva".
    - [ ] *Sub-acción 10.1.1:* Ajustar prompts en el motor Imagen 3 combinando atmósfera desértica de Mexicali con estética cyberpunk.
    - [ ] *Sub-acción 10.1.2:* Integrar assets en el Cloud Picker de Canva PRO para ensamblaje rápido de thumbnails por episodio.

