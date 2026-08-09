import { buildPrompt } from './promptBuilder.js';

export const generateBaseAvatar = async (imageBuffer, traits) => {
    const prompt = buildPrompt({
        mode: 'base',
        traits
    });

    // TODO: integrar con modelo IA (DALL·E, Flux, SDXL, etc.)
    console.log('🎨 Generando avatar base con prompt:', prompt);

    // Placeholder
    return {
        avatar: imageBuffer, // reemplazar con imagen generada
        prompt
    };
};

// Integracion de runpod modelo python con avatar engine node.js
import axios from 'axios';
import { buildPrompt } from './promptBuilder.js';

const RUNPOD_URL = process.env.RUNPOD_URL;

export const generateBaseAvatar = async (imageBuffer, traits) => {
    const prompt = buildPrompt({
        mode: 'base',
        traits
    });

    const response = await axios.post(`${RUNPOD_URL}/generate`, {
        prompt,
        steps: 30,
        guidance: 7.5
    });

    const imageBase64 = response.data.image;

    return {
        avatar: Buffer.from(imageBase64, 'base64'),
        prompt
    };
};
