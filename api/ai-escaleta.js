/**
 * API: GENERADOR DE ESCALETA TÉCNICA Y PRODUCCIÓN EJECUTIVA EN NODE.JS NATIVO
 * La Cueva del Güero Podcast
 */

const { callGemini } = require('./_gemini');

module.exports = async function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    if (req.method !== 'POST') {
        return res.status(405).json({ status: 'error', message: 'Método no permitido. Usa POST.' });
    }

    let input = req.body;
    if (typeof input === 'string') {
        try { input = JSON.parse(input); } catch (e) { input = {}; }
    }
    input = input || {};

    const campos = ['nombre', 'ocupacion', 'signo', 'fecha', 'barrio', 'trayectoria', 'herida', 'incomodo', 'gustos'];
    const errores = campos.filter(c => !input[c] || !String(input[c]).trim());

    if (errores.length) {
        return res.status(400).json({
            status: 'error',
            message: `Faltan campos obligatorios: ${errores.join(', ')}`
        });
    }

    const datos = {};
    for (const c of campos) {
        datos[c] = String(input[c]).trim();
    }
    const momento = String(input.momento || 'Superación y resiliencia').trim();
    const logros  = String(input.logros  || 'Éxito y consolidación').trim();

    const prompt = "# ROL: DIRECTOR CREATIVO Y PRODUCTOR EJECUTIVO JAVIER GALLARDO 'EL GALLO' - LA CUEVA DEL GÜERO PODCAST\n\n" +
        "Genera la ESCALETA TÉCNICA DE PRODUCCIÓN, un RESUMEN DEL GUIÓN y las CUE CARDS para el set de grabación en Mexicali, B.C.\n\n" +
        "EQUIPO CANÓNICO DEL SHOW:\n" +
        "- Personaje Principal & Mascota Inspiración: 'El Güero' el perro (presencia en set y marca).\n" +
        "- CEO & Host Conductor: Ariel Higuera 'El Junior' (frente a micrófonos).\n" +
        "- Director Creativo & Productor Ejecutivo: Javier Gallardo 'El Gallo' (dirección y dinámicas).\n" +
        "- Socia Ángel & Finanzas: Maria Elena Anguiano 'La Mary' (administración y marcas).\n\n" +
        `- Invitado: ${datos.nombre}\n` +
        `- Ocupación: ${datos.ocupacion}\n` +
        `- Barrio: ${datos.barrio}\n` +
        `- Trayectoria: ${datos.trayectoria}\n` +
        `- Herida / Conflicto: ${datos.herida}\n` +
        `- Momento Decisivo: ${momento}\n` +
        `- Temas Incómodos: ${datos.incomodo}\n` +
        `- Gustos: ${datos.gustos}\n` +
        `- Logros: ${logros}\n\n` +
        "FORMATO DE SALIDA REQUERIDO:\n" +
        "Debes responder ESTRICTAMENTE un JSON válido (sin texto antes ni después) con las siguientes 3 claves:\n" +
        "{\n" +
        "  \"escaleta\": \"(Parrilla técnica con timecodes, tiros de cámara, cortes, menciones de marcas administradas por La Mary y coordinación de El Gallo)\",\n" +
        "  \"guion\": \"(Estructura conversacional base para Ariel Higuera 'El Junior' con preguntas detonantes y remates)\",\n" +
        "  \"cue_cards\": \"(Lista de viñetas claras con las preguntas más detonantes para que Ariel 'El Junior' las lea en cabina)\"\n" +
        "}";

    const geminiRes = await callGemini(prompt, { temperature: 0.7, maxOutputTokens: 2500, timeoutMs: 25000 });

    if (geminiRes.success) {
        let text = geminiRes.text;
        const match = text.match(/```(?:json)?\s*([\s\S]*?)\s*```/);
        if (match) {
            text = match[1];
        }

        try {
            const parsed = JSON.parse(text);
            return res.status(200).json({
                status: 'success',
                escaleta: parsed.escaleta || text,
                guion: parsed.guion || `Guión en desarrollo para ${datos.nombre}.`,
                cue_cards: parsed.cue_cards || `Cue cards en cabina para ${datos.nombre}.`,
                model: geminiRes.model
            });
        } catch (e) {
            return res.status(200).json({
                status: 'success',
                escaleta: text,
                guion: `Guión base para ${datos.nombre}.`,
                cue_cards: `Preguntas para ${datos.nombre}.`,
                model: geminiRes.model
            });
        }
    }

    return res.status(500).json({
        status: 'error',
        message: 'No fue posible generar la escaleta con Gemini. Intenta nuevamente.'
    });
};
