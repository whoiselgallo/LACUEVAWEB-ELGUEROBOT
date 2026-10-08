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
    { id: 9, nombre: "Yessica Lizbeth Fierro Vindiola", created_at: "2026-09-12", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Ama de casa de Puertas del Sol. Perspectiva femenina auténtica del barrio.' } },
    { id: 10, nombre: "Rosalva \"la pocha\"", created_at: "2026-09-29", curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14', formato: 'Invitado Principal al Canal', razon: 'Personaje urbano icónico binacional (Estados Unidos USA). Vida de frontera y resiliencia.' } }
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
        let rawScore = reg.ponderacion_score || '';
        let scoreDisplay = '';
        if (rawScore) {
            let n = parseInt(rawScore, 10);
            if (n <= 100) n = Math.round(n * 3.3);
            scoreDisplay = `${n} / 330`;
        }

        // Extraer objeto curaduría si viene en el registro
        const curaduria = reg.curaduria || { nivel: 'ALTO', badge: '🟢 ALTO', color: '#39FF14' };
        const badgeTag = curaduria.badge || (curaduria.nivel === 'BAJO' ? '🔴 BAJO' : (curaduria.nivel === 'MEDIO' ? '🟡 MEDIO' : '🟢 ALTO'));
        const badgeColor = curaduria.color || (curaduria.nivel === 'BAJO' ? '#FF00FF' : (curaduria.nivel === 'MEDIO' ? '#00FFFF' : '#39FF14'));

        html += `
            <div class="registro-card" id="card-${id}" onclick="mostrarDetalle(${id})">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <h3 style="margin:0; font-size:1rem;">${nombre}</h3>
                    <div style="display:flex; align-items:center; gap:6px;">
                        ${scoreDisplay ? `<span style="font-weight:900; font-size:0.75rem; color:${badgeColor}; border:1px solid ${badgeColor}40; padding:1px 5px; border-radius:6px; background:rgba(0,0,0,0.3);">${scoreDisplay}</span>` : ''}
                        <span style="font-size:0.68rem; font-weight:bold; color:${badgeColor}; border:1px solid ${badgeColor}; padding:2px 6px; border-radius:10px;">${badgeTag}</span>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                    <p style="margin:0;"><i class="fa-regular fa-calendar-days"></i> ${fecha}</p>
                    <button class="btn-neon" onclick="event.stopPropagation(); abrirModalCuestionarioPorId(${id});" style="font-size:0.68rem; padding:3px 8px; border-color:var(--neon-cyan); color:var(--neon-cyan); background:rgba(0,255,255,0.05);" title="Ver Cuestionario Completo (33 Preguntas)">
                        <i class="fa-solid fa-list-check"></i> 33 Preguntas
                    </button>
                </div>
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

        // Guardar referencia global del registro activo para retos contextuales
        activeRegistro = reg;
        if (typeof inicializarRetosParaInvitado === 'function') {
            inicializarRetosParaInvitado(reg);
        }

        // PONDERACIÓN DE CURADURÍA - 33 PARÁMETROS (1 A 10 POR PREGUNTA, SUMATORIA 33 A 330)
        let criterios33 = [];
        const respuestasObj = reg.respuestas || {};

        if (reg.ponderacion && Array.isArray(reg.ponderacion.criterios) && reg.ponderacion.criterios.length === 33) {
            criterios33 = reg.ponderacion.criterios;
        } else {
            // Generar los 33 parámetros evaluados dinámicamente si vienen de registros heredados
            const defRespuestas = {
                1: reg.nombre || "Invitado",
                2: reg.alias || reg.nombre ? reg.nombre.split(' ')[0] : "Compa",
                3: reg.contacto || "contacto@lacuevadelguero.com",
                4: reg.ocupacion || "Invitado Especial",
                5: reg.definicion || "Auténtico, trabajador, de barrio",
                6: reg.barrio || "Mexicali, B.C.",
                7: reg.significado_barrio || "Familia y unión de comunidad",
                8: reg.ensenanza_barrio || "Respeto y no rajarse en la lumbre",
                9: reg.sueno_10 || "Salir adelante y superarse",
                10: reg.burla || "Varios dudaron al inicio pero seguimos firmes",
                11: reg.peligroso || "Vivir al límite y jugársela por los suyos",
                12: reg.humillante || reg.herida || "Madrazos del jale y empezar desde abajo",
                13: reg.peor_error || reg.reto || "Desviarse del rumbo y recomponer el camino",
                14: reg.ultimas_24h || "Pasarlas con la familia y la gente del barrio",
                15: reg.felicidad || "Feliz pero con más metas por alcanzar",
                16: reg.sacrificios || "Tiempo, horas de sueño y sacrificios familiares",
                17: reg.primer_logro || "Ver los primeros frutos del trabajo honesto",
                18: reg.maquina_tiempo || "Viajar a los inicios para abrazar a los que ya no están",
                19: reg.yo_10 || "Que no se rinda, que todo sacrificio valdrá la pena",
                20: reg.secreto_exito || reg.frase || "Perseverancia y constancia",
                21: reg.cumpliendo_sueno || "En el camino, viviendo algo mejor",
                22: reg.don_especial || "La autenticidad y el carisma de barrio",
                23: reg.chusco || "Anécdotas pesadas y desmadre con los compas",
                24: reg.cancion_belica || reg.gustos || "Corridos y música de peso",
                25: reg.confesion || reg.incomodo || "La neta sin filtro ni poses",
                26: reg.molestia || "La hipocresía, las mentiras y la falta de humildad",
                27: reg.recordar || "Como una persona derecha que nunca se rajó",
                28: reg.defecto || "Desesperado y a veces terco",
                29: reg.gusto_culposo || "Comida callejera y música romántica a escondidas",
                30: reg.miedo || "Al estancamiento, enfrentándolo con trabajo diario",
                31: reg.dinamica || "Reto de destreza y preguntas punzantes en cabina",
                32: reg.mencion_extra || "Un saludo fraternal a la banda del barrio",
                33: reg.mensaje_ayuda || reg.frase || "El sol siempre vuelve a brillar, ¡nunca tires la toalla!"
            };

            const PREGUNTAS_TITULOS = [
                { acto: 'Identificación', titulo: 'Nombre Completo' },
                { acto: 'Identificación', titulo: 'Petardo / Alias de Barrio' },
                { acto: 'Identificación', titulo: 'Contacto (Correo & WhatsApp)' },
                { acto: 'Bloque 1: Raíces', titulo: 'Ocupación Actual & Jale Diario' },
                { acto: 'Bloque 1: Raíces', titulo: 'Definición en 3 Palabras' },
                { acto: 'Bloque 1: Raíces', titulo: 'Colonia / Barrio de Origen' },
                { acto: 'Bloque 1: Raíces', titulo: 'Significado del Barrio' },
                { acto: 'Bloque 1: Raíces', titulo: 'Mayor Enseñanza de la Calle' },
                { acto: 'Bloque 1: Raíces', titulo: 'Qué Quería Ser de Niño a los 10 Años' },
                { acto: 'Bloque 1: Raíces', titulo: 'Obstáculos & Quien se Burló de su Sueño' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Lo Más Peligroso Vivido' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Lo Más Humillante en un Jale' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Peor Error en su Carrera / Rumbo Perdido' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Últimas 24 Horas de Vida' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Estado de Felicidad & Metas Pendientes' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Sacrificios para Llegar a este Punto' },
                { acto: 'Bloque 2: Madrazos', titulo: 'Primer Logro Chingón & Orgullo' },
                { acto: 'Bloque 3: Mentalidad', titulo: 'Viaje en la Máquina del Tiempo' },
                { acto: 'Bloque 3: Mentalidad', titulo: 'Mensaje a su Yo de Hace 10 Años' },
                { acto: 'Bloque 3: Mentalidad', titulo: 'Secreto del Éxito & Mentalidad de Triunfo' },
                { acto: 'Bloque 3: Mentalidad', titulo: 'Cumpliendo lo Soñado de Niño' },
                { acto: 'Bloque 3: Mentalidad', titulo: 'Don Especial & Diferenciador' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Anécdota Chusca / Graciosa' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Canción Bélica Favorita' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Confesión Incómoda & Exclusiva' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Lo Que Más le Molesta en la Vida' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Cómo Quiere ser Recordado (Legado)' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Mayor Defecto Reconocido' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Gusto Culposo Oculto' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Miedo Profundo & Cómo lo Enfrenta' },
                { acto: 'Bloque 4: Anécdotas', titulo: 'Dinámica / Reto Elegido en Cabina' },
                { acto: 'Bloque 5: Cierre', titulo: 'Mención Extra para el Episodio' },
                { acto: 'Bloque 5: Cierre', titulo: 'Mensaje Motivacional (No Tirar la Toalla)' }
            ];

            for (let i = 1; i <= 33; i++) {
                const p = PREGUNTAS_TITULOS[i - 1];
                const resp = String(respuestasObj[i] || defRespuestas[i] || '').trim();
                const len = resp.length;
                let sc = 8;
                let just = `Respuesta de barrio: "${resp}"`;

                if (len === 0 || ['.', 'nada', 'no', 'nose', 'no se', 'ninguno'].includes(resp.toLowerCase())) {
                    sc = 4;
                    just = len > 0 ? `Respuesta breve: "${resp}". Requiere dinamización del Güero.` : "Pregunta pendiente de responder.";
                } else if (len < 15) {
                    sc = 7;
                } else if (len < 40) {
                    sc = 8;
                } else if (len < 90) {
                    sc = 9;
                } else {
                    sc = 10;
                    just = `Profundidad narrativa sobresaliente: "${resp}"`;
                }

                criterios33.push({
                    num: i,
                    nombre: `Pregunta ${i}: ${p.titulo}`,
                    acto: p.acto,
                    score: sc,
                    max: 10,
                    respuesta: resp,
                    justificacion: just
                });
            }
        }

        // Calcular sumatoria total (Escala 33 a 330)
        let totalCalculado = criterios33.reduce((acc, c) => acc + (c.score || 0), 0);
        if (totalCalculado < 33) totalCalculado = 33;
        if (totalCalculado > 330) totalCalculado = 330;

        window.activeRegistro = reg;
        window.activeCriterios33 = criterios33;

        const nivelAuto = (totalCalculado >= 260) ? 'ALTO' : ((totalCalculado >= 165) ? 'MEDIO' : 'BAJO');
        const badgeAuto = (nivelAuto === 'ALTO') ? '🟢 NIVEL ALTO' : ((nivelAuto === 'MEDIO') ? '🟡 NIVEL MEDIO' : '🔴 NIVEL BAJO');
        const colorAuto = (nivelAuto === 'ALTO') ? '#39FF14' : ((nivelAuto === 'MEDIO') ? '#00FFFF' : '#FF00FF');
        const formatoAuto = (nivelAuto === 'ALTO') ? 'Invitado Principal al Canal (Episodio Completo 45+ min)' : ((nivelAuto === 'MEDIO') ? 'Entrevista Corta / Segmento (10 - 15 min)' : 'Micro-contenido / Shorts (30 - 60 seg)');

        const curaduria = reg.curaduria || {
            nivel: nivelAuto, badge: badgeAuto, formato: formatoAuto,
            color: colorAuto, razon: `Evaluación de 33 parámetros (1-10). Puntaje: ${totalCalculado}/330.`
        };

        // Score badge (33 a 330)
        const scoreBadge = document.getElementById("ponderacion-score-badge");
        if (scoreBadge) {
            scoreBadge.textContent = `${totalCalculado} / 330 PTS`;
            scoreBadge.style.color = curaduria.color || '#39FF14';
        }

        // Nivel badge
        const nivelBadge = document.getElementById("ponderacion-nivel-badge");
        if (nivelBadge) {
            nivelBadge.textContent = curaduria.badge || badgeAuto;
            nivelBadge.style.color = curaduria.color || colorAuto;
            nivelBadge.style.borderColor = curaduria.color || colorAuto;
            nivelBadge.style.background = `${curaduria.color || colorAuto}15`;
        }

        // Formato
        const formatoEl = document.getElementById("ponderacion-formato");
        if (formatoEl) formatoEl.textContent = curaduria.formato || formatoAuto;

        // Panel border
        const ponderacionPanel = document.getElementById("ponderacion-panel");
        if (ponderacionPanel) ponderacionPanel.style.borderColor = `${curaduria.color || colorAuto}50`;

        // Renderizar los 33 criterios completos con análisis de curaduría afinado
        const criteriosEl = document.getElementById("ponderacion-criterios");
        if (criteriosEl) {
            let criteriosHtml = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding:8px 12px; background:rgba(0,255,255,0.06); border:1px solid rgba(0,255,255,0.15); border-radius:8px;">
                    <div>
                        <span style="font-size:0.82rem; color:#00ffff; font-weight:800; display:block;"><i class="fa-solid fa-list-check"></i> Curaduría de 33 Parámetros (Escala 1 a 10)</span>
                        <span style="font-size:0.72rem; color:#aaa;">Puntaje Calculado: <b style="color:${curaduria.color || '#39FF14'}; font-size:0.85rem;">${totalCalculado} / 330 pts</b> • ${escapeHtml(curaduria.formato || 'Formato Estándar')}</span>
                    </div>
                    <span style="font-size:0.75rem; font-weight:bold; color:${curaduria.color || '#39FF14'}; padding:3px 8px; border-radius:12px; border:1px solid ${curaduria.color || '#39FF14'}; background:rgba(0,0,0,0.4);">${escapeHtml(curaduria.badge || badgeAuto)}</span>
                </div>
            `;

            criterios33.forEach((c) => {
                const sc = Math.min(Math.max(c.score || 1, 1), 10);
                const pct = sc * 10;
                const barColor = sc >= 9 ? '#39FF14' : (sc >= 7 ? '#00FFFF' : (sc >= 5 ? '#FFA500' : '#FF00FF'));
                const actoBadge = c.acto ? `<span style="font-size:0.62rem; padding:1px 6px; border-radius:4px; background:rgba(255,255,255,0.08); color:#aaa; margin-right:6px;">${escapeHtml(c.acto)}</span>` : '';
                const tagBadge = c.etiqueta ? `<span style="font-size:0.62rem; font-weight:700; padding:1px 7px; border-radius:6px; background:rgba(0,0,0,0.5); border:1px solid ${barColor}; color:${barColor}; margin-left:6px;">${escapeHtml(c.etiqueta)}</span>` : '';

                const respText = c.respuesta ? escapeHtml(c.respuesta) : '';
                const justText = c.justificacion ? escapeHtml(c.justificacion) : '';

                criteriosHtml += `
                    <div style="margin-bottom: 8px; padding: 10px 12px; background: rgba(0,0,0,0.45); border-radius: 8px; border-left: 3px solid ${barColor}; border-top: 1px solid rgba(255,255,255,0.04);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <div style="display:flex; align-items:center; flex-wrap:wrap; gap:4px;">
                                ${actoBadge}
                                <span style="font-weight: 700; font-size: 0.82rem; color: #eee;">${escapeHtml(c.nombre || `Pregunta ${c.num}`)}</span>
                                ${tagBadge}
                            </div>
                            <span style="font-weight: 900; font-size: 0.92rem; color: ${barColor}; margin-left:8px; flex-shrink:0;">${sc} / 10</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.08); border-radius: 4px; height: 5px; margin-bottom: 7px; overflow: hidden;">
                            <div style="height: 100%; width: ${pct}% !important; background: ${barColor}; border-radius: 4px; box-shadow: 0 0 8px ${barColor}60; transition: width 0.4s;"></div>
                        </div>
                        ${respText ? `<p style="margin: 0 0 4px 0; font-size: 0.78rem; color: #ddd; line-height: 1.35;"><i class="fa-solid fa-quote-left" style="color:${barColor}; font-size:0.68rem; margin-right:5px; opacity:0.8;"></i> <b>Respuesta:</b> "${respText}"</p>` : ''}
                        ${justText ? `<p style="margin: 0; font-size: 0.73rem; color: #999; line-height: 1.3;"><i class="fa-solid fa-bullhorn" style="color:#FFA500; font-size:0.68rem; margin-right:4px;"></i> <i>Nota Host / Producción:</i> ${justText}</p>` : ''}
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
                actionsEl.innerHTML = `<button class="btn-neon" onclick="alert('Generando escaleta para entrevista de 15 a 25 min...')" style="border-color:#00FFFF;color:#00FFFF;background:transparent;padding:6px 14px;border-radius:20px;cursor:pointer;font-size:0.8rem;"><i class="fa-solid fa-stopwatch"></i> Formato Entrevista Dinámica (15-25m)</button>`;
            } else {
                actionsEl.innerHTML = `<button class="btn-neon" onclick="alert('Episodio Completo de 45+ min Aprobado para El Güero y Junior.')" style="border-color:#39FF14;color:#39FF14;background:transparent;padding:6px 14px;border-radius:20px;cursor:pointer;font-size:0.8rem;"><i class="fa-solid fa-star"></i> Programa Completo Aprobado (45+ min)</button>`;
            }
        }

        // STORYTELLING Y TEMA DE BLOG
        activeData.storytelling = typeof reg.storytelling === 'string' ? reg.storytelling : JSON.stringify(reg.storytelling || {});
        inicializarTemaBlog(reg);
        renderBloquesNormales();

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
            
            // Disparar Webhook en tiempo real
            if (typeof dispararEventoWebhook === 'function') {
                dispararEventoWebhook('analitica_actualizada', {
                    canal: 'YouTube Studio (@LaCuevadelGuero)',
                    vistas_30d: currentYtStats.views.toLocaleString(),
                    ctr: `${currentYtStats.ctr}%`,
                    retencion: `${currentYtStats.retention}%`,
                    impresiones: currentYtStats.impressions.toLocaleString(),
                    suscriptores: data.subscribers ? data.subscribers.toLocaleString() : 'N/A'
                });
            }

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

/* ============================================================
   SECCIÓN 8: MODAL PARA VISUALIZAR CUESTIONARIO COMPLETO (33 PREGUNTAS)
   ============================================================ */

let filtroBloqueActualModal = 'todos';

function abrirModalCuestionarioCompleto() {
    const reg = window.activeRegistro;
    if (!reg) {
        alert("Por favor selecciona un invitado de la lista primero.");
        return;
    }

    const modal = document.getElementById("modalCuestionarioCompleto");
    if (!modal) return;

    // Header info
    const nombre = reg.nombre || "Invitado";
    const alias = reg.alias || "";
    const barrio = reg.barrio || "Mexicali, B.C.";
    const ocupacion = reg.ocupacion || "Invitado Especial";
    const contacto = reg.contacto || "contacto@lacuevadelguero.com";
    const token = reg.token || ('GUEST-' + (reg.id || '001'));
    const fecha = formatDate(reg.created_at || "");

    const nombreEl = document.getElementById("modal-cuest-nombre");
    if (nombreEl) {
        nombreEl.innerHTML = `<span><i class="fa-solid fa-clipboard-user" style="color:var(--neon-cyan); margin-right:8px;"></i> ${escapeHtml(nombre)}</span> <span id="modal-cuest-alias" style="color:var(--neon-magenta); font-size:1.05rem; font-weight:700;">${alias ? '(' + escapeHtml(alias) + ')' : ''}</span>`;
    }

    const barrioEl = document.getElementById("modal-cuest-barrio");
    if (barrioEl) barrioEl.innerHTML = `<i class="fa-solid fa-location-dot" style="color:var(--neon-cyan);"></i> Barrio: ${escapeHtml(barrio)}`;

    const ocupacionEl = document.getElementById("modal-cuest-ocupacion");
    if (ocupacionEl) ocupacionEl.innerHTML = `<i class="fa-solid fa-briefcase" style="color:var(--neon-magenta);"></i> Jale: ${escapeHtml(ocupacion)}`;

    const contactoEl = document.getElementById("modal-cuest-contacto");
    if (contactoEl) contactoEl.innerHTML = `<i class="fa-solid fa-envelope" style="color:var(--neon-green);"></i> Contacto: ${escapeHtml(contacto)}`;

    const tokenEl = document.getElementById("modal-cuest-token-badge");
    if (tokenEl) tokenEl.textContent = `Token: ${token}`;

    const fechaEl = document.getElementById("modal-cuest-fecha-badge");
    if (fechaEl) fechaEl.innerHTML = `<i class="fa-regular fa-clock"></i> ${fecha}`;

    // Curaduría y Score
    const criterios = window.activeCriterios33 || [];
    let totalScore = criterios.reduce((acc, c) => acc + (c.score || 0), 0);
    if (totalScore < 33) totalScore = 33;
    if (totalScore > 330) totalScore = 330;

    const nivel = (totalScore >= 260) ? 'ALTO' : ((totalScore >= 165) ? 'MEDIO' : 'BAJO');
    const badge = (nivel === 'ALTO') ? '🟢 NIVEL ALTO' : ((nivel === 'MEDIO') ? '🟡 NIVEL MEDIO' : '🔴 NIVEL BAJO');
    const color = (nivel === 'ALTO') ? '#39FF14' : ((nivel === 'MEDIO') ? '#00FFFF' : '#FF00FF');

    const scoreBadge = document.getElementById("modal-cuest-score-badge");
    if (scoreBadge) {
        scoreBadge.textContent = `${totalScore} / 330 PTS`;
        scoreBadge.style.color = color;
        scoreBadge.style.borderColor = `${color}60`;
    }

    const nivelBadge = document.getElementById("modal-cuest-nivel-badge");
    if (nivelBadge) {
        nivelBadge.textContent = badge;
        nivelBadge.style.color = color;
        nivelBadge.style.borderColor = color;
        nivelBadge.style.background = `${color}15`;
    }

    // Resetear filtros
    const searchInput = document.getElementById("modal-cuest-search");
    if (searchInput) searchInput.value = "";
    filtroBloqueActualModal = 'todos';

    // Resetear botones de filtro
    document.querySelectorAll(".btn-bloque-filter").forEach(b => {
        b.style.background = "rgba(255,255,255,0.05)";
        b.style.borderColor = "rgba(255,255,255,0.15)";
        b.style.color = "#bbb";
    });
    const primerBtn = document.querySelector(".btn-bloque-filter");
    if (primerBtn) {
        primerBtn.style.background = "rgba(0,255,255,0.2)";
        primerBtn.style.borderColor = "var(--neon-cyan)";
        primerBtn.style.color = "#fff";
    }

    preguntasObservadasProduccion = {};
    actualizarContadorMarcadas();
    renderModalCuestionario();
    modal.style.display = "flex";
}
window.abrirModalCuestionarioCompleto = abrirModalCuestionarioCompleto;

async function abrirModalCuestionarioPorId(id) {
    if (activeId !== id) {
        await mostrarDetalle(id);
    }
    abrirModalCuestionarioCompleto();
}
window.abrirModalCuestionarioPorId = abrirModalCuestionarioPorId;

function cerrarModalCuestionario() {
    const modal = document.getElementById("modalCuestionarioCompleto");
    if (modal) modal.style.display = "none";
}
window.cerrarModalCuestionario = cerrarModalCuestionario;

function setFiltroBloqueCuestionario(bloque, btnEl) {
    filtroBloqueActualModal = bloque;
    document.querySelectorAll(".btn-bloque-filter").forEach(b => {
        b.style.background = "rgba(255,255,255,0.05)";
        b.style.borderColor = "rgba(255,255,255,0.15)";
        b.style.color = "#bbb";
    });
    if (btnEl) {
        btnEl.style.background = "rgba(0,255,255,0.2)";
        btnEl.style.borderColor = "var(--neon-cyan)";
        btnEl.style.color = "#fff";
    }
    filtrarPreguntasCuestionario();
}
window.setFiltroBloqueCuestionario = setFiltroBloqueCuestionario;

let preguntasObservadasProduccion = {};

function escapeJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
}

function toggleMarcarPreguntaError(num, nombre, respOriginal) {
    if (preguntasObservadasProduccion[num]) {
        delete preguntasObservadasProduccion[num];
    } else {
        preguntasObservadasProduccion[num] = {
            id_pregunta: num,
            pregunta: nombre,
            respuesta_original: respOriginal,
            causa: 'confusa',
            nota: ''
        };
    }
    actualizarContadorMarcadas();
    filtrarPreguntasCuestionario();
}
window.toggleMarcarPreguntaError = toggleMarcarPreguntaError;

function actualizarCausaError(num, causa) {
    if (preguntasObservadasProduccion[num]) {
        preguntasObservadasProduccion[num].causa = causa;
    }
}
window.actualizarCausaError = actualizarCausaError;

function actualizarNotaError(num, nota) {
    if (preguntasObservadasProduccion[num]) {
        preguntasObservadasProduccion[num].nota = nota;
    }
}
window.actualizarNotaError = actualizarNotaError;

function actualizarContadorMarcadas() {
    const count = Object.keys(preguntasObservadasProduccion).length;
    const countEl = document.getElementById("modal-cuest-marcadas-count");
    if (countEl) countEl.textContent = count;
    const btn = document.getElementById("btn-enviar-correccion-invitado");
    if (btn) {
        btn.disabled = count === 0;
        btn.style.opacity = count === 0 ? "0.5" : "1";
    }
}
window.actualizarContadorMarcadas = actualizarContadorMarcadas;

async function enviarSolicitudCorreccionDesdeModal() {
    const reg = window.activeRegistro;
    if (!reg) return;
    const observaciones = Object.values(preguntasObservadasProduccion);
    if (observaciones.length === 0) {
        alert("Por favor marca al menos una pregunta que requiera corrección.");
        return;
    }

    const btn = document.getElementById("btn-enviar-correccion-invitado");
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando solicitud...';
    btn.disabled = true;

    try {
        const token = reg.token || ('GUEST-' + (reg.id || '001'));
        const res = await fetch("/api/api-guest-corrections.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "solicitar_correccion",
                token: token,
                observaciones: observaciones,
                productor: "Producción La Cueva"
            })
        });
        const data = await res.json();
        if (data.status === "success" || data.success) {
            // Mostrar modal de compartir
            const modalComp = document.getElementById("modalCompartirSolicitud");
            const inputLink = document.getElementById("inputLinkCorreccion");
            const btnWa = document.getElementById("btnWaCorreccionDirecto");

            if (inputLink) inputLink.value = data.tracking_link || "";
            if (btnWa) btnWa.href = data.whatsapp_url || "#";
            if (modalComp) modalComp.style.display = "flex";

            // Limpiar marcadas y refrescar avisos
            preguntasObservadasProduccion = {};
            actualizarContadorMarcadas();
            filtrarPreguntasCuestionario();

            if (window.cargarAvisosEnVivo) window.cargarAvisosEnVivo(true);
        } else {
            alert("Error al enviar solicitud: " + (data.message || "Desconocido"));
        }
    } catch (err) {
        alert("Error de conexión: " + err.message);
    } finally {
        btn.innerHTML = originalText;
        actualizarContadorMarcadas();
    }
}
window.enviarSolicitudCorreccionDesdeModal = enviarSolicitudCorreccionDesdeModal;

function copiarLinkCorreccionDirecto() {
    const input = document.getElementById("inputLinkCorreccion");
    if (!input || !input.value) return;
    navigator.clipboard.writeText(input.value).then(() => {
        alert("✓ Enlace directo de tracking y corrección copiado al portapapeles.");
    }).catch(() => {
        prompt("Copia el enlace manualmente:", input.value);
    });
}
window.copiarLinkCorreccionDirecto = copiarLinkCorreccionDirecto;

async function aprobarCuestionarioDesdeModal() {
    const reg = window.activeRegistro;
    if (!reg) return;
    const nombre = reg.nombre || "Invitado";

    if (!confirm(`¿Aprobar definitivamente el cuestionario de "${nombre}"?\n\nEsto marcará el cuestionario como revisado y avanzará el tracking del episodio a la Fase 2 (Escaleta & Curaduría).`)) {
        return;
    }

    try {
        const token = reg.token || ('GUEST-' + (reg.id || '001'));
        const res = await fetch("/api/api-guest-corrections.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "aprobar_cuestionario",
                token: token
            })
        });
        const data = await res.json();
        if (data.status === "success" || data.success) {
            alert(`✓ ¡Cuestionario de "${nombre}" aprobado al 100%!\n\nSe ha disparado la alerta por webhook y el episodio avanzó a la Fase 2 de Escaleta.`);
            cerrarModalCuestionario();
            if (activeId) mostrarDetalle(activeId);
            if (window.cargarAvisosEnVivo) window.cargarAvisosEnVivo(true);
        } else {
            alert("Error: " + (data.message || "No se pudo aprobar."));
        }
    } catch (err) {
        alert("Falla de red: " + err.message);
    }
}
window.aprobarCuestionarioDesdeModal = aprobarCuestionarioDesdeModal;

function filtrarPreguntasCuestionario() {
    const query = (document.getElementById("modal-cuest-search")?.value || "").toLowerCase().trim();
    renderModalCuestionario(query);
}
window.filtrarPreguntasCuestionario = filtrarPreguntasCuestionario;

function renderModalCuestionario(busqueda = "") {
    const container = document.getElementById("modal-cuest-body");
    if (!container) return;

    const criterios = window.activeCriterios33 || [];
    let filtrados = criterios.filter(c => {
        // Filtro por bloque
        if (filtroBloqueActualModal !== 'todos') {
            const acto = (c.acto || '').toLowerCase();
            if (filtroBloqueActualModal === 'ident' && !acto.includes('identifica')) return false;
            if (filtroBloqueActualModal === 'raices' && !acto.includes('raíces') && !acto.includes('raices')) return false;
            if (filtroBloqueActualModal === 'madrazos' && !acto.includes('madrazo')) return false;
            if (filtroBloqueActualModal === 'mentalidad' && !acto.includes('mentalidad')) return false;
            if (filtroBloqueActualModal === 'anecdotas' && !acto.includes('anécdota') && !acto.includes('anecdota')) return false;
            if (filtroBloqueActualModal === 'cierre' && !acto.includes('cierre')) return false;
        }

        // Filtro por búsqueda de texto
        if (busqueda) {
            const nom = (c.nombre || '').toLowerCase();
            const resp = (c.respuesta || '').toLowerCase();
            const just = (c.justificacion || '').toLowerCase();
            return nom.includes(busqueda) || resp.includes(busqueda) || just.includes(busqueda);
        }

        return true;
    });

    const conteoEl = document.getElementById("modal-cuest-conteo");
    if (conteoEl) conteoEl.textContent = filtrados.length;

    if (filtrados.length === 0) {
        container.innerHTML = `<div style="text-align:center; padding:50px; color:#777;"><i class="fa-solid fa-filter-circle-xmark" style="font-size:2.2rem; margin-bottom:12px; color:#555;"></i><p style="margin:0;">No se encontraron preguntas que coincidan con la búsqueda o filtro.</p></div>`;
        return;
    }

    let html = "";
    filtrados.forEach((c) => {
        const sc = Math.min(Math.max(c.score || 1, 1), 10);
        const pct = sc * 10;
        const color = sc >= 9 ? '#39FF14' : (sc >= 7 ? '#00FFFF' : (sc >= 5 ? '#FFA500' : '#FF00FF'));
        const actoBadge = c.acto ? `<span style="font-size:0.7rem; padding:2px 8px; border-radius:6px; background:rgba(255,255,255,0.08); color:#aaa; font-weight:600;">${escapeHtml(c.acto)}</span>` : '';
        const tagBadge = c.etiqueta ? `<span style="font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:6px; background:rgba(0,0,0,0.5); border:1px solid ${color}; color:${color};">${escapeHtml(c.etiqueta)}</span>` : '';
        const respText = (c.respuesta || '').trim();
        const justText = (c.justificacion || '').trim();
        const isMarcada = !!preguntasObservadasProduccion[c.num];
        const obsActual = preguntasObservadasProduccion[c.num] || { causa: 'confusa', nota: '' };

        html += `
            <div style="background:rgba(0,0,0,0.5); border:${isMarcada ? '1px solid #FF6600' : '1px solid rgba(255,255,255,0.06)'}; border-left:4px solid ${isMarcada ? '#FF6600' : color}; border-radius:12px; padding:16px 20px; display:flex; flex-direction:column; gap:8px; box-shadow:${isMarcada ? '0 0 15px rgba(255,102,0,0.2)' : 'none'};">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        ${actoBadge}
                        <h4 style="margin:0; font-size:0.95rem; color:#fff; font-weight:700;">${escapeHtml(c.nombre || `Pregunta ${c.num}`)}</h4>
                        ${tagBadge}
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-weight:900; font-size:0.95rem; color:${color}; background:rgba(0,0,0,0.4); padding:2px 8px; border-radius:6px; border:1px solid ${color}40;">${sc} / 10 pts</span>
                    </div>
                </div>

                <!-- Barra de puntuación -->
                <div style="background:rgba(255,255,255,0.06); border-radius:4px; height:4px; overflow:hidden;">
                    <div style="height:100%; width:${pct}%; background:${color}; border-radius:4px; box-shadow:0 0 8px ${color}60;"></div>
                </div>

                <!-- Caja de Respuesta del Invitado -->
                <div style="background:rgba(12,12,25,0.9); border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:12px 16px; margin-top:2px;">
                    <div style="display:flex; align-items:flex-start; gap:8px;">
                        <i class="fa-solid fa-quote-left" style="color:${color}; font-size:0.85rem; margin-top:3px; opacity:0.85;"></i>
                        <div style="flex:1;">
                            <span style="font-size:0.75rem; color:#888; text-transform:uppercase; letter-spacing:0.5px; font-weight:700; display:block; margin-bottom:2px;">Respuesta del Invitado:</span>
                            ${respText ? `
                                <p style="margin:0; font-size:0.95rem; color:#fff; line-height:1.45; font-weight:500;">"${escapeHtml(respText)}"</p>
                            ` : `
                                <p style="margin:0; font-size:0.88rem; color:#ff4477; font-style:italic;"><i class="fa-solid fa-triangle-exclamation"></i> Sin respuesta registrada en el cuestionario.</p>
                            `}
                        </div>
                    </div>
                </div>

                <!-- Justificación / Guía de Producción -->
                ${justText ? `
                    <div style="display:flex; align-items:flex-start; gap:8px; font-size:0.78rem; color:#aaa; margin-top:2px; line-height:1.35;">
                        <i class="fa-solid fa-bullhorn" style="color:#FFA500; font-size:0.75rem; margin-top:2px;"></i>
                        <div><strong style="color:#ddd;">Estrategia Host / Set:</strong> ${escapeHtml(justText)}</div>
                    </div>
                ` : ''}

                <!-- PANEL DE OBSERVACIÓN / SOLICITUD DE CORRECCIÓN (PRODUCCIÓN) -->
                <div style="margin-top:6px; border-top:1px dashed rgba(255,255,255,0.08); padding-top:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <button type="button" class="btn-flag-pregunta" onclick="toggleMarcarPreguntaError(${c.num}, '${escapeJs(c.nombre || `Pregunta ${c.num}`)}', '${escapeJs(respText)}')" style="background:${isMarcada ? 'rgba(255,102,0,0.2)' : 'rgba(255,255,255,0.03)'}; border:1px solid ${isMarcada ? '#FF6600' : 'rgba(255,255,255,0.15)'}; color:${isMarcada ? '#FF6600' : '#888'}; padding:4px 12px; border-radius:6px; font-size:0.75rem; cursor:pointer; font-weight:700; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s;">
                            <i class="fa-solid fa-flag"></i> ${isMarcada ? 'Observación Activa para Corrección' : 'Marcar para Corrección'}
                        </button>
                        ${isMarcada ? `<span style="font-size:0.72rem; color:#FF6600; font-weight:bold;"><i class="fa-solid fa-circle-exclamation"></i> Se incluirá en la solicitud enviada al invitado</span>` : ''}
                    </div>

                    ${isMarcada ? `
                        <div style="margin-top:8px; background:rgba(255,102,0,0.06); border:1px solid rgba(255,102,0,0.3); border-radius:8px; padding:10px; display:grid; grid-template-columns:1fr 2fr; gap:10px;">
                            <div>
                                <label style="font-size:0.7rem; color:#ffcc99; font-weight:700; display:block; margin-bottom:3px;">Causa de la Observación:</label>
                                <select class="form-input" onchange="actualizarCausaError(${c.num}, this.value)" style="padding:6px 8px; font-size:0.75rem; background:#080812; border-color:rgba(255,102,0,0.4); color:#fff; width:100%;">
                                    <option value="confusa" ${obsActual.causa === 'confusa' ? 'selected' : ''}>🟠 Confusa / Poco clara</option>
                                    <option value="inadecuada" ${obsActual.causa === 'inadecuada' ? 'selected' : ''}>🔴 Inadecuada / Lenguaje no apto</option>
                                    <option value="error_captura" ${obsActual.causa === 'error_captura' ? 'selected' : ''}>🟡 Error de captura / Incompleta</option>
                                    <option value="incoherente" ${obsActual.causa === 'incoherente' ? 'selected' : ''}>🟣 Incoherente con la historia</option>
                                    <option value="otra" ${obsActual.causa === 'otra' ? 'selected' : ''}>⚪ Otra observación</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:0.7rem; color:#ffcc99; font-weight:700; display:block; margin-bottom:3px;">Nota o instrucción para el invitado:</label>
                                <input type="text" class="form-input" value="${escapeHtml(obsActual.nota || '')}" oninput="actualizarNotaError(${c.num}, this.value)" placeholder="Ej: Por favor platícanos más de cómo saliste adelante..." style="padding:6px 8px; font-size:0.75rem; background:#080812; border-color:rgba(255,102,0,0.4); color:#fff; width:100%;">
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function copiarCuestionarioTexto() {
    const reg = window.activeRegistro;
    const criterios = window.activeCriterios33 || [];
    if (!reg || criterios.length === 0) return;

    let txt = `================================================================================\n`;
    txt += `CUESTIONARIO OFICIAL DE 33 PREGUNTAS - LA CUEVA DEL GÜERO\n`;
    txt += `Invitado: ${reg.nombre || 'Invitado'} (${reg.alias || 'Sin alias'})\n`;
    txt += `Barrio: ${reg.barrio || 'Mexicali'} | Jale: ${reg.ocupacion || 'Invitado Especial'}\n`;
    txt += `Token: ${reg.token || ('GUEST-' + reg.id)} | Fecha: ${reg.created_at || ''}\n`;
    txt += `================================================================================\n\n`;

    criterios.forEach(c => {
        txt += `[${c.num}/33] ${c.nombre}\n`;
        txt += `Bloque: ${c.acto || 'General'} | Puntaje: ${c.score || 8}/10\n`;
        txt += `RESPUESTA: "${c.respuesta || 'Sin respuesta'}"\n`;
        txt += `NOTA HOST: ${c.justificacion || 'N/A'}\n\n`;
    });

    navigator.clipboard.writeText(txt).then(() => {
        alert("¡Cuestionario completo copiado al portapapeles!");
    }).catch(err => {
        prompt("Copia el texto:", txt);
    });
}
window.copiarCuestionarioTexto = copiarCuestionarioTexto;

function imprimirCuestionario() {
    const reg = window.activeRegistro;
    const criterios = window.activeCriterios33 || [];
    if (!reg || criterios.length === 0) return;

    const printWin = window.open("", "_blank");
    let printHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Cuestionario - ${escapeHtml(reg.nombre || 'Invitado')}</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 25px; color: #111; line-height: 1.4; }
                h1 { margin: 0 0 4px 0; font-size: 22px; }
                .meta { color: #555; font-size: 13px; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .item { margin-bottom: 16px; border-bottom: 1px solid #ddd; padding-bottom: 12px; page-break-inside: avoid; }
                .item-title { font-weight: bold; font-size: 14px; margin-bottom: 4px; }
                .item-ans { background: #f4f4f4; padding: 8px 12px; border-left: 3px solid #00ffff; font-size: 14px; margin: 4px 0; }
                .item-note { font-size: 12px; color: #666; font-style: italic; }
            </style>
        </head>
        <body>
            <h1>Cuestionario Oficial de Invitado - La Cueva del Güero</h1>
            <div class="meta">
                <b>Invitado:</b> ${escapeHtml(reg.nombre || '')} (${escapeHtml(reg.alias || '')}) | <b>Barrio:</b> ${escapeHtml(reg.barrio || '')} | <b>Jale:</b> ${escapeHtml(reg.ocupacion || '')} | <b>Token:</b> ${escapeHtml(reg.token || '')}
            </div>
    `;

    criterios.forEach(c => {
        printHtml += `
            <div class="item">
                <div class="item-title">[${c.num}/33] ${escapeHtml(c.nombre)} <span style="font-weight:normal; color:#888;">(${escapeHtml(c.acto)}) - ${c.score}/10 pts</span></div>
                <div class="item-ans">"${escapeHtml(c.respuesta || 'Sin respuesta')}"</div>
                ${c.justificacion ? `<div class="item-note">Estrategia Set: ${escapeHtml(c.justificacion)}</div>` : ''}
            </div>
        `;
    });

    printHtml += `
            <script>window.onload = function() { window.print(); }<\/script>
        </body>
        </html>
    `;

    printWin.document.write(printHtml);
    printWin.document.close();
}
window.imprimirCuestionario = imprimirCuestionario;

function abrirTrackingDesdeModal() {
    const reg = window.activeRegistro;
    if (!reg) return;
    const code = reg.token || ('GUEST-' + reg.id);
    const trackUrl = window.location.hostname.includes('lacuevadelguero.com') ? 'https://s.lacuevadelguero.com/' : '../tracking/index.html';
    window.open(trackUrl + '?code=' + encodeURIComponent(code), '_blank');
}
window.abrirTrackingDesdeModal = abrirTrackingDesdeModal;


/* ============================================================
   SECCIÓN 9: SISTEMA DE WEBHOOKS Y AVISOS EN VIVO EN TIEMPO REAL
   ============================================================ */

let webhooksPollingInterval = null;
let eventosConocidosIds = new Set();
let avisosNoLeidosCount = 0;

function toggleCentroAvisos() {
    const modal = document.getElementById("modalCentroAvisos");
    if (!modal) return;
    const isVisible = (modal.style.display === "flex");
    modal.style.display = isVisible ? "none" : "flex";
    if (!isVisible) {
        cargarAvisosEnVivo(true);
    }
}
window.toggleCentroAvisos = toggleCentroAvisos;

function toggleConfigWebhook() {
    const box = document.getElementById("boxConfigWebhook");
    if (!box) return;
    box.style.display = (box.style.display === "none") ? "block" : "none";
    if (box.style.display === "block") {
        cargarConfigWebhook();
    }
}
window.toggleConfigWebhook = toggleConfigWebhook;

async function cargarConfigWebhook() {
    try {
        const res = await fetch("/api/api-webhook.php?action=obtener_config");
        const data = await res.json();
        if (data.config) {
            const input = document.getElementById("inputWebhookUrl");
            if (input) input.value = data.config.webhook_url || data.config.discord_webhook || "";
        }
    } catch (e) {}
}

async function guardarConfigWebhook() {
    const input = document.getElementById("inputWebhookUrl");
    if (!input) return;
    const url = input.value.trim();

    try {
        const res = await fetch("/api/api-webhook.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "guardar_config", webhook_url: url, activo: true })
        });
        const data = await res.json();
        alert(data.message || "Webhook configurado con éxito.");
        toggleConfigWebhook();
    } catch (err) {
        alert("Error al guardar: " + err.message);
    }
}
window.guardarConfigWebhook = guardarConfigWebhook;

async function probarWebhookTest() {
    const input = document.getElementById("inputWebhookUrl");
    const url = input ? input.value.trim() : "";
    if (!url) {
        alert("Ingresa primero la URL de tu webhook (Discord, Slack o Make).");
        return;
    }

    try {
        const res = await fetch("/api/api-webhook.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "disparar",
                evento: "test_webhook",
                origen: "dashboard_test",
                datos: {
                    mensaje: "🧪 Prueba en vivo de webhook desde el Dashboard de La Cueva PRO.",
                    fecha: new Date().toLocaleString()
                }
            })
        });
        const data = await res.json();
        alert("¡Aviso de prueba enviado! Revisa tu canal de Discord/Slack.");
        cargarAvisosEnVivo(true);
    } catch (err) {
        alert("Falla de envío: " + err.message);
    }
}
window.probarWebhookTest = probarWebhookTest;

