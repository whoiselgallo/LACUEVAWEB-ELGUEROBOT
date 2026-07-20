// paw-modes.js
import { pawAgentState } from './paw-core.js';
import { addPawMessage } from './paw-chat.js';
import { manejarStorytelling, generarStorytellingDesdeBD } from './paw-storytelling.js';

export function manejarModoEquipo(message) {
    const lower = message.toLowerCase();

    // ═══════════════════════════════════════════════════════════════════════════
    // STORYTELLING CONVERSACIONAL
    // ═══════════════════════════════════════════════════════════════════════════
    if (lower.includes("story") || lower.includes("storytelling") || lower.includes("presentación")) {
    pawAgentState.visitType = "story";
        addPawMessage("🎬 Va perro, vamos a armar tu storytelling PRO. Dime tu nombre completo.", "bot");
        return true;
    }

    if (lower.includes("escaleta"))
        return addPawMessage("Va, mándame los datos del invitado.", "bot"), true;

    if (lower.includes("guion"))
        return addPawMessage("Dame el tema del episodio y te armo el guion.", "bot"), true;

    if (lower.includes("cue"))
        return addPawMessage("Dime los puntos clave y te armo las cue cards.", "bot"), true;

    if (lower.includes("blog"))
        return addPawMessage("Pega el texto o PDF y lo convierto en artículo.", "bot"), true;

    return false;
}

export function manejarModoSeguidor(message) {
    const lower = message.toLowerCase();

    if (lower.includes("hola") || lower.includes("qué onda"))
        return addPawMessage("Qué rollo, compa. ¿Ya viste el último episodio?", "bot"), true;

    if (lower.includes("dónde") || lower.includes("como veo"))
        return addPawMessage("Cáele al canal 👉 https://www.youtube.com/@lacuevadelguero", "bot"), true;

    if (lower.includes("sígueme") || lower.includes("redes"))
        return addPawMessage("Síguenos en Insta y TikTok 👉 @lacuevadelguero", "bot"), true;

    return false;
}
