/**
 * 🎨 LA CUEVA DEL GÜERO - EDITOR DE FOTOS & POSTERS ESTILO CANVA PRO
 * File: /js/editor-canva.js
 */

document.addEventListener("DOMContentLoaded", () => {
    initCanvaEditor();
});

let canvaCtx = null;
let currentImage = null;

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
                        canvas.width = img.width;
                        canvas.height = img.height;
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
}

function applyCanvaFilters() {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas || !canvaCtx || !currentImage) return;

    const brightness = document.getElementById("canva-brightness")?.value || 100;
    const contrast = document.getElementById("canva-contrast")?.value || 100;
    const saturate = document.getElementById("canva-saturate")?.value || 100;
    const sepia = document.getElementById("canva-sepia")?.value || 0;

    canvaCtx.clearRect(0, 0, canvas.width, canvas.height);
    canvaCtx.filter = `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturate}%) sepia(${sepia}%)`;
    canvaCtx.drawImage(currentImage, 0, 0, canvas.width, canvas.height);

    // Dibujar texto de poster si existe
    const posterText = document.getElementById("canva-text")?.value;
    if (posterText) {
        const fontSize = Math.floor(canvas.height * 0.07);
        canvaCtx.filter = "none";
        canvaCtx.font = `800 ${fontSize}px 'Outfit', sans-serif`;
        canvaCtx.fillStyle = "#00FFFF";
        canvaCtx.shadowColor = "#FF00FF";
        canvaCtx.shadowBlur = 20;
        canvaCtx.textAlign = "center";
        canvaCtx.fillText(posterText, canvas.width / 2, canvas.height * 0.9);
    }
}

function removerFondoCanva() {
    const canvas = document.getElementById("canvaCanvas");
    if (!canvas || !canvaCtx || !currentImage) {
        alert("Primero sube una foto para eliminar el fondo.");
        return;
    }

    const imgData = canvaCtx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imgData.data;

    // Algoritmo de eliminación de fondo claro/oscuro
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        // Si el píxel es muy claro o blanco, hacerlo transparente
        if (r > 230 && g > 230 && b > 230) {
            data[i + 3] = 0;
        }
    }

    canvaCtx.putImageData(imgData, 0, 0);
    alert("Fondo eliminado. Ya puedes exportar como PNG con fondo transparente.");
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
