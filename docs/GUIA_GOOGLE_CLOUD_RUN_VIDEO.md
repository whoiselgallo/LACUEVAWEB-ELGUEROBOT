# 🎬 GUÍA COMPLETA: CONFIGURACIÓN DE GOOGLE CLOUD RUN JOBS PARA EDICIÓN DE VIDEO
## La Cueva del Güero Podcast (Episodios de 1-2 Horas y Clips Virales 9:16)

Esta guía te acompaña paso a paso para configurar tu entorno en **Google Cloud Platform (GCP)** y enlazarlo con el **Editor de Video PRO** de La Cueva del Güero.

---

### 📋 REQUISITOS PREVIOS
1. Una cuenta de Google (Gmail o Workspace).
2. Tarjeta de crédito/débito para activar la facturación en Google Cloud (Google te regala **$300 USD de crédito gratuito** durante los primeros 90 días y nunca cobrará automáticamente al terminar el periodo de prueba sin tu permiso).
3. Google Cloud CLI (`gcloud`) instalado en tu computadora (o puedes usar directamente la consola web **Cloud Shell** desde el navegador sin instalar nada).

---

### PASO 1: CREAR EL PROYECTO EN GOOGLE CLOUD
1. Ingresa a la consola: [https://console.cloud.google.com](https://console.cloud.google.com)
2. En la barra superior, haz clic en el selector de proyectos y luego en **"Nuevo Proyecto"**.
3. Asigna un nombre a tu proyecto:
   * **Nombre del proyecto**: `cueva-podcast-pro`
   * Anota tu **ID de proyecto** (por ejemplo: `cueva-podcast-pro-XXXXXX`).
4. Ve a la sección **Facturación (Billing)** y vincula una cuenta de facturación para habilitar las APIs.

---

### PASO 2: HABILITAR LAS APIS EN GCP
Abre la terminal web **Cloud Shell** (ícono de terminal `>_` en la esquina superior derecha de la consola de Google Cloud) y ejecuta el siguiente comando para activar todas las APIs con un solo clic:

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

Ejecuta en Cloud Shell (reemplaza `cueva-podcast-pro` por el ID de tu proyecto si difiere):
```bash
# 1. Bucket para archivos de entrada (videos sin procesar)
gcloud storage buckets create gs://cueva-raw-videos --location=us-central1 --uniform-bucket-level-access

# 2. Bucket para salidas renderizadas (videos listos para descargar o publicar)
gcloud storage buckets create gs://cueva-processed-videos --location=us-central1 --uniform-bucket-level-access
```

#### Configurar CORS en el bucket de entrada (para permitir subidas directas desde la web):
Crea un archivo temporal `cors.json`:
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
Esta cuenta permite que el servidor web de La Cueva despache trabajos a Cloud Run y firme URLs de descarga.

1. **Crear la cuenta de servicio**:
```bash
gcloud iam service-accounts create cueva-video-sa \
    --description="Cuenta de servicio para el worker de video de La Cueva" \
    --display-name="cueva-video-sa"
```

2. **Asignarle los permisos necesarios**:
```bash
# Permiso para gestionar Cloud Run Jobs
gcloud projects add-iam-policy-binding $(gcloud config get-value project) \
    --member="serviceAccount:cueva-video-sa@$(gcloud config get-value project).iam.gserviceaccount.com" \
    --role="roles/run.admin"

# Permiso para leer y escribir en Cloud Storage
gcloud projects add-iam-policy-binding $(gcloud config get-value project) \
    --member="serviceAccount:cueva-video-sa@$(gcloud config get-value project).iam.gserviceaccount.com" \
    --role="roles/storage.admin"

# Permiso de invocación de cuentas de servicio
gcloud projects add-iam-policy-binding $(gcloud config get-value project) \
    --member="serviceAccount:cueva-video-sa@$(gcloud config get-value project).iam.gserviceaccount.com" \
    --role="roles/iam.serviceAccountUser"
```

3. **Descargar la clave privada (`gcp-key.json`)**:
```bash
gcloud iam service-accounts keys create gcp-key.json \
    --iam-account=cueva-video-sa@$(gcloud config get-value project).iam.gserviceaccount.com
```

4. **Instalar la clave en tu proyecto**:
   * Descarga el archivo `gcp-key.json` generado en Cloud Shell.
   * Colócalo en la carpeta local de tu proyecto:
     `t:\LACUEVAWEB+ELGUEROBOT\config\gcp-key.json`
   *(Nota: Este archivo ya se encuentra protegido por `.gitignore` para no exponerse en GitHub).*

---

### PASO 5: CONSTRUIR Y DESPLEGAR EL CONTENEDOR WORKER EN CLOUD RUN JOBS

1. **Crear el repositorio de imágenes Docker en Artifact Registry**:
```bash
gcloud artifacts repositories create cueva-repo \
    --repository-format=docker \
    --location=us-central1 \
    --description="Repositorio de Docker de La Cueva del Güero"
```

2. **Compilar y subir la imagen Docker usando Cloud Build**:
   Sube la carpeta `docker/video-worker/` a Cloud Shell y ejecuta:
```bash
PROJECT_ID=$(gcloud config get-value project)

gcloud builds submit \
    --tag us-central1-docker.pkg.dev/$PROJECT_ID/cueva-repo/cueva-video-worker:latest \
    ./docker/video-worker
```

3. **Crear el Cloud Run Job configurado para episodios largos (2 horas) y alta potencia**:
```bash
PROJECT_ID=$(gcloud config get-value project)

gcloud run jobs create cueva-video-worker \
    --image us-central1-docker.pkg.dev/$PROJECT_ID/cueva-repo/cueva-video-worker:latest \
    --region us-central1 \
    --tasks 1 \
    --max-retries 1 \
    --task-timeout 7200s \
    --cpu 4 \
    --memory 16Gi \
    --set-env-vars GEMINI_API_KEY="TU_CLAVE_DE_GEMINI_AQUI"
```

> [!TIP]
> **¿Por qué 7200s (2 horas), 4 CPUs y 16 GB de RAM?**
> Esta configuración permite que episodios enteros de podcast en alta definición (1080p a 60 FPS) se codifiquen a alta velocidad mediante FFmpeg multihilo, mientras que clips cortos de 1 a 5 minutos para TikTok/Reels se renderizan en menos de 30 a 60 segundos. Recuerda que con Cloud Run Jobs **solo pagas por los segundos exactos en que la máquina esté procesando**.

---

### PASO 6: CONFIGURAR TU ARCHIVO `.env` EN LA CUEVA
Agrega o actualiza estas líneas en tu archivo `.env`:

```env
# --- GOOGLE CLOUD PLATFORM (VIDEO PROCESSOR) ---
GCP_PROJECT_ID=cueva-podcast-pro
GCP_REGION=us-central1
GCP_RAW_BUCKET=cueva-raw-videos
GCP_PROCESSED_BUCKET=cueva-processed-videos
GCP_CLOUD_RUN_JOB=cueva-video-worker
GCP_KEY_PATH=config/gcp-key.json
```

---

### PASO 7: CÓMO USAR EL SISTEMA DESDE EL DASHBOARD PRO
1. Entra a `dashboard/index.php` en tu navegador.
2. Abre la pestaña **Editor de Video PRO**.
3. Carga tu clip de video o episodio mediante el botón **"Cargar Video"** o impórtalo desde **Google Drive / Dropbox**.
4. Haz clic en cualquiera de las herramientas profesionales:
   * ✂️ **Cortar Silencios**: Ejecuta recorte de pausas mayores a 1.0s.
   * 🎙️ **Remover Muletillas**: Elimina frases repetitivas (`"eh"`, `"este"`, `"pues"`).
   * 🎵 **Normalizar Sonoridad (-14 LUFS)**: Iguala los niveles al estándar oficial de Spotify y YouTube.
   * 💬 **Subtítulos Auto IA**: Envía la pista de audio a Gemini Flash y quema subtítulos estilizados en Neón.
   * 📤 **Exportar Presets**: Convierte tu contenido a formato vertical **TikTok (9:16)** o **YouTube HD (16:9)**.
5. Observa en tiempo real la **Terminal Neón de FFmpeg & Whisper IA** con los logs del contenedor y descarga tu video terminado al finalizar.
