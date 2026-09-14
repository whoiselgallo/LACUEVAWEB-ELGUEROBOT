#!/usr/bin/env python3
"""
==============================================================================
🎬 LA CUEVA DEL GÜERO - VIDEO WORKER CORE (GOOGLE CLOUD RUN JOBS)
Procesamiento real de video con FFmpeg, Gemini Flash IA y Google Cloud Storage.
==============================================================================
"""

import os
import sys
import json
import time
import shutil
import argparse
import subprocess
import tempfile
import requests
from google.cloud import storage
import google.generativeai as genai

# -----------------------------------------------------------------------------
# LOGGING & WEBHOOK NOTIFIER
# -----------------------------------------------------------------------------
def log(msg, tag="System", webhook_url=None, job_id=None):
    formatted = f"[{tag}] {msg}"
    print(formatted, flush=True)
    if webhook_url and job_id:
        try:
            requests.post(webhook_url, json={
                "job_id": job_id,
                "log": formatted,
                "tag": tag,
                "timestamp": time.time()
            }, timeout=3)
        except Exception:
            pass

# -----------------------------------------------------------------------------
# GOOGLE CLOUD STORAGE UTILS
# -----------------------------------------------------------------------------
def parse_gcs_uri(gcs_uri):
    """Convierte gs://bucket/path/to/file en (bucket, blob_name)"""
    if not gcs_uri.startswith("gs://"):
        raise ValueError(f"URI de GCS inválida: {gcs_uri}")
    parts = gcs_uri[5:].split("/", 1)
    bucket_name = parts[0]
    blob_name = parts[1] if len(parts) > 1 else ""
    return bucket_name, blob_name

def download_from_gcs(gcs_uri, local_path, webhook_url=None, job_id=None):
    bucket_name, blob_name = parse_gcs_uri(gcs_uri)
    log(f"Descargando {gcs_uri} -> {local_path}...", tag="Storage", webhook_url=webhook_url, job_id=job_id)
    client = storage.Client()
    bucket = client.bucket(bucket_name)
    blob = bucket.blob(blob_name)
    blob.download_to_filename(local_path)
    size_mb = os.path.getsize(local_path) / (1024 * 1024)
    log(f"Descarga completa ({size_mb:.2f} MB)", tag="Storage", webhook_url=webhook_url, job_id=job_id)

def upload_to_gcs(local_path, gcs_uri, content_type=None, webhook_url=None, job_id=None):
    bucket_name, blob_name = parse_gcs_uri(gcs_uri)
    log(f"Subiendo {local_path} -> {gcs_uri}...", tag="Storage", webhook_url=webhook_url, job_id=job_id)
    client = storage.Client()
    bucket = client.bucket(bucket_name)
    blob = bucket.blob(blob_name)
    if content_type:
        blob.content_type = content_type
    blob.upload_from_filename(local_path)
    size_mb = os.path.getsize(local_path) / (1024 * 1024)
    log(f"Subida completada con éxito ({size_mb:.2f} MB)", tag="Storage", webhook_url=webhook_url, job_id=job_id)

# -----------------------------------------------------------------------------
# ACCIONES DE VIDEO / AUDIO
# -----------------------------------------------------------------------------

def action_trim_silences(input_path, output_path, threshold=1.0, webhook_url=None, job_id=None):
    """
    Recorte automático de silencios muertos usando FFmpeg silencedetect o auto-editor
    """
    log(f"Iniciando recorte de pausas y silencios (> {threshold}s)...", tag="FFmpeg", webhook_url=webhook_url, job_id=job_id)
    
    # Intentamos primero con auto-editor si está instalado para cortes precisos de frames
    try:
        cmd = [
            "auto-editor", input_path,
            "--margin", "0.2s",
            "--edit", f"audio:threshold=-30dB,stream=all,mincut={threshold}s",
            "--output", output_path,
            "--no-open"
        ]
        log(f"Ejecutando auto-editor: {' '.join(cmd)}", tag="Auto-Editor", webhook_url=webhook_url, job_id=job_id)
        res = subprocess.run(cmd, capture_output=True, text=True)
        if res.returncode == 0 and os.path.exists(output_path):
            log("Cortes de silencios completados exitosamente con auto-editor", tag="Auto-Editor", webhook_url=webhook_url, job_id=job_id)
            return True
        else:
            log(f"Auto-editor retorno advertencia, aplicando fallback a FFmpeg silenceremove: {res.stderr[:200]}", tag="FFmpeg", webhook_url=webhook_url, job_id=job_id)
    except Exception as e:
        log(f"Fallback a FFmpeg estándar: {e}", tag="FFmpeg", webhook_url=webhook_url, job_id=job_id)

    # Fallback con FFmpeg silenceremove filter
    cmd = [
        "ffmpeg", "-y", "-i", input_path,
        "-af", f"silenceremove=stop_periods=-1:stop_duration={threshold}:stop_threshold=-30dB",
        "-c:v", "copy",
        "-c:a", "aac", "-b:a", "192k",
        output_path
    ]
    subprocess.run(cmd, check=True)
    log("Procesamiento de silencios finalizado con FFmpeg", tag="FFmpeg", webhook_url=webhook_url, job_id=job_id)
    return True


