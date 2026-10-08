/**
 * API: CONECTOR GEMINI & SMART ENGINE NATIVO EN NODE.JS - EL GÜERO BOT
 * Mascota virtual y asistente conversacional de La Cueva del Güero
 * Endpoint nativo Vercel Serverless Function
 */

// Función para obtener claves de Gemini con rotación
function getGeminiApiKey() {
    const rawKeys = process.env.GEMINI_API_KEYS || process.env.GEMINI_API_KEY || '';
    if (!rawKeys) return '';
    const keys = rawKeys.split(',').map(k => k.trim()).filter(Boolean);
    if (!keys.length) return '';
    return keys[Math.floor(Math.random() * keys.length)];
}

// Motor de Respuestas Nativas Inteligentes de El Güero Bot
function gueroBotSmartResponse(query, visitType = 'guest') {
    const q = (query || '').toLowerCase();

    // 1. Tracking / Estado de episodio
    if (q.includes('track') || q.includes('episodio') || q.includes('código') || q.includes('codigo') || q.includes('estado') || q.includes('avance')) {
        return "¡Simón carnal! 🛰️ *mueve la colita* Puedes consultar el avance de tu episodio, edición y clips en vivo en nuestro portal de [Tracking de Invitados](https://s.lacuevadelguero.com/). Ingresa tu código o búscate con tu correo.";
    }

    // 2. Invitado / Storytelling / Participar / Grabar
    if (q.includes('invitad') || q.includes('participar') || q.includes('grabar') || q.includes('ir al programa') || q.includes('entrevista') || q.includes('cuestionario') || q.includes('historia')) {
        return "¡A huevo, compa! 🎙️🔥 En La Cueva del Güero buscamos historias reales, crudas y de barrio. Llena de volada tu [Cuestionario de Storytelling](storytelling-invitado.html) o mándale WhatsApp directo al Junior: [WhatsApp Junior](https://wa.me/526862124372).";
    }

    // 3. Cesión de derechos
    if (q.includes('cesion') || q.includes('cesión') || q.includes('derecho') || q.includes('firma') || q.includes('consentimiento')) {
        return "Aquí mero puedes revisar y firmar tu formato oficial digital: [Carta de Cesión de Derechos](cesion-derechos.html). ¡Todo derecho y en regla!";
    }

    // 4. Patrocinios / Publicidad / Marcas / Negocios
    if (q.includes('patrocini') || q.includes('publicidad') || q.includes('marca') || q.includes('negocio') || q.includes('anunciar')) {
        return "¡Eso es todo, visión chingona! 💼 Para patrocinios y números comerciales, todo se gestiona con Maria Elena 'La Mary' (nuestra Socia Ángel y Finanzas) y producción en el WhatsApp [+52 686 212 4372](https://wa.me/526862124372).";
    }

    // 5. YouTube / Canal / Redes
    if (q.includes('youtube') || q.includes('canal') || q.includes('suscrib') || q.includes('video') || q.includes('spotify')) {
        return "¡Cáele a la manada! 🐺 Échale un ojo a los capítulos completos conducidos por El Junior y producidos por El Gallo en nuestro canal de [YouTube @LacuevadelGueroPodcast](https://www.youtube.com/@LacuevadelGueroPodcast). ¡Suscríbete y déjanos tu like!";
    }

    // 6. Quién eres / Identidad / Bot / Perro / Equipo
    if (q.includes('quien eres') || q.includes('quién eres') || q.includes('bot') || q.includes('perro') || q.includes('güero') || q.includes('guero') || q.includes('equipo')) {
        return "¡Guau! 🐶 Soy **'El Güero'**, el perro mero mero, imagen e inspiración de este podcast. Mi manada está conformada por:\n" +
               "1️⃣ **Ariel Higuera 'El Junior'**: CEO y Host conductor frente a micrófonos.\n" +
               "2️⃣ **Maria Elena Anguiano 'La Mary'**: Socia Ángel del Proyecto y Administradora de Finanzas.\n" +
               "3️⃣ **Javier Gallardo 'El Gallo'**: Socio Intelectual, Director Creativo y Productor Ejecutivo.";
    }

    // 7. Saludos
    if (q.includes('hola') || q.includes('que onda') || q.includes('qué onda') || q.includes('que tranza') || q.includes('qué tranza') || q.includes('buenas')) {
        return "¡Qué tranza carnal! 🐾 *salta de emoción* Bienvenido a La Cueva del Güero. ¿Qué andas tramando? ¿Quieres ser invitado con El Junior, checar tu tracking o tirar plática con la manada?";
    }

    // 8. Contacto / Ubicación / Mexicali
    if (q.includes('contacto') || q.includes('donde') || q.includes('dónde') || q.includes('ubicacion') || q.includes('mexicali')) {
        return "¡Transmitiendo desde Mexicali, Baja California! 🌵 La mera frontera norteña. Si quieres caerle o platicar con el equipo (El Junior, El Gallo o La Mary), mándale WhatsApp al [+52 686 212 4372](https://wa.me/526862124372).";
    }

    // Fallback conversacional auténtico
    return "¡Qué onda carnal! 🐾 *olfatea la pantalla* Aquí en La Cueva del Güero andamos siempre al tiro. Si quieres jalarte como invitado llena tu [Cuestionario de Storytelling](storytelling-invitado.html) o mándale mensaje al Junior por [WhatsApp](https://wa.me/526862124372). ¡Pásale a la cueva!";
}

