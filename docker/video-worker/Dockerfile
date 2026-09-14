# ==============================================================================
# 🎬 LA CUEVA DEL GÜERO - VIDEO PROCESSING WORKER (CLOUD RUN JOBS)
# Contenedor optimizado para renderizado batch de episodios largos y shorts 9:16
# ==============================================================================

FROM python:3.11-slim-bookworm

# Evitar prompts interactivos durante apt-get
ENV DEBIAN_FRONTEND=noninteractive
ENV PYTHONUNBUFFERED=1

# Instalar dependencias del sistema y FFmpeg completo con librerías de códecs
RUN apt-get update && apt-get install -y --no-install-recommends \
    ffmpeg \
    fonts-liberation \
    fonts-dejavu-core \
    libsm6 \
    libxext6 \
    curl \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Crear directorio de trabajo
WORKDIR /app

# Copiar archivo de requerimientos e instalar paquetes de Python
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copiar código del worker y recursos (fuentes/plantillas)
COPY worker.py .
COPY fonts/ ./fonts/

# Punto de entrada para Cloud Run Jobs
ENTRYPOINT ["python", "worker.py"]