def action_loudnorm(input_path, output_path, target_i=-14, true_peak=-1.0, webhook_url=None, job_id=None):
    """
    Normalización de sonoridad profesional de dos pasos estándar YouTube/Spotify (-14 LUFS)
    """
    log(f"Iniciando normalización EBU R128 de 2 pasos (Target: {target_i} LUFS, TP: {true_peak} dB)...", tag="Loudnorm", webhook_url=webhook_url, job_id=job_id)
    
    # Paso 1: Medición
    cmd1 = [
        "ffmpeg", "-i", input_path,
        "-af", f"loudnorm=I={target_i}:TP={true_peak}:LRA=11:print_format=json",
        "-f", "null", "-"
    ]
    res1 = subprocess.run(cmd1, capture_output=True, text=True)
    stderr = res1.stderr

    measured_data = {}
    try:
        json_start = stderr.rfind("{")
        json_end = stderr.rfind("}") + 1
        if json_start != -1 and json_end != -1:
            measured_data = json.loads(stderr[json_start:json_end])
            log(f"Valores medidos: Input I = {measured_data.get('input_i')} LUFS, True Peak = {measured_data.get('input_tp')} dB", tag="Loudnorm", webhook_url=webhook_url, job_id=job_id)
    except Exception as e:
        log(f"Aviso parseando json loudnorm (usando paso 1 genérico): {e}", tag="Loudnorm", webhook_url=webhook_url, job_id=job_id)

    # Paso 2: Aplicación lineal precisa
    if measured_data:
        filter_str = (
            f"loudnorm=I={target_i}:TP={true_peak}:LRA=11:"
            f"measured_I={measured_data.get('input_i')}:"
            f"measured_TP={measured_data.get('input_tp')}:"
            f"measured_LRA={measured_data.get('input_lra')}:"
            f"measured_thresh={measured_data.get('input_thresh')}:"
            f"offset={measured_data.get('target_offset')}:linear=true"
        )
    else:
        filter_str = f"loudnorm=I={target_i}:TP={true_peak}:LRA=11"

    cmd2 = [
        "ffmpeg", "-y", "-i", input_path,
        "-c:v", "copy",
        "-af", filter_str,
        "-c:a", "aac", "-b:a", "256k",
        output_path
    ]
    subprocess.run(cmd2, check=True)
    log(f"Normalización de audio completada a {target_i} LUFS", tag="Loudnorm", webhook_url=webhook_url, job_id=job_id)
    return True


