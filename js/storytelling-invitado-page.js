/* =========================================================
   storytelling-invitado-page.js
   Motor de generación para la página HTML de Storytelling
========================================================= */

const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

function v(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
}

function escapeHtml(text) {
    return String(text || "").replace(/[&<>"']/g, (char) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    }[char]));
}

function setResultadoBloques(bloques) {
    const cont = document.getElementById("st_bloques");
    const panel = document.getElementById("st_resultado");
    cont.innerHTML = "";

    bloques.forEach(b => {
        const div = document.createElement("div");
        div.className = "bloque";
        div.innerHTML = `
            <h3>${b.titulo}</h3>
            <pre>${escapeHtml(b.contenido)}</pre>
        `;
        cont.appendChild(div);
    });

    panel.classList.add("visible");
    panel.scrollIntoView({ behavior: "smooth" });
}

function resultadoPlano() {
    return document.getElementById("st_bloques")?.innerText.trim() || "";
}

async function generarDesdeFormulario() {
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

    const requeridos = ["nombre", "ocupacion", "frase", "barrio", "historia", "anecdota", "momento", "herida", "trayectoria"];
    const faltantes = requeridos.filter(k => !data[k]);
    if (faltantes.length) {
        alert("Faltan campos obligatorios: " + faltantes.join(", "));
        return;
    }

    // Generar textos
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
Bloques de preguntas:

1) Infancia y barrio
   - ¿Cómo era crecer en ${data.barrio}?
   - ¿Qué recuerdos de infancia te marcaron?

2) Herida emocional
   - Cuéntanos más de ese momento difícil: ${data.herida}.
   - ¿Qué cambió en ti después de eso?

3) Momento decisivo
   - ¿Por qué consideras este momento como el más decisivo? (${data.momento})
   - ¿Qué decisiones tomaste a partir de ahí?

4) Logros
   - ${data.logros || "Agrega aquí logros específicos que quieras resaltar."}

5) Vulnerabilidad
   - ${data.vulnerabilidad || "Profundizar en momentos donde se sintió expuesto."}

6) Temas incómodos
   - ${data.incomodo || "Definir temas que no quiere tocar o que deben tratarse con cuidado."}

7) Pasiones
   - ${data.pasiones || "Música, proyectos, causas, etc."}

8) Cierre
   - ¿Qué mensaje le dejarías a quien está viviendo algo parecido a tu historia?
    `.trim();

    const escaleta = `
ESCALETA DEL EPISODIO – ${data.nombre}

1) Presentación (0–5 min)
   - El Güero presenta a ${data.nombre} como ${data.ocupacion}.
   - Frase clave: "${data.frase}".
   - Contexto de ${data.barrio}.

2) Storytelling de apertura (5–15 min)
   - Narración de la anécdota detonante.
   - Conexión con el momento decisivo.
   - Introducción de la herida emocional.

3) Bloques temáticos (15–60 min)
   - Infancia y barrio.
   - Herida emocional.
   - Momento decisivo.
   - Logros.
   - Vulnerabilidad.
   - Temas incómodos.
   - Pasiones.

4) Preguntas clave
   - Se toman del guion de preguntas generado.

5) Cierre emocional (60–75 min)
   - Mensaje final del invitado.
   - Resumen del aprendizaje.
   - Call to action o reflexión.
    `.trim();

    const cue = `
CUE CARDS – ${data.nombre}

Datos rápidos:
- Nombre: ${data.nombre}
- Ocupación: ${data.ocupacion}
- Barrio: ${data.barrio}
- Frase: "${data.frase}"

Frases clave:
- "${data.frase}"
- Momento decisivo: ${data.momento}
- Herida: ${data.herida}

Temas sensibles:
- ${data.incomodo || "Definir antes de grabar."}
- Vulnerabilidad: ${data.vulnerabilidad || "A explorar con cuidado."}

Momentos fuertes:
- Anécdota detonante: ${data.anecdota}
- Punto de quiebre: ${data.momento}
- Logros: ${data.logros || "Agregar logros específicos."}
    `.trim();

    const bloques = [
        { titulo: "🎙 Presentación del invitado", contenido: presentacion },
        { titulo: "🔥 Storytelling de apertura", contenido: storytelling },
        { titulo: "🧩 Guion de preguntas", contenido: guion },
        { titulo: "🎬 Escaleta del episodio", contenido: escaleta },
        { titulo: "🎴 Cue Cards", contenido: cue }
    ];

    setResultadoBloques(bloques);

    // Guardar en BD
    try {
        await fetch(API_KNOWLEDGE_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nombre: data.nombre,
                storytelling: { presentacion, storytelling, guion, escaleta, cue }
            })
        });
    } catch (err) {
        console.warn("No se pudo guardar storytelling en BD:", err);
    }
}

function copiarStory() {
    const txt = resultadoPlano();
    if (!txt) return;
    navigator.clipboard.writeText(txt);
    alert("Resultado copiado al portapapeles.");
}

function descargarStory() {
    const txt = resultadoPlano();
    if (!txt) return;
    const blob = new Blob([txt], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `storytelling-${new Date().toISOString().slice(0, 10)}.txt`;
    a.click();
    URL.revokeObjectURL(url);
}

function limpiarStory() {
    document.getElementById("formStorytelling")?.reset();
    document.getElementById("st_bloques").innerHTML = "";
    document.getElementById("st_resultado").classList.remove("visible");
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("st_generar")?.addEventListener("click", generarDesdeFormulario);
    document.getElementById("st_copiar")?.addEventListener("click", copiarStory);
    document.getElementById("st_descargar")?.addEventListener("click", descargarStory);
    document.getElementById("st_limpiar")?.addEventListener("click", limpiarStory);
});
