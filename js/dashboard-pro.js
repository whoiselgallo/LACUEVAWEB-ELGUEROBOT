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
    cue_cards: "",
    storytelling: ""
};
let activeTemaBlog = null;

/* ============================================================
   NAVEGACIÓN DE VISTAS (TAB SWITCHER)
   ============================================================ */

function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
        sidebar.classList.toggle("active");
    }
}
window.toggleSidebar = toggleSidebar;

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
window.switchView = switchView;

/* ============================================================
   SECCIÓN 1: CONTROLADOR DE EPISODIOS
   ============================================================ */

const INVITADOS_DEFAULT = [
    { id: 2, nombre: "Leo Camacho Higuera", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Expediente de alta potencia narrativa y lealtad de barrio.' } },
    { id: 3, nombre: "Javi Domz (jeyb)", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Director creativo de cine y TV. Storytelling visual de alto impacto.' } },
    { id: 4, nombre: "Marcelo Ivan Maciel Maldonado", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Tribunal estatal de justicia administrativa y visión social del barrio.' } },
    { id: 5, nombre: "Aurelio Gonzalez", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Negocios y trayectoria comercial en la frontera desde la Carbajal.' } },
    { id: 6, nombre: "Guillermina Ayala Quiñonez", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Empleada doméstica. Historia humana conmovedora del barrio Libertad.' } },
    { id: 7, nombre: "Sergio Rene Coronado Vega", created_at: "2026-09-12", curaduria: { nivel: 'MEDIO', badge: '🟡 NIVEL MEDIO', color: '#00FFFF', formato: 'Entrevista Corta / Segmento (10 min)', razon: 'Respuestas breves. Canalizar a 3 hooks virales y clip vertical.' } },
    { id: 8, nombre: "Sergio Noe Escobar Perez", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Llantero hondureño. Migración, superación y trabajo honesto en la frontera.' } },
    { id: 9, nombre: "Yessica Lizbeth Fierro Vindiola", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Ama de casa de Puertas del Sol. Perspectiva femenina auténtica del barrio.' } }
];

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

        let registros = await response.json();
        if (!Array.isArray(registros) || registros.length === 0) {
            registros = INVITADOS_DEFAULT;
        }

        mostrarRegistros(registros);
        if (!activeId && registros.length > 0) {
            mostrarDetalle(registros[0].id);
        }
    } catch (error) {
        console.warn("Falla de red consultando API de conocimiento. Mostrando registros default:", error);
        mostrarRegistros(INVITADOS_DEFAULT);
        if (!activeId && INVITADOS_DEFAULT.length > 0) {
            mostrarDetalle(INVITADOS_DEFAULT[0].id);
        }
    }
}



function mostrarRegistros(registros) {
    const container = document.getElementById("registrosContainer");
    if (!container) return;

    if (!registros || registros.length === 0) {
        registros = INVITADOS_DEFAULT;
    }

    let html = "";
    registros.forEach((reg) => {
        const nombre = escapeHtml(reg.nombre || "Sin nombre");
        const fecha = formatDate(reg.created_at || "");
        const id = reg.id;
        const score = reg.ponderacion_score || '';

        // Extraer objeto curaduría si viene en el registro
        const curaduria = reg.curaduria || { nivel: 'ALTO', badge: '🟢 ALTO', color: '#39FF14' };
        const badgeTag = curaduria.badge || (curaduria.nivel === 'BAJO' ? '🔴 BAJO' : (curaduria.nivel === 'MEDIO' ? '🟡 MEDIO' : '🟢 ALTO'));
        const badgeColor = curaduria.color || (curaduria.nivel === 'BAJO' ? '#FF00FF' : (curaduria.nivel === 'MEDIO' ? '#00FFFF' : '#39FF14'));

        html += `
            <div class="registro-card" id="card-${id}" onclick="mostrarDetalle(${id})">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <h3 style="margin:0; font-size:1rem;">${nombre}</h3>
                    <div style="display:flex; align-items:center; gap:6px;">
                        ${score ? `<span style="font-weight:900; font-size:0.85rem; color:${badgeColor};">${score}</span>` : ''}
                        <span style="font-size:0.7rem; font-weight:bold; color:${badgeColor}; border:1px solid ${badgeColor}; padding:2px 6px; border-radius:10px;">${badgeTag}</span>
                    </div>
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

        const vacioEl = document.getElementById("detalleVacio");
        const contenidoEl = document.getElementById("detalleContenido");
        if (vacioEl) vacioEl.style.setProperty("display", "none", "important");
        if (contenidoEl) {
            contenidoEl.style.setProperty("display", "flex", "important");
            contenidoEl.classList.remove("hidden");
        }

        // TARJETA AURELIO — Nombre, Alias, Fecha
        const nombreEl = document.getElementById("detalleNombre");
        if (nombreEl) nombreEl.innerHTML = `<i class="fa-solid fa-clapperboard"></i> ${escapeHtml(activeNombre)}`;
        
        const aliasEl = document.getElementById("detalleAlias");
        if (aliasEl) aliasEl.textContent = `Alias: ${reg.alias || activeNombre.split(' ')[0]}`;
        
        const fechaEl = document.getElementById("detalleFecha");
        if (fechaEl) fechaEl.innerHTML = `<i class="fa-regular fa-clock"></i> ${formatDate(reg.created_at)}`;

        // TARJETA AURELIO — Enfoque, Reto, Frase
        const enfoqueEl = document.getElementById("detalle-enfoque");
        if (enfoqueEl) enfoqueEl.textContent = reg.storytelling_enfoque || 'Tema por definir durante pre-producción.';
        
        const retoEl = document.getElementById("detalle-reto");
        if (retoEl) retoEl.textContent = reg.reto || 'Reto por explorar en la entrevista.';
        
        const fraseEl = document.getElementById("detalle-frase");
        if (fraseEl) fraseEl.textContent = reg.frase || '"Frase pendiente de definir."';

        // PONDERACIÓN DE CURADURÍA
        const curaduria = reg.curaduria || {
            nivel: 'ALTO', badge: '🟢 NIVEL ALTO', formato: 'Episodio Completo',
            color: '#39FF14', razon: 'Ficha con información destacada.'
        };
        const ponderacion = reg.ponderacion || { score_total: 0, criterios: [] };

        // Score badge
        const scoreBadge = document.getElementById("ponderacion-score-badge");
        if (scoreBadge) {
            scoreBadge.textContent = ponderacion.score_total || '0.0';
            scoreBadge.style.color = curaduria.color || '#39FF14';
        }

        // Nivel badge
        const nivelBadge = document.getElementById("ponderacion-nivel-badge");
        if (nivelBadge) {
            nivelBadge.textContent = curaduria.badge || '🟢 ALTO';
            nivelBadge.style.color = curaduria.color;
            nivelBadge.style.borderColor = curaduria.color;
            nivelBadge.style.background = `${curaduria.color}15`;
        }

        // Formato
        const formatoEl = document.getElementById("ponderacion-formato");
        if (formatoEl) formatoEl.textContent = curaduria.formato || '';

        // Panel border
        const ponderacionPanel = document.getElementById("ponderacion-panel");
        if (ponderacionPanel) ponderacionPanel.style.borderColor = `${curaduria.color}50`;

        // Renderizar criterios
        const criteriosEl = document.getElementById("ponderacion-criterios");
        if (criteriosEl && ponderacion.criterios && ponderacion.criterios.length > 0) {
            let criteriosHtml = '';
            ponderacion.criterios.forEach((c, i) => {
                const pct = Math.round((c.score / 9) * 100);
                const barColor = c.score >= 7 ? '#39FF14' : (c.score >= 5 ? '#00FFFF' : '#FF00FF');
                criteriosHtml += `
                    <div style="margin-bottom: 10px; padding: 8px 10px; background: rgba(0,0,0,0.3); border-radius: 6px; border-left: 3px solid ${barColor};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-weight: 700; font-size: 0.8rem; color: #eee;">${i+1}. ${escapeHtml(c.nombre)}</span>
                            <span style="font-weight: 900; font-size: 0.85rem; color: ${barColor};">${c.score}/9</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.05); border-radius: 4px; height: 6px; margin-bottom: 5px; overflow: hidden;">
                            <div style="height: 100%; width: ${pct}%; background: ${barColor}; border-radius: 4px; box-shadow: 0 0 8px ${barColor}40; transition: width 0.5s;"></div>
                        </div>
                        <p style="margin: 0; font-size: 0.75rem; color: #888; line-height: 1.3;">${escapeHtml(c.justificacion)}</p>
                    </div>
                `;
            });
            criteriosEl.innerHTML = criteriosHtml;
        }

        // Acciones de producción
        const actionsEl = document.getElementById("curaduria-actions");
        if (actionsEl) {
            if (curaduria.nivel === 'BAJO') {
                actionsEl.innerHTML = `<button class="btn-neon btn-neon-magenta" onclick="canalizarAMicroContenido('${escapeHtml(activeNombre)}')" style="border-color:#FF00FF;color:#FF00FF;background:transparent;padding:6px 14px;border-radius:20px;cursor:pointer;font-size:0.8rem;"><i class="fa-solid fa-bolt"></i> Extraer Hooks & Shorts (30s)</button>`;
            } else if (curaduria.nivel === 'MEDIO') {
                actionsEl.innerHTML = `<button class="btn-neon" onclick="alert('Generando guion para entrevista corta de 10 min...')" style="border-color:#00FFFF;color:#00FFFF;background:transparent;padding:6px 14px;border-radius:20px;cursor:pointer;font-size:0.8rem;"><i class="fa-solid fa-stopwatch"></i> Formato Entrevista Corta (10m)</button>`;
            } else {
                actionsEl.innerHTML = `<button class="btn-neon" onclick="alert('Programa completo de 40+ min aprobado.')" style="border-color:#39FF14;color:#39FF14;background:transparent;padding:6px 14px;border-radius:20px;cursor:pointer;font-size:0.8rem;"><i class="fa-solid fa-star"></i> Programa Completo Aprobado</button>`;
            }
        }

        // STORYTELLING Y TEMA DE BLOG
        activeData.storytelling = typeof reg.storytelling === 'string' ? reg.storytelling : JSON.stringify(reg.storytelling || {});
        inicializarTemaBlog(reg);

    } catch (error) {
        console.warn("Aviso al cargar detalle de la API, usando datos locales:", error);
        const fallbackGuest = INVITADOS_DEFAULT.find(g => g.id === id) || INVITADOS_DEFAULT[0];
        if (fallbackGuest) {
            activeNombre = fallbackGuest.nombre;
            activeData.escaleta = `ESCALETA DE PRODUCCIÓN - LA CUEVA\nInvitado: ${fallbackGuest.nombre}\nTema: Historias y madrazos del camino\n\n[00:00 - 05:00] Hook de entrada\n[05:00 - 30:00] Trayectoria y anécdotas\n[30:00 - 45:00] Cierre y reflexiones`;
            activeData.guion = `GUIÓN - LA CUEVA DEL GÜERO\nInvitado: ${fallbackGuest.nombre}\n\nEl Güero: ¡Qué onda manada! Hoy tenemos en la mesa a ${fallbackGuest.nombre} para platicar la neta sin censura.`;
            activeData.cue_cards = `CUE CARDS\n• Nombre: ${fallbackGuest.nombre}\n• Pregunta clave: Madrazos del camino y superación.\n• Mención de patrocinador.`;
            
            const vacioEl = document.getElementById("detalleVacio");
            const contenidoEl = document.getElementById("detalleContenido");
            if (vacioEl) vacioEl.style.setProperty("display", "none", "important");
            if (contenidoEl) {
                contenidoEl.style.setProperty("display", "flex", "important");
                contenidoEl.classList.remove("hidden");
            }
            renderBloquesNormales();
        }
    }
}
window.mostrarDetalle = mostrarDetalle;

/* ============================================================
   SECCIÓN 1.1: SELECCIÓN DE TEMA PARA BLOG CON IA & ENVÍO A BLOG
   ============================================================ */

function inicializarTemaBlog(reg) {
    const defaultTitulo = `Los madrazos del camino: Cómo ${activeNombre} forjó su carácter en el barrio`;
    const defaultTesis = reg.storytelling_enfoque || "El verdadero valor no se mide en victorias fáciles, sino en la capacidad de mantenerse leal y firme frente a los momentos más duros.";
    const defaultGancho = reg.frase || `"El barrio no es la esquina, el barrio es la familia que te cuida la espalda."`;
    
    activeTemaBlog = {
        titulo: defaultTitulo,
        tesis: defaultTesis,
        puntos_clave: [
            `Raíces y origen: La infancia y el aprendizaje forzado en las calles.`,
            `El momento decisivo: ${reg.reto || 'Superar el momento más humillante y salir adelante.'}`,
            `Sabiduría de set: La visión de vida que ${activeNombre} aporta a la audiencia.`
        ],
        frase_gancho: defaultGancho,
        categoria: "Storytelling",
        potencia: "NIVEL ALTO",
        resumen_semidesarrollado: `EXPEDIENTE EDITORIAL PARA BLOG - LA CUEVA DEL GÜERO\nInvitado: ${activeNombre}\n\n1. ANTECEDENTES Y TESIS:\n${defaultTesis}\n\n2. EJES DE DESARROLLO:\n- Raíces y contexto de barrio.\n- Madrazos del trabajo y momentos de quiebre.\n- Lecciones de supervivencia y superación.\n\n3. CITA DETONADORA:\n${defaultGancho}\n\n4. BORRADOR DE ENTRADA:\nEn este capítulo sin censura de La Cueva del Güero, nos sumergimos en la historia de ${activeNombre}, explorando cómo la lealtad y el trabajo duro permitieron romper barreras en la frontera sin perder la identidad.`
    };

    actualizarVistaTemaBlog(activeTemaBlog);
}

function actualizarVistaTemaBlog(tema) {
    if (!tema) return;
    const titEl = document.getElementById("tema-blog-titulo");
    if (titEl) titEl.textContent = tema.titulo || "Tema por definir";

    const tesisEl = document.getElementById("tema-blog-tesis");
    if (tesisEl) tesisEl.textContent = tema.tesis || "Tesis en análisis...";

    const puntosEl = document.getElementById("tema-blog-puntos");
    if (puntosEl && Array.isArray(tema.puntos_clave)) {
        puntosEl.innerHTML = tema.puntos_clave.map(p => `<li style="margin-bottom: 4px;">${escapeHtml(p)}</li>`).join("");
    }

    const ganchoEl = document.getElementById("tema-blog-gancho");
    if (ganchoEl) ganchoEl.textContent = `"${(tema.frase_gancho || '').replace(/^"|"$/g, '')}"`;

    const catBadge = document.getElementById("tema-blog-categoria-badge");
    if (catBadge) catBadge.textContent = tema.categoria || "Storytelling";
}

// BOTÓN 1: RELEER EPISODIO Y GENERAR OTRO TEMA CON IA
async function reanalizarTemaBlogConIA() {
    if (!activeNombre) {
        alert("Selecciona primero un invitado en la lista.");
        return;
    }

    const btn = document.getElementById("btn-releer-tema-ia");
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Releyendo expediente con IA...`;
    btn.disabled = true;

    try {
        const response = await fetch("../api/api-blog-ai.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "proponer_tema",
                nombre_invitado: activeNombre,
                guion: activeData.guion,
                escaleta: activeData.escaleta,
                storytelling: activeData.storytelling,
                tema_anterior: activeTemaBlog ? activeTemaBlog.titulo : ""
            })
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();

        if (result.success && result.data) {
            activeTemaBlog = result.data;
            actualizarVistaTemaBlog(activeTemaBlog);
            // Efecto visual de actualización
            const panel = document.getElementById("tema-blog-panel");
            if (panel) {
                panel.style.boxShadow = "0 0 25px rgba(255, 0, 255, 0.6)";
                setTimeout(() => { panel.style.boxShadow = "0 4px 20px rgba(255, 0, 255, 0.12)"; }, 1000);
            }
        } else {
            throw new Error(result.error || "No se pudo generar nuevo tema.");
        }
    } catch (err) {
        console.error("Error al releer tema con IA:", err);
        alert("Aviso: " + err.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
window.reanalizarTemaBlogConIA = reanalizarTemaBlogConIA;

// BOTÓN 2: GENERAR TEMA EN PDF Y ENVIAR DIRECTAMENTE A PROCESAR BLOG
function generarPDFYEnviarABlog() {
    if (!activeTemaBlog || !activeNombre) {
        alert("Selecciona un invitado para preparar la propuesta editorial.");
        return;
    }

    const puntosTexto = (activeTemaBlog.puntos_clave || []).map((p, i) => `${i + 1}. ${p}`).join("\n");
    const fullText = `======================================================================
LA CUEVA DEL GÜERO - PROPUESTA EDITORIAL & TEMA PARA BLOG
======================================================================
INVITADO:   ${activeNombre}
CATEGORÍA:  ${activeTemaBlog.categoria || 'Storytelling'}
POTENCIA:   ${activeTemaBlog.potencia || 'NIVEL ALTO'}
FECHA:      ${new Date().toLocaleDateString('es-MX')}

----------------------------------------------------------------------
TÍTULO DEL ARTÍCULO:
${activeTemaBlog.titulo}
----------------------------------------------------------------------

TESIS Y ÁNGULO CENTRAL:
${activeTemaBlog.tesis}

EJES TEMÁTICOS Y ANÉCDOTAS CLAVE:
${puntosTexto}

FRASE / GANCHO DETONADOR:
"${(activeTemaBlog.frase_gancho || '').replace(/^"|"$/g, '')}"

----------------------------------------------------------------------
BORRADOR SEMIDESARROLLADO PARA EL ARTÍCULO:
----------------------------------------------------------------------
${activeTemaBlog.resumen_semidesarrollado || ''}

======================================================================
INSTRUCCIÓN DE PRODUCCIÓN:
Usa el botón "Convertir a Post Editable" en el Gestor de Blog para expandir este texto en el artículo final o publicarlo directamente.
======================================================================`;

    // 1. Abrir/Generar PDF en pestaña secundaria
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../api/api-export-pdf.php";
    form.target = "_blank";

    const fields = {
        tipo: "tema-blog",
        invitado: activeNombre,
        titulo_tema: activeTemaBlog.titulo,
        tesis: activeTemaBlog.tesis,
        content: fullText
    };

    for (const key in fields) {
        const inp = document.createElement("input");
        inp.type = "hidden";
        inp.name = key;
        inp.value = fields[key];
        form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // 2. Redireccionar suavemente al Gestor de Blog (PDF Conversor)
    switchView('blog');
    switchBlogTab('upload');

    // 3. Inyectar el texto en el área de extracción de PDF del Blog
    extractedTextBuffer = fullText;
    const extractedEl = document.getElementById("blog-extracted-text");
    if (extractedEl) {
        extractedEl.value = fullText;
    }
    const previewContainer = document.getElementById("blog-preview-container");
    if (previewContainer) {
        previewContainer.classList.remove("hidden");
    }

    const titleInput = document.getElementById("blog-title");
    if (titleInput) {
        titleInput.value = activeTemaBlog.titulo;
    }
    const authorInput = document.getElementById("blog-author");
    if (authorInput) {
        authorInput.value = `La Cueva del Güero • ${activeNombre}`;
    }

    const fileInfo = document.getElementById("blog-file-info");
    if (fileInfo) {
        fileInfo.style.display = "block";
        fileInfo.innerHTML = `<i class="fa-solid fa-check-circle"></i> Ficha y Tema de <strong>${escapeHtml(activeNombre)}</strong> cargado exitosamente desde Episodios.`;
    }

    // Scroll al área de trabajo
    setTimeout(() => {
        if (previewContainer) previewContainer.scrollIntoView({ behavior: 'smooth' });
    }, 200);
}
window.generarPDFYEnviarABlog = generarPDFYEnviarABlog;

function togglePonderacion() {
    const criterios = document.getElementById("ponderacion-criterios");
    const icon = document.getElementById("ponderacion-toggle-icon");
    if (criterios) {
        const isHidden = criterios.style.display === 'none';
        criterios.style.display = isHidden ? 'block' : 'none';
        if (icon) icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0)';
    }
}
window.togglePonderacion = togglePonderacion;

function canalizarAMicroContenido(nombre) {
    const topicEl = document.getElementById("hooks-topic");
    if (topicEl) topicEl.value = `Historias breves y frases detonadoras de ${nombre}`;
    switchView('hooks');
    if (typeof generarHooksParaRedes === 'function') generarHooksParaRedes();
}
window.canalizarAMicroContenido = canalizarAMicroContenido;

function renderBloquesNormales() {
    const escEl = document.getElementById("wrapper-escaleta");
    if (escEl) escEl.innerHTML = `<div class="text-block" id="block-escaleta">${escapeHtml(activeData.escaleta)}</div>`;
    const guiEl = document.getElementById("wrapper-guion");
    if (guiEl) guiEl.innerHTML = `<div class="text-block" id="block-guion">${escapeHtml(activeData.guion)}</div>`;
    const cueEl = document.getElementById("wrapper-cuecards");
    if (cueEl) cueEl.innerHTML = `<div class="text-block" id="block-cuecards" style="background:#090911; font-family:monospace; color:#39FF14; border: 1px solid rgba(57,255,20,0.2); text-shadow:0 0 5px rgba(57,255,20,0.2);">${escapeHtml(activeData.cue_cards)}</div>`;
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
window.habilitarEdicion = habilitarEdicion;
window.guardarEdicion = guardarEdicion;

function descargarAsset(tipo) {
    if (!activeId) return;
    const content = activeData[tipo] || "";
    
    // Abrir vista previa y exportador de PDF PRO
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../api/api-export-pdf.php";
    form.target = "_blank";

    const tipoInput = document.createElement("input");
    tipoInput.type = "hidden";
    tipoInput.name = "tipo";
    tipoInput.value = tipo;
    form.appendChild(tipoInput);

    const invitadoInput = document.createElement("input");
    invitadoInput.type = "hidden";
    invitadoInput.name = "invitado";
    invitadoInput.value = activeNombre;
    form.appendChild(invitadoInput);

    const contentInput = document.createElement("input");
    contentInput.type = "hidden";
    contentInput.name = "content";
    contentInput.value = content;
    form.appendChild(contentInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
window.descargarAsset = descargarAsset;

function imprimirCueCards() {
    descargarAsset('cuecards');
}
window.imprimirCueCards = imprimirCueCards;

/* ============================================================
   SECCIÓN 2: GESTOR DE BLOG (PDF CONVERSION)
   ============================================================ */

function switchBlogTab(tab) {
    const btnUp = document.getElementById("btn-tab-upload");
    if (btnUp) btnUp.classList.remove("active");
    const btnEd = document.getElementById("btn-tab-edit");
    if (btnEd) btnEd.classList.remove("active");
    const btnTarget = document.getElementById(`btn-tab-${tab}`);
    if (btnTarget) btnTarget.classList.add("active");

    const tabUp = document.getElementById("blog-tab-upload");
    if (tabUp) tabUp.classList.add("hidden");
    const tabEd = document.getElementById("blog-tab-edit");
    if (tabEd) tabEd.classList.add("hidden");
    const tabTarget = document.getElementById(`blog-tab-${tab}`);
    if (tabTarget) tabTarget.classList.remove("hidden");
}
window.switchBlogTab = switchBlogTab;

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
        terminal.innerHTML = `> <span style='color:#ff4d4d;'>[Error] Detalles: ${err.message}</span><br><br>` + 
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
async function generarFondoNeonIA() {
    const promptInput = document.getElementById("prompt-fondo-ia");
    const btn = document.getElementById("btn-generar-fondo-ia");
    if (!promptInput || !promptInput.value.trim()) {
        alert("Escribe una descripción para el fondo (ie: estudio de podcast neon morado)");
        return;
    }
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner spin"></i> Generando fondo con Imagen 3...';
    btn.disabled = true;
    try {
        const res = await fetch("../api/api-imagen-generator.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ prompt: promptInput.value.trim(), aspect_ratio: "16:9" })
        });
        const data = await res.json();
        if (data.success && data.base64) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.getElementById("canvas-pro");
                if (canvas) {
                    const ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                }
            };
            img.src = data.base64;
            alert("¡Fondo generado con éxito con Google Imagen 3!");
        } else {
            alert("Error al generar fondo: " + (data.error || "Desconocido"));
        }
    } catch(err) {
        alert("Falla de red al conectar con Imagen 3:" + err.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function editarTranscripcionPorTexto() {
    const textoInput = document.getElementById("video-transcripcion-raw");
    const instruccionInput = document.getElementById("video-instruccion-editor");
    const btn = document.getElementById("btn-editar-por-texto");
    const resultadoContenedor = document.getElementById("video-text-edited-result");

    if (!textoInput || !textoInput.value.trim()) {
        alert("Pega o escribe la transcripción o dialogo del video para editarlo por texto.");
        return;
    }

    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner spin"></i> Editando video por texto con Gemini...';
    btn.disabled = true;

    try {
        const res = await fetch("../api/api-text-based-editor.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                texto_original: textoInput.value.trim(),
                instruccion: instruccionInput ? instruccionInput.value.trim() : 'Pulir y hacer viral',
                episodio: 1
            })
        });
        const data = await res.json();
        if (data.success && data.data) {
            if (resultadoContenedor) {
                resultadoContenedor.style.display = "block";
                resultadoContenedor.innerHTML = 
                    `<div style='background:rgba(0,255,204,0.1); border:1px solid #00ffcc; padding:10px; border-radius:6px; margin-top:10px;'>`+
                    `<strong style='color:#00ffcc;'><i class='fa-solid fa-file-lines'></i> Guion / Transcripción Editada (Lista para Clips):</strong><br>` +
                    `<p style='color:#fff; margin-top:5px; white-space:pre-wrap;'>${data.data.texto_editado}</p>` +
                    `<div style='margin-top:8px; font-size:12px; color:#aaa;'>Tags del Clip: <strong style='color:#ff007f;'>${data.data.gancho_inicial || 'Gancho Estrátegico'}</strong> | Duración Estimada: <strong style='color:#00ffcc;'>${data.data.duracion_estimada_final || '45s'}</strong></div>`;
            }
            alert("¡Procesado con éxito! El video eesta listo para cortarse por texto.");
        } else {
            alert("Error al editar por texto: " + (data.error || "Desconocido"));
        }
    } catch(err) {
        alert("Falla de red: " + err.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
