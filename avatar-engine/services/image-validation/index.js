import { detectFace } from './detectFace.js';
import { detectMinor } from './detectMinor.js';
import { detectCelebrity } from './detectCelebrity.js';
import { detectContentPolicy } from './detectContentPolicy.js';
import { ApiError } from '../../api/core/ApiError.js';

export const validateImage = async (imageBuffer) => {
    // 1. Validar que haya rostro humano
    const faceData = await detectFace(imageBuffer);
    if (!faceData || !faceData.found) {
        throw new ApiError('No se detectó un rostro humano válido en la imagen.', 400);
    }

    // 2. Validar que no sea menor
    const isMinor = await detectMinor(imageBuffer);
    if (isMinor) {
        throw new ApiError('No se permite generar avatares de menores de edad.', 400);
    }

    // 3. Validar que no sea celebridad
    const isCelebrity = await detectCelebrity(imageBuffer);
    if (isCelebrity) {
        throw new ApiError('No se permite generar avatares de celebridades.', 400);
    }

    // 4. Validar contenido prohibido
    const policyViolation = await detectContentPolicy(imageBuffer);
    if (policyViolation) {
        throw new ApiError('La imagen contiene contenido no permitido.', 400);
    }

    return {
        success: true,
        faceData
    };
};
