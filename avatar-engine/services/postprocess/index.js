import { applyNeon } from './applyNeon.js';
import { applyContours } from './applyContours.js';
import { exportPNG } from './exportPNG.js';

export const postprocessAvatar = async (imageBuffer) => {
    let processed = imageBuffer;

    // 1. Aplicar contornos estilo comic
    processed = await applyContours(processed);

    // 2. Aplicar iluminación neón cian/magenta
    processed = await applyNeon(processed);

    // 3. Exportar como PNG transparente
    processed = await exportPNG(processed);

    return processed;
};
