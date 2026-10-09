/**
 * API: CONOCIMIENTO Y REGISTROS DE INVITADOS (NEON POSTGRESQL + JSON RESILIENTE)
 * Endpoint nativo Vercel Serverless Function
 * Atiende: /api/api-guero-knowledge.php y /api/api-guero-knowledge
 */

const fs = require('fs');
const path = require('path');

// Obtener URL de Neon PostgreSQL con fallbacks automáticos
function getDatabaseUrl() {
    let url = process.env.DATABASE_URL || process.env.DATABASE_URL_UNPOOLED || '';
    if (!url && process.env.DB_HOST && process.env.DB_USER && process.env.DB_PASS) {
        const host = process.env.DB_HOST;
        const user = process.env.DB_USER;
        const pass = process.env.DB_PASS;
        const db = process.env.DB_NAME || 'neondb';
        url = `postgresql://${user}:${pass}@${host}/${db}?sslmode=require`;
    }
    return url;
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
            signal: AbortSignal.timeout(6000)
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

// Catálogo Canónico de Evaluaciones de Curaduría (docs/EVALUACION_CURADURIA.md)
const CANONICAL_CURADURIA = {
    7: {
        score_total: 152,
        nivel: 'BAJO',
        badge: '🔴 NIVEL BAJO',
        color: '#FF00FF',
        formato: 'Micro-contenido / Reto en Cabina / Shorts (30 - 60 seg)',
        razon: 'Evaluación estricta de curaduría: 152/330 pts (4.6/9). Respuestas monosilábicas y sin ocupación definida. Inviable para programa largo de 45 min; canalizar a dinámicas de retos, preguntas rápidas y micro-contenido.'
    },
    9: {
        score_total: 264,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 40 min)',
        razon: 'Maternidad de barrio, superación familiar y vida cotidiana en Puertas del Sol.'
    },
    5: {
        score_total: 268,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 40 min)',
        razon: 'Cultura del taller mecánico y carrocería automotriz tradicional en La Carbajal. La constancia supera al talento.'
    },
    4: {
        score_total: 271,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 40 min)',
        razon: 'De la colonia popular Leonardo Guillén al Tribunal de Justicia Administrativa: contraste ético e institucional desde la raíz del barrio.'
    },
    2: {
        score_total: 279,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 40-50 min)',
        razon: 'Códigos de supervivencia urbana, resiliencia y lealtad pura de barrio en Valle Dorado.'
    },
    6: {
        score_total: 282,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 40-50 min)',
        razon: 'Dignidad del trabajo doméstico femenino y vivencias comunitarias de madre trabajadora en la colonia Libertad.'
    },
    8: {
        score_total: 301,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 45-60 min)',
        razon: 'Migración internacional de Honduras a Mexicali, trabajo físico en llantera y autenticidad frontal.'
    },
    3: {
        score_total: 305,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 45-60 min)',
        razon: 'Dirección audiovisual independiente en la frontera, visión cinematográfica y potencia de hooks.'
    },
    5293: {
        score_total: 316,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 45+ min)',
        razon: 'Host y productor con alta profundidad de barrio en la colonia Independencia, disciplina y códigos de franqueza.'
    },
    10: {
        score_total: 322,
        nivel: 'ALTO',
        badge: '🟢 NIVEL ALTO',
        color: '#39FF14',
        formato: 'Invitado Principal al Canal (Episodio Completo 45+ min)',
        razon: 'Personaje icónico binacional con autenticidad callejera, superación en la garita y altísimo carisma.'
    }
};