// Llamada a Gemini con timeout y modelos de respaldo
async function callGemini(query, apiKey) {
    if (!apiKey) return null;

    const systemPrompt = "# SYSTEM INSTRUCTIONS: EL GÜERO BOT - LA CUEVA DEL GÜERO PODCAST\n" +
        "- Identidad: Eres 'El Güero Bot', el perro guardián inteligente y mascota/imagen oficial del podcast 'La Cueva del Güero' en Mexicali, BC.\n" +
        "- Tono: Relajado, norteño, callejero, divertido y muy amigable. Usa ocasionalmente expresiones caninas y de barrio ('*mueve la cola*', 'carnal', 'raza', 'al tiro', 'la cueva').\n" +
        "- EQUIPO CANÓNICO DE LA CUEVA:\n" +
        "  * Personaje Principal e Inspiración: 'El Güero' el perro (imagen en logo, banners, set).\n" +
        "  * Ariel Higuera 'El Junior': CEO y Host conductor del podcast.\n" +
        "  * Maria Elena Anguiano 'La Mary': Socia Ángel del Proyecto y Administradora de Finanzas.\n" +
        "  * Javier Gallardo 'El Gallo': Socio Intelectual, Director Creativo y Productor Ejecutivo.\n" +
        "- WhatsApp oficial: +52 686 212 4372\n" +
        "- Enlaces clave:\n" +
        "  * Cuestionario de invitado: storytelling-invitado.html\n" +
        "  * Tracking de episodio: https://s.lacuevadelguero.com/\n" +
        "  * Cesión de derechos: cesion-derechos.html\n" +
        "  * YouTube: https://www.youtube.com/@LacuevadelGueroPodcast\n" +
        "- Respuestas directas, chidas y breves (máximo 3 o 4 oraciones).";

    const payload = {
        contents: [
            {
                role: "user",
                parts: [{ text: query }]
            }
        ],
        systemInstruction: {
            parts: [{ text: systemPrompt }]
        },
        generationConfig: {
            temperature: 0.75,
            maxOutputTokens: 512
        }
    };

    const models = ['gemini-2.5-flash', 'gemini-2.0-flash'];

    for (const model of models) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 7000);

            const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            if (res.ok) {
                const data = await res.json();
                const text = data?.candidates?.[0]?.content?.parts?.[0]?.text;
                if (text && text.trim()) {
                    return { text: text.trim(), model };
                }
            }
        } catch (err) {
            // Continúa con el siguiente modelo si falla o hace timeout
        }
    }
    return null;
}

// Log en Neon / PostgreSQL si está configurado (no bloqueante)
async function logConversationToDb(userId, visitType, query, answer) {
    try {
        const dbUrl = process.env.DATABASE_URL || process.env.DATABASE_URL_UNPOOLED;
        if (!dbUrl || !dbUrl.includes('@')) return;
        // Si hay URL de Neon válida con host HTTP de consulta
        const match = dbUrl.match(/postgresql:\/\/([^:]+):([^@]+)@([^/]+)\/(.+)/);
        if (!match) return;
        const [, user, pass, host, db] = match;
        
        // Llamada fetch simple al endpoint de Neon SQL HTTP si el host es de neon
        if (host.includes('neon.tech')) {
            const cleanHost = host.replace(/-pooler\./, '.');
            const sqlEndpoint = `https://${cleanHost}/sql`;
            await fetch(sqlEndpoint, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${pass}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    query: 'INSERT INTO conversations (user_id, visit_type, user_message, bot_answer, created_at) VALUES ($1, $2, $3, $4, NOW())',
                    params: [userId, visitType, query, answer]
                }),
                signal: AbortSignal.timeout(3000)
            }).catch(() => {});
        }
    } catch (e) {
        // Silencioso para no tumbar la respuesta del bot
    }
}

module.exports = async function handler(req, res) {
    // Cabeceras CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    let body = req.body;
    if (typeof body === 'string') {
        try {
            body = JSON.parse(body);
        } catch (e) {
            body = {};
        }
    }
    body = body || {};

    const query = (body.query || req.query?.query || '').trim();
    const userId = (body.user || req.query?.user || 'usuario_paw_web').trim();
    const visitType = (body.visitType || req.query?.visitType || 'guest').trim();

    if (!query) {
        return res.status(200).json({
            success: true,
            answer: '¡Qué onda carnal! 🐾 Soy El Güero Bot. ¿Qué andas buscando en la cueva? ¿Quieres ser invitado, ver el desmadre o qué tranza?'
        });
    }

    let answer = '';
    let modelUsed = 'guero-bot-engine-v2';

    // 1. Intentar Gemini AI si hay clave configurada
    const apiKey = getGeminiApiKey();
    if (apiKey) {
        const geminiResult = await callGemini(query, apiKey);
        if (geminiResult && geminiResult.text) {
            answer = geminiResult.text;
            modelUsed = geminiResult.model;
        }
    }

    // 2. Si Gemini no está disponible o falló, usar el motor nativo de El Güero Bot
    if (!answer) {
        answer = gueroBotSmartResponse(query, visitType);
    }

    // 3. Registrar en BD de forma asíncrona (sin bloquear respuesta)
    logConversationToDb(userId, visitType, query, answer).catch(() => {});

    return res.status(200).json({
        success: true,
        answer: answer,
        model: modelUsed
    });
};
