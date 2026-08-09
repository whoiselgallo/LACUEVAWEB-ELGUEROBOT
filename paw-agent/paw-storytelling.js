/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * PAW STORYTELLING - Módulo de formulario conversacional avanzado
 * ═════════════════════════════════════════════════════════════════════════════════
 * 
 * Maneja la generación de:
 * - Presentación del invitado
 * - Storytelling de apertura
 * - Guion de preguntas
 * - Escaleta completa
 * - Cue Cards
 * 
 * Totalmente conversacional y modular.
 */

import { pawAgentState } from './paw-core.js';
import { addPawMessage } from './paw-chat.js';

// ═════════════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ═════════════════════════════════════════════════════════════════════════════════

const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

export const storytellingCampos = [
  "nombre", "ocupacion", "frase", "barrio",
  "historia", "anecdota", "momento", "herida",
  "trayectoria", "incomodo", "vulnerabilidad",
  "pasiones", "logros"
];

const preguntasStory = {
  1: "Va perro. ¿A qué te dedicas actualmente?",
  2: "Dame una frase que te describa en una línea.",
  3: "¿De qué barrio o ciudad vienes?",
  4: "Cuéntame tu historia resumida (5–10 líneas).",
  5: "Dame una anécdota detonante que te represente.",
  6: "¿Cuál ha sido el momento más decisivo de tu vida?",
  7: "¿Qué herida emocional o golpe fuerte te marcó?",
  8: "Explícame tu trayectoria, ¿cómo llegaste hasta aquí?",
  9: "¿Qué temas te incomodan o prefieres evitar?",
  10: "¿Qué te hace sentir vulnerable?",
  11: "¿Qué temas te apasionan?",
  12: "¿Cuáles son tus logros clave?"
};

// ═════════════════════════════════════════════════════════════════════════════════
// HANDLER PRINCIPAL DEL FORMULARIO CONVERSACIONAL
// ═════════════════════════════════════════════════════════════════════════════════

export function manejarStorytelling(message) {
  // Inicio del formulario
  if (!pawAgentState.storyStep) {
    pawAgentState.storyStep = 1;
    pawAgentState.storyData = {};
    addPawMessage("🎬 A ver compa, vamos a armar tu storytelling PRO. Dime tu nombre completo.", "bot");
    return true;
  }

  const step = pawAgentState.storyStep;

  // ═════════════════════════════════════════════════════════════════════════════════
  // VALIDACIONES
  // ═════════════════════════════════════════════════════════════════════════════════

  // Nombre: debe tener al menos nombre y apellido
  if (step === 1 && (!message.includes(" ") || message.length < 5)) {
    addPawMessage("Dime tu nombre completo, perro. Nombre y apellido.", "bot");
    return true;
  }

  // Historia: mínimo de contenido
  if (step === 4 && message.length < 20) {
    addPawMessage("Dame más carnita, compa. Mínimo una historia coherente (5+ líneas).", "bot");
    return true;
  }

  // Anécdota: debe tener detalle
  if (step === 5 && message.length < 10) {
    addPawMessage("Esa anécdota está muy flaca, dame más detalle, che.", "bot");
    return true;
  }

  // ═════════════════════════════════════════════════════════════════════════════════
  // GUARDAR RESPUESTA
  // ═════════════════════════════════════════════════════════════════════════════════

  pawAgentState.storyData[storytellingCampos[step - 1]] = message;

  // ═════════════════════════════════════════════════════════════════════════════════
  // SIGUIENTE PREGUNTA
  // ═════════════════════════════════════════════════════════════════════════════════

  if (step < 13) {
    pawAgentState.storyStep++;
    const nextQuestion = preguntasStory[step];
    addPawMessage(nextQuestion, "bot");
    return true;
  }

  // ═════════════════════════════════════════════════════════════════════════════════
  // FINAL DEL FORMULARIO - GENERAR PAQUETE
  // ═════════════════════════════════════════════════════════════════════════════════

  addPawMessage("✨ Listo compa, ya tengo todos tus datos. Generando tu storytelling PRO…", "bot");

  generarPaqueteStorytelling(pawAgentState.storyData);

  // Reset del estado
  pawAgentState.storyStep = 0;
  pawAgentState.storyData = {};

  return true;
}

// ═════════════════════════════════════════════════════════════════════════════════
// MOTOR DE GENERACIÓN DEL PAQUETE COMPLETO
// ═════════════════════════════════════════════════════════════════════════════════