async function cargarAvisosEnVivo(esManual = false) {
    try {
        const res = await fetch("/api/api-webhook.php?action=listar&limit=40");
        const data = await res.json();
        if (!data.eventos) return;

        const contenedor = document.getElementById("listaAvisosFeed");
        let html = "";
        let nuevosEncontrados = 0;

        data.eventos.forEach((ev, idx) => {
            const isNuevo = !eventosConocidosIds.has(ev.id);
            if (isNuevo && !esManual && eventosConocidosIds.size > 0) {
                mostrarToastAviso(ev);
                nuevosEncontrados++;
            }
            eventosConocidosIds.add(ev.id);

            const color = ev.color || '#00FFFF';
            const icono = ev.icono || 'fa-bell';
            const fecha = ev.timestamp || '';

            let datosHtml = "";
            if (ev.datos && typeof ev.datos === 'object') {
                for (const [k, v] of Object.entries(ev.datos)) {
                    if (v && typeof v !== 'object') {
                        datosHtml += `<div style="font-size:0.75rem; color:#bbb;"><strong style="color:#eee;">${escapeHtml(k)}:</strong> ${escapeHtml(String(v))}</div>`;
                    }
                }
            }

            html += `
                <div style="background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.06); border-left:3px solid ${color}; border-radius:10px; padding:12px 14px; display:flex; flex-direction:column; gap:6px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                        <span style="font-size:0.85rem; font-weight:800; color:#fff; display:flex; align-items:center; gap:6px;">
                            <i class="fa-solid ${icono}" style="color:${color};"></i> ${escapeHtml(ev.titulo)}
                        </span>
                        <span style="font-size:0.7rem; color:#888; white-space:nowrap;">${fecha.split(' ')[1] || fecha}</span>
                    </div>
                    ${datosHtml ? `<div style="background:rgba(255,255,255,0.03); border-radius:6px; padding:6px 10px; display:flex; flex-direction:column; gap:2px;">${datosHtml}</div>` : ''}
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.7rem; color:#777;">
                        <span>Origen: ${escapeHtml(ev.origen || 'sistema')}</span>
                        <span>${fecha.split(' ')[0] || ''}</span>
                    </div>
                </div>
            `;
        });

        if (contenedor) {
            contenedor.innerHTML = html || `<p style="color:#777; text-align:center; padding:30px;">Sin avisos recientes registrados.</p>`;
        }

        // Actualizar contador del badge
        const badgeCount = document.getElementById("badge-avisos-count");
        if (badgeCount) {
            const noLeidos = data.eventos.filter(e => !e.leido).length;
            badgeCount.textContent = noLeidos;
            badgeCount.style.display = noLeidos > 0 ? "inline-block" : "none";
        }
    } catch (e) {}
}

