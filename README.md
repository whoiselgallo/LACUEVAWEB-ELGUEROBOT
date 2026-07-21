# 🎙️ La Cueva del Güero - Plataforma Web & Güero Bot PRO

¡Bienvenidos a la plataforma digital oficial de **La Cueva del Güero**! Este proyecto es un ecosistema interactivo y portal de producción inteligente que une la landing page promocional del podcast con un chatbot de Inteligencia Artificial (Dify.ai) y una central de producción automatizada para el equipo del show.

---

## 🚀 Arquitectura y Tecnologías
La plataforma está diseñada con una arquitectura ligera de alto rendimiento y bajo costo operativo:

* **Frontend:** HTML5 semántico, Vanilla CSS3 personalizado (diseño responsivo, estética de Neón Urbano, Glassmorphism, y efectos magnéticos 3D), y Vanilla Javascript (ES6+).
* **Backend:** PHP 8.2 (Servidor Web Apache con soporte Docker).
* **Base de Datos:** PostgreSQL Serverless en **Neon.tech** con soporte SSL para almacenar historiales, conversaciones y assets.
* **Inteligencia Artificial:** Orquestado con **Dify.ai** para el motor conversacional y generación de copys creativos.
* **Infraestructura de Despliegue:** Dockerizado y alojado de forma continua en **Render.com**.

---

## 📂 Directorio del Proyecto y Componentes

A continuación se detalla la estructura completa de archivos y el propósito de cada sección:

