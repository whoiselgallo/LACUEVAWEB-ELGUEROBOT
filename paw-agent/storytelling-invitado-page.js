/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * STORYTELLING INVITADO PAGE
 * Script para la página HTML del formulario de storytelling
 * ═════════════════════════════════════════════════════════════════════════════════
 */

import { generarPaqueteStorytelling } from './paw-storytelling.js';

// ═════════════════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═════════════════════════════════════════════════════════════════════════════════

/**
 * Obtener valor de input de forma segura
 */
function v(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
}

/**
 * Escapar HTML para evitar XSS
 */
function escapeHtml(text) {
    return String(text || "").replace(/[&<>"']/g, (char) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    }[char]));
}

/**
 * Mostrar sección de resultado
 */
function mostrarResultado() {
    const resultado = document.getElementById("seccionResultado");
    resultado.style.display = "block";
    resultado.scrollIntoView({ behavior: "smooth" });
}

/**
 * Ocultar sección de resultado
 */
function ocultarResultado() {
    const resultado = document.getElementById("seccionResultado");
    resultado.style.display = "none";
}

/**
 * Renderizar bloques de resultado
 */
function setResultadoBloques(bloques) {
    const cont = document.getElementById("st_bloques");
    cont.innerHTML = "";
    
    bloques.forEach((b, idx) => {
        const div = document.createElement("div");
        div.className = "bloque";
        div.style.animationDelay = `${idx * 0.1}s`;
        div.innerHTML = `
            <h3>${b.titulo}</h3>
            <pre>${escapeHtml(b.contenido)}</pre>
        `;
        cont.appendChild(div);
    });
    
    mostrarResultado();
}

/**
 * Obtener resultado como texto plano
 */
function resultadoPlano() {
    const bloques = document.getElementById("st_bloques");
    if (!bloques) return "";
    
    let texto = "";
    bloques.querySelectorAll(".bloque").forEach(bloque => {
        const titulo = bloque.querySelector("h3")?.textContent || "";
        const contenido = bloque.querySelector("pre")?.textContent || "";
        texto += `${titulo}\n${contenido}\n\n`;
    });
    
    return texto.trim();
}

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIONES PRINCIPALES
// ═════════════════════════════════════════════════════════════════════════════════

/**
 * Generar paquete desde el formulario
 */
async function generarDesdeFormulario() {
    // Recopilar datos
    const data = {
        nombre: v("st_nombre"),
        ocupacion: v("st_ocupacion"),
        frase: v("st_frase"),
        barrio: v("st_barrio"),
        historia: v("st_historia"),
        anecdota: v("st_anecdota"),
        momento: v("st_momento"),
        herida: v("st_herida"),
        trayectoria: v("st_trayectoria"),
        incomodo: v("st_incomodo"),
        vulnerabilidad: v("st_vulnerabilidad"),
        pasiones: v("st_pasiones"),
        logros: v("st_logros"),
        fecha: v("st_fecha"),
        contacto: v("st_contacto")
    };

    // Validar campos requeridos
    const requeridos = ["nombre", "ocupacion", "frase", "barrio", "historia", "anecdota", "momento", "herida", "trayectoria"];
    const faltantes = requeridos.filter(k => !data[k]);
    
    if (faltantes.length > 0) {
        alert("❌ Faltan campos obligatorios:\n\n" + faltantes.join("\n"));
        return;
    }

    try {
        // Deshabilitar botón mientras se procesa
        const btnGenerar = document.getElementById("st_generar");
        btnGenerar.disabled = true;
        btnGenerar.textContent = "⏳ Generando...";

        // Generar usando el motor del PAW Agent
        // Esto también muestra los resultados
        await generarPaqueteStorytelling(data);

        // Armar bloques para mostrar en la página
        const presentacion = `
${data.nombre} llega a La Cueva del Güero como ${data.ocupacion}.

"${data.frase}"

Originario de ${data.barrio}.

Historia resumida:
${data.historia}
        `.trim();

        const storytelling = `
Anécdota detonante:
${data.anecdota}

Momento decisivo:
${data.momento}

Herida emocional:
${data.herida}

Trayectoria:
${data.trayectoria}
        `.trim();

        const guion = `
BLOQUES DE PREGUNTAS PARA ${data.nombre.toUpperCase()}

1️⃣ INFANCIA Y BARRIO
   - ¿Cómo era crecer en ${data.barrio}?
   - ¿Qué recuerdos de infancia te marcaron más?

2️⃣ HERIDA EMOCIONAL
   - ${data.herida.substring(0, 100)}...
   - ¿Qué cambió después de eso?

3️⃣ MOMENTO DECISIVO
   - ${data.momento.substring(0, 100)}...
   - ¿Qué decidiste a partir de ahí?

4️⃣ LOGROS Y TRAYECTORIA
   - ${data.logros || "Logros clave del invitado"}

5️⃣ VULNERABILIDAD Y PASIONES
   - ${data.vulnerabilidad || "Momentos de vulnerabilidad"}
   - ${data.pasiones || "Temas que apasionan"}

6️⃣ TEMAS INCÓMODOS / LÍMITES
   - ${data.incomodo || "Temas que prefieres evitar"}

7️⃣ CIERRE EMOCIONAL
   - ¿Qué mensaje dejarías a otros?
        `.trim();

        const escaleta = `
🎬 ESCALETA DEL EPISODIO – ${data.nombre}

1️⃣ PRESENTACIÓN (0–5 min)
   - El Güero presenta a ${data.nombre}
   - Frase clave: "${data.frase}"
   - Contexto: ${data.barrio}

2️⃣ STORYTELLING (5–20 min)
   - Anécdota detonante
   - Momento decisivo
   - Introducción de la herida

3️⃣ BLOQUES TEMÁTICOS (20–60 min)
   - Infancia, herida, decisión
   - Logros, vulnerabilidad, pasiones
   - Límites y temas incómodos

4️⃣ EXPLORACIÓN
   - Preguntas clave según flujo

5️⃣ CIERRE (60–75 min)
   - Mensaje final
   - Reflexión
   - Despedida
        `.trim();

        const cueCards = `
📇 CUE CARDS – ${data.nombre}

DATOS RÁPIDOS
├─ Nombre: ${data.nombre}
├─ Ocupación: ${data.ocupacion}
├─ Barrio: ${data.barrio}
└─ Frase: "${data.frase}"

FRASES DE IMPACTO
├─ "${data.frase}"
├─ Anécdota: ${data.anecdota.substring(0, 80)}...
└─ Momento: ${data.momento.substring(0, 80)}...

TEMAS SENSIBLES ⚠️
├─ Incómodo: ${data.incomodo || "A definir"}
├─ Vulnerable: ${data.vulnerabilidad || "A explorar"}
└─ Logros: ${data.logros || "Destacar durante"}

TIMELINE
├─ Presentación: 5 min
├─ Storytelling: 15 min
├─ Bloques: 40 min
└─ TOTAL: 75 min
        `.trim();

        // Mostrar bloques
        const bloques = [
            { titulo: "🎙 PRESENTACIÓN DEL INVITADO", contenido: presentacion },
            { titulo: "🔥 STORYTELLING DE APERTURA", contenido: storytelling },
            { titulo: "🧩 GUION DE PREGUNTAS", contenido: guion },
            { titulo: "🎬 ESCALETA DEL EPISODIO", contenido: escaleta },
            { titulo: "📇 CUE CARDS", contenido: cueCards }
        ];

        setResultadoBloques(bloques);

        // Reactivar botón
        btnGenerar.disabled = false;
        btnGenerar.innerHTML = '<i class="fas fa-rocket"></i> Generar Paquete';

    } catch (err) {
        console.error("Error al generar:", err);
        alert("❌ Error al generar el paquete. Consulta la consola.");
        document.getElementById("st_generar").disabled = false;
        document.getElementById("st_generar").innerHTML = '<i class="fas fa-rocket"></i> Generar Paquete';
    }
}

/**
 * Copiar resultado al portapapeles
 */
async function copiarStory() {
    const txt = resultadoPlano();
    if (!txt) {
        alert("⚠️ No hay resultado que copiar. Genera primero el paquete.");
        return;
    }

    try {
        await navigator.clipboard.writeText(txt);
        alert("✅ Resultado copiado al portapapeles.");
    } catch (err) {
        console.error("Error al copiar:", err);
        alert("❌ No se pudo copiar al portapapeles.");
    }
}

/**
 * Descargar resultado como TXT
 */
function descargarStory() {
    const txt = resultadoPlano();
    if (!txt) {
        alert("⚠️ No hay resultado que descargar. Genera primero el paquete.");
        return;
    }

    try {
        const fecha = new Date().toISOString().slice(0, 10);
        const nombre = v("st_nombre") || "storytelling";
        const filename = `storytelling-${nombre}-${fecha}.txt`;

        const blob = new Blob([txt], { type: "text/plain;charset=utf-8" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);

        alert(`✅ Archivo descargado: ${filename}`);
    } catch (err) {
        console.error("Error al descargar:", err);
        alert("❌ No se pudo descargar el archivo.");
    }
}

/**
 * Limpiar formulario y resultado
 */
function limpiarStory() {
    if (!confirm("¿Estás seguro de que quieres limpiar todo?")) {
        return;
    }

    document.getElementById("formStorytelling")?.reset();
    ocultarResultado();
    document.getElementById("st_bloques").innerHTML = "";
}

// ═════════════════════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═════════════════════════════════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", () => {
    // Botón Generar
    document.getElementById("st_generar")?.addEventListener("click", generarDesdeFormulario);

    // Botón Copiar
    document.getElementById("st_copiar")?.addEventListener("click", copiarStory);

    // Botón Descargar
    document.getElementById("st_descargar")?.addEventListener("click", descargarStory);

    // Botón Limpiar
    document.getElementById("st_limpiar")?.addEventListener("click", limpiarStory);

    // Permitir Enter en textarea para no enviar el formulario
    document.querySelectorAll(".form-storytelling textarea").forEach(ta => {
        ta.addEventListener("keydown", (e) => {
            // No hacer nada especial, solo permitir Ctrl+Enter si quieres
        });
    });

    console.log("✅ Storytelling Invitado Page cargado");
});