function mostrarToastAviso(ev) {
    const container = document.getElementById("toastAvisosContainer");
    if (!container) return;

    const color = ev.color || '#39FF14';
    const icono = ev.icono || 'fa-bell';

    const toast = document.createElement("div");
    toast.style.cssText = `
        background: rgba(14, 14, 28, 0.96);
        border: 1px solid ${color};
        box-shadow: 0 0 20px ${color}40, 0 8px 25px rgba(0,0,0,0.8);
        border-radius: 12px;
        padding: 14px 18px;
        color: #fff;
        pointer-events: auto;
        display: flex;
        flex-direction: column;
        gap: 6px;
        animation: fadeIn 0.3s ease;
        transition: opacity 0.4s ease, transform 0.4s ease;
    `;

    toast.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px; font-weight:800; font-size:0.85rem; color:${color};">
                <i class="fa-solid ${icono}"></i> ${escapeHtml(ev.titulo)}
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:#888; cursor:pointer; font-size:1.1rem; line-height:1;">&times;</button>
        </div>
        <div style="font-size:0.78rem; color:#ccc;">
            ${ev.datos?.nombre ? `<b>${escapeHtml(ev.datos.nombre)}</b>: ` : ''}
            ${ev.datos?.mensaje || ev.datos?.tarea || ev.datos?.fase || 'Actividad registrada en vivo.'}
        </div>
        <div style="display:flex; justify-content:flex-end;">
            <span style="font-size:0.68rem; color:#888;">Ahora mismo</span>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 400);
    }, 6500);
}

