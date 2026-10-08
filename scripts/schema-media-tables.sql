-- ═══════════════════════════════════════════════════════════════════════
-- ESQUEMA DE GESTIÓN MULTIMEDIA: RAW vs. EDITED (LA CUEVA DEL GÜERO)
-- Base de Datos PostgreSQL (Neon.tech / Google Cloud SQL)
-- ═══════════════════════════════════════════════════════════════════════

-- ---------------------------------------------------------------------
-- 1. TABLA: media_raw (Material Original en Bruto antes de Edición)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS media_raw (
    id SERIAL PRIMARY KEY,
    invitado_id INT REFERENCES invitados(id) ON DELETE SET NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo_medio VARCHAR(50) DEFAULT 'video',           -- video, audio, camara_a, camara_b, microfono
    camara_angulo VARCHAR(50) DEFAULT 'principal',      -- camara_1_elguero, camara_2_invitado, general_mesa
    archivo_nombre VARCHAR(255) NOT NULL,
    archivo_size_bytes BIGINT DEFAULT 0,
    duracion_segundos NUMERIC(10, 2) DEFAULT 0.00,
    resolucion VARCHAR(50) DEFAULT '1920x1080',       -- 1920x1080, 3840x2160, etc.
    fps NUMERIC(6, 2) DEFAULT 60.00,
    codec_video VARCHAR(50) DEFAULT 'h264',
    codec_audio VARCHAR(50) DEFAULT 'aac',
    almacenamiento VARCHAR(50) DEFAULT 'gcs',         -- gcs, s3, local, neon
    gcs_bucket VARCHAR(150) DEFAULT 'cueva-raw-videos',
    gcs_uri TEXT NOT NULL,                            -- gs://cueva-raw-videos/raw/...
    url_acceso TEXT,                                  -- URL pública o prefirmada
    fecha_grabacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subido_por VARCHAR(100) DEFAULT 'produccion',
    tags TEXT[],                                      -- ['entrevista', 'bloque1', 'camara_guero']
    metadata_json JSONB DEFAULT '{}'::jsonb,          -- Detalles técnicos completos de ffprobe
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_media_raw_invitado ON media_raw(invitado_id);
CREATE INDEX IF NOT EXISTS idx_media_raw_tipo ON media_raw(tipo_medio);
CREATE INDEX IF NOT EXISTS idx_media_raw_fecha ON media_raw(fecha_grabacion DESC);
COMMENT ON TABLE media_raw IS 'Catálogo de archivos y grabaciones originales en bruto (cámaras, tomas multicámara y audio sin editar).';


-- ---------------------------------------------------------------------
-- 2. TABLA: media_edited (Material Procesado, Renderizado y Editado)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS media_edited (
    id SERIAL PRIMARY KEY,
    raw_id INT REFERENCES media_raw(id) ON DELETE SET NULL,
    invitado_id INT REFERENCES invitados(id) ON DELETE SET NULL,
    job_id VARCHAR(100),                              -- ID del Cloud Run Job o proceso local
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    formato_destino VARCHAR(50) DEFAULT 'tiktok_9_16', -- tiktok_9_16, reels_9_16, youtube_shorts, youtube_hd_16_9, spotify_audio
    aspect_ratio VARCHAR(20) DEFAULT '9:16',          -- 9:16, 16:9, 1:1, 4:5
    resolucion VARCHAR(50) DEFAULT '1080x1920',
    fps NUMERIC(6, 2) DEFAULT 60.00,
    duracion_segundos NUMERIC(10, 2) DEFAULT 0.00,
    archivo_nombre VARCHAR(255) NOT NULL,
    archivo_size_bytes BIGINT DEFAULT 0,
    almacenamiento VARCHAR(50) DEFAULT 'gcs',
    gcs_bucket VARCHAR(150) DEFAULT 'cueva-processed-videos',
    gcs_uri TEXT NOT NULL,                            -- gs://cueva-processed-videos/...
    url_reproduccion TEXT,
    loudness_lufs NUMERIC(5, 2) DEFAULT -14.00,       -- Normalización de audio (-14 LUFS estándar)
    tiene_subtitulos_karaoke BOOLEAN DEFAULT FALSE,
    tiene_autocrop_rostros BOOLEAN DEFAULT FALSE,
    silencios_eliminados_seg NUMERIC(8, 2) DEFAULT 0.00,
    version_edicion INT DEFAULT 1,
    estado_publicacion VARCHAR(50) DEFAULT 'borrador', -- borrador, aprobado, programado, publicado
    plataformas_destino TEXT[],                       -- ['tiktok', 'instagram', 'youtube']
    metadata_edicion JSONB DEFAULT '{}'::jsonb,       -- Parámetros de filtros, cortes y transcripción Whisper
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_media_edited_invitado ON media_edited(invitado_id);
CREATE INDEX IF NOT EXISTS idx_media_edited_raw ON media_edited(raw_id);
CREATE INDEX IF NOT EXISTS idx_media_edited_formato ON media_edited(formato_destino);
CREATE INDEX IF NOT EXISTS idx_media_edited_estado ON media_edited(estado_publicacion);
COMMENT ON TABLE media_edited IS 'Catálogo de clips listos, renderizados y optimizados para publicación en redes o canales.';
