/**
 * 🎨 LA CUEVA DEL GÜERO - EDITOR CANVA PRO CON INTERACCIÓN Y MOVIMIENTO
 * File: /js/editor-canva.js
 */

document.addEventListener("DOMContentLoaded", () => {
    initCanvaEditor();
});

let canvaCtx = null;
let currentImage = null;

// Estado del Lienzo e Capas Muestrales
let state = {
    imgX: 0,
    imgY: 0,
    imgScale: 1,
    textX: 0,
    textY: 0,
    isDraggingImg: false,
    isDraggingText: false,
    dragStartX: 0,
    dragStartY: 0,
    activeLayer: 'img', // 'img' o 'text'
    bgRemoved: false
};

function initCanvaEditor() {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas) return;
    canvaCtx = canvas.getContext("2d");

    const fileInput = document.getElementById("canvaFileInput");
    if (fileInput) {
        fileInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = new Image();
                    img.onload = () => {
                        currentImage = img;
                        canvas.width = Math.min(img.width, 800);
                        canvas.height = Math.floor(img.height * (canvas.width / img.width));

                        state.imgX = 0;
                        state.imgY = 0;
                        state.imgScale = 1;
                        state.textX = canvas.width / 2;
                        state.textY = canvas.height * 0.85;
                        state.bgRemoved = false;

                        applyCanvaFilters();
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Escuchar controles de ajuste de color
    const sliders = ["brightness", "contrast", "saturate", "sepia"];
    sliders.forEach((id) => {
        const input = document.getElementById(`canva-${id}`);
        if (input) {
            input.addEventListener("input", applyCanvaFilters);
        }
    });

    // EVENTOS DE ARRASTRE Y MOVIMIENTO (MOUSE & TOUCH)
    canvas.addEventListener("mousedown", onMouseDown);
    canvas.addEventListener("mousemove", onMouseMove);
    canvas.addEventListener("mouseup", onMouseUp);
    canvas.addEventListener("mouseleave", onMouseUp);

    canvas.addEventListener("touchstart", (e) => {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent("mousedown", {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    });

    canvas.addEventListener("touchmove", (e) => {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent("mousemove", {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    });

    canvas.addEventListener("touchend", () => {
        const mouseEvent = new MouseEvent("mouseup", {});
        canvas.dispatchEvent(mouseEvent);
    });
}

function getCanvasCoords(e) {
    const canvas = document.getElementById("canvaCanvas");
    const rect = canvas.getBoundingClientRect();
    return {
        x: (e.clientX - rect.left) * (canvas.width / rect.width),
        y: (e.clientY - rect.top) * (canvas.height / rect.height)
    };
}

function onMouseDown(e) {
    if (!currentImage) return;
    const coords = getCanvasCoords(e);

    // Verificar si el clic fue cerca del texto
    const textVal = document.getElementById("canva-text")?.value;
    if (textVal) {
        const distToText = Math.hypot(coords.x - state.textX, coords.y - state.textY);
        if (distToText < 80) {
            state.isDraggingText = true;
            state.dragStartX = coords.x - state.textX;
            state.dragStartY = coords.y - state.textY;
            return;
        }
    }

    // Arrastrar Imagen
    state.isDraggingImg = true;
    state.dragStartX = coords.x - state.imgX;
    state.dragStartY = coords.y - state.imgY;
}

function onMouseMove(e) {
    if (!currentImage) return;
    const coords = getCanvasCoords(e);

    if (state.isDraggingText) {
        state.textX = coords.x - state.dragStartX;
        state.textY = coords.y - state.dragStartY;
        applyCanvaFilters();
    } else if (state.isDraggingImg) {
        state.imgX = coords.x - state.dragStartX;
        state.imgY = coords.y - state.dragStartY;
        applyCanvaFilters();
    }
}

function onMouseUp() {
    state.isDraggingImg = false;
    state.isDraggingText = false;
}

function applyCanvaFilters() {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas || !canvaCtx || !currentImage) return;

    const brightness = document.getElementById("canva-brightness")?.value || 100;
    const contrast = document.getElementById("canva-contrast")?.value || 100;
    const saturate = document.getElementById("canva-saturate")?.value || 100;
    const sepia = document.getElementById("canva-sepia")?.value || 0;

    canvaCtx.clearRect(0, 0, canvas.width, canvas.height);

    canvaCtx.save();
    canvaCtx.filter = `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturate}%) sepia(${sepia}%)`;
    canvaCtx.drawImage(currentImage, state.imgX, state.imgY, canvas.width * state.imgScale, canvas.height * state.imgScale);
    canvaCtx.restore();

    // DIBUJAR TEXTO INTERACTIVO
    const posterText = document.getElementById("canva-text")?.value;
    if (posterText) {
        const fontSize = Math.floor(canvas.height * 0.08);
        canvaCtx.save();
        canvaCtx.font = `800 ${fontSize}px 'Outfit', sans-serif`;
        canvaCtx.fillStyle = "#00FFFF";
        canvaCtx.shadowColor = "#FF00FF";
        canvaCtx.shadowBlur = 25;
        canvaCtx.textAlign = "center";
        canvaCtx.fillText(posterText, state.textX, state.textY);
        canvaCtx.restore();
    }
}

// ALGORITMO MEJORADO DE ELIMINACIÓN DE FONDO CON MUESTRO DE BORDES
function removerFondoCanva() {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas || !canvaCtx || !currentImage) {
        alert("Primero sube una foto para eliminar el fondo.");
        return;
    }

    const imgData = canvaCtx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imgData.data;

    // Muestrear el color de la esquina superior izquierda como referencia del fondo
    const bgR = data[0];
    const bgG = data[1];
    const bgB = data[2];

    const umbral = 65; // Tolerancia de color

    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];

        // Comparar similitud con el color de fondo o si es muy claro/muy oscuro según muestra
        const diffR = Math.abs(r - bgR);
        const diffG = Math.abs(g - bgG);
        const diffB = Math.abs(b - bgB);

        if ((diffR < umbral && diffG < umbral && diffB < umbral) || (r > 220 && g > 220 && b > 220)) {
            data[i + 3] = 0; // Transparencia total
        }
    }

    canvaCtx.putImageData(imgData, 0, 0);

    // Crear nueva imagen limpia desde el canvas actual
    const newImg = new Image();
    newImg.onload = () => {
        currentImage = newImg;
        state.bgRemoved = true;
        applyCanvaFilters();
        alert("Fondo eliminado con precisión. Puedes mover el objeto y el texto dentro del lienzo.");
    };
    newImg.src = canvas.toDataURL("image/png");
}

// BOTÓN: LIMPIAR ÁREA DE POSTER
function limpiarAreaPoster() {
    const canvas = document.getElementById("canvaCanvas");
    if (canvaCtx && canvas) {
        canvaCtx.clearRect(0, 0, canvas.width, canvas.height);
    }

    currentImage = null;

    // Resetear sliders
    ["brightness", "contrast", "saturate"].forEach(id => {
        const el = document.getElementById(`canva-${id}`);
        if (el) el.value = 100;
    });
    const sepiaEl = document.getElementById("canva-sepia");
    if (sepiaEl) sepiaEl.value = 0;

    const textEl = document.getElementById("canva-text");
    if (textEl) textEl.value = "";

    const fileInput = document.getElementById("canvaFileInput");
    if (fileInput) fileInput.value = "";

    state = {
        imgX: 0,
        imgY: 0,
        imgScale: 1,
        textX: 0,
        textY: 0,
        isDraggingImg: false,
        isDraggingText: false,
        dragStartX: 0,
        dragStartY: 0,
        activeLayer: 'img',
        bgRemoved: false
    };

    alert("Área de trabajo del poster limpiada correctamente.");
}

function exportarImagenCanva(formato) {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas || !currentImage) {
        alert("Carga una imagen en el lienzo antes de exportar.");
        return;
    }

    let mime = "image/png";
    let filename = `poster_cueva_${Date.now()}.png`;

    if (formato === "jpeg") {
        mime = "image/jpeg";
        filename = `poster_cueva_${Date.now()}.jpg`;
    } else if (formato === "webp") {
        mime = "image/webp";
        filename = `poster_cueva_${Date.now()}.webp`;
    }

    const dataUrl = canvas.toDataURL(mime, 0.95);
    const a = document.createElement("a");
    a.href = dataUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// 🎨 CARGAR AVATAR GENERADO EN EL LIENZO CANVA DIRECTAMENTE
function cargarImagenDesdeUrl(url) {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas) return;
    
    // Cambiar a la vista del editor Canva
    switchView('canva');
    
    const img = new Image();
    img.onload = () => {
        currentImage = img;
        canvas.width = 600; // Ancho estándar
        canvas.height = 600; // Alto estándar
        
        state.imgX = 0;
        state.imgY = 0;
        state.imgScale = 1;
        state.textX = canvas.width / 2;
        state.textY = canvas.height * 0.85;
        state.bgRemoved = true; // El avatar ya es transparente sin fondo
        
        dibujarLienzo();
        alert("¡Avatar cargado exitosamente en el Editor Canva PRO! Puedes arrastrarlo y agregarle textos.");
    };
    img.src = url;
}
