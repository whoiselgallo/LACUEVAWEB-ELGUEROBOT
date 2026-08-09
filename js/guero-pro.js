/* ============================================================
   GÜERO PRO — GENERADOR UNIFICADO 2026
   Escaleta + Guion + Cue Cards (texto)
   APIs oficiales:
   - api-escaleta.php
   - api-cuecards.php
   - api-guero-knowledge.php
   ============================================================ */

const API_ESCALETA_URL  = `${window.location.origin}/api/api-escaleta.php`;
const API_CUECARDS_URL  = `${window.location.origin}/api/api-cuecards.php`;
const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

/* ============================================================
   UTILIDADES
   ============================================================ */
function obtenerValor(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
}

function escapeHtml(text) {
    const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    };
    return String(text || "").replace(/[&<>"']/g, (char) => map[char]);
}

function setResultado(html) {
    const cont = document.getElementById("resultado");
    if (!cont) return;
    cont.innerHTML = html;
    cont.classList.add("active");
    if (cont.scrollIntoView) {
        try {
            cont.scrollIntoView({ behavior: "smooth" });
        } catch (e) {
            cont.scrollIntoView();
        }
    }
}

function textoResultadoPlano() {
    const cont = document.getElementById("resultado");
    return cont ? cont.innerText.trim() : "";
}

/* ============================================================
   1) GENERAR ESCALETA + GUION + CUECARDS (TEXTO)
   ============================================================ */
async function generarEscaleta() {
    const boton = document.getElementById("botonGenerar");

    const campos = [
        "nombre", "ocupacion", "signo", "fecha", "barrio",
        "trayectoria", "herida", "incomodo", "gustos"
    ];

    const datos = Object.fromEntries(campos.map(c => [c, obtenerValor(c)]));
    const faltantes = campos.filter(c => !datos[c]);

    if (faltantes.length) {
        setResultado(`<p>Faltan campos: ${faltantes.join(", ")}</p>`);
        return;
    }

    try {
        if (boton) {
            boton.disabled = true;
            boton.textContent = "Generando…";
        }

        const response = await fetch(API_ESCALETA_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(datos)
        });

        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }

        let data;
        try {
            data = await response.json();
        } catch {
            const text = await response.text();
            throw new Error("Respuesta inválida del servidor: " + text);
        }

        if (data.status !== "success") {
            throw new Error(data.message || "Error desconocido.");
        }

        const escaleta = escapeHtml(data.escaleta || "");
        const guion = escapeHtml(data.guion || "");
        const cue = escapeHtml(data.cue_cards || "");

        setResultado(`
            <div class="seccion-resultado">
                <h3>🎬 Escaleta</h3>
                <pre>${escaleta}</pre>
            </div>

            <div class="seccion-resultado">
                <h3>📝 Guion</h3>
                <pre>${guion}</pre>
            </div>

            <div class="seccion-resultado">
                <h3>🎴 Cue Cards (texto)</h3>
                <pre>${cue}</pre>
            </div>

            <button id="btnCueCardsHTML" class="btn-cuecards">
                🎴 Generar Cue Cards para impresión
            </button>
        `);

        const btnCueCards = document.getElementById("btnCueCardsHTML");
        if (btnCueCards) {
            btnCueCards.addEventListener("click", () => generarCueCardsHTML(datos.nombre, data.cue_cards));
        }

        guardarConocimiento(datos.nombre, escaleta, guion, cue);

    } catch (err) {
        setResultado(`<p style="color:#FF00FF;">❌ ${escapeHtml(err.message)}</p>`);
    } finally {
        if (boton) {
            boton.disabled = false;
            boton.textContent = "🚀 Generar Escaleta";
        }
    }
}

/* ============================================================
   2) GENERAR CUE CARDS HTML (IMPRESIÓN)
   ============================================================ */
async function generarCueCardsHTML(nombre, cueCardsTexto) {
    // Dividir el texto de las cue cards en un array de líneas limpias
    const tarjetasArray = cueCardsTexto
        ? cueCardsTexto.split('\n').map(line => line.trim()).filter(line => line.length > 0)
        : ["Tarjetas de conducción generales para " + nombre];

    try {
        const response = await fetch(API_CUECARDS_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                invitado: nombre,
                tarjetas: tarjetasArray,
                user: "guero-bot-pro"
            })
        });

        if (!response.ok) {
            throw new Error(`Error HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.status !== "success" || !data.html) {
            throw new Error(data.message || "No se pudo generar HTML.");
        }

        const win = window.open("", "_blank");
        if (!win) {
            throw new Error("No se pudo abrir ventana. Verifica permisos del navegador.");
        }

        win.document.write(data.html);
        win.document.close();
        win.focus();

        guardarConocimiento(nombre, null, null, data.html);

    } catch (err) {
        alert("Error generando Cue Cards: " + err.message);
    }
}

/* ============================================================
   3) GUARDAR EPISODIO EN BD
   ============================================================ */
async function guardarConocimiento(nombre, escaleta, guion, cuecards) {
    try {
        await fetch(API_KNOWLEDGE_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nombre,
                escaleta,
                guion,
                cuecards
            })
        });
    } catch (err) {
        console.warn("No se pudo guardar conocimiento:", err);
    }
}

/* ============================================================
   4) UTILIDADES UI
   ============================================================ */
function copiarResultado() {
    const texto = textoResultadoPlano();
    if (!texto) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto)
            .then(() => alert("Copiado al portapapeles."))
            .catch(() => copiarAlPortapapelesFallback(texto));
    } else {
        copiarAlPortapapelesFallback(texto);
    }
}

function copiarAlPortapapelesFallback(texto) {
    const textarea = document.createElement("textarea");
    textarea.value = texto;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand("copy");
        alert("Copiado al portapapeles.");
    } catch (err) {
        alert("No se pudo copiar. Intenta manualmente.");
    }

    document.body.removeChild(textarea);
}

function descargarResultado() {
    const texto = textoResultadoPlano();
    if (!texto) return;

    const blob = new Blob([texto], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = `escaleta-${new Date().toISOString().slice(0, 10)}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    URL.revokeObjectURL(url);
}

function limpiarFormulario() {
    const formulario = document.getElementById("formularioEscaleta");
    const resultado = document.getElementById("resultado");

    if (formulario) formulario.reset();
    if (resultado) {
        resultado.innerHTML = "";
        resultado.classList.remove("active");
    }
}

/* ============================================================
   5) EVENTOS
   ============================================================ */
document.addEventListener("DOMContentLoaded", () => {
    const botonGenerar = document.getElementById("botonGenerar");
    const botonCopiar = document.getElementById("botonCopiar");
    const botonDescargar = document.getElementById("botonDescargar");
    const botonLimpiar = document.getElementById("botonLimpiar");

    if (botonGenerar) botonGenerar.addEventListener("click", generarEscaleta);
    if (botonCopiar) botonCopiar.addEventListener("click", copiarResultado);
    if (botonDescargar) botonDescargar.addEventListener("click", descargarResultado);
    if (botonLimpiar) botonLimpiar.addEventListener("click", limpiarFormulario);
});