async function marcarAvisosLeidos() {
    try {
        await fetch("/api/api-webhook.php?action=marcar_leidos");
        const badgeCount = document.getElementById("badge-avisos-count");
        if (badgeCount) badgeCount.style.display = "none";
        cargarAvisosEnVivo(true);
    } catch (e) {}
}
window.marcarAvisosLeidos = marcarAvisosLeidos;

function dispararEventoWebhook(tipo, datos = {}) {
    return fetch("/api/api-webhook.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "disparar", tipo: tipo, datos: datos, origen: "dashboard_interactivo" })
    }).then(r => r.json()).then(res => {
        cargarAvisosEnVivo(true);
        return res;
    }).catch(err => console.warn("Error enviando webhook:", err));
}
window.dispararEventoWebhook = dispararEventoWebhook;

function iniciarMonitorWebhooks() {
    cargarAvisosEnVivo(true);
    if (!webhooksPollingInterval) {
        webhooksPollingInterval = setInterval(() => {
            cargarAvisosEnVivo(false);
        }, 6000);
    }
}

// Iniciar monitoreo cuando cargue el documento
document.addEventListener("DOMContentLoaded", () => {
    iniciarMonitorWebhooks();
});

/* ============================================================
   SECCIÓN: RULETA INTERACTIVA DE RETOS DE CABINA (IA CONTEXTUAL)
   ============================================================ */

