# 📋 CUEVA BOT KANBAN BOARD
**Proyecto: La Cueva del Güero (Podcast)**

Este tablero Kanban representa el estado del desarrollo, corrección de errores, rediseño y sincronización con el panel TSolution. Las tareas están organizadas por columnas y se pueden marcar con `[x]` para completarse.

---

## 📥 Acumulación de Pendientes (Backlog)
*Tareas planificadas que esperan ser asignadas a desarrollo.*
- [ ] Integrar CRM para Onboarding previo de invitados (formulario externo).
- [ ] Desarrollar módulo de transcripción de audio automática utilizando la API de Whisper.
- [ ] Desarrollar extractor de clips virales e identificador de ganchos del episodio.
- [ ] Crear el generador de prompts automáticos neón para Midjourney/DALL-E.

---

## 📌 Listo (Ready / To Do)
*Tareas priorizadas y listas para ser tomadas por desarrollo.*
- [ ] **Configurar Cloud Run Jobs para el Dashboard PRO:** Orquestar trabajos por lotes en segundo plano para el procesamiento pesado del ecosistema (transcripción de podcasts con Whisper, corte de clips virales con FFmpeg, sincronización programada de analíticas de YouTube y respaldos de base de datos) desacoplándolos de las peticiones web directas.
- [ ] Rediseño visual del sitio bajo las pautas del Design System Neón.
- [ ] Conexión del inicio de sesión (SSO) unificado con la Landing de TSolutions.
- [ ] Configuración del API Gateway de estadísticas en la landing central.
- [ ] Optimización de las imágenes de fondo gigantes de la carpeta `images/`.

---

## ⚡ En Progreso (In Progress)
*Acciones actualmente en desarrollo activo.*
- [ ] Pruebas en vivo con usuarios y creadores en el panel de control.

---

## 🔍 En Resumen (Under Review / Verification)
*Tareas completadas que están siendo verificadas en producción en Google Cloud Run.*
- [x] **Prompt Maestro de Hooks:** Probado en `/api/api-hooks-ai.php` con neuro-marketing y fórmula de retención.
- [x] **Gemini 100% Nativo:** Guión, escaleta y cue cards migrados con éxito desacoplados de Dify.
- [x] **Descargables PDF PRO:** Implementado `/api/api-export-pdf.php` con formatos broadcast, tarjetas de set y parrilla ejecutiva.
- [x] **El Güero Bot:** Restaurado con personalidad norteña urbana y captación de leads en `/api/api-el-guero-bot.php`.

---

## 🛠️ Modificado (Modified)
*Componentes modificados estructuralmente durante el desarrollo para dar soporte a nuevas funciones.*
- [x] **api-hooks-ai.php:** Implementado nuevo Prompt Maestro de neuro-marketing y copywriting persuasivo con salidas JSON por plataforma.
- [x] **api-guion.php:** Migrado a Gemini 100% nativo con formato cinematográfico broadcast de 6 bloques y timecodes.
- [x] **api-escaleta.php:** Migrado a Gemini con generación de escaleta técnica, guión base y preguntas de cabina.
- [x] **api-cuecards.php:** Migrado a Gemini con tarjetas A5 de alto contraste para set de grabación.
- [x] **api-export-pdf.php:** Creado motor de impresión y guardado como PDF profesional para Guiones, Cue Cards y Escaletas.
- [x] **dashboard-pro.js:** Integrado botón de exportación PDF y modal de impresión directa.
- [x] **config.php:** Modificado para actuar como el núcleo dinámico de datos del servidor cargando credenciales mediante variables de entorno (con fallbacks estables).
- [x] **Guardar-evaluacion.php:** Vinculado al archivo de configuración central para evitar base de datos y passwords hardcodeados.

---

## 🎨 Rediseñado (Redesigned)
*Mejoras aplicadas al diseño visual, tipografía y tokens.*
- [x] **Módulo de Impresión / PDF:** Estilos aplicados para lectura en cabina (A5 apaisado) y formato guión de cine (Courier Prime / dos columnas).

---

## 🐛 Corrección de Errores (Bug Fixes)
*Errores críticos y fallas de dependencias solucionados.*
- [x] **Navegación Móvil:** Corregido el auto-cierre de los enlaces dentro del dropdown en `js/scripts.js`.
- [x] **Reactivación El Güero Bot:** Restaurado el conector de IA Gemini en `api/api-el-guero-bot.php` y sincronizado con `js/paw-agent.js`.
- [x] **Renombrado de carpeta:** Carpeta `config1` renombrada a `config` para habilitar las dependencias require PHP.
- [x] **Rutas relativas de API:** Corrección de `require_once` rotos de la carpeta `api/` (cambiado de `/../../config/` a `/../config/`).
- [x] **Doble salida JSON:** Reparado el bug de doble impresión JSON y variables indefinidas en `api-invitados-save.php`.
- [x] **Helpers Indefinidos:** Declaración de las funciones globales `call_dify_api()`, `log_conversation()`, `sanitize_input()` y `json_response()` que estaban ausentes.
- [x] **Cue Cards 400 Bad Request:** Se corrigió el envío de arreglos de tarjetas vacíos en el frontend y se flexibilizó la validación en `api-cuecards.php`.

---

## ✅ Hecho (Done)
*Hitos completados y validados.*
- [x] Estructura inicial del servidor restaurada y en funcionamiento.
- [x] Conexión de base de datos MySQL (PDO) y llamadas cURL a Dify centralizadas en `config.php`.
- [x] Repositorio local sincronizado con el origen en GitHub (rama `main`).