### 1. Portal Público (Landing Page)
* **[index.html](file:///T:/LACUEVAWEB+ELGUEROBOT/index.html):** Landing page principal con información del show, reproductor de episodios, sección del blog, formulario de feedback y la huella digital interactiva (widget del bot).
* **[js/scripts.js](file:///T:/LACUEVAWEB+ELGUEROBOT/js/scripts.js):** Lógica general del sitio web (navegación fluida, efectos de foco neón y control de despliegue del menú móvil corregido).
* **[css/styles.css](file:///T:/LACUEVAWEB+ELGUEROBOT/css/styles.css):** Estilos y variables CSS principales del ecosistema neón oscuro.

### 🐾 El Widget del Chatbot (Güero Bot Paw)
* **[js/paw-agent.js](file:///T:/LACUEVAWEB+ELGUEROBOT/js/paw-agent.js):** Controla el widget flotante con forma de huella digital de neón. Gestiona los estados del chat y envía las peticiones del usuario a la API de Dify de forma asíncrona.
* **[css/paw-agent.css](file:///T:/LACUEVAWEB+ELGUEROBOT/css/paw-agent.css):** Estilos visuales del widget flotante de chat.

### 📝 Ficha de Onboarding del Invitado
* **[storytelling-invitado.html](file:///T:/LACUEVAWEB+ELGUEROBOT/storytelling-invitado.html):** Formulario interactivo de 10 preguntas clave de storytelling para el invitado.
  * *Seguridad:* Esta página cuenta con un script de bloqueo. Si el usuario intenta entrar de forma directa desde la URL sin identificarse como invitado conversando con el Güero Bot primero, el sistema denegará el acceso.
* **[js/guero-pro.js](file:///T:/LACUEVAWEB+ELGUEROBOT/js/guero-pro.js):** Controlador que envía los datos del formulario de invitado al generador de Dify y los guarda en Neon.

### 🎛️ 2. Portal de Producción Privado (Admin Dashboard)
Ubicado en la carpeta `/dashboard/`:
* **[dashboard/login.php](file:///T:/LACUEVAWEB+ELGUEROBOT/dashboard/login.php):** Pantalla de inicio de sesión segura con diseño neón de glassmorphism. Autentica las sesiones de administrador.
* **[dashboard/logout.php](file:///T:/LACUEVAWEB+ELGUEROBOT/dashboard/logout.php):** Destruye la sesión del servidor de forma segura.
* **[dashboard/index.php](file:///T:/LACUEVAWEB+ELGUEROBOT/dashboard/index.php):** Interfaz del portal unificado de administración que implementa una navegación lateral (sidebar) hacia 4 áreas:
  1. **Episodios y Fichas:** Listado lateral de invitados y visualizador del material generado. Permite editar manualmente el contenido, imprimir las Cue Cards físicas o descargarlo en `.txt`.
  2. **Gestor de Blog:** Conversor inteligente que procesa archivos PDF con `PDF.js` y extrae su texto para convertirlo en borradores editables de blog y publicarlos con un clic.
  3. **Generador de Hooks:** Crea al instante ganchos de alta retención para 6 redes sociales distintas presentados en tarjetas magnéticas 3D de neón.
  4. **Editor de Video:** Panel multimedia que simula el proceso de carga de archivos (con barra de progreso neón al 100%) y monta el clip local en el reproductor HTML5 usando buffers del navegador para su edición.
* **[js/dashboard-pro.js](file:///T:/LACUEVAWEB+ELGUEROBOT/js/dashboard-pro.js):** Lógica del portal que controla el switch de pestañas, llamados a base de datos, descargas de archivos, impresión, extracción de PDF y generación de copys de hooks.

### ⚙️ 3. Configuración y APIs (Backend PHP)
* **[config/config.php](file:///T:/LACUEVAWEB+ELGUEROBOT/config/config.php):** Archivo central del sistema. Parsea dinámicamente las variables de entorno o el archivo local `.env` para establecer la conexión PDO segura a PostgreSQL en Neon.tech y definir las constantes del Dify API Key.
* **[db_init.php](file:///T:/LACUEVAWEB+ELGUEROBOT/db_init.php):** Inicializador de base de datos. Crea y configura las tablas e índices únicos requeridos en Neon.tech.
* **[api/api-el-guero-bot.php](file:///T:/LACUEVAWEB+ELGUEROBOT/api/api-el-guero-bot.php):** Endpoint que procesa el chat del widget flotante. Administra la memoria del bot, inyecta el contexto de visita y conecta con el API de Dify.
* **[api/api-guero-knowledge.php](file:///T:/LACUEVAWEB+ELGUEROBOT/api/api-guero-knowledge.php):** Endpoint que conecta el dashboard con la base de datos Neon. Soporta listados, lecturas por ID, actualizaciones manuales (`update`) y mezcla (`merge`) inteligente de datos para evitar pérdida de información.

---

## 🚦 Guía de Uso del Ecosistema

### A. Para el Visitante Común
1. Navega por la landing page oficial, lee los artículos publicados en el blog y deja tu evaluación de comentarios en la sección correspondiente.
2. Abre el Güero Bot (huella neón) en la esquina inferior derecha para hacerle cualquier pregunta sobre los episodios, la vibra del podcast o chatear de forma informal en tono norteño.

### B. Para el Invitado (Flujo Onboarding)
1. El invitado abre el chat del Güero Bot en la página principal y escribe algo como: *"Soy invitado al programa y quiero llenar mi ficha"*.
2. El Güero Bot de Dify validará su identidad conversacionalmente, activará el permiso local en su navegador (`paw_guest_access = true`) y le responderá con el link del formulario: `/storytelling-invitado.html`.
3. Al hacer clic, el invitado podrá rellenar sus 10 respuestas y presionar **Enviar**. Esto genera de fondo su Escaleta, Diálogos de Guión y Cue Cards preliminares, guardándolos en la base de datos de Neon.tech.

### C. Para el Productor / Administrador
1. Accede a `/dashboard/index.php`. El sistema te pedirá iniciar sesión.
2. Ingresa con tus credenciales seguras.
3. **Gestión de Shows:** En *Episodios y Fichas*, haz clic en un invitado. Puedes leer su guión, presionar **Editar** para hacer ajustes manuales, descargar el contenido para tus dispositivos o presionar **Imprimir** para obtener las Cue Cards físicas impresas para el conductor en cabina.
4. **Conversión de PDF:** En *Gestor de Blog*, sube el PDF de la ficha de producción, procesa el texto e insértalo directo en el blog de la landing page.
5. **Redacción de Hooks:** En *Generador de Hooks*, pon la frase clave de la plática, presiona generar y copia los copys formateados para Instagram, TikTok, YouTube Shorts, etc.
6. **Edición:** En *Editor de Video*, sube el clip grabado de la entrevista para previsualizarlo y marcar los cortes de clips en el reproductor.

---

## 🛠️ Instalación y Configuración Local
1. Clona el repositorio en tu servidor local.
2. Crea un archivo `.env` en la raíz del proyecto con las siguientes credenciales:
   ```env
   DB_HOST=tu_host_neon_tech
   DB_PORT=5432
   DB_NAME=neondb
   DB_USER=neondb_owner
   DB_PASS=tu_contraseña_neon
   DIFY_API_KEY=tu_dify_api_key
   ADMIN_USER=admin
   ADMIN_PASS=contraseña_dashboard
   ```
3. Inicia tu servidor web Apache local con soporte PHP.
4. Ejecuta por única vez la URL `/db_init.php` desde tu navegador para crear las tablas correspondientes en Neon PostgreSQL.
5. ¡Listo! Todo el sistema estará operando localmente sincronizado con la nube.