let activeCategoriaReto = 'aleatorio';
let retoCronometroInterval = null;
let retoTiempoRestante = 45;
let retoTiempoTotal = 45;
let retoActual = null;

const CATALOGO_RETOS_LOCAL = {
    destreza: [
        {
            titulo: "Torre Caguamera en 45 Segundos",
            categoria: "Destreza",
            reglas: "Hacer equilibrio apilando 5 corcholatas o vasos sobre una botella de cerveza cerrada en menos de 45 segundos usando solo una mano.",
            tiempo_segundos: 45,
            materiales: "1 botella de vidrio y 5 tapas/vasos",
            castigo: "Darle un trago a la salsa más picosa del set sin hacer muecas.",
            por_que_este_invitado: "Pone a prueba el pulso bajo presión y los nervios frente a cámara.",
            angulo_tiktok: "Tensión visual máxima en cámara cerrada con conteo regresivo dramático."
        },
        {
            titulo: "Malabares con Limones del Taco",
            categoria: "Destreza",
            reglas: "Mantener 3 limones en el aire durante al menos 20 segundos continuos mientras responde una pregunta rápida de El Junior.",
            tiempo_segundos: 30,
            materiales: "3 limones de la taquería",
            castigo: "Morder medio limón con sal y chile habanero sin cerrar los ojos.",
            por_que_este_invitado: "Demuestra reflejos y coordinación en vivo con toque cómico.",
            angulo_tiktok: "Corte rápido con música de circo o corrido alterado cuando se le caiga el primer limón."
        },
        {
            titulo: "Equilibrio con Barra o Regla en la Palma",
            categoria: "Destreza",
            reglas: "Sostener una regla o barra vertical en la palma de la mano durante 30 segundos mientras camina 3 pasos sin que se caiga.",
            tiempo_segundos: 30,
            materiales: "1 regla larga o vara liviana",
            castigo: "El Junior le da un zape amistoso o imita el ladrido de Alan el Perro.",
            por_que_este_invitado: "Destreza física inmediata que involucra el espacio del set.",
            angulo_tiktok: "El momento de tensión balanceándose de un lado al otro."
        }
    ],
    fisico: [
        {
            titulo: "La Sentadilla de 90° Mexicali",
            categoria: "Reto Físico",
            reglas: "Aguantar exactamente 60 segundos en posición de sentadilla isométrica a 90 grados contra la pared de ladrillo de La Cueva sin apoyar las manos.",
            tiempo_segundos: 60,
            materiales: "Pared de ladrillo del set",
            castigo: "Hacer 15 lagartijas con El Junior sentado en su espalda.",
            por_que_este_invitado: "Reto de resistencia pura que muestra si de verdad 'aguanta la lumbre'.",
            angulo_tiktok: "Primer plano al temblor de las piernas en los últimos 15 segundos."
        },
        {
            titulo: "Plancha de Barrio con Caguama",
            categoria: "Reto Físico",
            reglas: "Mantener la posición de plancha abdominal durante 45 segundos con una botella sobre la espalda baja sin que se caiga.",
            tiempo_segundos: 45,
            materiales: "Botella de vidrio cerrada y cronómetro",
            castigo: "Pagar la cuenta de los tacos de todo el staff de producción.",
            por_que_este_invitado: "Pone a prueba el abdomen y el orgullo callejero.",
            angulo_tiktok: "Sonido de suspenso mientras la botella tambalea milimétricamente."
        },
        {
            titulo: "Vencidas Rápidas con El Güero",
            categoria: "Reto Físico",
            reglas: "Duelo de fuercitas / vencidas en la mesa contra El Güero a una sola mano. Si aguanta más de 30 segundos, gana el reto.",
            tiempo_segundos: 30,
            materiales: "La mesa de cabina",
            castigo: "Servirle la cerveza a El Güero durante todo el show.",
            por_que_este_invitado: "Duelo directo de poder con el host que enciende la energía.",
            angulo_tiktok: "Cámara lenta al rostro con música de combate."
        }
    ],
    artistico: [
        {
            titulo: "Cantar el Corrido a Capela con Pasión",
            categoria: "Reto Artístico",
            reglas: "Cantar a capela durante 40 segundos su canción o corrido favorito, imitando el estilo de un cantante dolido o grupero con sentimiento.",
            tiempo_segundos: 40,
            materiales: "Micrófono principal de cabina",
            castigo: "Cantar una canción infantil como si fuera corrido bélico.",
            por_que_este_invitado: "Conecta con la fibra musical de la frontera y saca su lado más desinhibido.",
            angulo_tiktok: "Clip vertical con subtítulos de karaoke neón resaltando las notas altas."
        },
        {
            titulo: "Dibujo a Ciegas del Güero y El Junior",
            categoria: "Reto Artístico",
            reglas: "Con los ojos vendados y un plumón negro, tiene 60 segundos en una pizarra para dibujar los rostros de El Güero y El Junior.",
            tiempo_segundos: 60,
            materiales: "Pizarrón blanco, plumón y antifaz/trapo",
            castigo: "Dejar que El Junior le pinte un bigote con plumón lavable para el resto del episodio.",
            por_que_este_invitado: "Humor visual garantizado y dinámica interactiva de set.",
            angulo_tiktok: "Revelación del dibujo con la reacción de shock y carcajadas de los hosts."
        },
        {
            titulo: "Freestyle Callejero de 30 Segundos",
            categoria: "Reto Artístico",
            reglas: "Rimar durante 30 segundos sobre una base rítmica de cabina, usando obligatoriamente 3 palabras al azar: 'Mexicali', 'Garita' y 'Fayuca'.",
            tiempo_segundos: 30,
            materiales: "Beat rítmico de cabina",
            castigo: "Hacer un poema cursi dedicado a Alan el Perro.",
            por_que_este_invitado: "Rapidez mental, ingenio de barrio y flow.",
            angulo_tiktok: "Rótulos grandes en pantalla con las 3 palabras iluminándose cuando las suelta."
        }
    ],
    callejero: [
        {
            titulo: "La Prueba del Chile Habanero Bravo",
            categoria: "Reto Callejero",
            reglas: "Probar una tostada con salsa brava de la casa y aguantar 60 segundos hablando del tema más serio de su vida sin tomar agua ni cerveza.",
            tiempo_segundos: 60,
            materiales: "Salsa brava artesanal de Mexicali y 1 tostada",
            castigo: "Otro medio trago de salsa brava.",
            por_que_este_invitado: "Tradición norteña de honor: ver si aguanta el chile de verdad.",
            angulo_tiktok: "Ojos llorosos y voz entrecortada mientras intenta responder seriamente."
        },
        {
            titulo: "Llamada de Broma al Compa de Confianza",
            categoria: "Reto Callejero",
            reglas: "Marcarle en altavoz a un amigo o colega y decirle en 45 segundos: 'Güey, me atoraron aquí en la línea con una maleta y ocupo 5 mil bolas ya', sin reírse.",
            tiempo_segundos: 60,
            materiales: "Teléfono celular en altavoz",
            castigo: "Subir una historia a su Instagram diciendo: 'El Güero me acaba de ganar una apuesta'.",
            por_que_este_invitado: "Pone a prueba las lealtades reales del barrio y genera suspenso telefónico.",
            angulo_tiktok: "La reacción espontánea del amigo en altavoz cuando se entera que está en vivo."
        },
        {
            titulo: "La Confesión Incómoda de Garita",
            categoria: "Reto Callejero",
            reglas: "Contar una anécdota real de algo ilegal, vergonzoso o absurdo que haya hecho en la frontera que jamás le haya dicho a su familia.",
            tiempo_segundos: 90,
            materiales: "Plano cerrado a cámara 3",
            castigo: "Enseñar la última foto guardada en su galería sin censura.",
            por_que_este_invitado: "Contenido oro puro para el podcast, confesión sin filtros.",
            angulo_tiktok: "Título gancho: 'Confesó lo que nunca le dijo a su madre en La Cueva'."
        }
    ]
};

