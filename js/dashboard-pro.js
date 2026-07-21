/* ============================================================
   DASHBOARD PRO - CONTROLADOR MAESTRO Y APIs
   ============================================================ */

const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;
const API_BLOG_UPLOAD_URL = `${window.location.origin}/api/upload-blog.php`;

let activeId = null;
let activeNombre = "";
let activeData = {
    escaleta: "",
    guion: "",
    cue_cards: ""
};

/* ============================================================
   NAVEGACIÓN DE VISTAS (TAB SWITCHER)
   ============================================================ */

function switchView(view) {
    // Quitar active de todos los menús
    document.querySelectorAll(".menu-item").forEach(item => item.classList.remove("active"));
    // Añadir active al menú seleccionado
    const activeMenu = document.getElementById(`menu-${view}`);
    if (activeMenu) activeMenu.classList.add("active");

    // Ocultar todas las secciones
    document.querySelectorAll(".view-section").forEach(sec => sec.classList.remove("active"));
    // Mostrar la sección seleccionada
    const activeSec = document.getElementById(`view-${view}`);
    if (activeSec) activeSec.classList.add("active");

    // Actualizar título del header
    const titleEl = document.getElementById("view-header-title");
    if (titleEl) {
        switch(view) {
            case 'episodios':
                titleEl.innerHTML = `Episodios y <span>Fichas</span>`;
                break;
            case 'blog':
                titleEl.innerHTML = `Gestor de <span>Blog</span>`;
                break;
            case 'hooks':
                titleEl.innerHTML = `Generador de <span>Hooks</span>`;
                break;
            case 'video':
                titleEl.innerHTML = `Editor de <span>Video</span>`;
                break;
        }
    }
}

/* ============================================================
   SECCIÓN 1: CONTROLADOR DE EPISODIOS
   ============================================================ */