export async function generarPaqueteStorytelling(data) {
  // ───────────────────────────────────────────────────────────────────────────────
  // 1. PRESENTACIÓN DEL INVITADO
  // ───────────────────────────────────────────────────────────────────────────────

  const presentacion = `
${data.nombre} llega a La Cueva del Güero como ${data.ocupacion}.

"${data.frase}"

Originario de ${data.barrio}.

Historia resumida:
${data.historia}
  `.trim();

  // ───────────────────────────────────────────────────────────────────────────────
  // 2. STORYTELLING DE APERTURA
  // ───────────────────────────────────────────────────────────────────────────────

  const storytelling = `
Anécdota detonante:
${data.anecdota}

Momento decisivo:
${data.momento}

Herida emocional:
${data.herida}

Trayectoria:
${data.trayectoria}
  `.trim();

  // ───────────────────────────────────────────────────────────────────────────────
  // 3. GUION DE PREGUNTAS
  // ───────────────────────────────────────────────────────────────────────────────

  const guion = `
BLOQUES DE PREGUNTAS PARA ${data.nombre.toUpperCase()}

1️⃣ INFANCIA Y BARRIO
   - ¿Cómo era crecer en ${data.barrio}?
   - ¿Qué recuerdos de infancia te marcaron más?
   - ¿Cuáles eran los valores de tu familia?

2️⃣ HERIDA EMOCIONAL
   - Profundizando en: "${data.herida}"
   - ¿En qué momento te diste cuenta de esto?
   - ¿Cómo cambió tu perspectiva después?
   - ¿Qué aprendiste de eso?

3️⃣ MOMENTO DECISIVO
   - Hablemos de ese momento: "${data.momento}"
   - ¿Qué te hizo tomar esa decisión?
   - ¿Cuáles fueron las consecuencias?
   - ¿Si vueltas atrás, harías lo mismo?

4️⃣ TRAYECTORIA Y LOGROS
   - ${data.logros || "¿Cuáles son tus logros que debemos destacar?"}
   - ¿Cómo ha evolucionado tu carrera/proyecto?
   - ¿Cuál fue el punto de quiebre?

5️⃣ VULNERABILIDAD
   - ${data.vulnerabilidad || "¿En qué momentos te sientes más vulnerable?"}
   - ¿Hay algo que nadie conoce de ti?
   - ¿Cómo manejas tus miedos?

6️⃣ TEMAS INCÓMODOS / LÍMITES
   - ${data.incomodo || "Temas que prefieres no tocar o tratar con cuidado."}
   - ¿Hay algo que quieras que respetemos?
   - ¿Cuáles son tus límites?

7️⃣ PASIONES Y MOTIVACIONES
   - ${data.pasiones || "¿Qué temas te encanta hablar?"}
   - ¿Qué te mueve a levantarte cada día?
   - ¿Cuál es tu "por qué"?

8️⃣ CIERRE EMOCIONAL
   - ¿Qué mensaje le dejarías a quien está pasando por algo parecido a tu historia?
   - ¿Cuál es el aprendizaje principal que quieres que se lleven?
   - ¿Qué sigue en tu camino?
  `.trim();

  // ───────────────────────────────────────────────────────────────────────────────
  // 4. ESCALETA COMPLETA DEL EPISODIO
  // ───────────────────────────────────────────────────────────────────────────────

  const escaleta = `
🎬 ESCALETA DEL EPISODIO – ${data.nombre}
═════════════════════════════════════════════════════════════════

DURACIÓN TOTAL: 75 minutos

1️⃣ PRESENTACIÓN (0–5 minutos)
   ├─ El Güero presenta a ${data.nombre} como ${data.ocupacion}
   ├─ Frase clave: "${data.frase}"
   ├─ Contexto geográfico/cultural: ${data.barrio}
   └─ Tono: Amable, intrigante, que genere curiosidad

2️⃣ STORYTELLING DE APERTURA (5–20 minutos)
   ├─ Narración de la anécdota detonante
   ├─ Conexión emocional con el momento decisivo
   ├─ Introducción de la herida emocional (sin profundizar)
   └─ Gancho: ¿Por qué esto importa?

3️⃣ BLOQUES TEMÁTICOS (20–60 minutos)
   ├─ Infancia y barrio (10 min)
   ├─ Herida emocional (10 min)
   ├─ Momento decisivo (10 min)
   ├─ Logros y trayectoria (10 min)
   ├─ Vulnerabilidad (5 min)
   ├─ Pasiones (5 min)
   └─ Nota: Respetar temas incómodos

4️⃣ PREGUNTAS CLAVE Y EXPLORACIÓN (explorar según flujo)
   └─ Ver guion de preguntas generado

5️⃣ CIERRE EMOCIONAL (60–75 minutos)
   ├─ Mensaje final del invitado
   ├─ Resumen del aprendizaje
   ├─ Call to action o reflexión final
   └─ Despedida del Güero
  `.trim();

  // ───────────────────────────────────────────────────────────────────────────────
  // 5. CUE CARDS (referencias rápidas)
  // ───────────────────────────────────────────────────────────────────────────────

  const cueCards = `
📇 CUE CARDS – ${data.nombre}
═════════════════════════════════════════════════════════════════

DATOS RÁPIDOS
├─ Nombre: ${data.nombre}
├─ Ocupación: ${data.ocupacion}
├─ Barrio/Ciudad: ${data.barrio}
└─ Frase clave: "${data.frase}"

FRASES DE IMPACTO
├─ "${data.frase}"
├─ Anécdota detonante: ${data.anecdota.substring(0, 100)}...
└─ Momento decisivo: ${data.momento.substring(0, 100)}...

TEMAS SENSIBLES ⚠️
├─ Temas incómodos: ${data.incomodo || "A definir"}
├─ Vulnerabilidad: ${data.vulnerabilidad || "Explorar con cuidado"}
└─ Límites personales: Respetar durante la grabación

MOMENTOS FUERTES
├─ Herida emocional: ${data.herida.substring(0, 80)}...
├─ Logros: ${data.logros || "Agregar durante la charla"}
└─ Pasiones: ${data.pasiones || "A profundizar"}

TIEMPO RECOMENDADO POR BLOQUE
├─ Presentación: 5 min
├─ Storytelling: 15 min
├─ Bloques temáticos: 40 min
├─ Cierre: 15 min
└─ TOTAL: 75 min
  `.trim();

  // ═════════════════════════════════════════════════════════════════════════════════
  // MOSTRAR RESULTADOS EN EL PAW AGENT
  // ═════════════════════════════════════════════════════════════════════════════════

  addPawMessage("🎙 PRESENTACIÓN DEL INVITADO\n" + presentacion, "bot");
  addPawMessage("🔥 STORYTELLING DE APERTURA\n" + storytelling, "bot");
  addPawMessage("🧩 GUION DE PREGUNTAS\n" + guion, "bot");
  addPawMessage("🎬 ESCALETA DEL EPISODIO\n" + escaleta, "bot");
  addPawMessage("📇 CUE CARDS\n" + cueCards, "bot");

  // ═════════════════════════════════════════════════════════════════════════════════
  // GUARDAR EN BASE DE DATOS
  // ═════════════════════════════════════════════════════════════════════════════════

  try {
    const response = await fetch(API_KNOWLEDGE_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        nombre: data.nombre,
        tipo: "storytelling",
        storytelling: {
          presentacion,
          storytelling,
          guion,
          escaleta,
          cueCards
        }
      })
    });

    if (response.ok) {
      addPawMessage("✅ Storytelling guardado en la cueva.", "bot");
    }
  } catch (err) {
    console.warn("⚠️ No se pudo guardar storytelling en BD:", err);
  }
}