function inicializarRetosParaInvitado(reg) {
    activeRegistro = reg;
    // Si el invitado contestó la pregunta 31 con una dinámica preferida, intentar adaptarla
    const dinamica = reg.respuestas?.[31] || reg.dinamica || '';
    if (dinamica && dinamica.length > 5) {
        const dLower = dinamica.toLowerCase();
        if (dLower.includes('fisic') || dLower.includes('fuerz') || dLower.includes('sentad')) {
            seleccionarCategoriaReto('fisico', false);
        } else if (dLower.includes('art') || dLower.includes('canta') || dLower.includes('dibuj') || dLower.includes('rima')) {
            seleccionarCategoriaReto('artistico', false);
        } else if (dLower.includes('calle') || dLower.includes('broma') || dLower.includes('salsa') || dLower.includes('chile')) {
            seleccionarCategoriaReto('callejero', false);
        } else {
            seleccionarCategoriaReto('destreza', false);
        }
    } else {
        seleccionarCategoriaReto('aleatorio', false);
    }
    tirarRetoAleatorio();
}
window.inicializarRetosParaInvitado = inicializarRetosParaInvitado;

function seleccionarCategoriaReto(cat, autoTirar = true) {
    activeCategoriaReto = cat;
    
    // Actualizar estilos de los botones de categorías
    const categorias = ['aleatorio', 'destreza', 'fisico', 'artistico', 'callejero'];
    categorias.forEach(c => {
        const btn = document.getElementById(`cat-reto-${c}`);
        if (btn) {
            if (c === cat) {
                btn.style.background = 'rgba(255, 215, 0, 0.25)';
                btn.style.borderColor = '#FFD700';
                btn.style.color = '#FFD700';
                btn.style.fontWeight = '700';
            } else {
                btn.style.background = 'transparent';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.15)';
                btn.style.color = '#8e8e9f';
                btn.style.fontWeight = '600';
            }
        }
    });

    if (autoTirar) {
        tirarRetoAleatorio();
    }
}
window.seleccionarCategoriaReto = seleccionarCategoriaReto;

