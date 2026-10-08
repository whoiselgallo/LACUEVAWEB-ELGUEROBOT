/**
 * NÚCLEO CENTRAL DE INTELIGENCIA ARTIFICIAL - GEMINI MULTI-KEY & MULTI-MODEL
 * La Cueva del Güero & El Güero Bot
 */

const fs = require('fs');
const path = require('path');

// Obtener todas las claves configuradas con prioridad a process.env y fallback a .env
function getAllGeminiKeys() {
    let rawKeys = process.env.GEMINI_API_KEYS || process.env.GEMINI_API_KEY || '';

    if (!rawKeys) {
        try {
            const envPath = path.resolve(__dirname, '../.env');
            if (fs.existsSync(envPath)) {
                const content = fs.readFileSync(envPath, 'utf8');
                const matchKeys = content.match(/GEMINI_API_KEYS=([^\r\n]+)/);
                const matchKey = content.match(/GEMINI_API_KEY=([^\r\n]+)/);
                rawKeys = (matchKeys && matchKeys[1]) || (matchKey && matchKey[1]) || '';
            }
        } catch (e) {}
    }

    if (!rawKeys) return [];
    return rawKeys.split(',').map(k => k.trim().replace(/^["']|["']$/g, '')).filter(Boolean);
}

// Modelos activos verificados en producción con alta disponibilidad
const ACTIVE_MODELS = [
    'gemini-flash-latest',
    'gemini-3.5-flash-lite',
    'gemini-flash-lite-latest',
    'gemini-3.1-flash-lite'
];

/**
 * Llamada centralizada y ultra robusta a Gemini con failover continuo
 * @param {string|object} content Texto o estructura de contenido
 * @param {object} options Opciones de generación (systemInstruction, temperature, maxTokens)
 */
async function callGemini(content, options = {}) {
    const keys = getAllGeminiKeys();
    const systemPrompt = options.systemInstruction || '';
    const temperature = options.temperature ?? 0.7;
    const maxOutputTokens = options.maxOutputTokens ?? 2048;

    let userText = typeof content === 'string' ? content : JSON.stringify(content);

    const payload = {
        contents: [
            {
                role: "user",
                parts: [{ text: userText }]
            }
        ],
        generationConfig: {
            temperature,
            maxOutputTokens
        }
    };

    if (systemPrompt) {
        payload.systemInstruction = {
            parts: [{ text: systemPrompt }]
        };
    }

    // Iterar en todas las claves y modelos activos
    for (const apiKey of keys) {
        for (const model of ACTIVE_MODELS) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 8000);

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
                        return {
                            success: true,
                            text: text.trim(),
                            model,
                            keyPrefix: apiKey.substring(0, 10)
                        };
                    }
                }
            } catch (err) {
                // Siguiente modelo/clave
            }
        }
    }

    return {
        success: false,
        error: 'No se pudo obtener respuesta de ningún modelo ni clave de Gemini.'
    };
}

module.exports = {
    getAllGeminiKeys,
    ACTIVE_MODELS,
    callGemini
};
