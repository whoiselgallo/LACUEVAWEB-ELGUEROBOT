# Ecosistema Digital - La Cueva del Güero (v2.0.2)

Bienvenido al repositorio central de **La Cueva del Güero**, un ecosistema web interactivo y panel administrativo PRO para la preproducción, postproducción y promoción del podcast más chido de Mexicali. 

Este sistema integra automatizaciones inteligentes, edición de video multimedia, retoque gráfico, personajes interactivos y conectores en la nube, todo desplegado sobre **Google Cloud Platform** y potenciado por los modelos de lenguaje de **Google Gemini API**.

---

## 🚀 Arquitectura y Tecnologías Clave

* **Frontend:** SPA responsivo construido en HTML5, CSS3 neón personalizado (estilo Cyberpunk), Vanilla JS, FontAwesome y Google Fonts (*Outfit, Permanent Marker, Luckiest Guy, Architects Daughter*).
* **Backend de APIs:** Microservicios en PHP 8.x que gestionan la lógica intermedia, bases de datos y la orquestación con la IA.
* **Base de Datos:** PostgreSQL administrado sobre **Google Cloud SQL** en alta velocidad con IP Privada redundante ( Iowa - `us-central1` ).
* **Motor de Inteligencia Artificial:** Integración nativa con **Google AI Studio (Gemini 3 Flash Preview & Imagen 3)** con rotación automática de claves de API.
* **Procesamiento de Video/Audio:** Pipeline de postproducción integrado que simula y orquesta tareas locales mediante **FFmpeg**, **faster-whisper (Large-v3)**, **Auto-Editor** y **Demucs**.
* **Motor de Avatares (Avatar Engine):** Servidor independiente en **Express.js (Node.js)** conectado a servicios GPU en la nube para generación y segmentación de personajes.

---

## 🛠️ Estructura del Proyecto

```text
├── api/                             # Endpoints y microservicios backend PHP
│   ├── api-el-guero-bot.php         # Conector del Chatbot Paw Agent con Gemini API
│   ├── api-avatar-engine.php        # API de Imagen 3 para renderizar ilustraciones
│   ├── api-video-process.php        # Pipeline de edición FFmpeg, Whisper y Demucs
│   ├── api-db-test.php              # Tester de latencia y estado de base de datos
│   └── api-youtube-latest.php       # Scraper y caché del último video de YouTube
├── avatar-engine/                   # Capa del generador de avatares (Node.js/Next.js)
├── config/                          # Configuraciones del sistema
│   └── config.php                   # Constantes globales y rotación de claves
├── css/                             # Estilos neón cyberpunk globales
├── js/                              # Lógica de interacción en el cliente
│   ├── editor-canva.js              # Canvas multipista (Photoshop/Canva alternativo)
│   ├── video-editor.js              # Consola de postproducción interactiva en tiempo real
│   ├── paw-agent.js                 # Lógica perruna y widget de conversión
│   └── dashboard-pro.js             # Controlador maestro del panel de administración
├── dashboard/                       # Vistas administrativas del panel PRO
│   └── index.php                    # Dashboard unificado (Episodios, Blog, Canva, Mesa de Trabajo)
├── db_init.php                      # Script de inicialización de tablas de base de datos
├── index.html                       # Página web pública y promoción del podcast
├── .env                             # Variables de entorno locales (Ignorado en git)
└── README.md                        # Este archivo
```

---

## 🔑 Rotación Inteligente de Claves Gemini API

Para evitar cuellos de botella por límite de solicitudes (*Rate Limits*) en la capa gratuita de Google AI Studio, implementamos un algoritmo de balanceo y rotación aleatoria en `config/config.php`:

```php
function get_gemini_api_key() {
    $keysStr = getEnvVar('GEMINI_API_KEYS') ?: getEnvVar('GEMINI_API_KEY');
    // ...
    $keys = explode(',', $keysStr);
    $randomIndex = array_rand($keys);
    return $keys[$randomIndex]; // Retorna una clave aleatoria en cada llamada
}
```

---

## 🌟 Módulos Destacados

### 1. Mesa de Trabajo PRO
* **Test de Base de Datos:** Consulta en vivo la latencia a Google Cloud SQL y audita el número de registros en cada tabla.
* **Simulador de Pagos Stripe:** Permite realizar pruebas de suscripciones VIP seguras mediante generación de tokens (`pk_test_...`) y cobro virtual.
* **Auditoría de Conversión:** Calculadora dinámica de leads basada en tráfico y CTR que exporta de forma instantánea planillas CSV.
* **Kanban de Socios:** Tablero colaborativo para el Güero y el Junior con persistencia local.

### 2. Editor Canva PRO (Diseño Cyberpunk)
* **Gestión de Capas:** Inserta textos neón con las tipografías urbanas oficiales, logos de agua de la cueva y fotos de fondo.
* **Cloud Picker (OAuth):** Sincroniza archivos directamente desde **OneDrive, Google Drive, Dropbox y TeraBox**.
* **Algoritmo Rembg:** Eliminación de fondo a un clic en tus imágenes.

### 3. Editor de Video e IA
* **Corte de Silencios (FFmpeg):** Limpieza automática de pausas incómodas.
* **Cortes J y L:** Transiciones multicámara cruzadas automáticas.
* **Masterización Spotify/YouTube:** Normalización del audio a los estándares de distribución oficial (**-14 LUFS** y **-1.0 dB True Peak**).

---

## 🚀 Guía de Despliegue en Google Cloud Platform (GCP)

### Paso 1: Configurar Base de Datos Cloud SQL
1. Crea una instancia de **Cloud SQL (PostgreSQL)** llamada `cueva-db-prod` (Edición Enterprise, 1 vCPU, 3.75 GB RAM, Zona Única).
2. Crea una base de datos llamada `neondb`.
3. En la pestaña **Conexiones**, añade la IP del servidor web en las redes autorizadas.

### Paso 2: Crear Máquina Virtual Compute Engine
1. Crea una instancia de VM en **Compute Engine** con Ubuntu 22.04 LTS (tipo `e2-micro` o `e2-small`) en la misma región que tu base de datos (`us-central1`).
2. Habilita los cortafuegos para permitir tráfico **HTTP** y **HTTPS**.
3. Conéctate vía SSH e instala dependencias:
   ```bash
   sudo apt update && sudo apt upgrade -y && sudo apt install -y apache2 php libapache2-mod-php php-pdo php-pgsql php-curl curl git
   ```
4. Clona este repositorio en `/var/www/html/` y configura el archivo `.env` apuntando a tu nueva IP de Cloud SQL.
5. Inicializa las tablas ingresando a `http://TU_IP/db_init.php`.
6. Configura el certificado SSL con Certbot para tu dominio `https://lacuevadelguero.com`.