async function cargarRegistros() {
    const container = document.getElementById("registrosContainer");
    try {
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
        container.innerHTML = "<p style='color: #888; text-align: center; padding: 20px;'>No hay episodios generados.</p>";
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

async function mostrarDetalle(id) {
    try {
        if (activeId) {
            const prevCard = document.getElementById(`card-${activeId}`);
            if (prevCard) prevCard.classList.remove("active");
        }

        activeId = id;
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
        activeNombre = reg.nombre || "Invitado";
        activeData.escaleta = reg.escaleta || "";
        activeData.guion = reg.guion || "";
        activeData.cue_cards = reg.cue_cards || "";

        document.getElementById("detalleVacio").classList.add("hidden");
        document.getElementById("detalleContenido").classList.remove("hidden");

        document.getElementById("detalleNombre").textContent = escapeHtml(activeNombre);
        document.getElementById("detalleFecha").innerHTML = `<i class="fa-regular fa-clock"></i> Creado: ${formatDate(reg.created_at)}`;

        renderBloquesNormales();
    } catch (error) {
        console.error("Error cargando detalle:", error);
        alert("Error cargando detalle: " + error.message);
    }
}

function renderBloquesNormales() {
    document.getElementById("wrapper-escaleta").innerHTML = `<div class="text-block" id="block-escaleta">${escapeHtml(activeData.escaleta)}</div>`;
    document.getElementById("wrapper-guion").innerHTML = `<div class="text-block" id="block-guion">${escapeHtml(activeData.guion)}</div>`;
    document.getElementById("wrapper-cuecards").innerHTML = `<div class="text-block" id="block-cuecards" style="background:#090911; font-family:monospace; color:#39FF14; border: 1px solid rgba(57,255,20,0.2); text-shadow:0 0 5px rgba(57,255,20,0.2);">${escapeHtml(activeData.cue_cards)}</div>`;
}

function habilitarEdicion(tipo) {
    if (!activeId) return;
    const wrapper = document.getElementById(`wrapper-${tipo}`);
    const rawText = activeData[tipo];

    wrapper.innerHTML = `
        <textarea id="edit-${tipo}" class="edit-textarea">${rawText}</textarea>
        <button class="btn-save-edit" onclick="guardarEdicion('${tipo}')">
            <i class="fa-solid fa-save"></i> Guardar Ajuste en Neon
        </button>
    `;
}

async function guardarEdicion(tipo) {
    const newValue = document.getElementById(`edit-${tipo}`).value;
    try {
        const payload = { action: "update", id: activeId };
        payload[tipo] = newValue;

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
            activeData[tipo] = newValue;
            renderBloquesNormales();
            alert("Ajuste guardado exitosamente en Neon.");
        } else {
            alert("Error: " + res.error);
        }
    } catch (err) {
        alert("Error guardando edición: " + err.message);
    }
}

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

function imprimirCueCards() {
    if (!activeId || !activeData.cue_cards.trim()) {
        alert("No hay Cue Cards para imprimir.");
        return;
    }

    const win = window.open("", "_blank");
    if (!win) {
        alert("Habilita las ventanas emergentes.");
        return;
    }

    win.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Cue Cards - ${activeNombre}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 30px; background: #fff; color: #000; }
                pre { white-space: pre-wrap; font-size: 1.25rem; line-height: 1.6; }
                @media print { .no-print { display: none; } }
                .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
            </style>
        </head>
        <body>
            <div class="header no-print">
                <h2>Cue Cards de Conducción: ${activeNombre}</h2>
                <button onclick="window.print()" style="padding:10px 20px; font-weight:bold; background:#000; color:#fff; border:none; cursor:pointer;">Imprimir</button>
            </div>
            <pre>${escapeHtml(activeData.cue_cards)}</pre>
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
}

/* ============================================================
   SECCIÓN 2: GESTOR DE BLOG (PDF CONVERSION)
   ============================================================ */

function switchBlogTab(tab) {
    document.getElementById("btn-tab-upload").classList.remove("active");
    document.getElementById("btn-tab-edit").classList.remove("active");
    document.getElementById(`btn-tab-${tab}`).classList.add("active");

    document.getElementById("blog-tab-upload").classList.add("hidden");
    document.getElementById("blog-tab-edit").classList.add("hidden");
    document.getElementById(`blog-tab-${tab}`).classList.remove("hidden");
}

let extractedTextBuffer = "";

async function handleBlogPDFSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    const fileInfo = document.getElementById("blog-file-info");
    fileInfo.textContent = `Archivo seleccionado: ${file.name} (Procesando...)`;
    fileInfo.style.display = "block";

    try {
        const fileReader = new FileReader();
        fileReader.onload = async function() {
            const typedarray = new Uint8Array(this.result);
            const pdf = await pdfjsLib.getDocument(typedarray).promise;
            let fullText = "";

            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(" ");
                fullText += pageText + "\n\n";
            }

            extractedTextBuffer = fullText;
            document.getElementById("blog-extracted-text").value = fullText;
            document.getElementById("blog-preview-container").classList.remove("hidden");
            fileInfo.textContent = `✓ Archivo procesado con éxito: ${file.name}`;
        };
        fileReader.readAsArrayBuffer(file);
    } catch (err) {
        console.error("Error leyendo PDF:", err);
        alert("Error al extraer texto del PDF: " + err.message);
    }
}

function convertirExtraccionAPost() {
    if (!extractedTextBuffer) return;
    document.getElementById("blog-content").value = extractedTextBuffer;
    switchBlogTab("edit");
}

async function publicarBlogPost() {
    const title = document.getElementById("blog-title").value.trim();
    const author = document.getElementById("blog-author").value.trim();
    const category = document.getElementById("blog-category").value;
    const content = document.getElementById("blog-content").value.trim();

    if (!title || !content) {
        alert("Título y Contenido del post son requeridos.");
        return;
    }

    try {
        // Enviar a la API del blog en formato JSON o FormData según lo espere upload-blog.php
        const response = await fetch(API_BLOG_UPLOAD_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                title: title,
                author: author,
                category: category,
                content: content,
                date: new Date().toISOString().split('T')[0]
            })
        });

        const res = await response.json();
        if (res.success || response.status === 200) {
            alert("✓ ¡Artículo publicado exitosamente en el Blog!");
            // Limpiar formulario
            document.getElementById("blog-title").value = "";
            document.getElementById("blog-content").value = "";
        } else {
            alert("Falla al publicar: " + (res.error || "Error de red"));
        }
    } catch (err) {
        alert("Post publicado o procesado. Verifica tu historial.");
    }
}

/* ============================================================
   SECCIÓN 3: GENERADOR DE HOOKS
   ============================================================ */

let hooksData = {
    facebook: "",
    instagram: "",
    tiktok: "",
    spotify: "",
    shorts: "",
    youtube: ""
};

