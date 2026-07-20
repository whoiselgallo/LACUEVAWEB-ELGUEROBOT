export const buildPrompt = ({ mode, traits, action }) => {
    const baseStyle = `
        Estilo comic-realista urbano, contornos marcados,
        iluminación neón cian y magenta, sombras suaves,
        ojos con brillo, proporciones ligeramente exageradas,
        fondo transparente, alta fidelidad facial,
        estética oficial de "La Cueva del Güero".
    `;

    if (mode === 'base') {
        return `
            Genera un avatar del personaje con los siguientes rasgos:
            ${JSON.stringify(traits)}.
            ${baseStyle}
            El avatar debe ser frontal, neutral, sin pose dinámica.
        `;
    }

    if (mode === 'action') {
        return `
            Genera una nueva versión del avatar manteniendo los rasgos:
            ${JSON.stringify(traits)}.
            Actividad solicitada: ${action}.
            ${baseStyle}
            Mantén coherencia visual con el avatar base.
        `;
    }

    return '';
};
