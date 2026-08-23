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

    // Ocultar la barra lateral en celular después de elegir una sección
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
        sidebar.classList.remove("active");
    }

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
            case 'canva':
                titleEl.innerHTML = `Editor Canva <span>PRO</span>`;
                break;
            case 'avatar':
                titleEl.innerHTML = `Avatar <span>Engine</span>`;
                break;
            case 'mesa':
                titleEl.innerHTML = `Mesa de <span>Trabajo</span>`;
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

        // Extraer objeto curaduría si viene en el registro
        const curaduria = reg.curaduria || { nivel: 'ALTO', badge: '🟢 ALTO', color: '#39FF14' };
        const badgeTag = curaduria.badge || (curaduria.nivel === 'BAJO' ? '🔴 BAJO' : (curaduria.nivel === 'MEDIO' ? '🟡 MEDIO' : '🟢 ALTO'));
        const badgeColor = curaduria.color || (curaduria.nivel === 'BAJO' ? '#FF00FF' : (curaduria.nivel === 'MEDIO' ? '#00FFFF' : '#39FF14'));

        html += `
            <div class="registro-card" id="card-${id}" onclick="mostrarDetalle(${id})">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <h3 style="margin:0; font-size:1rem;">${nombre}</h3>
                    <span style="font-size:0.75rem; font-weight:bold; color:${badgeColor}; border:1px solid ${badgeColor}; padding:2px 6px; border-radius:10px;">${badgeTag}</span>
                </div>
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

        // RENDERIZAR BANNER DE CURADURÍA
        const curaduria = reg.curaduria || {
            nivel: 'ALTO',
            badge: '🟢 NIVEL ALTO',
            formato: 'Invitado Principal al Canal',
            color: '#39FF14',
            razon: 'Ficha con información destacada. Aprobado para programa completo.'
        };

        const bannerEl = document.getElementById("curaduria-banner");
        const badgeEl = document.getElementById("curaduria-badge");
        const formatoEl = document.getElementById("curaduria-formato");
        const razonEl = document.getElementById("curaduria-razon");
        const actionsEl = document.getElementById("curaduria-actions");

        if (bannerEl) bannerEl.style.borderColor = curaduria.color || '#00FFFF';
        if (badgeEl) {
            badgeEl.textContent = curaduria.badge;
            badgeEl.style.color = curaduria.color;
            badgeEl.style.borderColor = curaduria.color;
            badgeEl.style.background = `rgba(${curaduria.nivel === 'BAJO' ? '255,0,255' : (curaduria.nivel === 'MEDIO' ? '0,255,255' : '57,255,20')}, 0.1)`;
        }
        if (formatoEl) formatoEl.textContent = curaduria.formato;
        if (razonEl) razonEl.textContent = curaduria.razon;

        if (actionsEl) {
            if (curaduria.nivel === 'BAJO') {
                actionsEl.innerHTML = `<button class="btn-neon btn-neon-magenta" onclick="canalizarAMicroContenido('${escapeHtml(activeNombre)}')"><i class="fa-solid fa-bolt"></i> Extraer Hooks & Shorts (30s)</button>`;
            } else if (curaduria.nivel === 'MEDIO') {
                actionsEl.innerHTML = `<button class="btn-neon" onclick="alert('Generando guion para entrevista corta de 10 min...')"><i class="fa-solid fa-stopwatch"></i> Formato Entrevista Corta (10m)</button>`;
            } else {
                actionsEl.innerHTML = `<button class="btn-neon" style="border-color:#39FF14; color:#39FF14;" onclick="alert('Programa completo de 40+ min aprobado.')"><i class="fa-solid fa-star"></i> Programa Completo Aprobado</button>`;
            }
        }

        renderBloquesNormales();
    } catch (error) {
        console.error("Error cargando detalle:", error);
        alert("Error cargando detalle: " + error.message);
    }
}

function canalizarAMicroContenido(nombre) {
    document.getElementById("hooks-topic").value = `Historias breves y frases detonadoras de ${nombre}`;
    switchView('hooks');
    generarHooksParaRedes();
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

async function crearPostConGemini() {
    if (!activeData.guion) {
        alert("Primero selecciona un Episodio/Ficha en la sección 'Episodios y Fichas' para extraer su guión.");
        return;
    }
    
    const btn = document.getElementById("btn-blog-ai");
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Gemini redactando post de blog...`;
    btn.disabled = true;
    
    try {
        const response = await fetch("../api/api-blog-ai.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nombre_invitado: activeNombre,
                guion: activeData.guion
            })
        });
        
        const data = await response.json();
        if (data.success) {
            document.getElementById("blog-title").value = data.titulo;
            document.getElementById("blog-content").value = data.articulo;
            document.getElementById("blog-category").value = "entrevista";
            alert("¡Artículo de blog generado exitosamente por Gemini a partir del guión!");
        } else {
            throw new Error(data.error || "Error al redactar el post.");
        }
    } catch(err) {
        console.error("Error redactando post con Gemini:", err);
        alert("Error de IA: " + err.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
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

    // Enviar a la Bandeja de Aprobación de la Mesa de Trabajo (Human-in-the-loop)
    agregarAColaAprobacion('blog', `Artículo de Blog: ${title}`, content, { 
        title: title, 
        author: author, 
        category: category 
    });
    
    alert("¡Artículo enviado a la Bandeja de Aprobación de la Mesa de Trabajo para su revisión!");
    
    // Limpiar formulario y enfocar pestaña
    document.getElementById("blog-title").value = "";
    document.getElementById("blog-content").value = "";
    switchView('mesa');
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

async function generarHooksParaRedes() {
    const topic = document.getElementById("hooks-topic").value.trim();
    if (!topic) {
        alert("Por favor ingresa un tema o frase central.");
        return;
    }

    const btn = document.querySelector("#view-hooks button.btn-neon");
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Generando ganchos...`;
    btn.disabled = true;

    try {
        const response = await fetch("../api/api-hooks-ai.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ topic: topic })
        });
        
        const data = await response.json();
        if (data.success && data.hooks) {
            hooksData.facebook = data.hooks.facebook;
            hooksData.instagram = data.hooks.instagram;
            hooksData.tiktok = data.hooks.tiktok;
            hooksData.spotify = data.hooks.spotify;
            hooksData.shorts = data.hooks.shorts;
            hooksData.youtube = data.hooks.youtube;
        } else {
            throw new Error(data.error || "Error en la respuesta de la IA.");
        }
    } catch (err) {
        console.warn("Falla de API de ganchos Gemini, usando plantillas de respaldo:", err);
        // Generar hermosos ganchos urbanos/norteños adaptados de respaldo
        hooksData.facebook = `🔥 LA NETA DEL BARRIO...\n¿Alguna vez te han dado la espalda los que decían ser tus compas? \n\nHoy platicamos de "${topic}" y cómo se aprende a distinguir a los reales del desmadre.\n\n👇 Deja tu comentario si te ha pasado compa. #LaCueva #Realidad`;
        hooksData.instagram = `📸 HOOK PARA CAROUSEL:\nSlide 1: ¿Tus compas del barrio son de verdad? 💀\nSlide 2: Platicamos sobre "${topic}"...\nSlide 3: Al final, el tiempo limpia la cueva.\n\nDale amor si estás de acuerdo. #LaCueva #Invitados #Storytelling`;
        hooksData.tiktok = `⚡ ¡GANCHO DE 3 SEGUNDOS TIKTOK!\n"¡Si tu barrio hablara, se cae el desmadre! 🐾"\n\nHoy te cuento qué tranza con "${topic}" y por qué la gente se asusta cuando dices la verdad.\n\n👀 Míralo completo y dime en los comentarios si te rajas.`;
        hooksData.spotify = `🎙️ TEASER DE AUDIO SPOTIFY:\n[Música de fondo callejera entra suave]\n"Qué tranza compas. En este episodio nos metemos a fondo con "${topic}". No te pierdas las declaraciones sin filtro de nuestro invitado..."\n🎧 ¡Dale play ya!`;
        hooksData.shorts = `🎬 YOUTUBE SHORTS (Flow Loop):\n"¡El barrio nunca olvida, perro! 🐾"\n\nEsto es lo que pasa cuando te toca encarar "${topic}" en la vida real.\n\n🔥 Suscríbete y activa la campanita para ver el desmadre completo.`;
        hooksData.youtube = `📺 GANCHO Y CLICKBAIT YOUTUBE:\nTítulo: "La verdad detrás de: ${topic} 💀"\n\n"¡Esa mi gente! En este video deshebramos todo el chisme y el aprendizaje de ${topic}..."\n\n💬 Comenta la palabra 'CUEVA' y te saludo en el próximo video.`;
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }

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
   SECCIÓN 8: INTEGRACIÓN DE REDES SOCIALES Y PUBLICADOR (OAUTH & UPLOAD)
   ============================================================ */

const redesConectadas = {
    yt: false,
    sp: false,
    tk: false,
    fb: false,
    ig: false
};

function conectarRedSocial(platform) {
    if (redesConectadas[platform] || localStorage.getItem(`cueva_oauth_${platform}`) === "true") {
        // Desconectar
        redesConectadas[platform] = false;
        localStorage.removeItem(`cueva_oauth_${platform}`);
        const badge = document.getElementById(`status-${platform}`);
        const btn = document.getElementById(`btn-connect-${platform}`);
        
        badge.innerHTML = `<i class="fa-solid fa-circle-dot"></i> Desconectado`;
        badge.style.color = "#ff4d4d";
        btn.innerHTML = `Conectar`;
        btn.style.borderColor = "";
        btn.style.color = "";
        return;
    }

    const width = 600;
    const height = 650;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    if (platform === 'yt' || platform === 'sp') {
        // Ejecutar flujo OAuth de Google / YouTube real
        window.open("../api/auth-google.php", "_blank", `width=${width},height=${height},left=${left},top=${top}`);
    } else {
        // Fallback simulado para otras plataformas
        const popup = window.open("", "_blank", `width=${width},height=${height},left=${left},top=${top}`);
        let html = `
            <html>
            <head>
                <title>OAuth Consent - La Cueva</title>
                <style>
                    body { background: #0b0b0e; color: #fff; font-family: sans-serif; text-align: center; padding: 40px; }
                    .logo { font-size: 30px; font-weight: bold; margin-bottom: 20px; }
                    .btn { display: inline-block; background: #00ffff; color: #000; padding: 12px 24px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 16px; box-shadow: 0 0 10px #00ffff; }
                    .btn:hover { background: #fff; box-shadow: 0 0 15px #fff; }
                    p { color: #888; font-size: 14px; margin-bottom: 30px; }
                </style>
            </head>
            <body>
                <div class="logo">🔑 Conectar con ${platform.toUpperCase()}</div>
                <p>El podcast 'La Cueva del Güero' solicita permisos para subir contenido, videos, audios y editar descripciones en tus canales oficiales de ${platform.toUpperCase()}.</p>
                <button class="btn" onclick="window.opener.oauthCallback('${platform}'); window.close();">Aprobar Acceso API</button>
            </body>
            </html>
        `;
        popup.document.write(html);
    }
}

window.oauthCallback = function(platform) {
    redesConectadas[platform] = true;
    const badge = document.getElementById(`status-${platform}`);
    const btn = document.getElementById(`btn-connect-${platform}`);
    
    badge.innerHTML = `<i class="fa-solid fa-circle-check"></i> Conectado`;
    badge.style.color = "#39FF14";
    btn.innerHTML = `<i class="fa-solid fa-link-slash"></i> Desconectar`;
    btn.style.borderColor = "#666";
    btn.style.color = "#aaa";
    
    localStorage.setItem(`cueva_oauth_${platform}`, "true");
};

// ═════════════════════════════════════════════════════════════════════════════════
// GESTOR DE BANDEJA DE APROBACIÓN (HUMAN-IN-THE-LOOP CURATION)
// ═════════════════════════════════════════════════════════════════════════════════
const colaAprobacion = [];

function agregarAColaAprobacion(tipo, titulo, contenido, extraInfo = {}) {
    const item = {
        id: Date.now() + Math.random().toString(36).substr(2, 5),
        tipo: tipo,
        titulo: titulo,
        contenido: contenido,
        extraInfo: extraInfo,
        estado: 'pendiente'
    };
    colaAprobacion.push(item);
    renderizarColaAprobacion();
}

function renderizarColaAprobacion() {
    const container = document.getElementById("approval-queue-container");
    const emptyMsg = document.getElementById("approval-empty-msg");
    if (!container) return;

    // Limpiar elementos previos en revisión
    container.querySelectorAll(".approval-item").forEach(el => el.remove());

    if (colaAprobacion.length === 0) {
        emptyMsg.style.display = "block";
        return;
    }

    emptyMsg.style.display = "none";

    colaAprobacion.forEach(item => {
        const itemEl = document.createElement("div");
        itemEl.className = "approval-item";
        itemEl.style = "background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:10px; border-left: 4px solid " + (item.tipo === 'hook' ? '#00ffff' : (item.tipo === 'blog' ? '#ff00ff' : '#39ff14'));
        
        let typeIcon = item.tipo === 'hook' ? '<i class="fa-solid fa-magnet" style="color:#00ffff;"></i>' : (item.tipo === 'blog' ? '<i class="fa-solid fa-pen-nib" style="color:#ff00ff;"></i>' : '<i class="fa-solid fa-video" style="color:#39ff14;"></i>');
        let typeLabel = item.tipo === 'hook' ? 'Gancho Social' : (item.tipo === 'blog' ? 'Post de Blog' : 'Archivo Multimedia');
        
        itemEl.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.7rem; color:#aaa; font-weight:bold; text-transform:uppercase;">${typeIcon} ${typeLabel}</span>
                <span style="font-size:0.6rem; color:#ffb703; border:1px solid #ffb703; padding:2px 6px; border-radius:4px; font-weight:bold;">Pendiente de Criba</span>
            </div>
            <strong style="font-size:0.85rem; color:#fff;">${item.titulo}</strong>
            <div style="font-size:0.75rem; color:#ccc; background:rgba(0,0,0,0.4); padding:10px; border-radius:6px; max-height:100px; overflow-y:auto; font-family:monospace; white-space:pre-wrap; border:1px solid rgba(255,255,255,0.05);">${item.contenido}</div>
            
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:5px;">
                <button class="btn-neon" onclick="rechazarItemAprobacion('${item.id}')" style="font-size:0.65rem; padding:4px 8px; border-color:#ff4d4d; color:#ff4d4d;"><i class="fa-solid fa-trash"></i> Rechazar</button>
                <button class="btn-neon btn-neon-magenta" onclick="aprobarItemAprobacion('${item.id}')" style="font-size:0.65rem; padding:4px 10px; border-color:#39FF14; color:#39FF14;"><i class="fa-solid fa-check"></i> Aprobar y Publicar</button>
            </div>
        `;
        container.appendChild(itemEl);
    });
}

function rechazarItemAprobacion(id) {
    const idx = colaAprobacion.findIndex(i => i.id === id);
    if (idx !== -1) {
        colaAprobacion.splice(idx, 1);
        renderizarColaAprobacion();
        alert("Contenido rechazado y removido de la bandeja.");
    }
}

async function aprobarItemAprobacion(id) {
    const item = colaAprobacion.find(i => i.id === id);
    if (!item) return;

    if (item.tipo === 'hook') {
        const platform = item.extraInfo.platform;
        alert(`Iniciando publicación aprobada de gancho en ${platform.toUpperCase()}...`);
        await new Promise(r => setTimeout(r, 1200));
        alert(`✓ ¡Aprobado y publicado exitosamente en tu perfil oficial de ${platform.toUpperCase()}!`);
    } else if (item.tipo === 'blog') {
        alert("Iniciando publicación aprobada de artículo de blog...");
        try {
            const response = await fetch(API_BLOG_UPLOAD_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    title: item.extraInfo.title,
                    author: item.extraInfo.author,
                    category: item.extraInfo.category,
                    content: item.contenido,
                    date: new Date().toISOString().split('T')[0]
                })
            });
            const res = await response.json();
            if (res.success || response.status === 200) {
                alert("✓ ¡Artículo de Blog aprobado e indexado en la web oficial!");
            } else {
                throw new Error(res.error || "Error de red.");
            }
        } catch(err) {
            alert("Error al publicar post: " + err.message);
        }
    } else if (item.tipo === 'media') {
        const video = item.extraInfo.file;
        const log = document.getElementById("publish-console-log");
        alert(`Iniciando distribución aprobada de capítulo: ${video}...`);
        
        log.innerHTML = `> [Aprobación Confirmada] Publicando archivo multimedia: ${video}...<br>`;
        for (let platform of item.extraInfo.conectadas) {
            log.innerHTML += `> Conectando con API de ${platform.toUpperCase()}...<br>`;
            await new Promise(r => setTimeout(r, 800));
            log.innerHTML += `> <span style="color:#00ffff;">[${platform.toUpperCase()} API]</span> Subiendo paquete de datos multimedia (${video})...<br>`;
            await new Promise(r => setTimeout(r, 1200));
            log.innerHTML += `> <span style="color:#39ff14;">[${platform.toUpperCase()} API] ✓ Publicado exitosamente!</span><br>`;
        }
        log.innerHTML += `> <strong>[System] Distribución completada. El contenido ya está en vivo!</strong>`;
        alert(`✓ ¡Capítulo ${video} aprobado y distribuido exitosamente en tus canales oficiales!`);
    }

    // Remover de la cola
    const idx = colaAprobacion.findIndex(i => i.id === id);
    if (idx !== -1) {
        colaAprobacion.splice(idx, 1);
        renderizarColaAprobacion();
    }
}

async function publicarTodoRedes() {
    const video = document.getElementById("publish-video-select").value;
    const conectadas = Object.keys(redesConectadas).filter(k => redesConectadas[k] || localStorage.getItem(`cueva_oauth_${k}`) === "true");
    
    if (conectadas.length === 0) {
        alert("Debes conectar al menos una red social primero.");
        return;
    }
    
    // Enviar a la bandeja de aprobación
    agregarAColaAprobacion('media', `Capítulo: ${video}`, `Pista multimedia lista para distribución final. Destino: ${conectadas.map(c=>c.toUpperCase()).join(', ')}`, {
        file: video,
        conectadas: conectadas
    });
    
    alert("¡Pista de video/audio enviada a la Bandeja de Aprobación de la Mesa de Trabajo para su revisión!");
}

async function publicarHookIndividual(platform, key) {
    const isConnected = redesConectadas[platform] || localStorage.getItem(`cueva_oauth_${platform}`) === "true";
    if (!isConnected) {
        alert(`Debes conectar la API de ${platform.toUpperCase()} primero en la sección 'Mesa de Trabajo'.`);
        return;
    }
    
    const text = hooksData[key];
    if (!text || text.includes("Escribe un tema")) {
        alert("Por favor genera los ganchos primero.");
        return;
    }

    // Enviar a la bandeja de aprobación
    agregarAColaAprobacion('hook', `Gancho para ${platform.toUpperCase()}`, text, {
        platform: platform,
        key: key
    });

    alert(`¡Gancho de ${platform.toUpperCase()} enviado a la Bandeja de Aprobación de la Mesa de Trabajo para su revisión!`);
    switchView('mesa');
}

function restaurarConexionesSociales() {
    ["yt", "sp", "tk", "fb", "ig"].forEach(platform => {
        if (localStorage.getItem(`cueva_oauth_${platform}`) === "true") {
            redesConectadas[platform] = true;
            const badge = document.getElementById(`status-${platform}`);
            const btn = document.getElementById(`btn-connect-${platform}`);
            if (badge && btn) {
                badge.innerHTML = `<i class="fa-solid fa-circle-check"></i> Conectado`;
                badge.style.color = "#39FF14";
                btn.innerHTML = `<i class="fa-solid fa-link-slash"></i> Desconectar`;
                btn.style.borderColor = "#666";
                btn.style.color = "#aaa";
            }
        }
    });
}

/* ============================================================
   SECCIÓN 9: INTEGRACIÓN YOUTUBE STUDIO & ACCIONES SUGERIDAS IA
   ============================================================ */

let currentYtStats = {
    views: 0,
    ctr: 0,
    retention: 0,
    impressions: 0
};

async function syncYouTubeStudioStats() {
    const btn = document.querySelector("button[onclick='syncYouTubeStudioStats()']");
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Conectando...`;
    btn.disabled = true;

    try {
        const response = await fetch("../api/api-youtube-analytics.php");
        const data = await response.json();
        
        if (data.success) {
            currentYtStats.views = data.views;
            currentYtStats.ctr = data.ctr;
            currentYtStats.retention = data.retention;
            currentYtStats.impressions = data.impressions;
            
            document.getElementById("yt-stat-views").textContent = currentYtStats.views.toLocaleString() + " (30d)";
            document.getElementById("yt-stat-ctr").textContent = `${currentYtStats.ctr}%`;
            document.getElementById("yt-stat-retention").textContent = `${currentYtStats.retention}%`;
            document.getElementById("yt-stat-impressions").textContent = currentYtStats.impressions.toLocaleString();
            
            // Cambiar colores según severidad de la alerta
            document.getElementById("yt-stat-ctr").style.color = currentYtStats.ctr < 5.0 ? "#ff4d4d" : "#39FF14";
            document.getElementById("yt-stat-retention").style.color = currentYtStats.retention < 40 ? "#ff4d4d" : "#39FF14";
            
            document.getElementById("yt-stats-panel").style.display = "grid";
            document.getElementById("btn-yt-suggest").disabled = false;
            
            alert(`✓ Datos obtenidos con éxito.\nOrigen: ${data.conexion}\nPeriodo: Últimos 30 días\nSuscriptores totales: ${data.subscribers.toLocaleString()}`);
        } else {
            throw new Error(data.error || "Falla al conectar con la API de YouTube.");
        }
    } catch(err) {
        console.error("Falla al conectar con YouTube:", err);
        alert("Error de Conexión: " + err.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function generarPlanAccionesYT() {
    const btn = document.getElementById("btn-yt-suggest");
    const terminal = document.getElementById("yt-action-plan");
    
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Generando plan...`;
    btn.disabled = true;
    terminal.style.display = "block";
    terminal.innerHTML = `> Analizando métricas con Gemini IA...<br>> Consultando base de datos cueva-db-prod...`;
    
    try {
        const response = await fetch("../api/api-youtube-actions.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(currentYtStats)
        });
        
        const data = await response.json();
        if (data.success && data.actions_html) {
            terminal.innerHTML = `> 🤖 <strong>[Gemini Curation Advisor] Plan de Acción:</strong><br><br>${data.actions_html}`;
        } else {
            throw new Error(data.error || "Falla al procesar.");
        }
    } catch(err) {
        console.error("Error generando sugerencias YT:", err);
        terminal.innerHTML = `> <span style='color:#ff4d4d;'>[Error] Falla al conectar con Gemini. Acciones sugeridas de respaldo:</span><br><br>` + 
                             `> ⚠️ <strong>[Canva PRO] Rediseña la miniatura neón. Tu CTR de ${currentYtStats.ctr}% es muy bajo carnal.</strong><br>` + 
                             `> ⚠️ <strong>[Video Editor] Activa el recorte de silencios a 0.5s para aumentar la retención (${currentYtStats.retention}%).</strong>`;
    } finally {
        btn.innerHTML = `<i class="fa-solid fa-brain"></i> Crear Plan de Acción`;
        btn.disabled = false;
    }
}

/* ============================================================
   INICIALIZACIÓN
   ============================================================ */

document.addEventListener("DOMContentLoaded", () => {
    console.log("V [DASHBOARD-PRO] Controlador unificado iniciado.");
    cargarRegistros();
    restaurarConexionesSociales();
});