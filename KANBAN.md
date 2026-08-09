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
- [ ] Rediseño visual del sitio bajo las pautas del Design System Neón.
- [ ] Conexión del inicio de sesión (SSO) unificado con la Landing de TSolutions.
- [ ] Configuración del API Gateway de estadísticas en la landing central.
- [ ] Optimización de las imágenes de fondo gigantes de la carpeta `images/`.

---

## ⚡ En Progreso (In Progress)
*Acciones actualmente en desarrollo activo.*
- [ ] Corregir la navegación móvil del header en `js/scripts.js` (clases `.nav-toggle` y `.nav-links`).
- [ ] Reemplazar la simulación de `setTimeout` del chatbot en `js/paw-agent.js` por una llamada `fetch()` real a `/api/api-el-guero-bot.php`.

---

## 🔍 En Resumen (Under Review / Verification)
*Tareas completadas que están siendo verificadas en producción en Hostinger.*
- [ ] Verificar la llamada real de IA del generador de guiones en [api-guion.php](file:///t:/LACUEVAWEB+ELGUEROBOT/api/api-guion.php).
- [ ] Probar el generador de Cue Cards en [guero-pro.js](file:///t:/LACUEVAWEB+ELGUEROBOT/js/guero-pro.js) enviando el texto real para impresión en PDF.

---

## 🛠️ Modificado (Modified)
*Componentes modificados estructuralmente durante el desarrollo para dar soporte a nuevas funciones.*
- [x] **config.php:** Modificado para actuar como el núcleo dinámico de datos del servidor cargando credenciales mediante variables de entorno (con fallbacks estables).
- [x] **Guardar-evaluacion.php:** Vinculado al archivo de configuración central para evitar base de datos y passwords hardcodeados.

---

## 🎨 Rediseñado (Redesigned)
*Mejoras aplicadas al diseño visual, tipografía y tokens.*
- [ ] *Pendiente por comenzar en la Fase 3 del roadmap (Design System).*

---

## 🐛 Corrección de Errores (Bug Fixes)
*Errores críticos y fallas de dependencias solucionados.*
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