// ═════════════════════════════════════════════════════════════════════════════════
// GENERAR STORYTELLING DESDE BD (para el modal de invitados)
// ═════════════════════════════════════════════════════════════════════════════════

export async function generarStorytellingDesdeBD(nombre) {
  try {
    const response = await fetch(`/api/api-invitados-get.php?nombre=${encodeURIComponent(nombre)}`);
    const data = await response.json();

    if (!data || !data[0]) {
      addPawMessage("No encontré datos del invitado para generar storytelling.", "bot");
      return;
    }

    const inv = data[0];

    const payload = {
      nombre: inv.nombre || nombre,
      ocupacion: inv.ficha?.ocupacion || inv.ocupacion || "",
      frase: inv.ficha?.frase || inv.frase || "",
      barrio: inv.ficha?.barrio || inv.barrio || "",
      historia: inv.ficha?.historia || inv.historia || "",
      anecdota: inv.ficha?.anecdota || inv.anecdota || "",
      momento: inv.ficha?.momento || inv.momento || "",
      herida: inv.ficha?.herida || inv.herida || "",
      trayectoria: inv.ficha?.trayectoria || inv.trayectoria || "",
      incomodo: inv.ficha?.incomodo || inv.incomodo || "",
      vulnerabilidad: inv.ficha?.vulnerabilidad || inv.vulnerabilidad || "",
      pasiones: inv.ficha?.pasiones || inv.pasiones || "",
      logros: inv.ficha?.logros || inv.logros || ""
    };

    addPawMessage(`🎬 Procesando storytelling PRO de ${nombre}…`, "bot");
    await generarPaqueteStorytelling(payload);

  } catch (err) {
    console.error("Error al generar storytelling desde BD:", err);
    addPawMessage("❌ Error al generar storytelling desde la BD.", "bot");
  }
}
