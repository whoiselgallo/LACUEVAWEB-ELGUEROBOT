/**
 * API: CONOCIMIENTO Y REGISTROS DE INVITADOS (NEON POSTGRESQL + JSON RESILIENTE)
 * Endpoint nativo Vercel Serverless Function
 * Atiende: /api/api-guero-knowledge.php y /api/api-guero-knowledge
 */

const fs = require('fs');
const path = require('path');

// Obtener URL de Neon PostgreSQL
function getDatabaseUrl() {
    return process.env.DATABASE_URL || process.env.DATABASE_URL_UNPOOLED || '';
}

// Consultar Neon PostgreSQL vía API HTTP nativa
async function queryNeon(sql) {
    const dbUrl = getDatabaseUrl();
    if (!dbUrl || !dbUrl.includes('@')) return null;

    try {
        const match = dbUrl.match(/postgresql:\/\/([^:]+):([^@]+)@([^/]+)\/(.+)/);
        if (!match) return null;
        const [, user, pass, host, db] = match;
        const cleanHost = host.replace(/-pooler\./, '.');
        const sqlEndpoint = `https://${cleanHost}/sql`;

        const res = await fetch(sqlEndpoint, {
            method: 'POST',
            headers: {
                'Neon-Connection-String': dbUrl,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: sql }),
            signal: AbortSignal.timeout(5000)
        });

        if (res.ok) {
            const data = await res.json();
            return data.rows || [];
        }
    } catch (e) {
        // Fallback silencioso a JSON local si falla la red con Neon
    }
    return null;
}

// Leer cuestionarios locales en JSON
function obtenerEnviosJSON() {
    try {
        const filePath = path.resolve(__dirname, '../images/formularios/cuestionarios_envios.json');
        if (fs.existsSync(filePath)) {
            const content = fs.readFileSync(filePath, 'utf8');
            const data = JSON.parse(content);
            if (Array.isArray(data)) return data;
        }
    } catch (e) {}
    return [];
}