def action_auto_subtitles_gemini(input_path, output_path, gemini_api_key, webhook_url=None, job_id=None):
    """
    Extrae audio, lo envía a Gemini 1.5/2.0 Flash para transcripción y genera subtítulos animados quemados (.ass)
    """
    log("Extrayendo pista de audio para análisis con Gemini Flash IA...", tag="Subtítulos", webhook_url=webhook_url, job_id=job_id)
    temp_audio = tempfile.mktemp(suffix=".mp3")
    subprocess.run(["ffmpeg", "-y", "-i", input_path, "-vn", "-ac", "1", "-ar", "16000", "-b:a", "64k", temp_audio], check=True)

    genai.configure(api_key=gemini_api_key)
    model = genai.GenerativeModel("gemini-1.5-flash")

    log("Subiendo audio comprimido al servicio multimodal de Gemini...", tag="Gemini Flash", webhook_url=webhook_url, job_id=job_id)
    audio_file = genai.upload_file(path=temp_audio)

    # Esperar procesamiento del archivo
    while audio_file.state.name == "PROCESSING":
        time.sleep(2)
        audio_file = genai.get_file(audio_file.name)

    log("Generando marcas de tiempo por frase con Gemini Flash...", tag="Gemini Flash", webhook_url=webhook_url, job_id=job_id)
    prompt = (
        "Eres el transcriptor del podcast 'La Cueva del Güero'. "
        "Escucha atentamente el audio en español mexicano urbano. "
        "Genera un archivo de subtítulos en formato SRT válido con marcas de tiempo precisas "
        "(formato HH:MM:SS,mmm --> HH:MM:SS,mmm). "
        "Divide las frases en fragmentos cortos (máximo 4 a 6 palabras por línea) ideales para TikTok y Shorts. "
        "Responde ÚNICAMENTE con el bloque SRT entre etiquetas ```srt ... ``` sin comentarios adicionales."
    )

    response = model.generate_content([audio_file, prompt])
    raw_srt = response.text
    if "```" in raw_srt:
        lines = raw_srt.splitlines()
        clean_lines = []
        inside = False
        for l in lines:
            if l.startswith("```"):
                inside = not inside
                continue
            if inside:
                clean_lines.append(l)
        raw_srt = "\n".join(clean_lines)

    srt_path = tempfile.mktemp(suffix=".srt")
    with open(srt_path, "w", encoding="utf-8") as f:
        f.write(raw_srt)

    log(f"Subtítulos SRT generados ({len(raw_srt)} caracteres). Quemando en video con estilo Neón...", tag="FFmpeg", webhook_url=webhook_url, job_id=job_id)

    # Quemar subtítulos con estilo de La Cueva (Neón Cyan/Magenta, letras en mayúsculas, fondo oscuro)
    # Usamos subtítulos ASS o el filtro subtitles de FFmpeg con force_style
    style = "FontSize=20,PrimaryColour=&H0000FFFF,OutlineColour=&H00000000,BorderStyle=3,Outline=2,Shadow=1,MarginV=60,Bold=1"
    cmd = [
        "ffmpeg", "-y", "-i", input_path,
        "-vf", f"subtitles={srt_path}:force_style='{style}'",
        "-c:a", "copy",
        "-c:v", "libx264", "-preset", "fast", "-crf", "22",
        output_path
    ]
    subprocess.run(cmd, check=True)
    log("Subtítulos Neón quemados con éxito en el video", tag="Subtítulos", webhook_url=webhook_url, job_id=job_id)

    # Limpieza
    try:
        os.remove(temp_audio)
        os.remove(srt_path)
    except Exception:
        pass
    return True


def action_render_preset(input_path, output_path, preset="tiktok", webhook_url=None, job_id=None):
    """
    Renderiza y optimiza para presets:
    - tiktok / reels / shorts: Formato vertical 1080x1920 (9:16) con recorte y centrado inteligente
    - youtube-hd: 1080p horizontal (16:9) con faststart
    - youtube-4k: 2160p horizontal
    """
    log(f"Iniciando renderizado con Preset: '{preset}'...", tag="Preset", webhook_url=webhook_url, job_id=job_id)
    preset = preset.lower()

    if preset in ["tiktok", "reels", "shorts", "vertical"]:
        # Recorte centrado automático de 16:9 a 9:16 (1080x1920)
        vf_filter = "crop=ih*(9/16):ih,scale=1080:1920"
        cmd = [
            "ffmpeg", "-y", "-i", input_path,
            "-vf", vf_filter,
            "-c:v", "libx264", "-profile:v", "high", "-level", "4.2",
            "-preset", "medium", "-crf", "20",
            "-maxrate", "10M", "-bufsize", "20M",
            "-c:a", "aac", "-b:a", "192k", "-ar", "44100",
            "-movflags", "+faststart",
            output_path
        ]
    elif preset in ["youtube-4k", "4k"]:
        cmd = [
            "ffmpeg", "-y", "-i", input_path,
            "-vf", "scale=3840:2160",
            "-c:v", "libx264", "-preset", "slow", "-crf", "18",
            "-c:a", "aac", "-b:a", "320k",
            "-movflags", "+faststart",
            output_path
        ]
    else:  # youtube-hd / horizontal por defecto
        cmd = [
            "ffmpeg", "-y", "-i", input_path,
            "-vf", "scale=1920:1080",
            "-c:v", "libx264", "-preset", "fast", "-crf", "20",
            "-c:a", "aac", "-b:a", "256k",
            "-movflags", "+faststart",
            output_path
        ]

    log(f"Comando de codificación: {' '.join(cmd)}", tag="FFmpeg", webhook_url=webhook_url, job_id=job_id)
    subprocess.run(cmd, check=True)
    log(f"Video renderizado exitosamente para '{preset}'", tag="Preset", webhook_url=webhook_url, job_id=job_id)
    return True


