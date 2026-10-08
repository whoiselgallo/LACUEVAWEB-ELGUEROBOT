#!/usr/bin/env python3
"""
🎬 LA CUEVA DEL GÜERO - AI VISION WORKER (THE FORGE SEGMENTATION SERVICE)
Microservicio FastAPI para extracción de fondos con canal Alfa real (rembg / U-2-Net)
y separación de máscaras de sombras para el lienzo Fabric.js (The Darkroom).
"""

import io
import os
import base64
from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.responses import Response, JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from PIL import Image, ImageFilter, ImageOps
import numpy as np
from rembg import remove, new_session

app = FastAPI(title="La Cueva AI Vision Worker", version="2.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Inicializar sesión global de rembg para máxima velocidad
SESSION = new_session("u2net")

@app.get("/health")
def health_check():
    return {"status": "online", "service": "ai-vision-worker", "model": "u2net"}

@app.post("/segment")
async def segment_image(file: UploadFile = File(...)):
    """
    Recibe una imagen (render de Flux o foto de cámara),
    remueve el fondo y devuelve:
    - Sujeto con canal Alfa transparente real (PNG)
    - Máscara de sombra proyectada para el lienzo
    """
    try:
        contents = await file.read()
        input_image = Image.open(io.BytesIO(contents)).convert("RGBA")

        # 1. Extracción del Sujeto con rembg (Canal Alfa real)
        subject_transparent = remove(input_image, session=SESSION)

        # 2. Generar Máscara de Sombra Proyectada (Drop Shadow Mask)
        # Extraer canal alfa como máscara binaria
        alpha = subject_transparent.split()[3]
        # Crear capa de sombra negra con blur gaussiano
        shadow = Image.new("RGBA", subject_transparent.size, (0, 0, 0, 0))
        shadow_mask = ImageOps.invert(alpha)
        # Sombra suave
        shadow_blur = alpha.filter(ImageFilter.GaussianBlur(radius=15))
        shadow.putalpha(shadow_blur)

        # Convertir a Base64 para consumo directo en The Darkroom (Fabric.js)
        buf_subject = io.BytesIO()
        subject_transparent.save(buf_subject, format="PNG")
        b64_subject = base64.b64encode(buf_subject.getvalue()).decode("utf-8")

        buf_shadow = io.BytesIO()
        shadow.save(buf_shadow, format="PNG")
        b64_shadow = base64.b64encode(buf_shadow.getvalue()).decode("utf-8")

        return JSONResponse({
            "success": True,
            "subject_png_base64": f"data:image/png;base64,{b64_subject}",
            "shadow_png_base64": f"data:image/png;base64,{b64_shadow}",
            "width": subject_transparent.width,
            "height": subject_transparent.height
        })

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", 8080))
    uvicorn.run(app, host="0.0.0.0", port=port)