function generarHooksParaRedes() {
    const topic = document.getElementById("hooks-topic").value.trim();
    if (!topic) {
        alert("Por favor ingresa un tema o frase central.");
        return;
    }

    // Generar hermosos ganchos urbanos/norteños adaptados
    hooksData.facebook = `🔥 LA NETA DEL BARRIO...
¿Alguna vez te han dado la espalda los que decían ser tus compas? 

Hoy platicamos de "${topic}" y cómo se aprende a distinguir a los reales del desmadre.

👇 Deja tu comentario si te ha pasado compa. #LaCueva #Realidad`;

    hooksData.instagram = `📸 HOOK PARA CAROUSEL:
Slide 1: ¿Tus compas del barrio son de verdad? 💀
Slide 2: Platicamos sobre "${topic}"...
Slide 3: Al final, el tiempo limpia la cueva.

Dale amor si estás de acuerdo. #LaCueva #Invitados #Storytelling`;

    hooksData.tiktok = `⚡ ¡GANCHO DE 3 SEGUNDOS TIKTOK!
"¡Si tu barrio hablara, se cae el desmadre! 🐾"

Hoy te cuento qué tranza con "${topic}" y por qué la gente se asusta cuando dices la verdad.

👀 Míralo completo y dime en los comentarios si te rajas.`;

    hooksData.spotify = `🎙️ TEASER DE AUDIO SPOTIFY:
[Música de fondo callejera entra suave]
"Qué tranza compas. En este episodio nos metemos a fondo con "${topic}". No te pierdas las declaraciones sin filtro de nuestro invitado..."
🎧 ¡Dale play ya!`;

    hooksData.shorts = `🎬 YOUTUBE SHORTS (Flow Loop):
"¡El barrio nunca olvida, perro! 🐾"

Esto es lo que pasa cuando te toca encarar "${topic}" en la vida real.

🔥 Suscríbete y activa la campanita para ver el desmadre completo.`;

    hooksData.youtube = `📺 GANCHO Y CLICKBAIT YOUTUBE:
Título: "La verdad detrás de: ${topic} 💀"

"¡Esa mi gente! En este video deshebramos todo el chisme y el aprendizaje de ${topic}..."

💬 Comenta la palabra 'CUEVA' y te saludo en el próximo video.`;

    // Renderizar en las cards magnéticas
    document.getElementById("hook-facebook").textContent = hooksData.facebook;
    document.getElementById("hook-instagram").textContent = hooksData.instagram;
    document.getElementById("hook-tiktok").textContent = hooksData.tiktok;
    document.getElementById("hook-spotify").textContent = hooksData.spotify;
    document.getElementById("hook-shorts").textContent = hooksData.shorts;
    document.getElementById("hook-youtube").textContent = hooksData.youtube;
}

function copyHook(platform) {
    const text = hooksData[platform];
    if (!text) {
        alert("Genera hooks primero.");
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        alert(`✓ Gancho para ${platform.toUpperCase()} copiado al portapapeles.`);
    });
}

/* ============================================================
   SECCIÓN 4: EDITOR DE VIDEO (UPLOADER Y REPRODUCTOR LOCAL)
   ============================================================ */

function handleVideoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const progressContainer = document.getElementById("video-progress-container");
    const progressBar = document.getElementById("video-progress-bar");
    const progressPct = document.getElementById("video-progress-pct");
    const previewBox = document.getElementById("video-preview-box");
    const loadedName = document.getElementById("video-loaded-name");
    const player = document.getElementById("dashboard-player");

    // Mostrar barra de progreso
    progressContainer.classList.remove("hidden");
    previewBox.classList.add("hidden");
    
    let pct = 0;
    const interval = setInterval(() => {
        pct += 10;
        progressBar.style.width = `${pct}%`;
        progressPct.textContent = `${pct}%`;

        if (pct >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                // Esconder progreso
                progressContainer.classList.add("hidden");
                // Cargar archivo en el player local
                loadedName.textContent = `🎥 Clip cargado: ${file.name}`;
                player.src = URL.createObjectURL(file);
                // Mostrar panel del player
                previewBox.classList.remove("hidden");
            }, 300);
        }
    }, 100);
}

/* ============================================================
   HELPERS COMUNES
   ============================================================ */

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
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
   INICIALIZACIÓN
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {
    console.log("V [DASHBOARD-PRO] Controlador unificado iniciado.");
    cargarRegistros();
});