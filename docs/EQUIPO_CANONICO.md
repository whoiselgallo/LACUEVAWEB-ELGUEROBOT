# 🐺 EQUIPO CANÓNICO Y ROLES OFICIALES — LA CUEVA DEL GÜERO PODCAST

Este documento establece la estructura oficial e inmutable del equipo de **La Cueva del Güero Podcast** (Mexicali, Baja California) para todas las referencias pasadas, presentes y futuras en el código, guiones, escaletas, cue cards, retos, prompts de inteligencia artificial y metadatos de producción.

---

## 🐾 PERSONAJE PRINCIPAL & IDENTIDAD DE MARCA
### **"El Güero" (El Perro)**
* **Rol:** Personaje Principal, Imagen Central, Mascota Oficial e Inspiración del Proyecto.
* **Presencia:**
  * Logotipo oficial, isotipo y banner del canal.
  * Mascota e identidad del widget conversacional inteligente (**Paw Agent / El Güero Bot**).
  * Imagen y espíritu presente en el set de grabación en Mexicali.
  * Simboliza la lealtad, la manada, la calle y el corazón del show.

---

## 🎙️ PILARES DEL EQUIPO HUMANO Y FUNCIONES EJECUTIVAS

### 1. **Ariel Higuera "El Junior"**
* **Cargos:** CEO y Host / Conductor Principal de *La Cueva del Güero*.
* **Responsabilidades y Tareas:**
  * Rostro y voz principal frente a los micrófonos y cámaras.
  * Conducción de entrevistas, modulación del ritmo de la charla y conexión humana con los invitados.
  * Lectura y ejecución de las **Cue Cards** de cabina y retos interactivos.
  * Representación corporativa y alianzas estratégicas del podcast.

---

### 2. **Maria Elena Anguiano "La Mary"**
* **Cargos:** Socia Ángel del Proyecto y Administradora de Finanzas de *La Cueva del Güero*.
* **Responsabilidades y Tareas:**
  * Respaldo de inversión, capitalización y finanzas del show.
  * Administración de presupuestos de set, equipo técnico y producción de capítulos.
  * Supervisión y cierre de contratos de patrocinios comerciales y menciones de marca dentro del show.
  * Validadora financiera de los planes de expansión y eventos en vivo.

---

### 3. **Javier Gallardo "El Gallo"**
* **Cargos:** Socio Intelectual, Director Creativo y Productor Ejecutivo de *La Cueva del Güero*.
* **Responsabilidades y Tareas:**
  * Arquitectura narrativa, curaduría de invitados y diseño de los arcos dramáticos (storytelling).
  * Dirección de piso y control de cabina durante los rodajes (switcheo de cámaras, timecodes y audio).
  * Creación y orquestación de dinámicas, preguntas punzantes y **retos interactivos de cabina**.
  * Supervisión de postproducción, masterización (-14 LUFS) y estrategia de viralidad en redes sociales (TikTok, Shorts, Reels).

---

## 🎬 APLICACIÓN DIRECTA EN LOS DOCUMENTOS DE PRODUCCIÓN

1. **Guiones Broadcast (`/api/api-guion.php`):**
   * Diálogos liderados por **Ariel Higuera "El Junior"**.
   * Acotaciones de cabina y dirección técnica a cargo de **Javier Gallardo "El Gallo"**.
   * Menciones al espíritu y mascota **"El Güero"**.
   * Bloque comercial y menciones de patrocinadores coordinadas con **Maria Elena Anguiano "La Mary"**.

2. **Cue Cards de Cabina (`/api/api-cuecards.php`):**
   * Redactadas en formato A5 de alto contraste dirigidas exclusivamente a **Ariel "El Junior"** para lectura en tablet.
   * Notas de piso firmadas por **"El Gallo"** indicando cuándo presionar o guardar silencio.

3. **Retos y Dinámicas (`/api/api-retos-ai.php`):**
   * Diseñados bajo la visión creativa de **Javier Gallardo "El Gallo"**.
   * Ejecutados en vivo entre el invitado y **Ariel "El Junior"**.
   * Premios o castigos que involucren anécdotas de la manada de **"El Güero"** o multas del fondo de **"La Mary"**.

4. **El Güero Bot (`/api/api-el-guero-bot.php` y `paw-agent.js`):**
   * El bot habla en primera persona como **"El Güero" el perro** (*"mueve la colita"*).
   * Reconoce y presenta a sus tres humanos: **El Junior** (su conductor y CEO), **La Mary** (quien administra los dineros y patrocinios) y **El Gallo** (su director creativo y productor).
