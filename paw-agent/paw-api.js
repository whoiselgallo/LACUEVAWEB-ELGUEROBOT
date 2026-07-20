// paw-api.js
import { PAW_AGENT_API, pawAgentState } from './paw-core.js';
import { addPawMessage, removePawLoadingMessage } from './paw-chat.js';

/* ════════════════════════════════════════════════════════════════════════════
   CONFIGURACIÓN DE ENDPOINTS
   ════════════════════════════════════════════════════════════════════════════ */

// Endpoint principal (Hostinger/tu-servidor)
const MAIN_API = PAW_AGENT_API; // /api/api-el-guero-bot.php

const TIMEOUT_MS = 10000;   // 10 segundos
const RETRIES = 2;

/* ════════════════════════════════════════════════════════════════════════════
   UTILIDADES
   ════════════════════════════════════════════════════════════════════════════ */

function timeoutPromise(ms) {
    return new Promise((_, reject) =>
        setTimeout(() => reject(new Error("timeout")), ms)
    );
}

function sanitize(text) {
    return text.replace(/<[^>]+>/g, "").trim();
}

async function fetchWithTimeout(url, options) {
    return Promise.race([
        fetch(url, options),
        timeoutPromise(TIMEOUT_MS)
    ]);
}

/* ════════════════════════════════════════════════════════════════════════════
   REQUEST AL BACKEND
   ════════════════════════════════════════════════════════════════════════════ */

export async function enviarAlBackend(message) {
    addPawMessage("El güero está escribiendo...", "bot", true);

    const payload = {
        query: sanitize(message),
        visitType: pawAgentState.visitType,
        user: "paw-agent"
    };

    let intentos = 0;

    while (intentos < RETRIES) {
        try {
            console.log(`🐾 [PAW] Intento ${intentos + 1} → ${MAIN_API}`);

            const response = await fetchWithTimeout(MAIN_API, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${data.error || 'Error desconocido'}`);
            }

            removePawLoadingMessage();
            addPawMessage(data.answer || "El güero no supo qué responder.", "bot");
            return;

        } catch (err) {
            console.warn(`⚠ Intento ${intentos + 1} falló:`, err.message);
            intentos++;

            if (intentos < RETRIES) {
                await new Promise(resolve => setTimeout(resolve, 1000)); // Espera 1s antes de reintentar
            }
        }
    }

    // Si llegamos aquí, todos los reintentos fallaron
    console.error("❌ Todos los reintentos fallaron");
    removePawLoadingMessage();
    addPawMessage(
        "El güero anda dormido o sin señal. Intenta en un rato, compa. 🐾",
        "bot"
    );
}