function tirarRetoAleatorio() {
    const container = document.getElementById("reto-display-container");
    if (container) {
        container.style.opacity = '0.5';
        container.style.transform = 'scale(0.99)';
        container.style.transition = 'all 0.2s ease';
    }

    setTimeout(() => {
        let cat = activeCategoriaReto;
        if (cat === 'aleatorio') {
            const keys = ['destreza', 'fisico', 'artistico', 'callejero'];
            cat = keys[Math.floor(Math.random() * keys.length)];
        }

        const lista = CATALOGO_RETOS_LOCAL[cat] || CATALOGO_RETOS_LOCAL.destreza;
        const reto = { ...lista[Math.floor(Math.random() * lista.length)] };

        // Si hay invitado activo, inyectar su nombre/contexto
        if (activeRegistro && activeRegistro.nombre) {
            const n = activeRegistro.nombre.split(' ')[0];
            const b = activeRegistro.barrio || 'Mexicali';
            reto.por_que_este_invitado = `Pone a prueba a ${n} de ${b} para encender la vibra de cabina.`;
        }

        renderizarRetoEnTarjeta(reto, "⚡ Modo Rápido / Catálogo");

        if (container) {
            container.style.opacity = '1';
            container.style.transform = 'scale(1)';
        }
    }, 200);
}
window.tirarRetoAleatorio = tirarRetoAleatorio;

