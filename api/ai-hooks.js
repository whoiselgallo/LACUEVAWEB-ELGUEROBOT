/**
 * API: GENERADOR DE HOOKS Y MARKETING COPY EN NODE.JS NATIVO CON GEMINI IA
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
        return res.status(405).json({ success: false, error: 'Método no permitido.' });
    }

    let input = req.body;
    if (typeof input === 'string') {
        try { input = JSON.parse(input); } catch (e) { input = {}; }
    }
    input = input || {};

    const topic = (input.topic || req.query?.topic || '').trim();
    if (!topic) {
        return res.status(400).json({ success: false, error: 'El tema central está vacío.' });
    }

    const prompt = "Actúa como un director creativo experto en copywriting persuasivo, retención de audiencia y neuro-marketing para podcasts de alto impacto, estilo 'true crime', charlas urbanas y debate sin censura (estilo La Cueva del Güero).\n\n" +
        "Tu objetivo es generar 6 hooks optimizados para diferentes plataformas a partir del tema principal y la descripción del episodio que te proporcione el usuario.\n\n" +
        "REGLAS OBLIGATORIAS DE TONO Y ESTILO:\n" +
        "1. Voz y tono: Urbano, directo, sin rodeos, provocativo y conversacional (como una plática de sobremesa entre amigos con mucha calle). Evita formalismos aburridos.\n" +
        "2. Cero clichés corporativos: Prohibido empezar con frases vacías como 'No vas a creer...', 'En este episodio...' o 'Bienvenidos a un nuevo video'. Ve directo al dolor, la curiosidad, el conflicto o la tensión.\n" +
        "3. Estructura de retención (Fórmula Ganadora):\n" +
        "   - Gancho / Disruptor (Primeras 3 palabras): Rompe el patrón mental del usuario haciendo una pregunta incómoda, una declaración polémica o revelando la consecuencia más grave del tema.\n" +
        "   - Desarrollo del conflicto: Conecta el tema con una experiencia humana real (traición, lealtades rotas, calle, consecuencias).\n" +
        "   - Llamado a la Acción (CTA) Nativo: Pide la interacción adaptada a cada plataforma (comentarios para debate en TikTok, compartir con la 'manada' en Instagram, suscripción para tensión continua en Shorts).\n\n" +
        "FORMATO DE SALIDA REQUERIDO:\n" +
        "Genera estrictamente un objeto JSON válido (sin texto antes ni después) con los 6 hooks adaptados para cada una de las siguientes plataformas, usando emojis estratégicos:\n" +
        "{\n" +
        "  \"facebook\": \"[Facebook Feed] Enfoque en debate y curiosidad general...\",\n" +
        "  \"instagram\": \"[Instagram Carousel] Enfoque visual/mental, invitando a deslizar y etiquetar...\",\n" +
        "  \"tiktok\": \"[TikTok Hook] Enfoque ultra agresivo en los primeros 3 segundos, incitando a debatir...\",\n" +
        "  \"spotify\": \"[Spotify Intro Teaser] Enfoque auditivo, creando atmósfera de misterio o charla íntima y cruda...\",\n" +
        "  \"shorts\": \"[YouTube Shorts] Enfoque en la máxima tensión del corte, cerrando con invitación a suscribirse...\",\n" +
        "  \"youtube\": \"[YouTube Videos] Título optimizado para CTR + Bajada de descripción narrativa que invite al clic inmediato...\"\n" +
        "}\n\n" +
        `Tema del episodio a procesar: ${topic}`;

    const geminiRes = await callGemini(prompt, { temperature: 0.7, maxOutputTokens: 2048 });

    if (geminiRes.success) {
        let text = geminiRes.text;
        const match = text.match(/```(?:json)?\s*([\s\S]*?)\s*```/);
        if (match) {
            text = match[1];
        }

        try {
            const parsed = JSON.parse(text);
            if (parsed && parsed.facebook) {
                return res.status(200).json({
                    success: true,
                    hooks: parsed,
                    model: geminiRes.model
                });
            }
        } catch (e) {}

        return res.status(200).json({
            success: true,
            hooks: {
                facebook: `🚨 Lo más polémico sobre ${topic}. Mira el episodio completo en La Cueva del Güero.`,
                instagram: `🔥 Confesiones crudas sobre ${topic}. Compártelo con tu manada.`,
                tiktok: `😱 Lo que nadie se atreve a decir sobre ${topic}. ¿Tú qué piensas?`,
                spotify: `🎙️ Una plática sin censura acerca de ${topic}. Escúchalo en Spotify.`,
                shorts: `⚡ El momento más tenso del podcast hablando de ${topic}.`,
                youtube: `🔥 LA VERDAD SOBRE ${topic.toUpperCase()} | La Cueva del Güero Podcast`
            },
            model: geminiRes.model
        });
    }

    return res.status(500).json({
        success: false,
        error: 'No se pudo conectar con Gemini para generar los ganchos.'
    });
};
