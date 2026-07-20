/* ===============================
   GENERADOR DE CUE CARDS (HTML)
   VERSIÓN CORREGIDA
   - ✅ URLs corregidas
   - ✅ Funciones duplicadas eliminadas
   - ✅ Compatibilidad cross-browser mejorada
   =============================== */

// Configuración centralizada de URLs
const API_CUECARDS_URL = `${window.location.origin}/api/api-cuecards.php`;

/**
 * Generar Cue Cards desde el backend
 * @param {Object} payload - Datos para generar cue cards
 */
async function generarCueCards(payload) {
    try {
        // Validar que payload tiene la estructura esperada
        if (!payload || typeof payload !== 'object') {
            console.error("❌ Payload inválido para generarCueCards");
            alert("Error: datos inválidos para generar cue cards.");
            return;
        }

        const response = await fetch(API_CUECARDS_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.error) {
            console.error("Error del servidor:", data.error);
            alert("Hubo un error generando las tarjetas: " + data.error);
            return;
        }

        if (!data.html) {
            console.error("Respuesta sin HTML:", data);
            alert("El servidor no devolvió HTML válido.");
            return;
        }

        // Mostrar en nueva ventana
        mostrarCueCardsHTML(data.html);

    } catch (error) {
        console.error("Error en generarCueCards:", error);
        alert("No se pudo conectar con el servidor: " + error.message);
    }
}

/**
 * Mostrar HTML de Cue Cards en una nueva ventana
 * Compatible con navegadores modernos
 * @param {String} html - HTML a mostrar
 */
function mostrarCueCardsHTML(html) {
    try {
        const win = window.open("", "_blank");

        if (!win) {
            alert("No se pudo abrir la ventana. Verifica que no esté bloqueada por el navegador.");
            return;
        }

        // Método moderno: usar document.write con estructura HTML completa
        const doc = win.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Cue Cards – La Cueva del Güero</title>
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        margin: 0;
                        padding: 20px;
                        background: #f5f5f5;
                    }
                    @media print {
                        body { background: white; }
                    }
                </style>
            </head>
            <body>
                ${html}
            </body>
            </html>
        `);
        doc.close();

        // Enfocar la ventana
        win.focus();

    } catch (error) {
        console.error("Error mostrando Cue Cards:", error);
        alert("Error al abrir la ventana de Cue Cards.");
    }
}

/**
 * Inicializar botón de Cue Cards si existe en el DOM
 */
function initCueCardsButton() {
    const btnCueCards = document.getElementById("btnCueCards");

    if (!btnCueCards) {
        console.warn("⚠️ [CUE CARDS] No se encontró #btnCueCards en el DOM");
        return;
    }

    btnCueCards.addEventListener("click", async (e) => {
        e.preventDefault();

        // Efecto visual
        btnCueCards.style.boxShadow = "0 0 20px #FFA500";
        setTimeout(() => btnCueCards.style.boxShadow = "", 400);

        // Obtener datos del invitado
        const nombreInput = document.getElementById("nombreInvitado");
        const invitado = nombreInput ? nombreInput.value.trim() : "Invitado Desconocido";

        // Verificar que hay datos para generar
        if (!window.cueCardsList || window.cueCardsList.length === 0) {
            alert("Primero genera la escaleta para obtener las Cue Cards.");
            return;
        }

        // Preparar payload
        const payload = {
            invitado: invitado,
            tarjetas: window.cueCardsList,
            user: "frontend"
        };

        // Generar
        await generarCueCards(payload);
    });
}

/**
 * Generar Cue Cards desde una escaleta completa
 * @param {Object} payloadEscaleta - Objeto con escaleta, guion, nombre, etc.
 */
async function generarCueCardsDesdeEscaleta(payloadEscaleta) {
    try {
        if (!payloadEscaleta || !payloadEscaleta.escaleta) {
            console.error("❌ Payload de escaleta inválido");
            alert("Error: datos de escaleta inválidos.");
            return;
        }

        const cantidad = obtenerNumero("cantidadCueCards", 5);

        const response = await fetch(API_CUECARDS_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                cantidad,
                escaleta: payloadEscaleta.escaleta,
                guion: payloadEscaleta.guion,
                nombre: payloadEscaleta.nombre
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        // Abrir ventana imprimible
        const html = data.html || data;
        mostrarCueCardsHTML(html);

        // Guardar conocimiento con cue cards
        if (typeof guardarConocimientoInvitado === 'function') {
            guardarConocimientoInvitado(payloadEscaleta.nombre, {
                escaleta: payloadEscaleta.escaleta,
                guion: payloadEscaleta.guion,
                cue_cards: html
            });
        }

    } catch (err) {
        console.error("Error generando cue cards desde escaleta:", err);
        alert("Error generando cue cards: " + err.message);
    }
}

/**
 * Utilidad para obtener un número de un input
 * @param {String} id - ID del elemento
 * @param {Number} defaultValue - Valor por defecto
 */
function obtenerNumero(id, defaultValue = 0) {
    const el = document.getElementById(id);
    if (!el) return defaultValue;

    const val = parseInt(el.value, 10);
    return isNaN(val) ? defaultValue : val;
}

/**
 * Inicializar cuando el DOM esté listo
 */
document.addEventListener("DOMContentLoaded", () => {
    console.log("✓ [CUE CARDS] Módulo cargado");
    initCueCardsButton();
});