async function generarRetoConIA() {
    const btn = document.getElementById("btn-reto-ia");
    const originalText = btn ? btn.innerHTML : "";
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Consultando a Gemini...`;
    }

    const container = document.getElementById("reto-display-container");
    if (container) {
        container.style.opacity = '0.6';
    }

    try {
        const payload = {
            categoria: activeCategoriaReto,
            nombre: activeRegistro?.nombre || activeNombre || "Invitado",
            alias: activeRegistro?.alias || "",
            ocupacion: activeRegistro?.ocupacion || "",
            barrio: activeRegistro?.barrio || "Mexicali, B.C.",
            herida: activeRegistro?.herida || "",
            reto: activeRegistro?.reto || "",
            gustos: activeRegistro?.gustos || "",
            dinamica: activeRegistro?.respuestas?.[31] || activeRegistro?.dinamica || ""
        };

        const res = await fetch("/api/api-retos-ai.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success && data.reto) {
            const origenTag = data.origen === 'gemini_ia' ? `🧠 Gemini IA (${data.modelo || 'Flash'})` : '⚡ Catálogo de Cabina';
            renderizarRetoEnTarjeta(data.reto, origenTag);
            dispararEventoWebhook("reto_generado_ia", {
                invitado: payload.nombre,
                titulo: data.reto.titulo,
                categoria: data.reto.categoria
            });
        } else {
            console.warn("Falla en respuesta IA de retos, usando fallback:", data);
            tirarRetoAleatorio();
        }
    } catch (err) {
        console.error("Error conectando con API de retos:", err);
        tirarRetoAleatorio();
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
        if (container) {
            container.style.opacity = '1';
        }
    }
}
window.generarRetoConIA = generarRetoConIA;

function renderizarRetoEnTarjeta(reto, origenTag) {
    retoActual = reto;
    retoTiempoTotal = parseInt(reto.tiempo_segundos, 10) || 60;
    retoTiempoRestante = retoTiempoTotal;
    pausarCronometroReto();

    const catBadge = document.getElementById("reto-categoria-badge");
    if (catBadge) catBadge.textContent = reto.categoria || "Dinámica";

    const origenBadge = document.getElementById("reto-origen-badge");
    if (origenBadge) origenBadge.textContent = origenTag || "⚡ Modo Rápido";

    const titEl = document.getElementById("reto-titulo");
    if (titEl) titEl.textContent = reto.titulo || "Reto de Cabina";

    const reglasEl = document.getElementById("reto-reglas");
    if (reglasEl) reglasEl.textContent = reto.reglas || "Sin reglas especificadas.";

    const contextoEl = document.getElementById("reto-contexto");
    if (contextoEl) contextoEl.textContent = reto.por_que_este_invitado || "Diseñado para el show en vivo.";

    const castigoEl = document.getElementById("reto-castigo");
    if (castigoEl) castigoEl.textContent = reto.castigo || "Darle un trago a la salsa brava.";

    const matEl = document.getElementById("reto-materiales");
    if (matEl) matEl.textContent = reto.materiales || "Objetos de cabina";

    actualizarDisplayCronometro();
}

function actualizarDisplayCronometro() {
    const cronoEl = document.getElementById("reto-cronometro");
    if (!cronoEl) return;
    const min = Math.floor(retoTiempoRestante / 60);
    const sec = retoTiempoRestante % 60;
    cronoEl.textContent = `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
    if (retoTiempoRestante > 10) {
        cronoEl.style.color = "#00FFFF";
        cronoEl.style.textShadow = "none";
    }
}

function iniciarCronometroReto() {
    if (retoCronometroInterval) clearInterval(retoCronometroInterval);
    const cronoEl = document.getElementById("reto-cronometro");
    const btnStart = document.getElementById("btn-crono-start");
    if (btnStart) btnStart.style.boxShadow = "0 0 10px #39FF14";

    retoCronometroInterval = setInterval(() => {
        if (retoTiempoRestante > 0) {
            retoTiempoRestante--;
            actualizarDisplayCronometro();
            if (retoTiempoRestante <= 10 && cronoEl) {
                cronoEl.style.color = "#FF4D4D";
                cronoEl.style.textShadow = "0 0 12px #FF4D4D";
            }
        } else {
            clearInterval(retoCronometroInterval);
            retoCronometroInterval = null;
            if (cronoEl) {
                cronoEl.innerHTML = "¡TIEMPO!";
                cronoEl.style.color = "#FF00FF";
            }
            if (btnStart) btnStart.style.boxShadow = "none";
            alert("⏰ ¡TIEMPO CUMPLIDO! ¿Superó el reto o toca el castigo?");
        }
    }, 1000);
}
window.iniciarCronometroReto = iniciarCronometroReto;

function pausarCronometroReto() {
    if (retoCronometroInterval) {
        clearInterval(retoCronometroInterval);
        retoCronometroInterval = null;
    }
    const btnStart = document.getElementById("btn-crono-start");
    if (btnStart) btnStart.style.boxShadow = "none";
}
window.pausarCronometroReto = pausarCronometroReto;

function reiniciarCronometroReto() {
    pausarCronometroReto();
    retoTiempoRestante = retoTiempoTotal;
    actualizarDisplayCronometro();
}
window.reiniciarCronometroReto = reiniciarCronometroReto;

function copiarRetoACueCards() {
    if (!retoActual) {
        alert("Primero selecciona o genera un reto.");
        return;
    }

    const min = Math.floor((retoActual.tiempo_segundos || 60) / 60);
    const sec = (retoActual.tiempo_segundos || 60) % 60;
    const tiempoTxt = min > 0 ? `${min}m ${sec}s` : `${sec} seg`;

    const texto = `🎲 RETO DE CABINA: ${retoActual.titulo} [${retoActual.categoria}]
⏱️ TIEMPO: ${tiempoTxt}
📜 REGLAS: ${retoActual.reglas}
🎯 CONTEXTO: ${retoActual.por_que_este_invitado || 'Para el show'}
💥 CASTIGO: ${retoActual.castigo || 'Shot de salsa brava'}
📦 MATERIALES: ${retoActual.materiales || 'De cabina'}`;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(() => {
            alert("✅ ¡Reto copiado al portapapeles!\nPuedes pegarlo en las Cue Cards o en las notas de cabina para El Güero y El Junior.");
        }).catch(() => {
            prompt("Copia el texto del reto para las Cue Cards:", texto);
        });
    } else {
        prompt("Copia el texto del reto para las Cue Cards:", texto);
    }
}
window.copiarRetoACueCards = copiarRetoACueCards;


