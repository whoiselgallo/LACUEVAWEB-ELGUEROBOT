// paw-form.js
import { pawAgentState } from './paw-core.js';
import { addPawMessage } from './paw-chat.js';

const preguntas = {
    1: "Chingón. Ahora dime tu ocupación, ¿a qué te dedicas?",
    2: "Va perro. ¿Cuál es tu signo zodiacal?",
    3: "Fecha de nacimiento (DD/MM/AAAA):",
    4: "¿De qué barrio o ciudad vienes, compa?",
    5: "Cuéntame tu trayectoria, tu historia. ¿Qué te trajo hasta aquí?",
    6: "Ahora dime una herida o momento difícil que te marcó.",
    7: "¿Qué cosas te gustan o te interesan más?",
};

const campos = [
    "nombre",
    "ocupacion",
    "signo",
    "fecha",
    "barrio",
    "trayectoria",
    "herida",
    "gustos"
];

export function manejarFormularioInvitado(message) {
    if (!pawAgentState.formStep) {
        pawAgentState.formStep = 1;
        pawAgentState.formData = {};
        addPawMessage("A ver compa, vamos a armar tu ficha. Dime tu nombre completo.", "bot");
        return true;
    }

    const step = pawAgentState.formStep;

    // VALIDACIONES
    if (step === 1 && (!message.includes(" ") || message.length < 5))
        return addPawMessage("Dime tu nombre completo, compa.", "bot"), true;

    if (step === 2 && (message.length < 3 || /^\d+$/.test(message)))
        return addPawMessage("Esa no me la creo, perro. Dime bien a qué te dedicas.", "bot"), true;

    if (step === 3) {
        const signos = ["aries","tauro","géminis","geminis","cáncer","cancer","leo","virgo","libra","escorpio","sagitario","capricornio","acuario","piscis"];
        if (!signos.includes(message.toLowerCase()))
            return addPawMessage("Ese signo no existe, compa.", "bot"), true;
    }

    if (step === 4) {
        const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!regex.test(message))
            return addPawMessage("Pon la fecha así: DD/MM/AAAA", "bot"), true;
    }

    if (step === 5 && message.length < 3)
        return addPawMessage("Dime un barrio o ciudad real, compa.", "bot"), true;

    if (step === 6 && message.length < 10)
        return addPawMessage("Cuéntame un poquito más, perro.", "bot"), true;

    if (step === 7 && message.length < 5)
        return addPawMessage("Dime algo que sí te guste, compa.", "bot"), true;

    // GUARDAR
    pawAgentState.formData[campos[step - 1]] = message;

    // SIGUIENTE
    if (step < 8) {
        pawAgentState.formStep++;
        addPawMessage(preguntas[step], "bot");
        return true;
    }

    // FINAL
    const resumen = JSON.stringify(pawAgentState.formData, null, 2);
    addPawMessage("Listo compa, ya armé tu ficha. Aquí está:", "bot");
    addPawMessage(`<pre>${resumen}</pre>`, "bot");

    fetch('/api/api-guero-knowledge.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: `Ficha de invitado: ${resumen}` })
    });

    pawAgentState.formStep = 0;
    return true;
}