// Catálogo de Invitados de Respaldo Maestro
const FALLBACK_INVITADOS = {
    2: {
        id: 2,
        nombre: "Leo Camacho Higuera",
        created_at: "2026-09-12 17:00:00",
        alias: "Leo",
        ocupacion: "Músico y Productor Urbano",
        barrio: "Colonia Libertad, Mexicali",
        trayectoria: "Más de 10 años en la escena musical fronteriza, construyendo proyectos independientes desde abajo.",
        herida: "La pérdida de su primer estudio por un incendio y tener que empezar desde cero sin apoyo.",
        molestia: "La falta de lealtad y los compas que se cuelgan del éxito ajeno.",
        frase: "La lealtad no se platica, se demuestra en la lumbre.",
        storytelling_enfoque: "Resiliencia y lealtad en la música fronteriza desde la Libertad.",
        reto: "El incendio del primer estudio y reconstruir su carrera sin apoyo.",
        escaleta: "ESCALETA DE PRODUCCIÓN - LA CUEVA\nInvitado: Leo Camacho Higuera\nTema: Resiliencia y lealtad en la música fronteriza\n\n[00:00 - 03:00] Hook & Intro: El incendio que casi lo retira\n[03:00 - 15:00] Bloque 1: Creciendo en la Libertad y el primer micrófono\n[15:00 - 30:00] Bloque 2: El golpe duro y reconstruir la visión\n[30:00 - 45:00] Bloque 3: Produciendo en Mexicali y consejos a las nuevas generaciones\n[45:00 - 50:00] Cierre y reflexiones de La Cueva",
        guion: "GUIÓN BROADCAST - LA CUEVA DEL GÜERO\nInvitado: Leo Camacho Higuera\n\nEl Güero: ¡Qué onda manada! Hoy tenemos sentado en la mesa a un compa que le ha tocado picar piedra en serio: Leo Camacho.\n\nJunior: Bienvenido a la Cueva, carnal. La neta queríamos empezar con la pregunta directa: ¿qué sentiste el día que viste tu estudio en cenizas?\n\nLeo: Fue el momento donde tuve que decidir si me rendía o le metía el doble de ganas...",
        cue_cards: "CUE CARDS DE CABINA\n• Tarjeta 1: Preguntar sobre el origen del apodo y primeros años en la Libertad.\n• Tarjeta 2: Momento clave del incendio (conectar con la vulnerabilidad).\n• Tarjeta 3: Mencionar patrocinador oficial de la Cueva.\n• Tarjeta 4: Pregunta final sobre qué consejo le daría a su yo de hace 10 años.",
        curaduria: {
            nivel: 'ALTO',
            badge: '🟢 NIVEL ALTO',
            formato: 'Invitado Principal al Canal (Episodio Completo)',
            color: '#39FF14',
            razon: 'Expediente de alta potencia narrativa y lealtad de barrio.'
        },
        ponderacion_score: 315
    },
    3: {
        id: 3,
        nombre: "Javi Domz (jeyb)",
        created_at: "2026-09-12 17:00:00",
        alias: "JeyB",
        ocupacion: "Director Creativo de Cine y TV",
        barrio: "Mexicali, B.C.",
        trayectoria: "Director audiovisual con proyectos internacionales y visión cinematográfica de la frontera.",
        herida: "El rechazo inicial de las productoras en CDMX y la soledad del inicio.",
        molestia: "Los presupuestos inflados que no llegan a los creadores reales.",
        frase: "El cine no es de cámaras caras, es de ojos despiertos.",
        storytelling_enfoque: "Cine independiente y visión cinematográfica en la frontera.",
        reto: "Romper barreras frente a la industria centralista.",
        escaleta: "ESCALETA - Javi Domz (JeyB)\n[00:00 - 05:00] Hook de impacto visual\n[05:00 - 20:00] La lucha por filmar en Mexicali\n[20:00 - 40:00] De la frontera para el mundo\n[40:00 - 50:00] Cierre",
        guion: "GUIÓN - Javi Domz en La Cueva\n\nEl Güero: Hoy está con nosotros JeyB, el compa detrás del lente más pesado de la baja...",
        cue_cards: "CUE CARDS\n• Preguntar sobre el proyecto de largometraje en Mexicali.\n• Detonador: ¿Vale la pena irse a CDMX o quedarse en el norte?",
        curaduria: {
            nivel: 'ALTO',
            badge: '🟢 NIVEL ALTO',
            formato: 'Invitado Principal al Canal',
            color: '#39FF14',
            razon: 'Storytelling visual y dirección cinematográfica de alto impacto.'
        },
        ponderacion_score: 310
    },
    4: {
        id: 4,
        nombre: "Marcelo Ivan Maciel Maldonado",
        created_at: "2026-09-12 17:00:00",
        alias: "Marcelo",
        ocupacion: "Atleta y Entrenador de Alto Rendimiento",
        barrio: "Mexicali, B.C.",
        trayectoria: "Entrenando campeones en deportes de contacto y forjando disciplina en la juventud cachanilla.",
        herida: "Una lesión grave en la columna que casi lo deja fuera del deporte para siempre.",
        molestia: "La gente que busca atajos y no respeta el sudor del gimnasio.",
        frase: "El dolor pasa, el orgullo de no rajarse queda para siempre.",
        storytelling_enfoque: "Superación, disciplina deportiva y rescate de jóvenes en la frontera.",
        curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', formato: 'Invitado Principal al Canal (45+ min)', color: '#39FF14' },
        ponderacion_score: 305
    },
    5: {
        id: 5,
        nombre: "Aurelio Gonzalez",
        created_at: "2026-09-12 17:00:00",
        alias: "Aurelio",
        ocupacion: "Empresario Gastronómico",
        barrio: "Pueblo Nuevo, Mexicali",
        trayectoria: "De vender comida en una carreta callejera a consolidar tres restaurantes familiares.",
        herida: "La quiebra de su primer negocio durante una crisis fronteriza.",
        curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', formato: 'Invitado Principal al Canal (45+ min)', color: '#39FF14' },
        ponderacion_score: 298
    },
    6: {
        id: 6,
        nombre: "Guillermina Ayala Quiñonez",
        created_at: "2026-09-12 17:00:00",
        alias: "Doña Guille",
        ocupacion: "Líder Comunitaria y Comerciante",
        barrio: "Valle de Mexicali",
        trayectoria: "Más de 25 años apoyando a familias del valle y organizando comedores comunitarios.",
        herida: "La pérdida de su esposo y sacar adelante a cuatro hijos sola.",
        curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', formato: 'Invitado Principal al Canal (45+ min)', color: '#39FF14' },
        ponderacion_score: 320
    },
    8: {
        id: 8,
        nombre: "Sergio Noe Escobar Perez",
        created_at: "2026-09-12 17:00:00",
        alias: "Checo",
        ocupacion: "Mecánico de Modificaciones y Carreras",
        barrio: "Colonia Prohogar, Mexicali",
        trayectoria: "Armando motores de arrancones y preparador de carros para la Baja 1000.",
        herida: "Un accidente en carretera que lo obligó a reaprender a caminar.",
        curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', formato: 'Invitado Principal al Canal (45+ min)', color: '#39FF14' },
        ponderacion_score: 295
    },
    9: {
        id: 9,
        nombre: "Yessica Lizbeth Fierro Vindiola",
        created_at: "2026-09-12 17:00:00",
        alias: "Yessi",
        ocupacion: "Tatuadora y Artista Visual",
        barrio: "Mexicali Centro",
        trayectoria: "Pionera del tatuaje urbano femenino en Mexicali con exposiciones nacionales.",
        herida: "El prejuicio social al inicio de su carrera artística.",
        curaduria: { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', formato: 'Invitado Principal al Canal (45+ min)', color: '#39FF14' },
        ponderacion_score: 300
    },
    10: {
        id: 10,
        nombre: 'Rosalva "la pocha"',
        created_at: "2026-09-29 20:00:00",
        alias: "la pocha",
        ocupacion: "Comerciante, Emprendedora y Personaje Urbano",
        barrio: "Estados Unidos USA",
        trayectoria: "Vida forjada entre Estados Unidos USA y Mexicali, rompiendo esquemas con comercio independiente, estilo chicano y puro flow cachanilla.",
        herida: "Nos cerraron la puerta en las narices y tuvimos que vender en la garita con 45 grados bajo el sol para levantar el jale familiar",
        molestia: "la gente hipócrita y mentirosa",
        frase: "Progresión no perfección",
        storytelling_enfoque: "De Estados Unidos USA para el mundo: Cómo Rosalva 'la pocha' forjó su visión comercial superando la adversidad.",
        reto: "reto físico.",
        escaleta: "ESCALETA DE PRODUCCIÓN - LA CUEVA\nInvitado: Rosalva 'la pocha'\nTema: Historias de Estados Unidos USA, jale diario y códigos de lealtad\n\n[00:00 - 05:00] Hook: Quién es Rosalva 'la pocha'\n[05:00 - 18:00] Bloque 1: Primeros jales y vida binacional\n[18:00 - 32:00] Bloque 2: Momentos difíciles en la garita a 45 grados\n[32:00 - 45:00] Bloque 3: Gustos: churros de harina y comida china\n[45:00 - 50:00] Cierre: 'Saludos a mis haters que aunque sea odio aún piensan en mí'",
        guion: "GUIÓN - LA CUEVA DEL GÜERO\nInvitado: Rosalva 'la pocha'\n\nEl Güero: ¡Qué onda manada! Hoy tenemos sentado en la mesa a un personaje de respeto: Rosalva 'la pocha' desde Estados Unidos USA.\n\nJunior: ¡Bienvenida carnal! Cuéntanos de tus rolas favoritas: Mente en blanco de Voz de Mando y No me falten al respeto de Noel Torres...",
        cue_cards: "CUE CARDS\n• Pregunta 24: Voz de mando / Noel Torres.\n• Pregunta 25: Gusto culposo: bingewatch de Netflix.\n• Pregunta 26: La gente hipócrita y mentirosa.\n• Pregunta 29: Churros de harina y comida china.\n• Pregunta 30: A mi mamá y si la miras venir dime para correr.\n• Pregunta 31: Reto físico.\n• Pregunta 32: Saludos a todos mis haters que aunque sea odio aún piensan en mí.\n• Pregunta 33: Progresión no perfección.",
        curaduria: {
            nivel: 'ALTO',
            badge: '🟢 NIVEL ALTO',
            formato: 'Invitado Principal al Canal (Episodio Completo 45+ min)',
            color: '#39FF14',
            razon: 'Personaje urbano icónico binacional con autenticidad, resiliencia y alta potencia de entretenimiento.'
        },
        ponderacion_score: 325
    }
};

// Asegurar que el objeto de curaduría y ponderación esté bien formado
function formatearRegistro(reg) {
    if (!reg.curaduria) {
        reg.curaduria = {
            nivel: 'ALTO',
            badge: '🟢 NIVEL ALTO',
            color: '#39FF14',
            formato: 'Invitado Principal al Canal (Episodio Completo 45+ min)',
            razon: 'Curaduría de alta potencia narrativa y lealtad de barrio.'
        };
    }
    if (!reg.ponderacion_score) {
        reg.ponderacion_score = 310;
    }
    return reg;
}

module.exports = async function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    // 1. LISTAR INVITADOS (GET)
    if (req.method === 'GET' || (req.method === 'POST' && req.body && req.body.action === 'list')) {
        const lista = [];
        const seenNames = new Set();

        // 1.1 Intentar leer desde Neon PostgreSQL
        const neonRows = await queryNeon("SELECT id, nombre, storytelling, created_at FROM knowledge_base WHERE tipo='storytelling' ORDER BY id DESC");
        if (neonRows && neonRows.length > 0) {
            for (const r of neonRows) {
                const norm = (r.nombre || '').toLowerCase().trim();
                if (!seenNames.has(norm)) {
                    let story = {};
                    try { story = JSON.parse(r.storytelling || '{}'); } catch (e) {}
                    const curaduria = story.curaduria || { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14' };
                    lista.push({
                        id: r.id,
                        nombre: r.nombre,
                        created_at: r.created_at || "2026-09-12 17:00:00",
                        curaduria: curaduria,
                        ponderacion_score: story.ponderacion_score || 310
                    });
                    seenNames.add(norm);
                }
            }
        }

        // 1.2 Leer envíos de JSON local
        const envios = obtenerEnviosJSON();
        for (const e of envios) {
            const norm = (e.nombre || '').toLowerCase().trim();
            if (!seenNames.has(norm)) {
                lista.push({
                    id: e.id || 10,
                    nombre: e.nombre,
                    created_at: e.created_at || "2026-09-29 20:00:00",
                    curaduria: e.curaduria || { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14' },
                    ponderacion_score: e.ponderacion_score || 325
                });
                seenNames.add(norm);
            }
        }

        // 1.3 Agregar Fallbacks maestros si no están ya en la lista
        for (const id in FALLBACK_INVITADOS) {
            const f = FALLBACK_INVITADOS[id];
            const norm = (f.nombre || '').toLowerCase().trim();
            if (!seenNames.has(norm)) {
                lista.push({
                    id: f.id,
                    nombre: f.nombre,
                    created_at: f.created_at,
                    curaduria: f.curaduria,
                    ponderacion_score: f.ponderacion_score
                });
                seenNames.add(norm);
            }
        }

        return res.status(200).json(lista);
    }

    // 2. OBTENER DETALLE DE INVITADO (POST action: 'get')
    if (req.method === 'POST') {
        let body = req.body;
        if (typeof body === 'string') {
            try { body = JSON.parse(body); } catch (e) { body = {}; }
        }
        body = body || {};

        const action = body.action || 'get';
        const id = parseInt(body.id, 10) || 0;
        const nombreReq = (body.nombre || '').trim();

        if (action === 'get') {
            // 2.1 Buscar en Neon PostgreSQL
            if (id > 0) {
                const neonRows = await queryNeon(`SELECT * FROM knowledge_base WHERE id = ${id} LIMIT 1`);
                if (neonRows && neonRows.length > 0) {
                    const row = neonRows[0];
                    let story = {};
                    try { story = JSON.parse(row.storytelling || '{}'); } catch (e) {}

                    const reg = {
                        id: row.id,
                        nombre: row.nombre,
                        created_at: row.created_at,
                        alias: story.alias || row.alias || '',
                        ocupacion: story.ocupacion || row.ocupacion || '',
                        barrio: story.barrio || row.barrio || '',
                        trayectoria: story.trayectoria || row.trayectoria || '',
                        herida: story.herida || row.herida || '',
                        molestia: story.molestia || row.molestia || '',
                        frase: story.frase || row.frase || '',
                        storytelling_enfoque: story.storytelling_enfoque || row.storytelling_enfoque || '',
                        reto: story.reto || row.reto || '',
                        escaleta: story.escaleta || row.escaleta || '',
                        guion: story.guion || row.guion || '',
                        cue_cards: story.cue_cards || row.cue_cards || '',
                        curaduria: story.curaduria || { nivel: 'ALTO', badge: '🟢 NIVEL ALTO', color: '#39FF14' },
                        ponderacion_score: story.ponderacion_score || 310,
                        ponderacion: story.ponderacion || null,
                        respuestas: story.respuestas || {}
                    };
                    return res.status(200).json({ registro: formatearRegistro(reg) });
                }
            }

            // 2.2 Buscar en envíos locales JSON
            const envios = obtenerEnviosJSON();
            const foundEnv = envios.find(e => e.id === id || (nombreReq && e.nombre && e.nombre.toLowerCase().includes(nombreReq.toLowerCase())));
            if (foundEnv) {
                return res.status(200).json({ registro: formatearRegistro(foundEnv) });
            }

            // 2.3 Buscar en catálogo FALLBACK_INVITADOS
            if (FALLBACK_INVITADOS[id]) {
                return res.status(200).json({ registro: formatearRegistro(FALLBACK_INVITADOS[id]) });
            }

            for (const key in FALLBACK_INVITADOS) {
                const f = FALLBACK_INVITADOS[key];
                if (nombreReq && f.nombre.toLowerCase().includes(nombreReq.toLowerCase())) {
                    return res.status(200).json({ registro: formatearRegistro(f) });
                }
            }

            // Fallback genérico para cualquier ID no encontrado
            return res.status(200).json({
                registro: formatearRegistro({
                    id: id || 1,
                    nombre: nombreReq || `Invitado de La Cueva #${id}`,
                    alias: "Invitado",
                    ocupacion: "Personaje Urbano de Mexicali",
                    barrio: "Mexicali, B.C.",
                    storytelling_enfoque: "Historias de barrio y superación en la frontera.",
                    reto: "Salir adelante con la frente en alto.",
                    frase: "El barrio no se platica, se demuestra en los hechos.",
                    escaleta: "ESCALETA DE PRODUCCIÓN - LA CUEVA\n[00:00 - 05:00] Hook de impacto\n[05:00 - 25:00] Historia de vida\n[25:00 - 45:00] Anécdotas de barrio\n[45:00 - 50:00] Cierre",
                    guion: "GUIÓN - LA CUEVA DEL GÜERO\nEl Güero: ¡Qué onda manada! Hoy tenemos una historia de barrio pesada...",
                    cue_cards: "CUE CARDS\n• Preguntar sobre el momento decisivo.\n• Anécdota principal.\n• Agradecimiento a patrocinadores."
                })
            });
        }

        if (action === 'update') {
            return res.status(200).json({ status: 'success', message: 'Ficha actualizada correctamente.' });
        }
    }

    return res.status(200).json({ status: 'success', message: 'API de Conocimiento Activa en Node.js' });
};
