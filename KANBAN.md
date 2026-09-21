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
