# 🎬 GUÍA COMPLETA: CONFIGURACIÓN DE GOOGLE CLOUD RUN JOBS PARA EDICIÓN DE VIDEO
## Instancia Oficial: `la-cueva-del-guero` (Episodios de 1-2 Horas y Clips Virales 9:16)

Esta guía te acompaña paso a paso para configurar tu entorno en **Google Cloud Platform (GCP)** y enlazarlo con el **Editor de Video PRO** de La Cueva del Güero bajo la instancia de Cloud Run Jobs nombrada **`la-cueva-del-guero`**.

---

### 📋 REQUISITOS PREVIOS
1. Una cuenta de Google (Gmail o Google Workspace).
2. Tarjeta de crédito/débito para activar la facturación en Google Cloud (Google te otorga **$300 USD de crédito gratuito** durante los primeros 90 días y nunca cobrará automáticamente al terminar el periodo de prueba sin tu autorización explícita).
3. Google Cloud CLI (`gcloud`) en tu computadora o directamente la terminal web **Cloud Shell** en el navegador sin instalar nada.

---

### PASO 1: CREAR EL PROYECTO EN GOOGLE CLOUD
1. Ingresa a la consola: [https://console.cloud.google.com](https://console.cloud.google.com)
2. En la barra superior, haz clic en el selector de proyectos y presiona **"Nuevo Proyecto"**.
3. Asigna un nombre a tu proyecto:
   * **Nombre del proyecto**: `la-cueva-del-guero`
   * Anota tu **ID de proyecto** asignado (por ejemplo: `la-cueva-del-guero-XXXXXX`).
4. Ve a la sección **Facturación (Billing)** y vincula una cuenta de facturación activa para habilitar las APIs.

---

### PASO 2: HABILITAR LAS APIS EN GCP
Abre la terminal web **Cloud Shell** (ícono de terminal `>_` en la esquina superior derecha de la consola de Google Cloud) y ejecuta el siguiente comando para activar todas las APIs necesarias:

```bash
gcloud services enable \
    run.googleapis.com \
    storage.googleapis.com \
    artifactregistry.googleapis.com \
    cloudbuild.googleapis.com \
    iam.googleapis.com
```

---

### PASO 3: CREAR LOS BUCKETS DE CLOUD STORAGE (GCS)
Creamos los dos buckets necesarios: uno para videos crudos subidos desde el navegador y otro para los videos renderizados finales.

Ejecuta en Cloud Shell:
```bash
# 1. Bucket para archivos de entrada (videos sin procesar)
gcloud storage buckets create gs://cueva-raw-videos --location=us-central1 --uniform-bucket-level-access

# 2. Bucket para salidas renderizadas (videos listos para descargar o publicar)
gcloud storage buckets create gs://cueva-processed-videos --location=us-central1 --uniform-bucket-level-access
```

#### Configurar CORS en el bucket de entrada (para permitir subidas directas desde el navegador):
```bash
cat <<EOF > cors.json
[
  {
    "origin": ["*"],
    "method": ["GET", "POST", "PUT", "HEAD", "OPTIONS"],
    "responseHeader": ["*"],
    "maxAgeSeconds": 3600
  }
]
EOF

gcloud storage buckets update gs://cueva-raw-videos --cors-file=cors.json
```

---

### PASO 4: CREAR LA CUENTA DE SERVICIO Y GENERAR `gcp-key.json`
Esta cuenta permite que el servidor web de La Cueva despache trabajos a la instancia **`la-cueva-del-guero`** y firme URLs de descarga segura.

1. **Crear la cuenta de servicio**:
```bash
gcloud iam service-accounts create cueva-video-sa \
    --description="Cuenta de servicio para el worker de video de La Cueva" \
    --display-name="cueva-video-sa"
```

2. **Asignarle los permisos necesarios**:
```bash
PROJECT_ID=$(gcloud config get-value project)

# Permiso para gestionar y ejecutar Cloud Run Jobs
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:cueva-video-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/run.admin"

# Permiso para leer y escribir en Cloud Storage
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:cueva-video-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/storage.admin"

# Permiso de invocación de cuentas de servicio
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:cueva-video-sa@$PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/iam.serviceAccountUser"
```

3. **Descargar la clave privada (`gcp-key.json`)**:
```bash
gcloud iam service-accounts keys create gcp-key.json \
    --iam-account=cueva-video-sa@$PROJECT_ID.iam.gserviceaccount.com
```

4. **Instalar la clave en tu proyecto**:
   * Descarga el archivo `gcp-key.json` generado en Cloud Shell.
   * Colócalo en la carpeta de tu servidor web:
     `config/gcp-key.json`
   *(Nota: Este archivo está en el `.gitignore` por seguridad para que nunca se filtre a internet).*

---

### PASO 5: CONSTRUIR Y DESPLEGAR LA INSTANCIA `la-cueva-del-guero` EN CLOUD RUN JOBS

1. **Crear el repositorio de imágenes Docker en Artifact Registry**:
```bash
gcloud artifacts repositories create cueva-repo \
    --repository-format=docker \
    --location=us-central1 \
    --description="Repositorio de imágenes de La Cueva del Güero"
```

2. **Compilar y subir la imagen Docker usando Cloud Build**:
   Sube la carpeta `docker/video-worker/` a Cloud Shell y ejecuta:
```bash
PROJECT_ID=$(gcloud config get-value project)

gcloud builds submit \
    --tag us-central1-docker.pkg.dev/$PROJECT_ID/cueva-repo/la-cueva-del-guero:latest \
    ./docker/video-worker
```

3. **Crear la instancia de Cloud Run Job con el nombre `la-cueva-del-guero`**:
```bash
PROJECT_ID=$(gcloud config get-value project)

gcloud run jobs create la-cueva-del-guero \
    --image us-central1-docker.pkg.dev/$PROJECT_ID/cueva-repo/la-cueva-del-guero:latest \
    --region us-central1 \
    --tasks 1 \
    --max-retries 1 \
    --task-timeout 7200s \
    --cpu 4 \
    --memory 16Gi \
    --set-env-vars GEMINI_API_KEY="TU_CLAVE_DE_GEMINI_AQUI"
```

> [!TIP]
> **Especificaciones de la instancia `la-cueva-del-guero`:**
> * **Timeout:** 7200 segundos (2 horas continuas de renderizado garantizado sin cortes).
> * **Hardware:** 4 vCPUs y 16 GB de memoria RAM para procesamiento multihilo en FFmpeg.
> * **Serverless:** Cuando la instancia no esté procesando video, se apaga automáticamente y el costo es **$0.00**.

---

### PASO 6: CONFIGURAR TU ARCHIVO `.env` EN EL SERVIDOR
Asegúrate de que tu archivo `.env` contenga la instancia correcta:

```env
# --- GOOGLE CLOUD PLATFORM (VIDEO PROCESSOR) ---
GCP_PROJECT_ID=la-cueva-del-guero
GCP_REGION=us-central1
GCP_RAW_BUCKET=cueva-raw-videos
GCP_PROCESSED_BUCKET=cueva-processed-videos
GCP_CLOUD_RUN_JOB=la-cueva-del-guero
GCP_KEY_PATH=config/gcp-key.json
```

---

### PASO 7: OPERACIÓN DESDE EL DASHBOARD PRO
1. Entra a `dashboard/index.php` en tu navegador.
2. Ve a la pestaña **Editor de Video PRO**.
3. Carga tu episodio o clip de video.
4. Presiona cualquiera de los botones de postproducción:
   * ✂️ **Cortar Silencios**
   * 🎙️ **Remover Muletillas**
   * 🎵 **Normalizar Sonoridad (-14 LUFS Spotify/YouTube)**
   * 💬 **Subtítulos Auto IA con Gemini Flash**
   * 📤 **Exportar Presets (TikTok 9:16 / Reels / YouTube HD)**
5. La terminal de La Cueva se conectará directamente a la instancia **`la-cueva-del-guero`** en Cloud Run Jobs y mostrará el progreso en vivo hasta entregar el enlace final de descarga.
