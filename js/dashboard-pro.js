/* ============================================================
   DASHBOARD PRO - GESTOR DE REGISTROS (POSTGRESQL / NEON)
   ============================================================ */

const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

let activeId = null;
let activeNombre = "";
let activeData = {
    escaleta: "",
    guion: "",
    cue_cards: ""
};

/* ============================================================
   UTILIDADES
   ============================================================ */

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text || '').replace(/[&<>"']/g, (char) => map[char]);
}

function formatDate(dateStr) {
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateStr;
    }
}

/* ============================================================
   CARGAR REGISTROS
   ============================================================ */

async function cargarRegistros() {
    const container = document.getElementById("registrosContainer");
    try {
        // Enlazar con ?listar=true para activar el bloque GET del PHP
        const response = await fetch(`${API_KNOWLEDGE_URL}?listar=true`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const registros = await response.json();

        if (response.status !== 200 || !Array.isArray(registros)) {
            container.innerHTML = `<p style="color: #ff4d4d; text-align: center;">Error cargando registros.</p>`;
            return;
        }

        mostrarRegistros(registros);

    } catch (error) {
        console.error("Error cargando registros:", error);
        container.innerHTML = `<p style="color: #ff4d4d; text-align: center;">Error: ${error.message}</p>`;
    }
}

function mostrarRegistros(registros) {
    const container = document.getElementById("registrosContainer");
    if (!container) return;

    if (registros.length === 0) {
        container.innerHTML = "<p style='color: #666; text-align: center;'>No hay registros disponibles.</p>";
        return;
    }

    let html = "";
    registros.forEach((reg) => {
        const nombre = escapeHtml(reg.nombre || "Sin nombre");
        const fecha = formatDate(reg.created_at || "");
        const id = reg.id;

        html += `
            <div class="registro-card" id="card-${id}" onclick="mostrarDetalle(${id})">
                <h3>${nombre}</h3>
                <p><i class="fa-regular fa-calendar-days"></i> ${fecha}</p>
            </div>
        `;
    });

    container.innerHTML = html;
}

/* ============================================================
   MOSTRAR DETALLE
   ============================================================ */

async function mostrarDetalle(id) {
    try {
        // Deseleccionar card anterior
        if (activeId) {
            const prevCard = document.getElementById(`card-${activeId}`);
            if (prevCard) prevCard.classList.remove("active");
        }

        activeId = id;
        
        // Seleccionar card actual
        const currentCard = document.getElementById(`card-${id}`);
        if (currentCard) currentCard.classList.add("active");

        const response = await fetch(API_KNOWLEDGE_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "get", id: id })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!data.registro) {
            alert("No se encontró el registro.");
            return;
        }

        const reg = data.registro;
        
        // Guardar valores activos para edición/descargas
        activeNombre = reg.nombre || "Invitado";
        activeData.escaleta = reg.escaleta || "";
        activeData.guion = reg.guion || "";
        activeData.cue_cards = reg.cue_cards || "";

        // Cambiar visibilidad de paneles
        document.getElementById("detalleVacio").classList.add("hidden");
        document.getElementById("detalleContenido").classList.remove("hidden");

        // Rellenar cabecera
        document.getElementById("detalleNombre").textContent = escapeHtml(activeNombre);
        document.getElementById("detalleFecha").innerHTML = `<i class="fa-regular fa-clock"></i> Creado: ${formatDate(reg.created_at)}`;

        // Rellenar bloques de texto
        renderBloquesNormales();

    } catch (error) {
        console.error("Error cargando detalle:", error);
        alert("Error cargando detalle: " + error.message);
    }
}

function renderBloquesNormales() {
    const wrapEscaleta = document.getElementById("wrapper-escaleta");
    const wrapGuion = document.getElementById("wrapper-guion");
    const wrapCuecards = document.getElementById("wrapper-cuecards");

    wrapEscaleta.innerHTML = `<div class="text-block" id="block-escaleta">${escapeHtml(activeData.escaleta)}</div>`;
    wrapGuion.innerHTML = `<div class="text-block" id="block-guion">${escapeHtml(activeData.guion)}</div>`;
    wrapCuecards.innerHTML = `<div class="text-block" id="block-cuecards">${escapeHtml(activeData.cue_cards)}</div>`;
}

/* ============================================================
   HABILITAR EDICIÓN (Ajuste Manual)
   ============================================================ */

function habilitarEdicion(tipo) {
    if (!activeId) return;

    const wrapper = document.getElementById(`wrapper-${tipo}`);
    const rawText = activeData[tipo];

    wrapper.innerHTML = `
        <textarea id="edit-${tipo}" class="edit-textarea">${rawText}</textarea>
        <button class="btn-save-edit" onclick="guardarEdicion('${tipo}')">
            <i class="fa-solid fa-save"></i> Guardar Cambios
        </button>
    `;
}

async function guardarEdicion(tipo) {
    const newValue = document.getElementById(`edit-${tipo}`).value;

    try {
        const payload = {
            action: "update",
            id: activeId
        };
        payload[tipo] = newValue; // Ej: payload['escaleta'] = newValue

        const response = await fetch(API_KNOWLEDGE_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const res = await response.json();
        if (res.success) {
            // Actualizar datos locales
            activeData[tipo] = newValue;
            // Volver a renderizar
            renderBloquesNormales();
            alert("Ajuste guardado correctamente en la base de datos.");
        } else {
            alert("Error al guardar: " + res.error);
        }

    } catch (err) {
        console.error("Error guardando edición:", err);
        alert("Error guardando edición: " + err.message);
    }
}

/* ============================================================
   DESCARGAR ASSET
   ============================================================ */

function descargarAsset(tipo) {
    if (!activeId) return;

    const content = activeData[tipo];
    const extension = tipo === 'cuecards' ? 'html' : 'txt';
    const filename = `${activeNombre.toLowerCase().replace(/\s+/g, '_')}_${tipo}.${extension}`;
    
    const blob = new Blob([content], { type: "text/plain;charset=utf-8" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* ============================================================
   IMPRIMIR CUE CARDS
   ============================================================ */

function imprimirCueCards() {
    if (!activeId || !activeData.cue_cards.trim()) {
        alert("No hay Cue Cards disponibles para imprimir.");
        return;
    }

    const win = window.open("", "_blank");
    if (!win) {
        alert("No se pudo abrir la ventana de impresión.");
        return;
    }

    // El contenido de cue_cards ya viene preformateado en texto plano/html de Dify
    win.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Cue Cards - ${activeNombre}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 30px;
                    background: #fff;
                    color: #000;
                }
                pre {
                    white-space: pre-wrap;
                    line-height: 1.6;
                    font-size: 1.2rem;
                }
                @media print {
                    body { padding: 0; }
                    .no-print { display: none; }
                }
                .print-header {
                    display: flex;
                    justify-content: space-between;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .btn-print {
                    padding: 10px 20px;
                    background: #000;
                    color: #fff;
                    border: none;
                    border-radius: 4px;
                    font-weight: bold;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="print-header no-print">
                <h2>Impresión de Cue Cards: ${activeNombre}</h2>
                <button class="btn-print" onclick="window.print()">Imprimir Tarjetas</button>
            </div>
            <pre>${escapeHtml(activeData.cue_cards)}</pre>
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
}

/* ============================================================
   EVENTOS
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {
    console.log("V [DASHBOARD] Módulo cargado en Neon Postgres");
    cargarRegistros();
});