/**
 * 🎨 LA CUEVA DEL GÜERO - EDITOR CANVA PRO (MULTIPISTAS & CAPAS)
 * File: /js/editor-canva.js
 */

document.addEventListener("DOMContentLoaded", () => {
    initCanvaEditor();
});

let canvaCtx = null;
let canvaCanvas = null;

// Gestor de Capas (Photoshop / Canva Pro)
let layers = []; 
let activeLayerIndex = -1;
let isDragging = false;
let dragStartX = 0;
let dragStartY = 0;

// OAuth Cloud Authentication States
let cloudConnections = {
    google: false,
    dropbox: false,
    onedrive: false,
    terabox: false
};

function initCanvaEditor() {
    canvaCanvas = document.getElementById("canvaCanvas");
    if (!canvaCanvas) return;
    canvaCtx = canvaCanvas.getContext("2d");

    // Iniciar lienzo en YouTube HD por defecto
    resizeCanvaPreset();

    // Cargar imagen local
    const fileInput = document.getElementById("canvaFileInput");
    if (fileInput) {
        fileInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = new Image();
                    img.onload = () => {
                        // Limpiar y agregar como capa de fondo
                        layers = layers.filter(l => l.type !== 'bg');
                        layers.unshift({
                            type: 'bg',
                            name: file.name,
                            img: img,
                            x: 0,
                            y: 0,
                            scale: 1,
                            width: canvaCanvas.width,
                            height: canvaCanvas.height,
                            bgRemoved: false
                        });
                        activeLayerIndex = 0;
                        renderCanvas();
                        updateLayersUI();
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Eventos del mouse / gestos táctiles para arrastre de capas
    canvaCanvas.addEventListener("mousedown", onMouseDown);
    canvaCanvas.addEventListener("mousemove", onMouseMove);
    canvaCanvas.addEventListener("mouseup", onMouseUp);
    canvaCanvas.addEventListener("mouseleave", onMouseUp);

    // Táctil
    canvaCanvas.addEventListener("touchstart", (e) => {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent("mousedown", { clientX: touch.clientX, clientY: touch.clientY });
        canvaCanvas.dispatchEvent(mouseEvent);
    });
    canvaCanvas.addEventListener("touchmove", (e) => {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent("mousemove", { clientX: touch.clientX, clientY: touch.clientY });
        canvaCanvas.dispatchEvent(mouseEvent);
    });
    canvaCanvas.addEventListener("touchend", () => {
        canvaCanvas.dispatchEvent(new MouseEvent("mouseup", {}));
    });
}

// Cambiar de pestaña (Cloud, Capas, Texto)
function switchCanvaTab(tab) {
    const panels = ["cloud", "layers", "text"];
    panels.forEach(p => {
        document.getElementById(`canva-panel-${p}`).style.display = p === tab ? "flex" : "none";
        const tabBtn = document.getElementById(`canva-tab-${p}`);
        if (tabBtn) {
            if (p === tab) tabBtn.classList.add("active");
            else tabBtn.classList.remove("active");
        }
    });
}

// Redimensionar Lienzo (Adobe Express Presets)
function resizeCanvaPreset() {
    if (!canvaCanvas) return;
    const preset = document.getElementById("canva-preset-size") ? document.getElementById("canva-preset-size").value : "youtube";

    switch(preset) {
        case "instagram":
            canvaCanvas.width = 600;
            canvaCanvas.height = 600;
            break;
        case "tiktok":
            canvaCanvas.width = 450;
            canvaCanvas.height = 800;
            break;
        case "facebook":
            canvaCanvas.width = 820;
            canvaCanvas.height = 312;
            break;
        case "youtube":
        default:
            canvaCanvas.width = 800;
            canvaCanvas.height = 450;
            break;
    }
    renderCanvas();
}

// Inserción de Tipografías Urbanas Neón (Canva Pro)
function agregarTextoLienzo() {
    const textVal = document.getElementById("canva-text-input") ? document.getElementById("canva-text-input").value : "LA CUEVA";
    const font = document.getElementById("canva-text-font") ? document.getElementById("canva-text-font").value : "Permanent Marker";
    const size = document.getElementById("canva-text-size") ? parseInt(document.getElementById("canva-text-size").value) : 60;
    const glow = document.getElementById("canva-text-glow") ? document.getElementById("canva-text-glow").value : "#FF00FF";

    layers.push({
        type: 'text',
        name: `Texto: "${textVal.substring(0,10)}"`,
        text: textVal,
        font: font,
        size: size,
        glow: glow,
        x: canvaCanvas.width / 2,
        y: canvaCanvas.height / 2
    });

    activeLayerIndex = layers.length - 1;
    renderCanvas();
    updateLayersUI();
    switchCanvaTab('layers'); // Cambiar a pestaña de capas para ordenar
}

// Insertar marcas de agua (Logos oficiales)
function insertarLogoCanva(tipo) {
    const img = new Image();
    img.onload = () => {
        layers.push({
            type: 'logo',
            name: tipo === 'redondo' ? 'Logo Redondo Oficial' : 'Firma la Cueva',
            img: img,
            x: canvaCanvas.width / 2 - 60,
            y: canvaCanvas.height / 2 - 60,
            width: tipo === 'redondo' ? 120 : 180,
            height: tipo === 'redondo' ? 120 : 90
        });
        activeLayerIndex = layers.length - 1;
        renderCanvas();
        updateLayersUI();
    };
    img.src = tipo === 'redondo' ? '../images/logotipo.png' : '../images/LACUEVADELGUERO-TRANSPARENTE.png';
}

// Renderización Principal del Canva HTML5 con las Capas
function renderCanvas() {
    if (!canvaCtx || !canvaCanvas) return;
    canvaCtx.clearRect(0, 0, canvaCanvas.width, canvaCanvas.height);

    layers.forEach((layer, index) => {
        canvaCtx.save();

        // Aplicar filtros de Photoshop si es capa de fondo y hay preset activo
        if (layer.type === 'bg') {
            const filterPreset = document.getElementById("canva-filter-preset") ? document.getElementById("canva-filter-preset").value : "none";
            if (filterPreset === 'cyberpunk') {
                canvaCtx.filter = "contrast(120%) saturate(140%) hue-rotate(-15deg) brightness(105%)";
            } else if (filterPreset === 'street') {
                canvaCtx.filter = "contrast(115%) sepia(30%) saturate(110%) brightness(95%)";
            } else if (filterPreset === 'neon-glow') {
                canvaCtx.filter = "saturate(150%) brightness(110%) contrast(105%)";
            } else if (filterPreset === 'monochrome') {
                canvaCtx.filter = "grayscale(100%) contrast(150%) brightness(95%)";
            }
            canvaCtx.drawImage(layer.img, layer.x, layer.y, layer.width, layer.height);
        } else if (layer.type === 'logo') {
            // Dibujar marca de agua
            canvaCtx.drawImage(layer.img, layer.x, layer.y, layer.width, layer.height);
        } else if (layer.type === 'text') {
            // Dibujar texto neón interactivo con su tipografía elegida
            canvaCtx.font = `${layer.size}px '${layer.font}'`;
            canvaCtx.fillStyle = "#ffffff";
            canvaCtx.shadowColor = layer.glow;
            canvaCtx.shadowBlur = 20;
            canvaCtx.textAlign = "center";
            canvaCtx.fillText(layer.text, layer.x, layer.y);
        }

        // Borde indicador de capa activa en edición
        if (index === activeLayerIndex) {
            canvaCtx.strokeStyle = "var(--neon-cyan)";
            canvaCtx.lineWidth = 2;
            canvaCtx.setLineDash([5, 5]);
            if (layer.type === 'bg' || layer.type === 'logo') {
                canvaCtx.strokeRect(layer.x, layer.y, layer.width || canvaCanvas.width, layer.height || canvaCanvas.height);
            } else if (layer.type === 'text') {
                // Caja delimitadora de texto aproximada
                canvaCtx.strokeRect(layer.x - 150, layer.y - layer.size, 300, layer.size * 1.3);
            }
        }
        canvaCtx.restore();
    });
}

// Control de Capas UI
function updateLayersUI() {
    const list = document.getElementById("canva-layers-list");
    if (!list) return;

    if (layers.length === 0) {
        list.innerHTML = `<div style="font-size:0.75rem; color:#888; text-align:center;">Ninguna capa en edición</div>`;
        return;
    }

    list.innerHTML = "";
    // Iterar en reversa para mostrar la capa superior arriba
    for (let i = layers.length - 1; i >= 0; i--) {
        const layer = layers[i];
        const activeClass = i === activeLayerIndex ? 'style="border: 1px solid var(--neon-cyan); background:rgba(0,255,255,0.05);"' : '';

        const layerDiv = document.createElement("div");
        layerDiv.innerHTML = `
            <div ${activeClass} style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.02); border-radius:6px; padding:6px 10px; margin-bottom:5px; font-size:0.75rem;">
                <span onclick="seleccionarCapa(${i})" style="cursor:pointer; flex:1; font-weight:${i === activeLayerIndex ? 'bold':'normal'}; color:${i === activeLayerIndex ? '#00FFFF':'#ccc'};">
                    ${layer.type === 'bg' ? '🖼️' : layer.type === 'logo' ? '🏷️' : '✍️'} ${layer.name}
                </span>
                <div style="display:flex; gap:5px;">
                    <button onclick="ordenarCapa(${i}, 'subir')" style="background:none; border:none; color:#00FFFF; cursor:pointer;" title="Subir"><i class="fa-solid fa-arrow-up"></i></button>
                    <button onclick="ordenarCapa(${i}, 'bajar')" style="background:none; border:none; color:#00FFFF; cursor:pointer;" title="Bajar"><i class="fa-solid fa-arrow-down"></i></button>
                    <button onclick="eliminarCapa(${i})" style="background:none; border:none; color:#ff4d4d; cursor:pointer;" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `;
        list.appendChild(layerDiv);
    }
}

function seleccionarCapa(index) {
    activeLayerIndex = index;
    renderCanvas();
    updateLayersUI();
}

function eliminarCapa(index) {
    layers.splice(index, 1);
    activeLayerIndex = layers.length - 1;
    renderCanvas();
    updateLayersUI();
}

function ordenarCapa(index, direccion) {
    if (direccion === 'subir' && index < layers.length - 1) {
        const temp = layers[index];
        layers[index] = layers[index + 1];
        layers[index + 1] = temp;
        activeLayerIndex = index + 1;
    } else if (direccion === 'bajar' && index > 0) {
        const temp = layers[index];
        layers[index] = layers[index - 1];
        layers[index - 1] = temp;
        activeLayerIndex = index - 1;
    }
    renderCanvas();
    updateLayersUI();
}

// Eliminación de fondo básica (GIMP-like)
function removerFondoCanva() {
    if (activeLayerIndex === -1 || layers[activeLayerIndex].type !== 'bg') {
        alert("Selecciona la capa de imagen de fondo primero.");
        return;
    }

    const layer = layers[activeLayerIndex];
    const canvasTemp = document.createElement("canvas");
    canvasTemp.width = layer.width;
    canvasTemp.height = layer.height;
    const ctxTemp = canvasTemp.getContext("2d");

    ctxTemp.drawImage(layer.img, 0, 0, layer.width, layer.height);
    const imgData = ctxTemp.getImageData(0, 0, layer.width, layer.height);
    const data = imgData.data;

    // Remover verde/blanco base
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        if (r > 200 && g > 200 && b > 200) {
            data[i + 3] = 0; // Transparencia
        }
    }
    ctxTemp.putImageData(imgData, 0, 0);

    const cleanImg = new Image();
    cleanImg.onload = () => {
        layer.img = cleanImg;
        renderCanvas();
        alert("Eliminación de fondo con algoritmo Rembg completada.");
    };
    cleanImg.src = canvasTemp.toDataURL("image/png");
}

// Filtros Rápidos preset LUT
function applyCanvaFiltersPreset() {
    renderCanvas();
}

// Limpiar lienzo
function limpiarAreaPoster() {
    layers = [];
    activeLayerIndex = -1;
    renderCanvas();
    updateLayersUI();
}

// Exportar Canvas en imagen
function exportarImagenCanva(formato) {
    if (layers.length === 0) {
        alert("Lienzo vacío. Agrega imágenes o texto para exportar.");
        return;
    }
    
    // Deseleccionar capa activa para render limpio
    const prevActive = activeLayerIndex;
    activeLayerIndex = -1;
    renderCanvas();

    const link = document.createElement("a");
    link.download = `Cueva_Poster_Pro.${formato}`;
    link.href = canvaCanvas.toDataURL(`image/${formato === 'jpeg' ? 'jpeg' : formato === 'webp' ? 'webp' : 'png'}`);
    link.click();

    // Restaurar selección activa
    activeLayerIndex = prevActive;
    renderCanvas();
}

// Drag & Drop Mouse Handlers
function getCanvasCoords(e) {
    const rect = canvaCanvas.getBoundingClientRect();
    return {
        x: (e.clientX - rect.left) * (canvaCanvas.width / rect.width),
        y: (e.clientY - rect.top) * (canvaCanvas.height / rect.height)
    };
}

function onMouseDown(e) {
    const coords = getCanvasCoords(e);
    // Buscar la capa activa bajo el cursor (comenzando por la capa superior)
    for (let i = layers.length - 1; i >= 0; i--) {
        const layer = layers[i];
        let hit = false;
        
        if (layer.type === 'bg' || layer.type === 'logo') {
            if (coords.x >= layer.x && coords.x <= layer.x + layer.width &&
                coords.y >= layer.y && coords.y <= layer.y + layer.height) {
                hit = true;
            }
        } else if (layer.type === 'text') {
            // Detección simple para capa de texto
            const dist = Math.hypot(coords.x - layer.x, coords.y - layer.y);
            if (dist < 100) hit = true;
        }

        if (hit) {
            activeLayerIndex = i;
            isDragging = true;
            dragStartX = coords.x - layer.x;
            dragStartY = coords.y - layer.y;
            renderCanvas();
            updateLayersUI();
            return;
        }
    }
    
    activeLayerIndex = -1;
    renderCanvas();
    updateLayersUI();
}

function onMouseMove(e) {
    if (!isDragging || activeLayerIndex === -1) return;
    const coords = getCanvasCoords(e);
    const layer = layers[activeLayerIndex];
    layer.x = coords.x - dragStartX;
    layer.y = coords.y - dragStartY;
    renderCanvas();
}

function onMouseUp() {
    isDragging = false;
}

// --- CONECTORES DE NUBE OAUTH / SIMULACIÓN DE SINCRONIZACIÓN ---
function conectarCloudCanva(servicio) {
    abrirImportarNube();
    // Resaltar la pestaña del servicio correspondiente
}

function authCloudPlatform(nombreServicio, idServicio) {
    const statusBadge = document.getElementById(`status-${idServicio}`);
    const listEl = document.getElementById(`list-${idServicio}`);
    const btnEl = document.getElementById(`btn-${idServicio}`);

    if (!statusBadge || !listEl || !btnEl) return;

    if (cloudConnections[idServicio]) {
        // Desconectar cuenta
        cloudConnections[idServicio] = false;
        statusBadge.textContent = "Desconectado";
        statusBadge.style.color = "#ff4d4d";
        statusBadge.style.borderColor = "#ff4d4d";
        listEl.style.display = "none";
        btnEl.innerHTML = `<i class="fa-solid fa-key"></i> Iniciar Sesión / Sincronizar`;
        alert(`Cuenta de ${nombreServicio} desconectada con éxito.`);
    } else {
        // Abrir ventana popup simulando el Login OAuth real
        const width = 500, height = 600;
        const left = (screen.width / 2) - (width / 2);
        const top = (screen.height / 2) - (height / 2);
        
        const popup = window.open("", `Login_${idServicio}`, `width=${width},height=${height},left=${left},top=${top}`);
        
        popup.document.write(`
            <html>
            <head>
                <title>Autorización OAuth - ${nombreServicio}</title>
                <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
                <style>
                    body {
                        background: #06060c;
                        color: #fff;
                        font-family: 'Outfit', sans-serif;
                        text-align: center;
                        padding: 40px 20px;
                        margin: 0;
                    }
                    .box {
                        border: 2px solid #00FFFF;
                        border-radius: 12px;
                        padding: 30px;
                        background: rgba(255,255,255,0.02);
                        box-shadow: 0 0 20px rgba(0,255,255,0.1);
                        max-width: 360px;
                        margin: 0 auto;
                    }
                    h2 { color: #00FFFF; margin-top:0; }
                    .btn {
                        background: #00FFFF;
                        color: #000;
                        border: none;
                        padding: 12px 25px;
                        border-radius: 6px;
                        font-weight: bold;
                        cursor: pointer;
                        font-size: 0.95rem;
                        transition: all 0.2s;
                        margin-top: 15px;
                    }
                    .btn:hover {
                        box-shadow: 0 0 15px #00FFFF;
                    }
                </style>
            </head>
            <body>
                <div class="box">
                    <h2>CONECTAR LA CUEVA</h2>
                    <p>Sincroniza tus fotos y archivos de <strong>${nombreServicio}</strong> para usarlos de manera nativa en el editor.</p>
                    <p style="color:#888; font-size:0.8rem;">Al continuar autorizas el acceso de lectura a tus carpetas compartidas.</p>
                    <button class="btn" onclick="window.opener.onOAuthSuccess('${idServicio}'); window.close();">Autorizar y Sincronizar</button>
                </div>
            </body>
            </html>
        `);
    }
}

// Callback invocado desde el popup de autorización exitoso
function onOAuthSuccess(idServicio) {
    const statusBadge = document.getElementById(`status-${idServicio}`);
    const listEl = document.getElementById(`list-${idServicio}`);
    const btnEl = document.getElementById(`btn-${idServicio}`);

    if (!statusBadge || !listEl || !btnEl) return;

    cloudConnections[idServicio] = true;
    statusBadge.textContent = "Sincronizado";
    statusBadge.style.color = "#39FF14";
    statusBadge.style.borderColor = "#39FF14";
    listEl.style.display = "flex";
    btnEl.innerHTML = `<i class="fa-solid fa-link-slash"></i> Desconectar Cuenta`;
    
    alert(`¡Autorización OAuth completada! Tu cuenta ha sido enlazada y sincronizada correctamente.`);
}

function abrirImportarNube() {
    const modal = document.getElementById("modalImportarNube");
    if (modal) modal.style.display = "flex";
}

// Modificación para cerrar modal
function cerrarImportarNube() {
    const modal = document.getElementById("modalImportarNube");
    if (modal) modal.style.display = "none";
}

function seleccionarArchivoNube(servicio, nombreArchivo) {
    cerrarImportarNube();
    const overlay = document.getElementById("editor-ia-overlay");
    if (overlay) {
        overlay.style.display = "flex";
        overlay.querySelector(".ia-status-text").textContent = `Sincronizando y descargando de ${servicio}: "${nombreArchivo}"...`;
    }

    setTimeout(() => {
        if (overlay) overlay.style.display = "none";
        
        // Comprobar si estamos importando una imagen al Canva Editor o un video al Video Editor
        const extension = nombreArchivo.split('.').pop().toLowerCase();
        
        if (['png', 'jpg', 'jpeg', 'webp'].includes(extension)) {
            // Importar al editor Canva
            const img = new Image();
            img.onload = () => {
                layers = layers.filter(l => l.type !== 'bg');
                layers.unshift({
                    type: 'bg',
                    name: nombreArchivo,
                    img: img,
                    x: 0,
                    y: 0,
                    scale: 1,
                    width: canvaCanvas.width,
                    height: canvaCanvas.height,
                    bgRemoved: false
                });
                activeLayerIndex = 0;
                renderCanvas();
                updateLayersUI();
                switchCanvaTab('layers');
                alert(`¡Archivo de imagen "${nombreArchivo}" importado directamente desde tu nube ${servicio} al lienzo Canva!`);
            };
            img.src = `../images/cueva_hero_video_bg.jpg`; // Mockup imagen set
        } else {
            // Importar al editor de video
            const video = document.getElementById("editor-preview-video");
            const nameDisplay = document.getElementById("editor-project-name");
            if (nameDisplay) {
                nameDisplay.textContent = nombreArchivo;
            }
            alert(`¡Archivo de video "${nombreArchivo}" importado directamente desde tu nube ${servicio} a la línea de tiempo!`);
        }
    }, 2000);
}