# -----------------------------------------------------------------------------
# MAIN DISPATCHER
# -----------------------------------------------------------------------------
def main():
    parser = argparse.ArgumentParser(description="Worker de Edición de Video - La Cueva del Güero")
    parser.add_argument("--action", required=True, help="Acción a realizar: trim-silences, loudnorm-spotify, auto-subtitles, render-preset")
    parser.add_argument("--input-gcs-uri", required=True, help="URI de entrada en GCS (ej: gs://cueva-raw-videos/clip.mp4)")
    parser.add_argument("--output-gcs-uri", required=True, help="URI de salida en GCS (ej: gs://cueva-processed-videos/clip_out.mp4)")
    parser.add_argument("--job-id", default=f"job_{int(time.time())}", help="ID único del trabajo")
    parser.add_argument("--threshold", type=float, default=1.0, help="Umbral de silencio en segundos")
    parser.add_argument("--words", default="eh,este,pues,o sea", help="Muletillas a filtrar")
    parser.add_argument("--preset", default="tiktok", help="Preset de exportación (tiktok, reels, shorts, youtube-hd, youtube-4k)")
    parser.add_argument("--webhook-url", default=None, help="URL de callback para reportar logs y estado")
    parser.add_argument("--gemini-api-key", default=os.getenv("GEMINI_API_KEY", ""), help="Clave API de Gemini Flash")

    args = parser.parse_args()

    log(f"== INICIANDO TRABAJO CLOUD RUN: ID {args.job_id} | ACCIÓN: {args.action} ==", tag="Worker", webhook_url=args.webhook_url, job_id=args.job_id)

    work_dir = tempfile.mkdtemp(prefix="cueva_render_")
    local_input = os.path.join(work_dir, "input_" + os.path.basename(args.input_gcs_uri))
    local_output = os.path.join(work_dir, "output_" + os.path.basename(args.output_gcs_uri))

    try:
        # 1. Descarga desde GCS
        download_from_gcs(args.input_gcs_uri, local_input, webhook_url=args.webhook_url, job_id=args.job_id)

        # 2. Ejecutar Acción Solicitada
        if args.action == "trim-silences":
            action_trim_silences(local_input, local_output, threshold=args.threshold, webhook_url=args.webhook_url, job_id=args.job_id)
        elif args.action in ["loudnorm-spotify", "loudnorm"]:
            action_loudnorm(local_input, local_output, target_i=-14, true_peak=-1.0, webhook_url=args.webhook_url, job_id=args.job_id)
        elif args.action in ["auto-subtitles", "subtitles"]:
            if not args.gemini_api_key:
                raise ValueError("Se requiere GEMINI_API_KEY para generar subtítulos con Gemini Flash.")
            action_auto_subtitles_gemini(local_input, local_output, gemini_api_key=args.gemini_api_key, webhook_url=args.webhook_url, job_id=args.job_id)
        elif args.action in ["render-preset", "export"]:
            action_render_preset(local_input, local_output, preset=args.preset, webhook_url=args.webhook_url, job_id=args.job_id)
        else:
            raise ValueError(f"Acción no soportada: {args.action}")

        # 3. Subir resultado a GCS
        upload_to_gcs(local_output, args.output_gcs_uri, content_type="video/mp4", webhook_url=args.webhook_url, job_id=args.job_id)

        log(f"== TRABAJO {args.job_id} COMPLETADO CON ÉXITO ==", tag="Worker", webhook_url=args.webhook_url, job_id=args.job_id)

        # Notificar finalización vía webhook
        if args.webhook_url:
            try:
                requests.post(args.webhook_url, json={
                    "job_id": args.job_id,
                    "status": "completed",
                    "output_uri": args.output_gcs_uri,
                    "timestamp": time.time()
                }, timeout=5)
            except Exception:
                pass

    except Exception as e:
        log(f"ERROR CRÍTICO EN TRABAJO {args.job_id}: {e}", tag="Error", webhook_url=args.webhook_url, job_id=args.job_id)
        if args.webhook_url:
            try:
                requests.post(args.webhook_url, json={
                    "job_id": args.job_id,
                    "status": "failed",
                    "error": str(e),
                    "timestamp": time.time()
                }, timeout=5)
            except Exception:
                pass
        sys.exit(1)
    finally:
        # Limpieza de archivos temporales en el contenedor
        shutil.rmtree(work_dir, ignore_errors=True)

if __name__ == "__main__":
    main()
