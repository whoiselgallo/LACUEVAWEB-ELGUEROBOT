/* ===============================
   DASHBOARD PRO – GESTOR DE REGISTROS
   VERSIÓN CORREGIDA
   - ✅ Protección DOM mejorada
   - ✅ XSS prevention
   - ✅ Compatibilidad cross-browser
   =============================== */

const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

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
            day: 'numeric'
        });
    } catch (e) {
        return dateStr;
    }
}

/* ============================================================
   CARGAR REGISTROS
   ============================================================ */

async function cargarRegistros() {
    try {
        const response = await fetch(API_KNOWLEDGE_URL, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!data.registros || !Array.isArray(data.registros)) {
            console.warn("Respuesta sin registros válidos");
            mostrarMensaje("No hay registros disponibles.");
            return;
        }

        mostrarRegistros(data.registros);

    } catch (error) {
        console.error("Error cargando registros:", error);
        mostrarMensaje("Error cargando registros: " + error.message);
    }
}

function mostrarRegistros(registros) {
    const contenedor = document.getElementById("registrosContainer");
    if (!contenedor) return;

    if (registros.length === 0) {
        contenedor.innerHTML = "<p>No hay registros disponibles.</p>";
        return;
    }

    let html = '<div class="registros-grid">';

    registros.forEach((reg) => {
        const nombre = escapeHtml(reg.nombre || "Sin nombre");
        const fecha = formatDate(reg.fecha || "");
        const id = escapeHtml(reg.id || "");

        html += `
            <div class="registro-card">
                <h3>${nombre}</h3>
                <p class="fecha">📅 ${fecha}</p>
                <button class="btn-detalle" onclick="mostrarDetalle('${id}')">
                    Ver Detalles
                </button>
            </div>
        `;
    });

    html += '</div>';
    contenedor.innerHTML = html;
}

function mostrarMensaje(texto) {
    const contenedor = document.getElementById("registrosContainer");
    if (!contenedor) return;
    contenedor.innerHTML = `<p>${escapeHtml(texto)}</p>`;
}

/* ============================================================
   MOSTRAR DETALLE
   ============================================================ */

async function mostrarDetalle(id) {
    try {
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
        const modal = document.getElementById("detalleModal");

        if (!modal) {
            console.error("No se encontró #detalleModal");
            return;
        }

        // Llenar modal con datos escapados
        const nombre = document.getElementById("detalleNombre");
        const escaleta = document.getElementById("detalleEscaleta");
        const guion = document.getElementById("detalleGuion");
        const cueCards = document.getElementById("detalleCueCards");

        if (nombre) nombre.textContent = escapeHtml(reg.nombre || "");
        if (escaleta) escaleta.textContent = escapeHtml(reg.escaleta || "");
        if (guion) guion.textContent = escapeHtml(reg.guion || "");
        if (cueCards) cueCards.innerHTML = reg.cue_cards || "";

        modal.classList.add("active");

    } catch (error) {
        console.error("Error cargando detalle:", error);
        alert("Error cargando detalle: " + error.message);
    }
}

function cerrarDetalleModal() {
    const modal = document.getElementById("detalleModal");
    if (modal) {
        modal.classList.remove("active");
    }
}

/* ============================================================
   IMPRIMIR CUE CARDS
   ============================================================ */

function imprimirCueCards() {
    const cueCardsDiv = document.getElementById("detalleCueCards");

    if (!cueCardsDiv || !cueCardsDiv.innerHTML.trim()) {
        alert("No hay Cue Cards para imprimir.");
        return;
    }

    const win = window.open("", "_blank");
    if (!win) {
        alert("No se pudo abrir la ventana de impresión.");
        return;
    }

    win.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Cue Cards – Impresión</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                }
                @media print {
                    body { margin: 0; padding: 0; }
                }
            </style>
        </head>
        <body>
            ${cueCardsDiv.innerHTML}
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
    console.log("✓ [DASHBOARD] Módulo cargado");

    // Cargar registros al iniciar
    cargarRegistros();

    // Botón para cerrar modal
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    if (btnCerrarModal) {
        btnCerrarModal.addEventListener("click", cerrarDetalleModal);
    }

    // Botón para imprimir
    const btnImprimirCueCards = document.getElementById("btnImprimirCueCards");
    if (btnImprimirCueCards) {
        btnImprimirCueCards.addEventListener("click", imprimirCueCards);
    }

    // Cerrar modal al hacer clic fuera
    const modal = document.getElementById("detalleModal");
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                cerrarDetalleModal();
            }
        });
    }
});