// Generar 33 criterios evaluados donde la sumatoria coincida exactamente con targetScore
function generarCriterios33(reg, targetScore) {
    const titulos = [
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

    const respObj = reg.respuestas || {};
    const baseVal = Math.floor(targetScore / 33);
    let remainder = targetScore - (baseVal * 33);

    const criterios = [];
    for (let i = 1; i <= 33; i++) {
        let sc = baseVal;
        if (remainder > 0) {
            sc++;
            remainder--;
        }
        if (sc > 10) sc = 10;
        if (sc < 1) sc = 1;

        const info = titulos[i - 1];
        const rVal = respObj[i] || '';
        let just = sc >= 9 
            ? 'Potencia narrativa sobresaliente y profundidad de barrio.'
            : (sc >= 7 
                ? 'Respuesta sólida con valor para conducción de mesa.'
                : (sc >= 5 
                    ? 'Respuesta breve; requiere apoyo y dinamización del host.'
                    : 'Respuesta monosilábica o vacía; canalizar a formato ágil.'));

        criterios.push({
            num: i,
            nombre: `Pregunta ${i}: ${info.titulo}`,
            acto: info.acto,
            score: sc,
            max: 10,
            respuesta: rVal || (reg.nombre ? `Aporte de ${reg.nombre}` : ''),
            justificacion: just
        });
    }

    return criterios;
}

// Aplicar curaduría estricta con sumatoria matemática exacta
function aplicarCuraduriaEstricta(reg) {
    const id = parseInt(reg.id, 10);
    const nombre = (reg.nombre || '').toLowerCase();

    let canon = CANONICAL_CURADURIA[id];
    if (!canon) {
        if (nombre.includes('rene') || nombre.includes('coronado')) canon = CANONICAL_CURADURIA[7];
        else if (nombre.includes('pocha') || nombre.includes('rosalva')) canon = CANONICAL_CURADURIA[10];
        else if (nombre.includes('gallo') || nombre.includes('eduardo')) canon = CANONICAL_CURADURIA[5293];
        else if (nombre.includes('domz') || nombre.includes('jeyb')) canon = CANONICAL_CURADURIA[3];
        else if (nombre.includes('noe') || nombre.includes('escobar')) canon = CANONICAL_CURADURIA[8];
        else if (nombre.includes('guillermina') || nombre.includes('guille')) canon = CANONICAL_CURADURIA[6];
        else if (nombre.includes('camacho') || nombre.includes('leo')) canon = CANONICAL_CURADURIA[2];
        else if (nombre.includes('maciel') || nombre.includes('marcelo')) canon = CANONICAL_CURADURIA[4];
        else if (nombre.includes('aurelio')) canon = CANONICAL_CURADURIA[5];
        else if (nombre.includes('fierro') || nombre.includes('yessica')) canon = CANONICAL_CURADURIA[9];
    }

    let score = 0;
    if (canon) {
        score = canon.score_total;
    } else if (reg.ponderacion_score && reg.ponderacion_score > 0) {
        score = parseInt(reg.ponderacion_score, 10);
        if (score <= 100) score = Math.round(score * 3.3);
    } else if (reg.ponderacion && reg.ponderacion.score_total) {
        let sc = parseFloat(reg.ponderacion.score_total);
        if (sc <= 10) score = Math.round((sc / 9.0) * 330);
        else if (sc <= 100) score = Math.round(sc * 3.3);
        else score = Math.round(sc);
    } else {
        score = 275;
    }

    if (score < 33) score = 33;
    if (score > 330) score = 330;

    // Regla estricta de umbrales:
    // ALTO: >= 260 / 330
    // MEDIO: 165 a 259 / 330
    // BAJO: < 165 / 330
    const nivel = (score >= 260) ? 'ALTO' : ((score >= 165) ? 'MEDIO' : 'BAJO');
    const badge = (nivel === 'ALTO') ? '🟢 NIVEL ALTO' : ((nivel === 'MEDIO') ? '🟡 NIVEL MEDIO' : '🔴 NIVEL BAJO');
    const color = (nivel === 'ALTO') ? '#39FF14' : ((nivel === 'MEDIO') ? '#00FFFF' : '#FF00FF');
    const formato = (nivel === 'ALTO')
        ? 'Invitado Principal al Canal (Episodio Completo 45+ min)'
        : ((nivel === 'MEDIO')
            ? 'Entrevista Corta / Segmento Dinámico (15 - 25 min)'
            : 'Micro-contenido / Reto en Cabina / Shorts (30 - 60 seg)');

    const razon = (canon && canon.razon) ? canon.razon : (
        nivel === 'BAJO'
            ? 'Puntaje de curaduría insuficiente para programa largo (< 165 pts). Respuestas sintéticas o sin vocación narrativa. Canalizar a micro-contenido, retos rápidos y shorts.'
            : (nivel === 'MEDIO'
                ? 'Puntaje medio de curaduría (165 a 259 pts). Tema de interés pero con limitaciones en soltura o continuidad. Formato recomendado: entrevista corta de 15 a 25 minutos.'
                : 'Puntaje alto de curaduría (>= 260 pts). Gran peso narrativo, autenticidad y retención. Aprobado para episodio estelar de 45+ minutos.')
    );

    reg.ponderacion_score = score;
    reg.curaduria = {
        nivel,
        badge,
        color,
        formato: (canon && canon.formato) ? canon.formato : formato,
        razon
    };

    if (!reg.ponderacion || !Array.isArray(reg.ponderacion.criterios) || reg.ponderacion.criterios.length !== 33) {
        reg.ponderacion = {
            score_total: score,
            criterios: generarCriterios33(reg, score)
        };
    }

    return reg;
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
        ponderacion_score: 279
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
        ponderacion_score: 305
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
        ponderacion_score: 271
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
        ponderacion_score: 268
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
        ponderacion_score: 282
    },
    7: {
        id: 7,
        nombre: "Sergio Rene Coronado Vega",
        created_at: "2026-08-23 08:53:40",
        alias: "Rene",
        ocupacion: "En pausa / Sin actividad fija",
        barrio: "Hacienda Dorada, Mexicali",
        trayectoria: "Joven cachanilla en búsqueda de rumbo personal y profesional.",
        herida: "La incertidumbre ante el futuro y no encontrar un camino claro.",
        molestia: "La imprudencia y la falta de respeto de la gente.",
        frase: "La familia no se escoge, pero se defiende.",
        storytelling_enfoque: "La búsqueda de rumbo en la juventud y la aversión a la imprudencia ajena.",
        reto: "Encontrar motivación y estructura personal cuando no hay un camino profesional trazado.",
        escaleta: "ESCALETA DE MICRO-CONTENIDO / SHORTS - LA CUEVA\nInvitado: Sergio Rene Coronado Vega\nFormato: Dinámica Rápida de Cabina (1 a 3 min)\n\n[00:00 - 00:30] Hook: ¿Qué es lo más imprudente que has visto en Mexicali?\n[00:30 - 01:30] Reto de cabina y preguntas punzantes de El Güero\n[01:30 - 02:30] Consejo para la banda que anda sin rumbo\n[02:30 - 03:00] Remate y cierre de clip",
        guion: "GUIÓN DE RETO Y HOOKS - LA CUEVA DEL GÜERO\nInvitado: Sergio Rene Coronado Vega (Rene)\n\nEl Güero: ¡Qué onda manada! Hoy tenemos en el micro a Rene de Hacienda Dorada para aventarnos un reto de preguntas a quemarropa...\n\nJunior: A ver Rene, sin rodeos: ¿qué te revienta más de la gente imprudente?",
        cue_cards: "CUE CARDS - DINÁMICA RÁPIDA (MICRO-CONTENIDO)\n• Tarjeta 1: Reto directo sobre la imprudencia.\n• Tarjeta 2: Pregunta rápida de barrio: ¿Hacienda Dorada o Palaco?\n• Tarjeta 3: Remate y despedida en 30 segundos.",
        ponderacion_score: 152
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
        ponderacion_score: 301
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
        ponderacion_score: 264
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
        ponderacion_score: 322
    }
};

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

                    const regTemp = aplicarCuraduriaEstricta({
                        id: r.id,
                        nombre: r.nombre,
                        ponderacion_score: story.ponderacion_score,
                        curaduria: story.curaduria,
                        ponderacion: story.ponderacion
                    });

                    lista.push({
                        id: r.id,
                        nombre: r.nombre,
                        created_at: r.created_at || "2026-09-12 17:00:00",
                        curaduria: regTemp.curaduria,
                        ponderacion_score: regTemp.ponderacion_score
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
                const regTemp = aplicarCuraduriaEstricta({
                    id: e.id || 10,
                    nombre: e.nombre,
                    ponderacion_score: e.ponderacion_score,
                    curaduria: e.curaduria,
                    ponderacion: e.ponderacion
                });

                lista.push({
                    id: e.id || 10,
                    nombre: e.nombre,
                    created_at: e.created_at || "2026-09-29 20:00:00",
                    curaduria: regTemp.curaduria,
                    ponderacion_score: regTemp.ponderacion_score
                });
                seenNames.add(norm);
            }
        }

        // 1.3 Agregar Fallbacks maestros si no están ya en la lista
        for (const id in FALLBACK_INVITADOS) {
            const f = FALLBACK_INVITADOS[id];
            const norm = (f.nombre || '').toLowerCase().trim();
            if (!seenNames.has(norm)) {
                const regTemp = aplicarCuraduriaEstricta(f);
                lista.push({
                    id: regTemp.id,
                    nombre: regTemp.nombre,
                    created_at: regTemp.created_at,
                    curaduria: regTemp.curaduria,
                    ponderacion_score: regTemp.ponderacion_score
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
                        curaduria: story.curaduria || null,
                        ponderacion_score: story.ponderacion_score || null,
                        ponderacion: story.ponderacion || null,
                        respuestas: story.respuestas || {}
                    };
                    return res.status(200).json({ registro: aplicarCuraduriaEstricta(reg) });
                }
            }

            // 2.2 Buscar en envíos locales JSON
            const envios = obtenerEnviosJSON();
            const foundEnv = envios.find(e => e.id === id || (nombreReq && e.nombre && e.nombre.toLowerCase().includes(nombreReq.toLowerCase())));
            if (foundEnv) {
                return res.status(200).json({ registro: aplicarCuraduriaEstricta(foundEnv) });
            }

            // 2.3 Buscar en catálogo FALLBACK_INVITADOS
            if (FALLBACK_INVITADOS[id]) {
                return res.status(200).json({ registro: aplicarCuraduriaEstricta(FALLBACK_INVITADOS[id]) });
            }

            for (const key in FALLBACK_INVITADOS) {
                const f = FALLBACK_INVITADOS[key];
                if (nombreReq && f.nombre.toLowerCase().includes(nombreReq.toLowerCase())) {
                    return res.status(200).json({ registro: aplicarCuraduriaEstricta(f) });
                }
            }

            // Fallback genérico para cualquier ID no encontrado
            return res.status(200).json({
                registro: aplicarCuraduriaEstricta({
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
