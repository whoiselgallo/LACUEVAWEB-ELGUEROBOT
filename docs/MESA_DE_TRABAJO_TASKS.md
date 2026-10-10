# 🐺 MESA DE TRABAJO — PLAN MAESTRO DE TASKS Y MEJORAS
**Proyecto:** *La Cueva del Güero (Podcast) & El Güero Bot*  
**Ubicación:** Mexicali, Baja California  
**Alineación Canónica:** [EQUIPO_CANONICO.md](file:///t:/LACUEVAWEB+ELGUEROBOT/docs/EQUIPO_CANONICO.md) & [KANBAN.md](file:///t:/LACUEVAWEB+ELGUEROBOT/KANBAN.md)

---

## 👥 MATRIZ DE RESPONSABLES Y ROLES CANÓNICOS
* **Tú (Antigravity):** Arquitecto de Software, Full-Stack Dev, DevOps e Integraciones IA.
* **El Gallo (Javier Gallardo):** Director Creativo, Productor Ejecutivo y Dirección de Piso/Cabina.
* **El Junior (Ariel Higuera):** CEO y Host / Conductor Principal.
* **La Mary (Maria Elena Anguiano):** Socia Ángel, Administración de Finanzas, Presupuestos y Patrocinios.
* **El Güero (Mascota Oficial):** Identidad Central de Marca, Espíritu del Set y Agente Virtual (Paw Agent).

---

## 📋 BLOQUE A: IMPLEMENTACIONES PENDIENTES (DESARROLLO CORE)

### 1. Pruebas de Carga y Estrés Concurrentes en el Pipeline de Video
* **Área:** `Optimización` / `Producción`
* **Responsables:** **Tú (Antigravity)** & **El Gallo**
* **Objetivo:** Asegurar que el pipeline distribuido soporte múltiples renders simultáneos sin agotar memoria o bloquear CPU.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 1.1:** Configurar banco de pruebas de estrés local y en Cloud Run.
    - [ ] *Sub-acción 1.1.1:* Crear script de benchmarking con FFmpeg y separación de pistas Demucs para 3 a 5 episodios en paralelo.
    - [ ] *Sub-acción 1.1.2:* Monitorear picos de consumo de RAM, I/O en disco y latencia de transcodificación.
  - [ ] **Acción 1.2:** Ajustar límites y autoescalado en Google Cloud Run.
    - [ ] *Sub-acción 1.2.1:* Configurar cuotas de concurrencia y límites de tiempo de ejecución (timeout hasta 60 min).
    - [ ] *Sub-acción 1.2.2:* Verificar persistencia y entrega de enlaces de descarga en Cloud Storage / CDN.

---

### 2. Integración Directa con la API de Publicación de Spotify for Podcasters
* **Área:** `Actualización` / `Producción`
* **Responsables:** **Tú (Antigravity)**, **El Gallo** & **El Junior**
* **Objetivo:** Publicar automáticamente el episodio con un solo clic una vez validada la postproducción.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 2.1:** Conexión y protocolo de autenticación con Spotify Developer API.
    - [ ] *Sub-acción 2.1.1:* Tramitar Client ID y Client Secret en el portal de desarrolladores y resguardar en variables `.env`.
    - [ ] *Sub-acción 2.1.2:* Implementar rotación segura de tokens OAuth y almacenamiento del `refresh_token`.
  - [ ] **Acción 2.2:** Creación del endpoint `/api/api-spotify-publish.php`.
    - [ ] *Sub-acción 2.2.1:* Enviar metadatos completos: título del show, descripción optimizada con IA, cover art y archivo masterizado (-14 LUFS).
    - [ ] *Sub-acción 2.2.2:* Automatizar el cambio de estado a Fase 5 ("Publicado") en el portal de Tracking al recibir confirmación 200 OK de Spotify.

---

### 3. Monitoreo de Cuotas y Rendimiento del Balanceador de Claves Gemini API
* **Área:** `Optimización` / `Actualización`
* **Responsables:** **Tú (Antigravity)**
* **Objetivo:** Prevenir interrupciones por límites de tasa (429 Too Many Requests) en llamadas pesadas de generación.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 3.1:** Sistema de telemetría de uso de claves en `config.php`.
    - [ ] *Sub-acción 3.1.1:* Registro estructurado en `/logs/gemini_usage.log` de latencia, tokens de entrada/salida y fallos por llave.
    - [ ] *Sub-acción 3.1.2:* Estado de salud y notificación en el panel de administración cuando una clave entra en enfriamiento.
  - [ ] **Acción 3.2:** Optimización de prompts maestros.
    - [ ] *Sub-acción 3.2.1:* Condensar prompts de guiones broadcast sin sacrificar estilo narrativo ni estructura de 6 bloques.

---

### 4. Módulo de Pagos y Suscripción Recurrente con Stripe para Club VIP
* **Área:** `Finanzas` / `Actualización`
* **Responsables:** **La Mary**, **Tú (Antigravity)** & **El Junior**
* **Objetivo:** Habilitar monetización de la comunidad mediante membresías exclusivas.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 4.1:** Definición del modelo comercial y términos del Club VIP.
    - [ ] *Sub-acción 4.1.1:* Establecer tarifas (mensual/anual), preventas y contenido sin censura (**La Mary** & **El Junior**).
    - [ ] *Sub-acción 4.1.2:* Redacción de términos de servicio y políticas de cancelación (**La Mary**).
  - [ ] **Acción 4.2:** Integración de Stripe Checkout y Webhooks.
    - [ ] *Sub-acción 4.2.1:* Construir microservicio `/api/api-stripe-checkout.php` y handler de webhooks `/api/api-stripe-webhook.php` (**Tú**).
    - [ ] *Sub-acción 4.2.2:* Habilitar acceso dinámico a contenido VIP en el portal de miembros (**Tú**).

---

### 5. Conexión del Inicio de Sesión Unificado (SSO) con Landing TSolutions
* **Área:** `Actualización` / `Desarrollo e Infraestructura`
* **Responsables:** **Tú (Antigravity)** & **El Junior**
* **Objetivo:** Acceso unificado para el equipo y clientes corporativos entre la plataforma matriz y La Cueva.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 5.1:** Protocolo de Single Sign-On mediante JWT.
    - [ ] *Sub-acción 5.1.1:* Establecer esquema de tokens seguros con expiración controlada y handshake cruzado.
    - [ ] *Sub-acción 5.1.2:* Homologar roles y permisos de administrador, editor y productor entre ambos sistemas.

---

## 💡 BLOQUE B: MEJORAS RECOMENDADAS (PLAN DE ACCIÓN)

### 6. Automatización de Plantillas de Subtítulos Animados y Shorts 9:16
* **Área:** `Edición` / `Diseño` / `Producción`
* **Responsables:** **El Gallo**, **Tú (Antigravity)** & **El Güero**
* **Objetivo:** Elevar el engagement y retención en plataformas de video corto (TikTok, Reels, Shorts).
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 6.1:** Presets estilizados de subtitulado en `/api/api-video-clips.php`.
    - [ ] *Sub-acción 6.1.1:* Diseñar tipografía destacada con trazos Neón Cyan (`#00FFFF`) y Magenta (`#FF00FF`).
    - [ ] *Sub-acción 6.1.2:* Integrar overlay animado de la patita o avatar de "El Güero" en la esquina de cada clip (**El Güero** & **El Gallo**).
  - [ ] **Acción 6.2:** Auditoría de ganchos virales con el Prompt Maestro de Hooks.
    - [ ] *Sub-acción 6.2.1:* Calificar el hook de los primeros 3 segundos con fórmulas de neuromarketing antes de exportar (**El Gallo**).

---

### 7. Retos Interactivos de Cabina y Dinámicas de Set en Vivo
* **Área:** `Grabación` / `Pre-producción`
* **Responsables:** **El Junior**, **El Gallo** & **El Güero**
* **Objetivo:** Dinamizar la interacción espontánea con el invitado en cabina.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 7.1:** Módulo interactivo de dinámicas en Cue Cards digitales.
    - [ ] *Sub-acción 7.1.1:* Generar 3 dinámicas exclusivas por episodio: "La Pregunta del Güero", "Reto Picante" y "Verdad o Castigo" (**El Gallo**).
    - [ ] *Sub-acción 7.1.2:* Ensayo y calibración de timing con el invitado antes de encender cámaras (**El Junior**).

---

### 8. Políticas de Respaldo y Snapshot Periódico de la Base de Datos
* **Área:** `Optimización` / `Infraestructura`
* **Responsables:** **Tú (Antigravity)** & **La Mary**
* **Objetivo:** Blindar la base de datos de invitados, tracking y métricas contra pérdidas imprevistas.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 8.1:** Rutina automatizada de respaldos off-site.
    - [ ] *Sub-acción 8.1.1:* Configurar tarea cron nocturna con respaldo comprimido y cifrado hacia almacenamiento en la nube (**Tú**).
    - [ ] *Sub-acción 8.1.2:* Redactar y validar plan de continuidad de negocio y recuperación ante desastres (**La Mary** & **Tú**).

---

### 9. Gestión y Cierre de Patrocinios Comerciales en Guión Broadcast
* **Área:** `Pre-producción` / `Producción` / `Finanzas`
* **Responsables:** **La Mary**, **El Junior** & **El Gallo**
* **Objetivo:** Rentabilizar cada emisión garantizando menciones orgánicas y profesionales.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 9.1:** Coordinación y validación de menciones comerciales.
    - [ ] *Sub-acción 9.1.1:* Negociar paquetes publicitarios y validar requerimientos de marca (**La Mary**).
    - [ ] *Sub-acción 9.1.2:* Redactar el copyscript en el Bloque 4 del Guión Broadcast respetando el tono del show (**El Gallo** & **Tú**).
    - [ ] *Sub-acción 9.1.3:* Entonación y lectura natural en cabina durante la grabación (**El Junior**).

---

### 10. Banco de Assets Visuales y Miniaturas Neón con Google Imagen 3
* **Área:** `Diseño`
* **Responsables:** **El Gallo**, **El Güero** & **Tú (Antigravity)**
* **Objetivo:** Agilizar la maquetación de miniaturas de YouTube con calidad cinematográfica.
* **Desglose de Acciones y Sub-acciones:**
  - [ ] **Acción 10.1:** Generación masiva de fondos y elementos temáticos.
    - [ ] *Sub-acción 10.1.1:* Calibrar prompts con estética cueva neón, tonos cálidos de Mexicali y texturas de estudio de broadcast.
    - [ ] *Sub-acción 10.1.2:* Indexar las imágenes en el Cloud Picker de Canva PRO para ensamblaje en menos de 5 minutos por entrega.
