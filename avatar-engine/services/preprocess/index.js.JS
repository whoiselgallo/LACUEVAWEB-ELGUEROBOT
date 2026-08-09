import { cropFace } from './cropFace.js';
import { removeBackground } from './removeBackground.js';
import { normalizeLighting } from './normalizeLighting.js';

export const preprocessImage = async (imageBuffer, faceData) => {
    let processed = imageBuffer;

    // 1. Recorte inteligente del rostro
    processed = await cropFace(processed, faceData);

    // 2. Limpieza de fondo
    processed = await removeBackground(processed);

    // 3. Normalización de iluminación
    processed = await normalizeLighting(processed);

    return processed;
};
