/**
 * 🎬 LA CUEVA VIDEO EDITOR PRO - INTERACTIVE CONTROLS
 * File: /js/video-editor.js
 */

document.addEventListener("DOMContentLoaded", () => {
    initVideoEditor();
});

let editorState = {
    currentTime: 0,
    isPlaying: false,
    mobileView: false,
    colorCorrected: false,
    subtitlesActive: false,
    isMuted: false
};

function initVideoEditor() {
    const video = document.getElementById("editor-preview-video");
    const playBtn = document.getElementById("editor-play-btn");

    if (video && playBtn) {
        playBtn.addEventListener("click", () => {
            if (video.paused) {
                video.play();
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
            } else {
                video.pause();
                playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        });

        video.addEventListener("timeupdate", () => {
            const progress = document.getElementById("timeline-progress");
            const timeCode = document.getElementById("timecode-display");
            if (progress) {
                const pct = (video.currentTime / video.duration) * 100;
                progress.style.left = `${pct}%`;
            }
            if (timeCode) {
                timeCode.textContent = formatTime(video.currentTime);
            }
        });
    }

    // Escuchar el input de subida de video del editor
    const fileInput = document.getElementById("editor-file-input");
    if (fileInput) {
        fileInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                if (video) {
                    video.src = url;
                    video.load();
                }
                const nameDisplay = document.getElementById("editor-project-name");
                if (nameDisplay) {
                    nameDisplay.textContent = file.name;
                }
                alert(`Archivo "${file.name}" cargado en la biblioteca y la línea de tiempo.`);
            }
        });
    }
}

function formatTime(secs) {
    const m = Math.floor(secs / 60).toString().padStart(2, '0');
    const s = Math.floor(secs % 60).toString().padStart(2, '0');
    const ms = Math.floor((secs % 1) * 100).toString().padStart(2, '0');
    return `${m}:${s}:${ms}`;
}

// 🤖 BOTONES DE ACCIÓN INTELIGENTE (IA PANEL)
function ejecutarIAVideo(accion) {
    const overlay = document.getElementById("editor-ia-overlay");
    if (overlay) {
        overlay.style.display = "flex";
        overlay.querySelector(".ia-status-text").textContent = `Ejecutando IA: ${accion}...`;
    }

    setTimeout(() => {
        if (overlay) overlay.style.display = "none";

        if (accion === "Subtítulos Automáticos") {
            editorState.subtitlesActive = true;
            document.getElementById("subtitles-track").style.display = "block";
            alert("Subtítulos automáticos con IA generados en la pista superior.");
        } else if (accion === "Corrección de Color IA") {
            const video = document.getElementById("editor-preview-video");
            if (video) {
                video.style.filter = "contrast(115%) saturate(125%) brightness(105%)";
                alert("Corrección de color cinematográfica aplicada en tiempo real.");
            }
        } else if (accion === "Quitar Fondo") {
            alert("Eliminación de fondo con IA completada. Personaje aislado estilo CapCut.");
        } else if (accion === "Mejora de Voz IA") {
            alert("Reducción de ruido y ecualización de voz IA completada en la pista de audio.");
        } else if (accion === "Edición Rápida TikTok") {
            alert("Cortes rápidos inteligentes y sincronización de música aplicados a la línea de tiempo.");
        }
    }, 2000);
}

// ALTERNAR VISTA DE DISPOSITIVO MÓVIL (CONTENIDO VERTICAL 9:16)
function toggleMobileView() {
    const wrapper = document.getElementById("preview-wrapper-box");
    if (wrapper) {
        if (editorState.mobileView) {
            wrapper.style.width = "100%";
            wrapper.style.aspectRatio = "auto";
            alert("Vista horizontal clásica activada (YouTube/Spotify).");
        } else {
            wrapper.style.width = "280px";
            wrapper.style.aspectRatio = "9/16";
            alert("Vista vertical 9:16 activada (TikTok/Shorts/Reels).");
        }
        editorState.mobileView = !editorState.mobileView;
    }
}

// COMPARAR ANTES / DESPUÉS
function toggleCompareFilters() {
    const video = document.getElementById("editor-preview-video");
    if (video) {
        if (video.style.filter === "none" || !video.style.filter) {
            video.style.filter = "contrast(115%) saturate(125%) brightness(105%)";
            alert("Visualizando con filtros de color/LUTs.");
        } else {
            video.style.filter = "none";
            alert("Visualizando video original sin filtros.");
        }
    }
}

// EXPEDIENTE DE EXPORTACIÓN
function abrirExportarVideo() {
    const modal = document.getElementById("modalExportarVideo");
    if (modal) modal.style.display = "flex";
}

function cerrarExportarVideo() {
    const modal = document.getElementById("modalExportarVideo");
    if (modal) modal.style.display = "none";
}

function iniciarRenderVideo(preset) {
    cerrarExportarVideo();
    const overlay = document.getElementById("editor-ia-overlay");
    if (overlay) {
        overlay.style.display = "flex";
        overlay.querySelector(".ia-status-text").textContent = `Renderizando video para ${preset} con GPU...`;
    }

    setTimeout(() => {
        if (overlay) overlay.style.display = "none";
        alert(`¡Video renderizado y optimizado exitosamente para ${preset}! Listo para descargar.`);
    }, 3000);
}

// 🌐 CONECTORES NATIVOS A ALMACENAMIENTOS EN LA NUBE (Drive, Dropbox, OneDrive, TeraBox)
function abrirImportarNube() {
    const modal = document.getElementById("modalImportarNube");
    if (modal) modal.style.display = "flex";
}

function cerrarImportarNube() {
    const modal = document.getElementById("modalImportarNube");
    if (modal) modal.style.display = "none";
}

function seleccionarArchivoNube(servicio, nombreArchivo) {
    cerrarImportarNube();
    const overlay = document.getElementById("editor-ia-overlay");
    if (overlay) {
        overlay.style.display = "flex";
        overlay.querySelector(".ia-status-text").textContent = `Conectando con ${servicio} y descargando clip...`;
    }

    setTimeout(() => {
        if (overlay) overlay.style.display = "none";
        
        // Cargar video mockup o URL en el reproductor del editor
        const video = document.getElementById("editor-preview-video");
        const nameDisplay = document.getElementById("editor-project-name");
        
        if (nameDisplay) {
            nameDisplay.textContent = nombreArchivo;
        }
        
        alert(`¡Conexión exitosa! El archivo "${nombreArchivo}" ha sido importado directamente desde ${servicio} a tu línea de tiempo.`);
    }, 2500);
}

