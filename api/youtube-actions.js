/**
 * API: PLANIFICADOR DE ACCIONES YOUTUBE CON GEMINI IA EN NODE.JS NATIVO
 * Endpoint: /api/api-youtube-actions.php -> /api/youtube-actions.js
 */

const { callGemini } = require('./_gemini');

module.exports = async function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    let input = req.body;
    if (typeof input === 'string') {
        try { input = JSON.parse(input); } catch (e) { input = {}; }
    }
    input = input || {};

    const views = parseInt(input.views, 10) || 0;
    const ctr = parseFloat(input.ctr) || 5.8;
    const retention = parseInt(input.retention, 10) || 42;
    const impressions = parseInt(input.impressions, 10) || 0;

    const prompt = "Actúa como el Consultor Experto en YouTube Studio de 'La Cueva del Güero'. " +
        "Analiza las siguientes métricas de rendimiento del canal:\n" +
        `- Impresiones de miniaturas: ${impressions.toLocaleString()}\n` +
        `- Vistas del capítulo: ${views.toLocaleString()}\n` +
        `- Tasa de Clics (CTR): ${ctr}%\n` +
        `- Retención de audiencia promedio: ${retention}%\n\n` +
        "Traduce estas métricas en exactamente 3 acciones correctivas específicas que el usuario debe ejecutar dentro del panel de La Cueva:\n" +
        "1. Si el CTR es bajo (< 6%), sugiere crear un diseño neón llamativo en el 'Editor Canva PRO'.\n" +
        "2. Si la retención es baja (< 50%), sugiere acortar silencios a 0.5s en el 'Editor de Video'.\n" +
        "3. Si las impresiones son bajas, sugiere regenerar títulos llamativos con el 'Generador de Hooks'.\n\n" +
        "Escribe las sugerencias con jerga mexicana del norte, de forma muy concisa y directa. Formatea cada acción en una línea independiente.";

    const geminiRes = await callGemini(prompt, { temperature: 0.5, maxOutputTokens: 1024 });

    if (geminiRes.success) {
        const lines = geminiRes.text.split('\n');
        let formattedHtml = '';
        for (const line of lines) {
            const clean = line.replace(/^[-*\d.]+\s*/, '').trim();
            if (clean) {
                formattedHtml += `<div style='margin-bottom:8px;'>> ⚠️ <strong>${clean}</strong></div>`;
            }
        }

        return res.status(200).json({
            success: true,
            actions_html: formattedHtml || '> ⚠️ <strong>Métricas estables. Sigue subiendo contenido de alto impacto.</strong>',
            model: geminiRes.model
        });
    }

    return res.status(200).json({
        success: true,
        actions_html: `
            <div style='margin-bottom:8px;'>> ⚠️ <strong>CTR al ${ctr}%: Lánzate al 'Editor Canva PRO' y mete miniaturas con contrastes neón pesados.</strong></div>
            <div style='margin-bottom:8px;'>> ⚠️ <strong>Retención al ${retention}%: Usa el 'Editor de Video' y vuela los silencios a 0.5s para amarrar a la raza.</strong></div>
            <div style='margin-bottom:8px;'>> ⚠️ <strong>Impresiones: Saca ganchos bélicos en el 'Generador de Hooks' para reventar el algoritmo.</strong></div>
        `,
        model: 'fallback-advisor'
    });
};
